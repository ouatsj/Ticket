<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Lignes extends MY_Controller
    {
        public $property = array(
            'title' => 'Lignes',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $lignes;
        
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

                $this->property['pagetitle'] .= "• LISTE DES LIGNES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise);
                }
                $this->property['garedeparts'] = $this->m_gare_depart->get($this->company->id_entreprise);
                $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                return $this->layout->view('_ligne/view', $this->property);
        }

        //insertion
        public function add($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $gare_posd = strpos($this->input->post('garedepart'), '.');
            
            $sub_gcod = substr($this->input->post('garedepart'), 0, $gare_posd);
            $sub_direction = substr($this->input->post('garedepart'), $gare_posd + 1, strlen($this->input->post('garedepart')));
            
            $gare_posa = strpos($this->input->post('garearrivee'), '.');
            
            $sub_gcoda = substr($this->input->post('garearrivee'), 0, $gare_posa);

            $directionar = substr($this->input->post('garearrivee'), $gare_posa + 1, strlen($this->input->post('garearrivee')));
            
            $arrayligne = array(
                'ident_ligne' => $sub_gcod. '-' .$sub_gcoda,
                'gaexp_lg' => $sub_gcod,
                'gadest_lg' => $sub_gcoda,
                'nom_ligne' => $sub_direction. '-' .$directionar,
                'distancekm' => $this->input->post('distance'),
                'prixkm' => $this->input->post('distanceprix'),
            );
            $blg = $this->m_lignes->create($arrayligne);
            if ($blg != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('lignes/' . $this->session->company->ekey);
        }
        
        public function edit($ckey, $lg_id)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->lignes = $this->m_lignes->get($this->company->id_entreprise, $lg_id);
            $this->property['lignes'] = $this->lignes;
            $this->property['pagetitle'] .= " <strong class='text-warning'>{$this->company->nom_compagnie}</strong> • {$this->lignes->nom_ligne}";
            $this->layout->view('_ligne/edition', $this->property);
        }
        
        public function edit_($ckey, $lgid)
        {
            $gare_pos = strpos($this->input->post('garedepart'), '.');
            
            $sub_gcod = substr($this->input->post('garedepart'), 0, $gare_pos);
            $sub_direction = substr($this->input->post('garedepart'), $gare_pos + 1, strlen($this->input->post('garedepart')));
            
            $gare_posa = strpos($this->input->post('garearrivee'), '.');
            
            $sub_gcoda = substr($this->input->post('garearrivee'), 0, $gare_posa);
            $directionar = substr($this->input->post('garearrivee'), $gare_posa + 1, strlen($this->input->post('garearrivee')));
            $arrayedit = array(
                'ident_ligne' => $sub_gcod. '-' .$sub_gcoda,
                'gaexp_lg' => $sub_gcod,
                'gadest_lg' => $sub_gcoda,
                'nom_ligne' => $sub_direction. '-' .$directionar,
                'distancekm' => $this->input->post('distance'),
                'prixkm' => $this->input->post('distanceprix'),
            );
            if ($this->m_lignes->update($lgid, $arrayedit) != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                
                redirect('lignes/' . $this->session->company->ekey);
            }
        }
        
        public function itineraire($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• LISTES DES ITINERAIRES<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                
                //$this->property['itineraires'] = $this->m_itineraire->get($this->company->id_entreprise);

                $this->property['itineraires'] = $this->m_ligne_itineraire->get($this->company->id_entreprise);
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
                }
                else
                {
                    $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise);
                }
                $this->property['garedeparts'] = $this->m_gare_depart->get($this->company->id_entreprise);
                $this->property['garearrivees'] = $this->m_gare_arrivee->getad($this->company->id_entreprise);
                return $this->layout->view('_ligne/index', $this->property);
        }

        public function additine($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $gare_posd = strpos($this->input->post('garedepart'), '.');
            
            $sub_gcod = substr($this->input->post('garedepart'), 0, $gare_posd);
            $sub_direction = substr($this->input->post('garedepart'), $gare_posd + 1, strlen($this->input->post('garedepart')));
            
            $gare_posa = strpos($this->input->post('garearrivee'), '.');
            
            $sub_gcoda = substr($this->input->post('garearrivee'), 0, $gare_posa);
            $directionar = substr($this->input->post('garearrivee'), $gare_posa + 1, strlen($this->input->post('garearrivee')));

            $gare_posd1 = strpos($this->input->post('garedepartsecond'), '.');
            
            $sub_gcod1 = substr($this->input->post('garedepartsecond'), 0, $gare_posd1);
            $sub_direction1 = substr($this->input->post('garedepartsecond'), $gare_posd1 + 1, strlen($this->input->post('garedepartsecond')));
            
            $gare_posa1 = strpos($this->input->post('garearriveesecond'), '.');
            
            $sub_gcoda1 = substr($this->input->post('garearriveesecond'), 0, $gare_posa1);
            $directionar1 = substr($this->input->post('garearriveesecond'), $gare_posa1 + 1, strlen($this->input->post('garearriveesecond')));
                 $gare_posd2 = strpos($this->input->post('garedepartsecond2'), '.');
                
                $sub_gcod2 = substr($this->input->post('garedepartsecond2'), 0, $gare_posd2);
                $sub_direction2 = substr($this->input->post('garedepartsecond2'), $gare_posd2 + 1, strlen($this->input->post('garedepartsecond2')));
                
                $gare_posa2 = strpos($this->input->post('garearrivee2'), '.');
                
                $sub_gcoda2 = substr($this->input->post('garearrivee2'), 0, $gare_posa2);
                $directionar2 = substr($this->input->post('garearrivee2'), $gare_posa2 + 1, strlen($this->input->post('garearrivee2')));

                $gare_posd3 = strpos($this->input->post('garedepartsecond3'), '.');
                
                $sub_gcod3 = substr($this->input->post('garedepartsecond3'), 0, $gare_posd3);
                $sub_direction3 = substr($this->input->post('garedepartsecond3'), $gare_posd3 + 1, strlen($this->input->post('garedepartsecond3')));
                
                $gare_posa3 = strpos($this->input->post('garearriveesecond3'), '.');
                
                $sub_gcoda3 = substr($this->input->post('garearriveesecond3'), 0, $gare_posa3);
                $directionar3 = substr($this->input->post('garearriveesecond3'), $gare_posa3 + 1, strlen($this->input->post('garearriveesecond3')));
            
            if($this->input->post('garedepartsecond2') != NULL AND $this->input->post('garedepartsecond3') != NULL){
                $arrayitin = array(
                'code_itineraires' => $sub_gcod. '-' .$sub_gcoda,
                'nom_itineraires' => $sub_direction. '-' .$directionar,
                'depart_itine' =>  $sub_direction,
                'arrive_itine' => $directionar,
                );
                 $itid = $this->m_itineraire->create($arrayitin);
                 
                 $arrayitin1 = array(
                    'code_itineraires' => $sub_gcod1. '-' .$sub_gcoda1,
                    'nom_itineraires' => $sub_direction1. '-' .$directionar1,
                    'depart_itine' =>  $sub_direction1,
                    'arrive_itine' => $directionar1,
                );

                $itid1 = $this->m_itineraire->create($arrayitin1);

                //3em et 4eme
                $arrayitin2 = array(
                    'code_itineraires' => $sub_gcod2. '-' .$sub_gcoda2,
                    'nom_itineraires' => $sub_direction2. '-' .$directionar2,
                    'depart_itine' =>  $sub_direction2,
                    'arrive_itine' => $directionar2,
                );
                 $itid2 = $this->m_itineraire->create($arrayitin2);
                 
                 $arrayitin3 = array(
                    'code_itineraires' => $sub_gcod3. '-' .$sub_gcoda3,
                    'nom_itineraires' => $sub_direction3. '-' .$directionar3,
                    'depart_itine' =>  $sub_direction3,
                    'arrive_itine' => $directionar3,
                );

                $itid3 = $this->m_itineraire->create($arrayitin3);

                $arrayitinlg = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid,
                );
                $blg = $this->m_ligne_itineraire->create($arrayitinlg);

                $arrayitinlg1 = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid1,
                );
                $blg1 = $this->m_ligne_itineraire->create($arrayitinlg1);

                $arrayitinlg2 = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid2,
                );
                $blg2 = $this->m_ligne_itineraire->create($arrayitinlg2);

                $arrayitinlg3 = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid3,
                );
                $blg3 = $this->m_ligne_itineraire->create($arrayitinlg3);
            }

            if($this->input->post('garedepartsecond2') != NULL)
            {
                 $arrayitin = array(
                'code_itineraires' => $sub_gcod. '-' .$sub_gcoda,
                'nom_itineraires' => $sub_direction. '-' .$directionar,
                'depart_itine' =>  $sub_direction,
                'arrive_itine' => $directionar,
                );
                 $itid = $this->m_itineraire->create($arrayitin);
                 
                 $arrayitin1 = array(
                    'code_itineraires' => $sub_gcod1. '-' .$sub_gcoda1,
                    'nom_itineraires' => $sub_direction1. '-' .$directionar1,
                    'depart_itine' =>  $sub_direction1,
                    'arrive_itine' => $directionar1,
                );

                $itid1 = $this->m_itineraire->create($arrayitin1);

                //3em et 4eme
                $arrayitin2 = array(
                    'code_itineraires' => $sub_gcod2. '-' .$sub_gcoda2,
                    'nom_itineraires' => $sub_direction2. '-' .$directionar2,
                    'depart_itine' =>  $sub_direction2,
                    'arrive_itine' => $directionar2,
                );
                 $itid2 = $this->m_itineraire->create($arrayitin2);
                 
                 
                $arrayitinlg = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid,
                );
                $blg = $this->m_ligne_itineraire->create($arrayitinlg);

                $arrayitinlg1 = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid1,
                );
                $blg1 = $this->m_ligne_itineraire->create($arrayitinlg1);

                $arrayitinlg2 = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid2,
                );
                $blg2 = $this->m_ligne_itineraire->create($arrayitinlg2);

            }
            else
            {
                $arrayitin = array(
                    'code_itineraires' => $sub_gcod. '-' .$sub_gcoda,
                    'nom_itineraires' => $sub_direction. '-' .$directionar,
                    'depart_itine' =>  $sub_direction,
                    'arrive_itine' => $directionar,
                );
                 $itid = $this->m_itineraire->create($arrayitin);
                 
                 $arrayitin1 = array(
                    'code_itineraires' => $sub_gcod1. '-' .$sub_gcoda1,
                    'nom_itineraires' => $sub_direction1. '-' .$directionar1,
                    'depart_itine' =>  $sub_direction1,
                    'arrive_itine' => $directionar1,
                );

                $itid1 = $this->m_itineraire->create($arrayitin1);

                $arrayitinlg = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid,
                );
                $blg = $this->m_ligne_itineraire->create($arrayitinlg);

                $arrayitinlg1 = array(
                    'id_lignes' => $this->input->post('ligne'),
                    'ident_itineraires' => $itid1,
                );
                $blg1 = $this->m_ligne_itineraire->create($arrayitinlg1);
            }

            if ($blg != NULL) {
                $this->property['INSERT_SUCCESS'] = TRUE;
            }
            redirect('lignes/itineraire/' . $this->session->company->ekey);
        }

        public function editsous_($ckey, $lgid, $itl)
        {
            $gare_posd = strpos($this->input->post('garedepart'), '.');
            
            $sub_gcod = substr($this->input->post('garedepart'), 0, $gare_posd);
            $sub_direction = substr($this->input->post('garedepart'), $gare_posd + 1, strlen($this->input->post('garedepart')));
            
            $gare_posa = strpos($this->input->post('garearrivee'), '.');
            
            $sub_gcoda = substr($this->input->post('garearrivee'), 0, $gare_posa);
            $directionar = substr($this->input->post('garearrivee'), $gare_posa + 1, strlen($this->input->post('garearrivee')));
            
           
            $arrayitin = array(
                'code_itineraires' => $sub_gcod. '-' .$sub_gcoda,
                'nom_itineraires' => $sub_direction. '-' .$directionar,
                'depart_itine' =>  $sub_direction,
                'arrive_itine' => $directionar,
            );

            $itid = $this->m_itineraire->update($lgid, $arrayitin);

            if ($itid != FALSE) {
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('lignes/itineraire/' . $this->session->company->ekey);
            }
        }

        public function activeit($ckey, $idit, $iditlg, $stit, $stitlg)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($stit == 1 AND $stitlg == 1){

                        $stit = 0;
                        $stitlg = 0;
                    }
                    else
                    {
                        $stit = 1;
                        $stitlg = 1;
                    }

                    $upit = array(
                        'actiftine' => $stit,
                    );

                    $this->m_itineraire->update($idit, $upit);

                    $upitlg = array(
                        'actifint' => $stitlg,
                    );
                    
                    $this->m_ligne_itineraire->update($iditlg, $upitlg);

                $this->property['UPDATE_SUCCESS'] = TRUE;

                redirect('lignes/itineraire/' . $this->session->company->ekey);
        }
    }
    
    /** End of file: Lignes.php **/
    /** File location: application/controllers/Lignes.php **/
