<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Pages extends CI_Controller
    {
        public $property = array(
            'title' => 'Pages',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $page;
        
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
                $this->property['pagetitle'] .= "• LISTE DES PAGES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['pages'] = $this->m_dossier->get();
                
                return $this->layout->view('_pags/view', $this->property);
        }

        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arraypag = array(
                'typedossier' => $this->input->post('pagecompte'),
            );
            $pa = $this->m_dossier->create($arraypag);
            if ($pa != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('pages/' . $this->session->company->ekey);
        }
        
        
        public function editp_($ckey, $pgsid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $uparraypag = array(
                'typedossier' => $this->input->post('upagecompte'),
            );
            if ($this->m_dossier->update($pgsid, $uparrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }
    }
    
    /** End of file: Pages.php **/
    /** File location: application/controllers/Pages.php **/
