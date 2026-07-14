<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Genres extends MY_Controller
    {
        public $property = array(
            'title' => 'Genres',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $genre_recette;
        
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

                $this->property['pagetitle'] .= "• LISTE DES GENRES RECETTES";
                $this->property['genrerecettes'] = $this->m_genre_recette->get();
                return $this->layout->view('_genre/view', $this->property);
        }

        //depenses
        public function index($ckey)
        {
                $this->property['pagetitle'] .= "• LISTE DES GENRES DEPENSES";
                $this->property['genredepenses'] = $this->m_genre_depense->get();
                return $this->layout->view('_genre/indexdepense', $this->property);
        }

        //depots
        public function viewdepot($ckey)
        {

                $this->property['pagetitle'] .= "• LISTE DES GENRES DEPOTS";
                $this->property['genredepots'] = $this->m_genre_depot->get();
                return $this->layout->view('_genre/indexdepot', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $arraygenre = array(
                'genre_recet' => $this->input->post('genre'),
                'dategr_create' => now('UTC'),
            );
            $bgr = $this->m_genre_recette->create($arraygenre);
            if ($bgr != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('genres/genre_recettes/' . $this->session->company->ekey);
        }
    
        public function edit_($ckey, $idgenre)
        {
            $arrayedit = array(
                'genre_recet' => $this->input->post('genre'),
            );
            if ($this->m_genre_recette->update($idgenre, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }

        //add depense
        public function adddepense($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $arraygenre = array(
                'genre_depens' => $this->input->post('genre'),
                'dategdp_create' => now('UTC'),
            );
            $bgr = $this->m_genre_depense->create($arraygenre);
            if ($bgr != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('genres/genre_depenses/' . $this->session->company->ekey);
        }
    
        public function depenseedit_($ckey, $idgenre)
        {
            $arrayedit = array(
                'genre_depens' => $this->input->post('genre'),
            );
            if ($this->m_genre_depense->update($idgenre, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->index($ckey, $this->property);
            }
        }

        //add depot
        public function adddepot($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $arraygenre = array(
                'genre_depot' => $this->input->post('genre'),
                'creategdpo_at' => now('UTC'),
            );
            $bgr = $this->m_genre_depot->create($arraygenre);
            if ($bgr != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('genres/genre_depots/' . $this->session->company->ekey);
        }
    
        public function depotedit_($ckey, $idgenre)
        {
            $arrayedit = array(
                'genre_depot' => $this->input->post('genre'),
            );
            if ($this->m_genre_depot->update($idgenre, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->viewdepot($ckey, $this->property);
            }
        }
    }
    
    /** End of file: Genres.php **/
    /** File location: application/controllers/Genres.php **/