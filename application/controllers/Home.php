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
            $agent = $this->session->userdata('agent');
            $company = $this->session->userdata('company');

            // Déjà connecté : ne pas refaire activate_exclusive / recharger toute la session.
            if ($agent && $company
                && (int) $agent->cpuser_id === $uid
                && (int) $agent->userole === $r
                && (string) $company->ekey === (string) $key) {
                return $this->main1();
            }

              $this->charger['company'] = $this->m_entreprises->get_key($key);
            $rw = $this->m_compte_user->pick_attribution_at_login($uid, $r);
            if (!empty($rw)) {
                $this->load->model('Role_attribution_model', 'm_roleattribution');
                $this->m_roleattribution->activate_exclusive($uid, $r, $rw->roleattribut);
                $this->m_roleattribution->clear_stale_activeattrib();
            }
            // The User logged in
            $this->charger['agent'] = $this->m_compte_user->get($uid, $r);
            if (empty($this->charger['agent'])) {
                $this->session->set_flashdata('login_error', 1);
                redirect('login/ins');
                return;
            }
            // The session var is charged
            $this->session->set_userdata($this->charger);
            compte_arret_track_activity((int) $uid);
            
            $agentapp = $this->m_appdossier->gets($r, $uid);
            
            if($agentapp != NULL)
            {
                if($agentapp->iddoss == '1')
                {
                    // and we go right in the application main page.
                    return $this->main1($this->charger);
                }
                else
                {
                    $this->session->set_flashdata('login_error', 1);
                    redirect('login/ins');
                    return;
                }
            }
            else
            {
                $this->session->set_flashdata('login_error', 1);
                redirect('login/ins');
                return;
            }
           
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