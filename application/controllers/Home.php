<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Home extends CI_Controller
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
              $this->charger['company'] = $this->m_entreprises->get_key($key);
            // The User logged in
            $this->charger['agent'] = $this->m_compte_user->get($uid, $r);
            // The session var is charged
            $this->session->set_userdata($this->charger);
            
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
                    echo'PAS DE DROIT SUR CETTE APPLICATION';
                }
            }
            else
            {
                echo'PAS DE PAGE ATTRIBUER';
            }
           
        }
        
        
        public function main1(array $argv = NULL)
        {
            $this->property['pagetitle'] = 'Accueil';
            $this->property['UPDATE_SUCCESS'] = FALSE;
            $this->property['INSERT_SUCCESS'] = FALSE;
            
            $this->property['gares'] = $this->m_compte_user->attrib($this->session->agent->cpuser_id, $this->session->agent->userole);
            $this->property['villes'] = $this->m_villes->get();
            $this->property['compagnies'] = $this->m_compagnies->get();

            $this->layout->view('index1', $this->property);
        }
    }
    
    /** End of file: Home.php **/
    /** File location: application/controllers/Home.php **/