<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Entreprises extends MY_Controller
    {
        public $property = array(
            'title' => 'Entreprises',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $entreprise;
        
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

                $this->property['pagetitle'] .= "• LISTE DES ENTREPRISES";
                $this->property['entreprises'] = $this->m_entreprises->get();
                $this->property['villes'] = $this->m_villes->get();
                $this->property['paysidents'] = $this->m_pays->get();
                return $this->layout->view('_entreprise/view', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $arrayent = array(
                'id_ville_ent' => $this->input->post('ville'),
                'pays_id' => $this->input->post('paysidentif'),
                'nom_entreprise' => $this->input->post('nom_entreprise'),
                'logoent' => $this->input->post('logoentreprise'),
                'num_RCCM' => $this->input->post('num_rccm'),
                'num_IFU' => $this->input->post('numeroifu'),
                'adresseentre' => $this->input->post('adresse'),
                'boitepostal' => $this->input->post('postale'), 
                'contact' => $this->input->post('contact'),
                'regime' => $this->input->post('regime'),
                'licence_ent' => $this->input->post('licence'),
                'agrement' => $this->input->post('agrement'),
                'email_ent' => $this->input->post('email'),
                'siteweb' => $this->input->post('siteweb'),
            );
            $ent = $this->m_entreprises->create($arrayent);
            if ($ent != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            return $this->view($ckey, $this->property);
        }
        
        
        
        public function edit_($ckey, $etid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $arrayedit = array(
                'id_ville_ent' => $this->input->post('ville'),
                'pays_id' => $this->input->post('paysidentif'),
                'nom_entreprise' => $this->input->post('nom_entreprise'),
                'logoent' => $this->input->post('logoentreprise'),
                'num_RCCM' => $this->input->post('num_rccm'),
                'num_IFU' => $this->input->post('numeroifu'),
                'adresseentre' => $this->input->post('adresse'),
                'boitepostal' => $this->input->post('postale'),
                'contact' => $this->input->post('contact'),
                'regime' => $this->input->post('regime'),
                'licence_ent' => $this->input->post('licence'),
                'agrement' => $this->input->post('agrement'),
                'email_ent' => $this->input->post('email'),
                'siteweb' => $this->input->post('siteweb'),
            );
            if ($this->m_entreprises->update($etid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }
        //annuler entreprise
        public function supprime($ckey, $id_ent, $idvl)
        {
          
            $arraysup = array(
                'id_ville_ent' => $idvl,
                'pays_id' => $this->input->post('paysidentif'),
                'nom_entreprise' => $this->input->post('entrep'),
                'num_RCCM' => $this->input->post('entrep'),
                'num_IFU' => $this->input->post('entrep'),
                'adresseentre' => $this->input->post('entrep'),
                'boitepostal' => $this->input->post('postale'), 
                'contact' => $this->input->post('entrep'),
            );
                $this->m_entreprises->del($id_ent, $arraysup);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
        }
        
    }
    
    /** End of file: Entreprises.php **/
    /** File location: application/controllers/Entreprises.php **/