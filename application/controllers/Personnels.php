<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Personnels extends CI_Controller
    {
        public $property = array(
            'title' => 'Personnels',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $personnel;
        
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

                $this->property['pagetitle'] .= "• LISTE DU PERSONNEL<strong></strong>";
                $this->property['personnels'] = $this->m_personnels->getp($this->company->ekey);
                $this->property['typepersonnels'] = $this->m_type_personnel->get();
                $this->property['compagnies'] = $this->m_compagnies->get();

                return $this->layout->view('_personnel/view', $this->property);
        }

        public function index($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTE DU PERSONNEL ET PARTENAIRE<strong>•&nbsp;</strong>";
                $this->property['partenaires'] = $this->m_client->get();
                $this->property['compagnies'] = $this->m_compagnies->get();

                return $this->layout->view('_personnel/index', $this->property);
        }
        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);            
            $cbt  = '';

            if($this->input->post('_compag') == '5000'){
                $cbt  = 'B';
            }

            elseif($this->input->post('_compag') == '5001'){
                $cbt  = 'M';
            }

            elseif($this->input->post('_compag') == '5002'){
                $cbt  = 'C';
            }

            elseif($this->input->post('_compag') == '5003'){
                $cbt  = 'CI';
            }

            $mystring = $this->input->post('perso_matricule');
            $findme   = '$cbt';
            $pos = strpos($mystring, $findme);

            // Notez notre utilisation de ===.  == ne fonctionnerait pas comme attendu
            // car la position de 'a' est la 0-ième (premier) caractère.
            /*if ($pos === FALSE) {
                $mtr = $cbt.$this->input->post('perso_matricule');
            } else {
                $mtr = $this->input->post('perso_matricule');
            }*/

            if ($cbt.$this->input->post('perso_matricule') === $this->input->post('perso_matricule')){
                $mtr = $this->input->post('perso_matricule');
            } else {
                $mtr = $cbt.$this->input->post('perso_matricule');
            }

                if($this->input->post('persoclient') === 'client' OR $this->input->post('persoclient') === 'autrepersonnel'){
                   
                    $argv = array(
                        'nom_client' => $this->input->post('ruclient'),
                        'type_client' => $this->input->post('persoclient'),
                        'prenom_client' => $this->input->post('prclient'),
                        'contact_client' => $this->input->post('perso_tel'),
                        'date_delivre' => mdate("%Y-%m-%d", now('UTC')),
                        'lieu_delivre' => $this->input->post('lieu'),
                        'datedoc' => mdate("%Y-%m-%d", now('UTC')),
                    );
                    $clhid = $this->m_client->create($argv);
                }
                if($this->input->post('persoclient') === 'perso' AND $this->input->post('perso_matricule') != ''){
                    $arrayperso = array(
                        'matricule' => $mtr,
                        'type_perso' => $this->input->post('typeperso'),
                        'compagnie_perso' => $this->input->post('_compag'),
                        'adressepers' => $this->input->post('perso_adresse'),
                        'nomprenom_perso' => $this->input->post('perso_nom'),
                        'cat_permis' => $this->input->post('categ_permis'),
                        'contact_perso' => $this->input->post('premiercontact'),
                        'contact2' => $this->input->post('secondcontact'),
                        'pieces1' => $this->input->post('permis'),
                        'pieces2' => $this->input->post('cnib'),
                        'date_delivre1' => $this->input->post('date_permis'),
                        'date_delivre2' => $this->input->post('date_cnib'),
                        'date_expire1' => $this->input->post('date_expire'),
                        'date_expire2' => $this->input->post('expire_cnib'),
                        'dates_create' => now('UTC'),

                    );
                    $perso = $this->m_personnels->create($arrayperso);
                }
            
                $this->property['INSERT_SUCCESS'] = TRUE;
            
            redirect('personnels/' . $this->session->company->ekey);
                //var_dump($this->input->post('_compag') == '5000');
        }
        
        public function edit($ckey, $perso_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->personnel = $this->m_personnels->get($this->company->id_entreprise, $perso_id);
            $this->property['personnel'] = $this->personnel;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_entreprise}</strong> • {$this->personnel->nomprenom_perso}";
            $this->layout->view('_personnel/edition', $this->property);
        }
        
        public function edit_($ckey, $persid)
        {
            $cbt  = '';

            if($this->input->post('compag') == '5000'){

                $cbt  = 'B';
            }
            elseif($this->input->post('compag') == '5001'){
                $cbt  = 'M';
            }
            elseif($this->input->post('compag') == '5002')
            {
                $cbt  = 'C';
            }

            elseif($this->input->post('compag') == '5003')
            {
                $cbt  = 'CI';
            }

            if ($persid === $this->input->post('perso_matricule')){
                $mtr = $this->input->post('perso_matricule');
            } else {
                $mtr = $cbt.$this->input->post('perso_matricule');
            }

            $arrayedit = array(

                'matricule' => $mtr,
                'type_perso' => $this->input->post('typeperso'),
                'adressepers' => $this->input->post('perso_adresse'),
                'nomprenom_perso' => $this->input->post('perso_nom'),
                'compagnie_perso' => $this->input->post('compag'),
                'contact_perso' =>$this->input->post('premiercontact'),
                'contact2' =>$this->input->post('secondcontact'),
                'cat_permis' => $this->input->post('categ_permis'),
                'pieces1' =>$this->input->post('permis'),
                'pieces2' =>$this->input->post('cnib'),
                'date_delivre1' =>$this->input->post('date_permis'),
                'date_delivre2' =>$this->input->post('date_cnib'),
                'date_expire1' =>$this->input->post('date_expire'),
                'date_expire2' =>$this->input->post('expire_cnib'),
            );
            if ($this->m_personnels->update($persid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }
        
        public function editcl_($ckey, $persid)
        {
            $arrayedit = array(
                'nom_client' => $this->input->post('ruclient'),
                        'type_client' => $this->input->post('typeperso'),
                        'prenom_client' => $this->input->post('prclient'),
                        'contact_client' => $this->input->post('perso_tel'),
                        'lieu_delivre' => $this->input->post('lieu'),
                    );
            if ($this->m_client->update($persid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $this->property);
            }
        }
        public function verifinfos($m)
        {

            $persomat = $this->m_personnels->getinfo($m);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $persomat));

        }

        public function verifpersonne($pr)
        {
            
            $persomats = $this->m_personnels->getchs($this->session->company->ekey, $pr);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $persomats));
            
        }

        public function verifclient($pr)
        {
            
            $clientmats = $this->m_client->getp($pr);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $clientmats));
            
        }

        public function verifconvoi($pr)
        {
            
            $convoimats = $this->m_personnels->getconvs($this->session->company->ekey, $pr);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $convoimats));
            
        }
        
        public function active($ckey, $id, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $uperso = array(
                        'actif_perso' => $stat,
                    );
                    
                    $this->m_personnels->update($id, $uperso);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

                    return $this->view($ckey, $this->property);            
        }


        public function activeclient($ckey, $idcl, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 1)
                    {
                        $stat = 0;
                    }
                    else
                    {
                        $stat = 1;
                    }
                        $upclient = array(

                        'actifclient' => $stat,
                    );
                    
            $this->m_client->update($idcl, $upclient);

            $this->property['UPDATE_SUCCESS'] = TRUE;

            return $this->index($ckey, $this->property);            
        }

        public function permission($ckey, $id, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statut == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $uperso = array(
                        'persoactif' => $stat,
                    );
                    
                    $this->m_personnels->update($id, $uperso);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

                    return $this->view($ckey, $this->property);            
        }
    }
    
    /** End of file: Personnels.php **/
    /** File location: application/controllers/Personnels.php **/
