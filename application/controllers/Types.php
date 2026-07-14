<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Types extends MY_Controller
    {
        public $property = array(
            'title' => 'Types',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $types;
        
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

                $this->property['pagetitle'] .= "• LISTE DES TYPES PERSONNELS<strong>•&nbsp;{$this->company->nom_entreprise}•</strong>";
                $this->property['typespersonnels'] = $this->m_type_personnel->get();
                return $this->layout->view('_type/view', $this->property);
        }

        public function index($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['pagetitle'] .= "• LISTES DES TYPES CLIENTS<strong>•&nbsp;{$this->company->nom_entreprise}•</strong>";
                $this->property['typeclients'] = $this->m_type_client->get();
                return $this->layout->view('_type/index', $this->property);
        }

        public function indexdoc($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['pagetitle'] .= "• LISTES DES TYPES DE DOCUMENT<strong>•&nbsp;{$this->company->nom_entreprise}•</strong>";
                $this->property['typedocuments'] = $this->m_typedocument->get();
                return $this->layout->view('_type/indexdoc', $this->property);
        }
        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayclient = array(
                'type_personnel' => $this->input->post('type_perso'),
                'create_attpers' => now('UTC'),
            );
            $bcl = $this->m_type_personnel->create($arrayclient);
            if ($bcl != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('types/' . $this->session->company->ekey);
        }

         //insertion
         public function addclient($ckey)
         {
             $this->company = $this->m_entreprises->get_key($ckey);
             $arrayperso = array(
                 'nom_type' => $this->input->post('type_client'),
                 'create_attcl' => now('UTC'),
             );
             $bperso = $this->m_type_client->create($arrayperso);
             if ($bperso != NULL) {
                 $this->property['INSERT_SUCCESS'] = TRUE;
             }
             redirect('types/client/' . $this->session->company->ekey);
         }
 
        
    
        public function edit_($ckey, $idperso)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'type_personnel' => $this->input->post('type'),
            );
            if ($this->m_type_personnel->update($idperso, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }

        //update
        public function editcl_($ckey, $idclient)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'nom_type' => $this->input->post('typeclient'),
            );
            if ($this->m_type_client->update($idclient, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->index($ckey, $this->property);
            }
        }
        
         //insertion
         public function adddoc($ckey)
         {
             $this->company = $this->m_entreprises->get_key($ckey);
             $arraydoc = array(
                 'typedocument' => $this->input->post('type_document'),
                 'create_doc' => now('UTC'),
             );
             $doc = $this->m_typedocument->create($arraydoc);
             if ($doc!= NULL) {
                 $this->property['INSERT_SUCCESS'] = TRUE;
             }
             redirect('types/documents/' . $this->session->company->ekey);
         }
 
        
    
        public function editdoc_($ckey, $doc)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'typedocument' => $this->input->post('type'),
            );
            if ($this->m_typedocument->update($doc, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('types/documents/' . $this->session->company->ekey);
            }
        }
    }
    
    /** End of file: Types.php **/
    /** File location: application/controllers/Types.php **/
