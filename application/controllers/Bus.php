<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Bus extends MY_Controller
    {
        public $property = array(
            'title' => 'Bus',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $bus;
        public $profil;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        /**
         *
         */
        public function view($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['pagetitle'] .= "• LISTE DES BUS<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['buses'] = $this->m_bus->get($this->company->ekey);
                $this->property['categoriebus'] = $this->m_categories->get();
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_bus/view', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arraybus = array(
                'immatriculation' => $this->input->post('_immatriculation'),
                'id_compagniebus' => $this->input->post('_compagn'),
                'categoriebus' => $this->input->post('_categorie'),
            );
            $bu = $this->m_bus->create($arraybus);
            if ($bu != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('bus/' . $this->session->company->ekey);
        }
        
        public function edit($ckey, $bus_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->bus = $this->m_bus->get($this->company->id_entreprise, $bus_id);
            $this->property['bus'] = $this->bus;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong> • {$this->bus->immatriculation}";
            $this->layout->view('_bus/edition', $this->property);
        }
        
        public function edit_($ckey, $busid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'immatriculation' => $this->input->post('_immatriculation'),
                'id_compagniebus' => $this->input->post('compagn'),
                'categoriebus' => $this->input->post('categorie'),
            );
            if ($this->m_bus->update($busid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }
        //annuler bus
        public function supprime($ckey, $id_bu, $idcpg, $cte)
        {
          
             $arraysup = array(
                'id_compagniebus' => $idcpg,
                'categoriebus' => $cte,
            );
            $this->m_bus->del($id_bu, $arraysup);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
        }
        

        public function verificategorie($b)
        {
            
            $ouh = $this->m_bus->getb($this->session->company->ekey, $b);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $ouh));
            
        }
        public function verificategoriebis($bs)
        {
            
            $ouhb = $this->m_bus->getbiss($this->session->company->ekey, $bs);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $ouhb));
            
        }
        
    }
    
    /** End of file: Bus.php **/
    /** File location: application/controllers/Bus.php **/
