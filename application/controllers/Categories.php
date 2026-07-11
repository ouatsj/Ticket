<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Categories extends MY_Controller
    {
        public $property = array(
            'title' => 'Categories',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $categorie;
        
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

                $this->property['pagetitle'] .= "• LISTE DES CATEGORIES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['categories'] = $this->m_categories->get();
                return $this->layout->view('_categorie/view', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $nbsiege = $this->input->post('_nbr_place');
            $bid = $this->input->post('_categorie');
            $this->company = $this->m_entreprises->get_key($ckey);
            $arraycat= array(
                'categorie' => $this->input->post('_categorie'),
                'nbr_place' => $this->input->post('_nbr_place'),
                'nbr_colonne' => $this->input->post('_nbr_colonne'),
                'datecat_create' => now('UTC'),
            );
            $bk = $this->m_categories->create($arraycat);
            if ($bk != NULL)
                
            $argb = array(
                'id_cat_sieg' => $bid,
                'idcat_bus' => $bid,
                'siege_num' => $nbsiege,
            );
        
                for ($n = 1; $n <= $nbsiege; $n++) 
                {
                    $btid = $this->m_categories_siege->create(array('id_cat_sieg' => $n.$bid, 'siege_num' => $n,
                    'idcat_bus ' => $bid));
                
                }
            if ($btid != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            //var_dump($btid);
           redirect('categories/' . $this->session->company->ekey);
        }
        
        public function edit($ckey, $cat_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->categorie = $this->m_categories->get($this->company->id_entreprise, $cat_id);
            $this->property['categorie'] = $this->categorie;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong> • {$this->categorie->categorie}";
            $this->layout->view('_categorie/detail', $this->property);
        }
        
        public function edit_($ckey, $ctid)
        {
            $np = $this->input->post('nombre');

            $nbsiege = $this->input->post('_nbr_place');

            //$bid = $this->input->post('_catego');
            $bc = $this->input->post('_categ');

            $this->company = $this->m_entreprises->get_key($ckey);

            $arrayedit = array(
                'categorie' => $this->input->post('_categ'),
                'nbr_place' => $this->input->post('_nbr_place'),
                'nbr_colonne' => $this->input->post('_nombre_colonne'),
            );
            if ($this->m_categories->create($arrayedit) != FALSE) {
                
                for ($i = 1; $i <= $np; $i++) {
                    $buid = $this->m_categories_siege->del(array('idcat_bus' => $ctid));
                }
                $argb = array(
                'id_cat_sieg' => $bc,
                'idcat_bus' => $bc,
                'siege_num' => $nbsiege,
            );
        
                for ($n = 1; $n <= $nbsiege; $n++) 
                {
                    $btid = $this->m_categories_siege->create(array('id_cat_sieg' => $n.$bc, 'siege_num' => $n,
                    'idcat_bus' => $bc));
                
                }
                $this->property['UPDATE_SUCCESS'] = TRUE;

                return $this->view($ckey, $this->property);
            }
        }
        //annuler categorie
        public function supprime($ckey, $id_cat, $idcat, $nb_cl)
        {
          
             $arraysup = array(
                'categorie' => $idcat,
                'nbr_colonne' => $nb_cl,
            );
            $this->m_categories->del($id_cat, $arraysup);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
        }
        
        public function getnbrplace($c)
        {
            
            $place = $this->m_categories->max($c);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $place));
            
        }
    }
    
    /** End of file: Categories.php **/
    /** File location: application/controllers/Categories.php **/
