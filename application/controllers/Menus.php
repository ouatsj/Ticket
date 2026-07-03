<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Menus extends CI_Controller
    {
        public $property = array(
            'title' => 'Menus',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $menus;
        
        
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

                $this->property['pagetitle'] .= "• LISTE DES MENUS<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                $this->property['menus'] = $this->m_menu_bouton->get();
                return $this->layout->view('_menu/view', $this->property);
        }
        
        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arraymenu = array(
                'nom_attrib' => $this->input->post('namebouton'),
                'url_attribut' => $this->input->post('adressebouton'),
            );
            $m = $this->m_menu_bouton->create($arraymenu);
            if ($m != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('menus/' . $this->session->company->ekey);
        }


        public function edit_($ckey, $idm)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $upmenu = array(
                'nom_attrib' => $this->input->post('namebouton'),
                'url_attribut' => $this->input->post('adressebouton'),
            );
            $m = $this->m_menu_bouton->update($idm, $upmenu);
            if ($m != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('menus/' . $this->session->company->ekey);
        }
        
    }
    
    /** End of file: Menus.php **/
    /** File location: application/controllers/Menus.php **/
