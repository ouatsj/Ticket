<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Villes extends CI_Controller
    {
        public $property = array(
            'title' => 'Villes',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $ville;
        public $quartier;
        
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

                $this->property['pagetitle'] .= "• LISTE DES VILLES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['villes'] = $this->m_villes->get();
                $this->property['pays'] = $this->m_pays->get();
                return $this->layout->view('_ville/view', $this->property);
        }

        public function index($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTES DES QUARTIERS<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['villes'] = $this->m_villes->get();
                $this->property['quartiers'] = $this->m_quartier->get();
                return $this->layout->view('_ville/index', $this->property);
        }

        public function edit($ckey, $vil_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->ville = $this->m_villes->get($vil_id);
            $this->property['ville'] = $this->ville;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong> • {$this->ville->nom_ville}";
            $this->layout->view('_ville/edition', $this->property);
        }

        public function editq($ckey, $qa_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->quartier = $this->m_quartier->get($qa_id);
            $this->property['quartier'] = $this->quartier;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong> • {$this->quartier->nom_quartier}";
            $this->layout->view('_ville/editionquart', $this->property);
        }
        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $arrayville = array(
                'id_pay' => $this->input->post('paysid'),
                'nom_ville' => $this->input->post('ville'),
                'codville' => $this->input->post('codeville'),
            );
            $vi = $this->m_villes->create($arrayville);
            if ($vi != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('villes/' . $this->session->company->ekey);
        }

         //insertion
         public function addquart($ckey)
         {
             $this->company = $this->m_entreprises->get_key($ckey);
            $nom = $this->input->post('quartier');
            $ng = substr($nom, 0, 2);
            $m = strtoupper($ng);
             $arrayquart = array(
                 'id_ville_qua' => $this->input->post('paysid'),
                 'nom_quartier' => $this->input->post('quartier'),
                 'code_quart' => $this->input->post('codquartier'),
             );
             $qa = $this->m_quartier->create($arrayquart);
             if ($qa != NULL) {
                 $this->property['INSERT_SUCCESS'] = TRUE;
             }
             redirect('villes/quart/' . $this->session->company->ekey);
         }
 
        
    
        public function edit_($ckey, $idv)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'id_pay' => $this->input->post('idpays'),
                'nom_ville' => $this->input->post('nomville'),
                'codville' => $this->input->post('codeville'),
            );
            if ($this->m_villes->update($idv, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }

        //update
        public function editqua_($ckey, $idquart)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $nom = $this->input->post('nomquartier');
            $ng = substr($nom, 0, 2);
            $m = strtoupper($ng);
            $arrayquartier = array(
                'id_ville_qua' => $this->input->post('idville'),
                'nom_quartier' => $this->input->post('nomquartier'),
                'code_quart' => $this->input->post('codquartier'),
            );
            if ($this->m_quartier->update($idquart, $arrayquartier) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->index($ckey, $this->property);
            }
        }
        
        
    }
    
    /** End of file: Villes.php **/
    /** File location: application/controllers/Villes.php **/
