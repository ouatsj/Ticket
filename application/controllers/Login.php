<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Login extends CI_Controller
    {
        protected $logl = array();
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
        }
        
        public function in($key = NULL, array $in = NULL)
        {
            if ($key === NULL) {
                $this->logl['data'] = array(null);
                $this->load->view('_in/in', $this->logl['data']);
            } else {
                $this->logl['data'] = $this->m_personnels->get($key);
                $this->load->view('_in/in', $this->logl['data']);
            }
        }

        public function ins($key = NULL, array $in = NULL)
        {
            if ($key === NULL) {
                $this->logl['data'] = array(null);
                $this->load->view('_in/ins', $this->logl['data']);
            } else {
                $this->logl['data'] = $this->m_personnels->get($key);
                $this->load->view('_in/ins', $this->logl['data']);
            }
        }
    

        public function lin_($pk = NULL)
        {
            if ($pk != NULL) {
                
                
            } else {
                $ro = strpos($this->input->post('fonction'), '/');
                $r = substr($this->input->post('fonction'), 0, $ro);
                $u = substr($this->input->post('fonction'), $ro + 1, strlen($this->input->post('fonction')));
                
                 $detector = $this->m_compte_user->lookedfor1($u, $r);
            
                if (!empty($detector)) {
                       
                    foreach ($detector as $rw) {
                        $this->logl['company'] = $this->m_entreprises->get_key($rw->cle_comp);
                             
                                $array_acc = array(
                                'activeattrib' => 1,
                                );
                                
                        $this->m_roleattribution->update($rw->roleattribut, $array_acc);
                    }
                    redirect('home/' . $this->logl['company']->ekey . '/' . $u. '/' .$r);
                }
                 else
                    $this->lin('', $this->logl);
            }
                
        }

        public function lin_s($pk = NULL)
        {
            if ($pk != NULL) {
               
                
            } else {
                $username = $this->input->post('username');
                $upassword = sha1($this->input->post('upassword'));

                $detector = $this->m_compte_user->lookedfor($username, $upassword);
            
                if (!empty($detector)) {
                       
                        $this->logl['company'] = $this->m_entreprises->get_key($detector->cle_comp);
                        
                        $act_acc = array(
                        'is_conect' => 1,
                        'date_conect' => mdate("%Y-%m-%d %H:%i:%s", now('UTC')),
                        );
                        $msj = $this->m_compte_user->update($detector->cpuser_id, $act_acc);
                        redirect('welcome/' . $this->logl['company']->ekey . '/' . $detector->cpuser_id);
                    
                }
                 else
                    $this->lin('', $this->logl);
            }
                
        }
        
        public function lin($key = NULL, array $in = NULL)
        {
            if ($key === NULL) {
                $this->logl['data'] = array(null);
                $this->load->view('_in/ins', $this->logl['data']);
            } else {
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
                $this->logl['data'] = $this->m_utilisateur->get_user($key);
                $this->load->view('_in/ine', $this->logl['data']);
            }
        }
        
        public function lout($o = NULL, $a = NULL)
        {
            if ($o === $this->session->session_id AND $a === $this->session->agent->cpuser_id)
                $out_ac = array(
                    'is_conect' => 0,
                    'date_deconect' => mdate("%Y-%m-%d %H:%i:%s", now('UTC')),
                );

            $out_acc = $this->m_compte_user->update($this->session->agent->cpuser_id, $out_ac);

            $c = $this->session->agent->cpuser_id;
            $r = $this->session->agent->userole;

            $at = $this->db->query("SELECT * FROM attributions_role a
                 JOIN  user_login u ON a.idgestcompte = u.uid_login 
                 WHERE u.uid_usercpte = '$c'
                 AND a.userole = '$r'")->result();

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