<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Login extends MY_Controller
    {
        protected $logl = array();
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
        }

        /**
         * Modèles nécessaires aux actions d'authentification (pas à la page login GET).
         */
        protected function _load_auth_models()
        {
            $this->load->model('Compte_user_model', 'm_compte_user');
            $this->load->model('Entreprises_model', 'm_entreprises');
            $this->load->model('Personnels_model', 'm_personnels');
            $this->load->model('Role_attribution_model', 'm_roleattribution');
            $this->load->model('Utilisateur_model', 'm_utilisateur');
        }
        
        public function in($key = NULL, array $in = NULL)
        {
            if ($key === NULL) {
                $this->logl['data'] = array(null);
                $this->load->view('_in/in', $this->logl['data']);
            } else {
                $this->_load_auth_models();
                $this->logl['data'] = $this->m_personnels->get($key);
                $this->load->view('_in/in', $this->logl['data']);
            }
        }

        public function ins($key = NULL, array $in = NULL)
        {
            if ($key === NULL) {
                $viewData = array(
                    'login_error' => (bool) $this->session->flashdata('login_error'),
                );
                $this->load->view('_in/ins', $viewData);
            } else {
                $this->_load_auth_models();
                $this->logl['data'] = $this->m_personnels->get($key);
                $this->load->view('_in/ins', $this->logl['data']);
            }
        }
    

        public function lin_($pk = NULL)
        {
            if ($pk != NULL) {
                
                
            } else {
                $this->_load_auth_models();
                $ro = strpos($this->input->post('fonction'), '/');
                $r = substr($this->input->post('fonction'), 0, $ro);
                $u = substr($this->input->post('fonction'), $ro + 1, strlen($this->input->post('fonction')));
                
                 $rw = $this->m_compte_user->pick_attribution_at_login($u, $r);
            
                if (!empty($rw)) {
                    $this->logl['company'] = $this->m_entreprises->get_key($rw->cle_comp);
                    // Toujours activer une attribution : get() exige activeattrib=1 (sinon page blanche).
                    $this->m_roleattribution->activate_exclusive($u, $r, $rw->roleattribut);
                    redirect('home/' . $this->logl['company']->ekey . '/' . $u. '/' .$r);
                }

                $this->session->set_flashdata('login_error', 1);
                redirect('login/ins');
            }
                
        }

        public function lin_s($pk = NULL)
        {
            if ($pk != NULL) {
                return;
            }

            $this->_load_auth_models();
            $username = trim((string) $this->input->post('username'));
            $password = (string) $this->input->post('upassword');

            if ($username === '' || $password === '') {
                $this->session->set_flashdata('login_error', 1);
                redirect('login/ins');
                return;
            }

            $candidates = $this->m_compte_user->find_all_by_username($username);

            if ($candidates === false) {
                log_message('error', 'Login/lin_s: échec requête compte_user (droits MySQL ?)');
                show_error('Service temporairement indisponible. Réessayez dans quelques minutes.', 503);
                return;
            }

            $detector = null;
            foreach ($candidates as $row) {
                if (password_check($password, $row->upassword)
                    || password_check($password, $row->confirm_password)) {
                    $detector = $row;
                    break;
                }
            }

            if (!empty($detector)) {
                if ((int) $detector->activer === 1) {
                    $this->session->set_flashdata('login_error', 1);
                    $this->session->set_flashdata('login_error_msg', 'Ce compte est désactivé. Contactez l\'administrateur.');
                    redirect('login/ins');
                    return;
                }

                // Migration transparente : ré-hachage bcrypt du mot de passe
                // encore stocké en SHA-1 (ou dont le coût bcrypt a changé).
                if (password_should_rehash($detector->upassword)) {
                    $newhash = password_make($password);
                    $this->m_compte_user->update($detector->cpuser_id, array(
                        'upassword'        => $newhash,
                        'confirm_password' => $newhash,
                    ));
                }

                $this->logl['company'] = $this->m_entreprises->get_key($detector->cle_comp);

                if (empty($this->logl['company'])) {
                    $this->session->set_flashdata('login_error', 1);
                    redirect('login/ins');
                    return;
                }

                $act_acc = array(
                    'is_conect' => 1,
                    'date_conect' => mdate('%Y-%m-%d %H:%i:%s', now('UTC')),
                );
                $this->m_compte_user->update($detector->cpuser_id, $act_acc);
                compte_arret_track_activity($detector->cpuser_id);
                redirect('welcome/' . $this->logl['company']->ekey . '/' . $detector->cpuser_id);
                return;
            }

            $this->session->set_flashdata('login_error', 1);
            redirect('login/ins');
        }
        
        public function lin($key = NULL, array $in = NULL)
        {
            if ($key === NULL) {
                $this->logl['data'] = array(null);
                $this->load->view('_in/ins', $this->logl['data']);
            } else {
                $this->_load_auth_models();
                $this->logl['data'] = $this->m_utilisateur->get_user($key);
                $this->load->view('_in/ins', $this->logl['data']);
            }
        }
        
        public function line($key = NULL, array $in = NULL)
        {
            if ($key === NULL) {
                $this->logl['data'] = array(null);
                $this->load->view('_in/ine', $this->logl['data']);
            } else {
                $this->_load_auth_models();
                $this->logl['data'] = $this->m_utilisateur->get_user($key);
                $this->load->view('_in/ine', $this->logl['data']);
            }
        }
        
        public function lout($o = NULL, $a = NULL)
        {
            $this->_load_auth_models();

            $agent = $this->session->userdata('agent');

            if (empty($agent) || $o !== $this->session->session_id || $a !== $agent->cpuser_id) {
                redirect('login/ins/');
                return;
            }

            $out_ac = array(
                'is_conect' => 0,
                'date_deconect' => mdate("%Y-%m-%d %H:%i:%s", now('UTC')),
            );

            $this->m_compte_user->update($agent->cpuser_id, $out_ac);

            $c = $agent->cpuser_id;
            $r = $agent->userole;

            $at = $this->db->query("SELECT * FROM attributions_role a
                 JOIN  user_login u ON a.idgestcompte = u.uid_login 
                 WHERE u.uid_usercpte = ?
                 AND a.userole = ?", array($c, $r))->result();

            foreach ($at as $key => $value) {
                $array_acc = array(
                    'activeattrib' => 0,
                );
                
                $this->m_roleattribution->update($value->roleattribut, $array_acc);
            }
            unset($this->session);
            
            redirect('login/ins/');
        }
    }
    
    /** End of file: Login.php **/
    /** File location: application/controllers/Login.php **/