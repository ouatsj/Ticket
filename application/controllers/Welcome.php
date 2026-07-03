<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Welcome extends CI_Controller
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
            $this->property['agentroles'] = $this->m_compte_user->roleatt($m);

            $this->load->view('_in/index2', $this->property);
        }
        
    }
    
    /** End of file: Welcome.php **/
    /** File location: application/controllers/Welcome.php **/