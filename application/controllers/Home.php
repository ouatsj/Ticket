<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Home extends MY_Controller
    {
        protected $property = array(
            'title' => 'Accueil',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        protected $charger = array();
        
        public function __construct()
        {


            parent::__construct();
            $this->load->helper('scripts');
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = mdate("%d/%m/%Y", now('UTC'));
        }
        
        /*public function go($key, $uid, $r)
        {
            $this->charger['company'] = $this->m_entreprises->get_key($key);
            // The User logged in
            $this->charger['agent'] = $this->m_compte_user->get($uid, $r);
            
            // The session var is charged
            $this->session->set_userdata($this->charger);
            
            // and we go right in the application main page.
            return $this->main1($this->charger);
        }*/

        public function go($key, $uid, $r)
        {
            $uid = (int) $uid;
            $r = (int) $r;

            // Déjà connecté (liens Accueil / anciens home/ekey/cp/role) :
            // ne pas exiger login_pending → sinon page de connexion alors que la session est valide.
            if (!auth_session_validate_login_pending($uid, $key)
                && $this->session->userdata('agent')
                && $this->session->userdata('company')
                && (int) $this->session->agent->cpuser_id === $uid
                && (string) $this->session->company->ekey === (string) $key
            ) {
                if ((int) $this->session->agent->userole !== $r && $r > 0) {
                    // Même compte, autre profil : bascule via attribution si disponible.
                    $rw = $this->m_compte_user->pick_attribution_at_login($uid, $r);
                    if (!empty($rw)) {
                        $this->load->model('Role_attribution_model', 'm_roleattribution');
                        $this->m_roleattribution->activate_exclusive($uid, $r, $rw->roleattribut);
                        $this->m_roleattribution->clear_stale_activeattrib();
                        $agent = $this->m_compte_user->get($uid, $r);
                        if (!empty($agent)) {
                            $this->session->set_userdata('agent', $agent);
                        }
                    }
                }
                return $this->main1();
            }

            if (!auth_session_validate_login_pending($uid, $key)) {
                auth_session_login_transition_denied();
            }

            $this->charger['company'] = $this->m_entreprises->get_key($key);
            if (empty($this->charger['company'])) {
                auth_session_login_transition_denied();
            }

            if (!auth_session_validate_login_pending($uid, $this->charger['company']->ekey)) {
                auth_session_login_transition_denied();
            }

            $rw = $this->m_compte_user->pick_attribution_at_login($uid, $r);
            if (!empty($rw)) {
                $this->load->model('Role_attribution_model', 'm_roleattribution');
                $this->m_roleattribution->activate_exclusive($uid, $r, $rw->roleattribut);
                $this->m_roleattribution->clear_stale_activeattrib();
            }

            $this->m_compte_user->update($uid, array(
                'is_conect' => 1,
                'date_conect' => mdate('%Y-%m-%d %H:%i:%s', now('UTC')),
            ));

            $this->charger['agent'] = $this->m_compte_user->get($uid, $r);
            if (empty($this->charger['agent'])) {
                auth_session_login_transition_denied('Impossible d\'ouvrir la session. Reconnectez-vous.');
            }

            auth_session_finalize($uid, $this->charger['agent'], $this->charger['company']);
            auth_session_consume_login_pending($uid, $this->charger['company']->ekey);
            compte_arret_track_activity((int) $uid);

            $agentapp = $this->m_appdossier->gets($r, $uid);

            if ($agentapp != NULL) {
                if ($agentapp->iddoss == '1') {
                    return $this->main1($this->charger);
                }

                $this->session->set_flashdata('login_error', 1);
                auth_session_force_logout(true);
                redirect('login/ins');
                return;
            }

            $this->session->set_flashdata('login_error', 1);
            auth_session_force_logout(true);
            redirect('login/ins');
        }
        
        
        public function main1(array $argv = NULL)
        {
            $this->property['pagetitle'] = 'Accueil';
            $this->property['UPDATE_SUCCESS'] = FALSE;
            $this->property['INSERT_SUCCESS'] = FALSE;

            $cpuser_id = (int) $this->session->agent->cpuser_id;
            $role = (string) $this->session->agent->userole;
            $ekey = (string) $this->session->company->ekey;
            $this->property['agent_userole'] = $role;
            $this->property['company_ekey'] = $ekey;
            
            $this->property['gares'] = $this->m_compte_user->attrib($cpuser_id, $role);
            session_release_lock();

            $this->property['villes'] = $this->m_villes->get();
            $this->property['compagnies'] = $this->m_compagnies->get();

            if (in_array($role, array('1', '2', '4', '18'), TRUE)) {
                $gare_ids = array();
                foreach ($this->property['gares'] as $g) {
                    $gare_ids[] = $g->idengare;
                }
                $this->property['soldes'] = $this->m_compte_user->soldes_accueil(
                    $ekey,
                    $cpuser_id,
                    $role,
                    $gare_ids
                );
            } else {
                $this->property['soldes'] = array();
            }

            $this->property = array_merge($this->property, scripts_bundle_property('accueil'));
            $this->layout->view('index1', $this->property);
        }
    }
    
    /** End of file: Home.php **/
    /** File location: application/controllers/Home.php **/