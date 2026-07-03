<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Compagnies extends CI_Controller
    {
        public $property = array(
            'title' => 'Compagnies',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $entreprise;
        public $company;
        
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
            $this->entreprise = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTE DES COMPAGNIES";
                $this->property['compagnie'] = $this->m_compagnies->get();
                $this->property['entreprises'] = $this->m_entreprises->get();
                $this->property['villes'] = $this->m_villes->get();
                $this->property['paysidents'] = $this->m_pays->get();
                return $this->layout->view('_compagnie/view', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $compt = $this->db->query("SELECT COUNT(cle_compagnie) AS cle FROM compagnies")->row();
            
            $lien = 'assets/img/gallery/';

            $arraycpg = array(
                'cle_compagnie' => (500).($compt->cle),
                'id_entrep' => $this->input->post('prise'),
                'nom_compagnie' => $this->input->post('compagnie_nom'),
                'vilcompag' => $this->input->post('ville'),
                'idpayscomp' => $this->input->post('payidentif'),
                'logo' => $lien.$this->input->post('logocompagnie'),
                'logofond' => $lien.$this->input->post('logofond'),
                'slogan' => $this->input->post('slogan'), 
                'adresse' => $this->input->post('adresse'),
                'contact_comp' => $this->input->post('contact'),
                'contact_national' => $this->input->post('contactnation'),
                'contact_inter' => $this->input->post('contactsecd'),
                'num_ifu_comp' => $this->input->post('numifu'),
                'num_rccm_comp' => $this->input->post('numrccm'),
                'mail_comp' => $this->input->post('adresseemail'),
                'siteweb_comp' => $this->input->post('site_web'),
            );
            $cpg = $this->m_compagnies->create($arraycpg);
            if ($cpg != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('compagnies/' . $this->session->company->ekey);
        }
        
        public function edit($ckey, $cp_id)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            $this->entreprise = $this->m_compagnies->get($this->entreprise->id_entreprise, $et_id);
            $this->property['compagnie'] = $this->entreprise;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->entreprise->id_entreprise}</strong> • {$this->compagnie->nom_entreprise}";
            $this->layout->view('_compagnie/edition', $this->property);
        }
        
        public function upedit($ckey, $cpid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $lien = 'assets/img/gallery/';

            $arrayedit = array(
                'id_entrep' => $this->input->post('prise'),
                'nom_compagnie' => $this->input->post('compagnie_nom'),
                'logo' => $lien.$this->input->post('logocompagnie'),
                'logofond' => $lien.$this->input->post('logofond'),
                'vilcompag' => $this->input->post('ville'),
                'idpayscomp' => $this->input->post('payidentif'),
                'slogan' => $this->input->post('slogan'), 
                'adresse' => $this->input->post('adresse'),
                'contact_comp' => $this->input->post('contact'),
                'contact_national' => $this->input->post('contactnation'),
                'contact_inter' => $this->input->post('contactsecd'),
                'num_ifu_comp' => $this->input->post('numifu'),
                'num_rccm_comp' => $this->input->post('numrccm'),
                'mail_comp' => $this->input->post('adresseemail'),
                'siteweb_comp' => $this->input->post('site_web'),
            );
            
            $upcpg = $this->m_compagnies->update($cpid, $arrayedit);
            if ($upcpg != FALSE)
            {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }

        //annuler entreprise
        public function supprime($ckey, $id_cid, $ident)
        {
          
             $arraysup = array(
                'cle_compagnie' => $id_cid,
                'id_entrep' => $ident,
                'nom_compagnie' => $this->input->post('entrep'),
                'logo' => $this->input->post('entrep'),
                'slogan' => $this->input->post('entrep'), 
                'adresse' => $this->input->post('adresse'),
                'contact_comp' => $this->input->post('contact'),
            );
            $this->m_compagnies->del($id_cid, $arraysup);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
        }
        
    }
    
    /** End of file: Compagnies.php **/
    /** File location: application/controllers/Compagnies.php **/