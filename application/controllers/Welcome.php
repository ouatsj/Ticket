<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Welcome extends MY_Controller
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
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        
        public function go($key, $m)
        {
            $m = (int) $m;
            if (!auth_session_validate_login_pending($m, $key)) {
                auth_session_login_transition_denied();
            }

            $this->property['agentroles'] = $this->m_compte_user->roleatt($m);
            if (empty($this->property['agentroles'])) {
                auth_session_login_transition_denied('Aucun profil actif pour ce compte.');
            }

            if (count($this->property['agentroles']) === 1) {
                $role = (int) $this->property['agentroles'][0]->id_rols;
                redirect('welcome/pick_gare/' . $key . '/' . $m . '/' . $role);
                return;
            }

            $this->load->view('_in/index2', $this->property);
        }

        /**
         * Choix gare explicite (multi-gares) avant ouverture session.
         */
        public function pick_gare($key, $uid, $role)
        {
            $uid = (int) $uid;
            $role = (int) $role;

            if (!auth_session_validate_login_pending($uid, $key)) {
                auth_session_login_transition_denied();
            }

            $gares = $this->m_compte_user->lookedfor1($uid, $role);
            if (empty($gares)) {
                auth_session_login_transition_denied('Aucune gare pour ce profil.');
            }

            if (count($gares) === 1) {
                redirect('login/pick_gare_go/' . rawurlencode($key) . '/' . $uid . '/' . $role . '/' . rawurlencode($gares[0]->guser));
                return;
            }

            $this->property['gares'] = $gares;
            $this->property['ekey'] = $key;
            $this->property['cpuser_id'] = $uid;
            $this->property['userole'] = $role;
            if (!empty($gares[0]->type_rols)) {
                $this->property['type_rols'] = $gares[0]->type_rols;
            }

            $this->load->view('_in/pick_gare', $this->property);
        }
        
    }
    
    /** End of file: Welcome.php **/
    /** File location: application/controllers/Welcome.php **/
