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

            $all_gares = $this->m_compte_user->attrib($cpuser_id, $role);
            $accueil = auth_session_filter_accueil_gares($cpuser_id, $role, $all_gares);
            $this->property['gares'] = $accueil['gares'];
            $this->property['accueil_gare_filtree'] = !empty($accueil['filtered']);
            $this->property['accueil_active_garenom'] = isset($accueil['active_garenom'])
                ? $accueil['active_garenom'] : '';
            $this->property['accueil_changer_gare_url'] = isset($accueil['changer_gare_url'])
                ? $accueil['changer_gare_url'] : '';
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

        /**
         * Changement de gare après connexion (accueil filtré, rôles non-admin).
         */
        public function switch_gare()
        {
            if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
                redirect('login/ins');
                return;
            }

            $cpuser_id = (int) $this->session->agent->cpuser_id;
            $role = (int) $this->session->agent->userole;
            $ekey = (string) $this->session->company->ekey;

            if (auth_session_accueil_show_all_gares($role)) {
                return $this->main1();
            }

            $gares = $this->m_compte_user->lookedfor1($cpuser_id, $role);
            if (count($gares) <= 1) {
                return $this->main1();
            }

            $viewData = array(
                'gares' => $gares,
                'ekey' => $ekey,
                'cpuser_id' => $cpuser_id,
                'userole' => $role,
                'form_action' => 'Home/apply_gare',
                'pick_gare_title' => 'Choisissez une autre gare',
            );
            if (!empty($gares[0]->type_rols)) {
                $viewData['type_rols'] = $gares[0]->type_rols;
            }

            $this->load->view('_in/pick_gare', $viewData);
        }

        /**
         * Applique le changement de gare (session connectée).
         */
        public function apply_gare($pk = null)
        {
            if ($pk !== null) {
                return;
            }

            if (!$this->session->userdata('agent') || !$this->session->userdata('company')) {
                redirect('login/ins');
                return;
            }

            $gare_id = trim((string) $this->input->post('gare_id'));
            $cpuser_id = (int) $this->session->agent->cpuser_id;
            $role = (int) $this->session->agent->userole;

            if ($gare_id === '') {
                redirect('Home/switch_gare');
                return;
            }

            $rw = $this->m_compte_user->pick_attribution_on_gare($cpuser_id, $role, $gare_id);
            if (empty($rw)) {
                redirect('Home/switch_gare');
                return;
            }

            $this->load->model('Role_attribution_model', 'm_roleattribution');
            $this->m_roleattribution->activate_exclusive($cpuser_id, $role, $rw->roleattribut);
            $this->m_roleattribution->clear_stale_activeattrib();

            $agent = $this->m_compte_user->get_on_gare($cpuser_id, $role, $gare_id);
            if ($agent) {
                $this->session->set_userdata('agent', $agent);
            }

            return $this->main1();
        }
    }
    
    /** End of file: Home.php **/
    /** File location: application/controllers/Home.php **/