<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Heures extends MY_Controller
    {
        public $property = array(
            'title' => 'Heures',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $heure;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**
         *
         */

        public function index($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTE DES HEURES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['heures'] = $this->m_heure->getall();
                return $this->layout->view('_heure/index', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
            $hr = $this->input->post('heur');

            $h = $this->db->query("SELECT * FROM heures WHERE heures.heure = '$hr'")->row();

            $arrayheure = array(
                'heure' => $this->input->post('heur'),
            );

            if ($h === NULL) {
                $hre = $this->m_heure->create($arrayheure);
            }

            if ($hre != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('heures/' . $this->session->company->ekey);
        }
        
    
        public function edit_($ckey, $idheure)
        {
            $arrayedit = array(
                'heure' => $this->input->post('heure_ligne'),
            );
            if ($this->m_heure->update($idheure, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->index($ckey, $this->property);
            }
        }

        //heure active
        public function active($ckey, $id, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($statut == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $upheure = array(
                        'h_active' => $stat,
                    );
                    
                    $this->m_heure->update($id, $upheure);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            return $this->index($ckey, $this->property);            
        }
        
    }
    
    /** End of file: Heures.php **/
    /** File location: application/controllers/Heures.php **/
