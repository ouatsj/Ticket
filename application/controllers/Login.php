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

        protected function _login_fail($message = null)
        {
            if ($message !== null && $message !== '') {
                $this->session->set_flashdata('login_error_msg', $message);
            }
            $this->session->set_flashdata('login_error', 1);
            redirect('login/ins');
        }

        /**
         * Après mot de passe OK : jeton pending + profil unique ou écran Welcome.
         */
        protected function _after_password_ok($detector, $company)
        {
            auth_session_issue_login_pending((int) $detector->cpuser_id, (string) $company->ekey);
            $this->m_roleattribution->clear_stale_activeattrib();
            compte_arret_track_activity((int) $detector->cpuser_id);

            $roles = $this->m_compte_user->roleatt((int) $detector->cpuser_id);
            if (empty($roles)) {
                auth_session_login_transition_denied('Aucun profil actif pour ce compte.');
            }

            if (count($roles) === 1) {
                $this->_proceed_with_role($company, (int) $detector->cpuser_id, (int) $roles[0]->id_rols);
                return;
            }

            redirect('welcome/' . $company->ekey . '/' . (int) $detector->cpuser_id);
        }

        /**
         * Active une attribution (session) puis ouvre l'accueil.
         * Le choix de gare métier se fait sur l'accueil (VOIR GARES) — pas d'écran pick_gare au login.
         *
         * @param object $company
         * @param int $cpuser_id
         * @param int $role_id
         * @param string|null $gare_id Optionnel (compat liens anciens)
         */
        protected function _proceed_with_role($company, $cpuser_id, $role_id, $gare_id = null)
        {
            if (!auth_session_validate_login_pending($cpuser_id, $company->ekey)) {
                auth_session_login_transition_denied();
            }

            if ($gare_id !== null && $gare_id !== '') {
                $rw = $this->m_compte_user->pick_attribution_on_gare($cpuser_id, $role_id, $gare_id);
            } else {
                $rw = $this->m_compte_user->pick_attribution_at_login($cpuser_id, $role_id);
            }

            if (empty($rw)) {
                auth_session_login_transition_denied('Profil ou gare invalide.');
            }

            // is_conect=1 avant activate : sinon clear_stale_activeattrib() (is_conect=0)
            // efface immédiatement le activeattrib fraîchement posé.
            $this->m_compte_user->update($cpuser_id, array(
                'is_conect' => 1,
                'date_conect' => mdate('%Y-%m-%d %H:%i:%s', now('UTC')),
            ));

            if (!$this->m_roleattribution->activate_exclusive($cpuser_id, $role_id, $rw->roleattribut)) {
                auth_session_login_transition_denied(
                    'Impossible d\'activer le profil sur une gare. Contactez l\'administrateur.'
                );
            }
            redirect('home/' . $company->ekey . '/' . $cpuser_id . '/' . $role_id);
        }

        /**
         * Entrée session pour un rôle (après mot de passe / Welcome) — sans écran choix gare.
         */
        public function enter_role($ekey = null, $uid = null, $role = null)
        {
            $this->_load_auth_models();
            $uid = (int) $uid;
            $role = (int) $role;

            if ($uid <= 0 || $role <= 0) {
                auth_session_login_transition_denied('Profil invalide.');
            }

            $company = $this->m_entreprises->get_key($ekey);
            if (empty($company)) {
                auth_session_login_transition_denied();
            }

            $this->_proceed_with_role($company, $uid, $role);
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
                    'login_error_msg' => $this->session->flashdata('login_error_msg'),
                );
                $this->load->view('_in/ins', $viewData);
            } else {
                $this->_load_auth_models();
                $this->logl['data'] = $this->m_personnels->get($key);
                $this->load->view('_in/ins', $this->logl['data']);
            }
        }

        /**
         * Vérification session — détecte changement d'agent sur poste partagé.
         */
        public function whoami()
        {
            if (!$this->session->userdata('agent')) {
                $this->output->set_status_header(401);
                $this->output->set_content_type('application/json');
                $this->output->set_output(json_encode(array('error' => 'auth_required')));
                return;
            }

            $ctx = function_exists('auth_session_identity_context')
                ? auth_session_identity_context()
                : null;

            if (!$ctx) {
                $agent = $this->session->agent;
                $ctx = array(
                    'cpuser_id' => (int) $agent->cpuser_id,
                    'username' => (string) $agent->username,
                    'userole' => (string) $agent->userole,
                    'type_rols' => isset($agent->type_rols) ? (string) $agent->type_rols : '',
                    'roleattribut' => isset($agent->roleattribut) ? (int) $agent->roleattribut : 0,
                    'garenom' => '',
                    'gare_id' => '',
                );
            }

            $this->output->set_content_type('application/json');
            $this->output->set_output(json_encode($ctx));
        }
    

        public function lin_($pk = NULL)
        {
            if ($pk != NULL) {
                return;
            }

            $this->_load_auth_models();
            $fonction = (string) $this->input->post('fonction');
            $ro = strpos($fonction, '/');
            if ($ro === false) {
                $this->_login_fail('Choisissez un profil.');
                return;
            }

            $r = (int) substr($fonction, 0, $ro);
            $u = (int) substr($fonction, $ro + 1, strlen($fonction));

            if ($u <= 0 || $r <= 0) {
                $this->_login_fail('Profil invalide.');
                return;
            }

            $pending = auth_session_get_login_pending();
            if (!$pending) {
                auth_session_login_transition_denied();
            }

            $company = $this->m_entreprises->get_key($pending['ekey']);
            if (empty($company)) {
                auth_session_login_transition_denied();
            }

            if ((int) $pending['cpuser_id'] !== $u) {
                auth_session_login_transition_denied('Ce profil ne correspond pas au compte connecté.');
            }

            $this->_proceed_with_role($company, $u, $r);
        }

        /**
         * Choix gare validé (GET auto ou POST) pendant le flux login pending.
         */
        public function pick_gare_go($ekey = null, $uid = null, $role = null, $gare_id = null)
        {
            $this->_load_auth_models();
            $uid = (int) $uid;
            $role = (int) $role;
            $gare_id = (string) $gare_id;

            if ($uid <= 0 || $role <= 0 || $gare_id === '') {
                auth_session_login_transition_denied('Gare invalide.');
            }

            $company = $this->m_entreprises->get_key($ekey);
            if (empty($company)) {
                auth_session_login_transition_denied();
            }

            if (!auth_session_validate_login_pending($uid, $company->ekey)) {
                auth_session_login_transition_denied();
            }

            $allowed = $this->m_compte_user->pick_attribution_on_gare($uid, $role, $gare_id);
            if (empty($allowed)) {
                auth_session_login_transition_denied('Cette gare n\'est pas disponible pour votre profil.');
            }

            $this->_proceed_with_role($company, $uid, $role, $gare_id);
        }

        public function pick_gare_s($pk = null)
        {
            if ($pk !== null) {
                return;
            }

            $this->_load_auth_models();
            $ekey = (string) $this->input->post('ekey');
            $uid = (int) $this->input->post('cpuser_id');
            $role = (int) $this->input->post('userole');
            $gare_id = (string) $this->input->post('gare_id');

            if ($ekey === '' || $uid <= 0 || $role <= 0 || $gare_id === '') {
                auth_session_login_transition_denied('Choisissez une gare.');
            }

            redirect('login/pick_gare_go/' . rawurlencode($ekey) . '/' . $uid . '/' . $role . '/' . rawurlencode($gare_id));
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

                auth_session_reset_for_login();

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

                $this->_after_password_ok($detector, $this->logl['company']);
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
                auth_session_purge();
                redirect('login/ins/');
                return;
            }

            auth_session_force_logout(true);

            redirect('login/ins/');
        }
    }
    
    /** End of file: Login.php **/
    /** File location: application/controllers/Login.php **/
