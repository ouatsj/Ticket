<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Recettes extends MY_Controller
    {
        public $recettes;
        public $company;
        protected $property = array(
            'title' => 'Recettes',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
       public function view($ckey, $idrecet = NULL)
       {

            $this->company = $this->m_entreprises->get_key($ckey);

            $this->property['pagetitle'] .= " LES RECETTES NON VALIDEES &nbsp; <strong>{$this->company->nom_entreprise}</strong>";
            $this->property['recettes'] = $this->m_recette->get($this->company->ekey);
            $this->property['genrespersonnels'] = $this->m_type_personnel->get();
            $this->property['genres'] = $this->m_genre_recette->get();
            $this->property['typedocuments'] = $this->m_typedocument->get();
            $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
            return $this->layout->view('_recette/index', $this->property);
           
       }

       public function tripardate($ckey, $idgd, $idcais, $cpr, $sg)
       {

            $this->company = $this->m_entreprises->get_key($ckey);
            if (!$this->company) {
                $session_company = $this->session->userdata('company');
                if (is_object($session_company) && !empty($session_company->ekey)) {
                    $this->company = $this->m_entreprises->get_key($session_company->ekey);
                }
            }

            if (!$this->company) {
                show_404();
                return;
            }

            $d = trim((string) $this->input->post('datedebut'));
            $f = trim((string) $this->input->post('datefin'));
            $co = $this->input->post('_compag');

            $date_debut = DateTime::createFromFormat('!Y-m-d', $d);
            $date_fin = DateTime::createFromFormat('!Y-m-d', $f);
            $dates_valides = $date_debut
                && $date_fin
                && $date_debut->format('Y-m-d') === $d
                && $date_fin->format('Y-m-d') === $f
                && $date_debut <= $date_fin;

            if (!$dates_valides) {
                $this->session->set_flashdata(
                    'error',
                    'Veuillez choisir une date de début et une date de fin valides.'
                );
                redirect(
                    'caisses/' . $this->company->ekey
                    . '/gTv/' . $idgd
                    . '/' . (int) $idcais
                    . '/recette/' . (int) $cpr
                    . '/' . (int) $sg
                    . '/' . mdate('%d/%m/%Y', now('UTC'))
                );
                return;
            }

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $idgd, $sg);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $idgd, $cpr);
            $this->property['conex'] = $conex;
            $this->property['pagetitle'] .= " LES RECETTES&nbsp; <strong>{$d} au &nbsp;{$f}</strong>";
            $this->property['recettes'] = $this->m_recette->getrecettrisss($this->company->ekey, $idcais, $idgd, $cpr, $d, $f, $co);
            $this->property['genrespersonnels'] = $this->m_type_personnel->get();
            $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
            $this->property['typedocuments'] = $this->m_typedocument->get();
            $this->property['compagnies'] = $this->m_compagnies->get();
            $caisseident = $this->m_caisse->get($this->company->id_entreprise, $idgd, $idcais);
            $this->property['caisseident'] = $caisseident;
            return $this->layout->view('_recette/indexpardate', $this->property);
           
       }

       public function triadjoint($ckey, $idgd, $idcais, $cpr, $sg)
       {

            $this->company = $this->m_entreprises->get_key($ckey);
            $d = $this->input->post('datedebut');
            $f = $this->input->post('datefin');
            $co = $this->input->post('_compag');
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $idgd, $sg);
            $this->property['bus_stop'] = $bus_stop;
            
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $idgd, $cpr);
            $this->property['conex'] = $conex;

            $this->property['pagetitle'] .= " LES RECETTES&nbsp; <strong>{$d} au &nbsp;{$f}</strong>";
            $this->property['recettes'] = $this->m_recette->getupdate($this->company->ekey, $idcais, $idgd, $cpr, $d, $f, $co);
            $this->property['genrespersonnels'] = $this->m_type_personnel->get();
            $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
            $this->property['typedocuments'] = $this->m_typedocument->get();
            $this->property['compagnies'] = $this->m_compagnies->get();
            $caisseident = $this->m_caisse->get($this->company->id_entreprise, $idgd, $idcais);
            $this->property['caisseident'] = $caisseident;
            return $this->layout->view('_recette/update_index', $this->property);
           
       }
       

       //enregistrement des recettes
        public function add($ckey)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');  

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            
            if($this->input->post('daterecep')!= '')
            {
                if( $this->input->post('genre') != '' AND $this->input->post('_compag') != '' AND $this->input->post('interne') != '')
                {
                    if($this->input->post('client_infos')!= ''){
                        $arrayrecette = array(
                        'idcaisse' => $this->input->post('idcaisse'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $this->input->post('_compag'),
                        'type_recet' => $this->input->post('interne'),
                        'idopera' => $iduser,
                        'recetsgid' => $sgid,
                        'nom' => $this->input->post('client_infos'),
                        'montant_recet' => $this->input->post('montantverse'),
                        'commentaire_recet' => $this->input->post('comment'),
                        'date_recet' => $this->input->post('daterecep'),
                        'createdrecet_at' => now('UTC'),
                        );
                        $recette = $this->m_recette->create($arrayrecette);
                    }

                    if($this->input->post('personnel_infos')!= ''){
                        $arrayrecette = array(
                        'idcaisse' => $this->input->post('idcaisse'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $this->input->post('_compag'),
                        'type_recet' => $this->input->post('interne'),
                        'idopera' => $iduser,
                        'recetsgid' => $sgid,
                        'nom' => $this->input->post('personnel_infos'),
                        'montant_recet' => $this->input->post('montantverse'),
                        'commentaire_recet' => $this->input->post('comment'),
                        'date_recet' => $this->input->post('daterecep'),
                        'createdrecet_at' => now('UTC'),
                        );
                        $recette = $this->m_recette->create($arrayrecette);
                    }
                    
                    if($this->input->post('persoclient') === 'perso' AND $this->input->post('personnel_infos') === '')
                    {
                        $arrayperso = array(
                            'matricule' => $this->input->post('perso_mat'),
                            'type_perso' => $this->input->post('typeperso'),
                            'compagnie_perso' => $this->input->post('compag'),
                            'adressepers' => $this->input->post('perso_adresse'),
                            'nomprenom_perso' => $this->input->post('perso_nom'),
                            'cat_permis' => $this->input->post('categ_permis'),
                            'contact_perso' =>$this->input->post('premiercontact'),
                            'contact2' =>$this->input->post('secondcontact'),
                            'pieces1' =>$this->input->post('permis'),
                            'pieces2' =>$this->input->post('cnib'),
                            'date_delivre1' =>$this->input->post('date_permis'),
                            'date_delivre2' =>$this->input->post('date_cnib'),
                            'date_expire1' =>$this->input->post('date_expire'),
                            'date_expire2' =>$this->input->post('expire_cnib'),
                            'dates_create' => now('UTC'),

                        );

                        $perso = $this->m_personnels->create($arrayperso);

                        $arrayrecette = array(
                        'idcaisse' => $this->input->post('idcaisse'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $this->input->post('_compag'),
                        'type_recet' => $this->input->post('interne'),
                        'idopera' => $iduser,
                        'recetsgid' => $sgid,
                        'nom' => $this->input->post('perso_nom'),
                        'montant_recet' => $this->input->post('montantverse'),
                        'commentaire_recet' => $this->input->post('comment'),
                        'date_recet' => $this->input->post('daterecep'),
                        'createdrecet_at' => now('UTC'),
                        );

                        if($perso != NULL){

                            $recette = $this->m_recette->create($arrayrecette);

                        }
                        
                    }
                    if($this->input->post('persoclient') === 'autrepersonnel' AND $this->input->post('client_infos') === ''){

                        $argv = array(
                            'nom_client' => $this->input->post('ruclient'),
                            'type_client' => $this->input->post('persoclient'),
                            'prenom_client' => $this->input->post('prclient'),
                            'contact_client' => $this->input->post('perso_mat'),
                            'date_delivre' => mdate("%Y-%m-%d", now('UTC')),
                            'lieu_delivre' => $this->input->post('lieu'),
                            'datedoc' => mdate("%Y-%m-%d", now('UTC')),
                        );
                        $clhid = $this->m_client->create($argv);

                        $arrayrecette = array(
                        'idcaisse' => $this->input->post('idcaisse'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $this->input->post('_compag'),
                        'type_recet' => $this->input->post('interne'),
                        'idopera' => $iduser,
                        'recetsgid' => $sgid,
                        'nom' => $this->input->post('ruclient').' '.$this->input->post('prclient'),
                        'montant_recet' => $this->input->post('montantverse'),
                        'commentaire_recet' => $this->input->post('comment'),
                        'date_recet' => $this->input->post('daterecep'),
                        'createdrecet_at' => now('UTC'),
                        );

                        if($clhid != NULL){

                            $recette = $this->m_recette->create($arrayrecette);
                        }
                        
                    }

                    if($this->input->post('persoclient') === 'client' AND $this->input->post('client_infos') === '')
                    {

                            $argv = array(
                                'nom_client' => $this->input->post('ruclient'),
                                'type_client' => $this->input->post('persoclient'),
                                'prenom_client' => $this->input->post('prclient'),
                                'contact_client' => $this->input->post('perso_mat'),
                                'date_delivre' => mdate("%Y-%m-%d", now('UTC')),
                                'lieu_delivre' => $this->input->post('lieu'),
                                'datedoc' => mdate("%Y-%m-%d", now('UTC')),
                            );

                            $clhid1 = $this->m_client->create($argv);

                            $arrayrecette = array(
                            'idcaisse' => $this->input->post('idcaisse'),
                            'id_genre_recet' => $this->input->post('genre'),
                            'compkey_recet' => $this->input->post('_compag'),
                            'type_recet' => $this->input->post('interne'),
                            'idopera' => $iduser,
                            'recetsgid' => $sgid,
                            'nom' => $this->input->post('ruclient').' '.$this->input->post('prclient'),
                            'montant_recet' => $this->input->post('montantverse'),
                            'commentaire_recet' => $this->input->post('comment'),
                            'date_recet' => $this->input->post('daterecep'),
                            'createdrecet_at' => now('UTC'),
                            );
                            

                            if($clhid1 != NULL){

                                $recette = $this->m_recette->create($arrayrecette);
                            }
                    }
                        
                    if(recette_role_is_validateur_principal($this->session->agent->userole))
                    {
                        $upargv = array(
                            'active_recet' => 1, 
                            'is_validerecet' => 1, 
                            'is_actifrecet' => 1,
                            'operavalid' => $iduser,
                        );
                        $this->m_recette->update($recette, $upargv);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    }

                    if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                    {
                        $upargv = array(
                            'active_recet' => 1, 
                            'is_validerecet' => 1, 
                            'is_actifrecetad' => 1,
                            'operavalidad' => $iduser,
                        );
                        $this->m_recette->update($recette, $upargv);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    }
                    else 
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/recette_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
                redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
        }
        //modifier les recettes
        public function updaterecette($ckey, $recet)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');    
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            if($this->input->post('daterecep')!= '')
            {
               
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idcaisse'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('_compag'),
                    'type_recet' => $this->input->post('interne'),
                    'nom' => $this->input->post('nommodifier'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                );
                $recette = $this->m_recette->update($recet, $arrayrecette);
                        
                $this->property['UPDATE_SUCCESS'] = TRUE;
                
                if($this->session->agent->userole === '4' OR $this->session->agent->userole === '18')
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }

                else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/recette_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
                redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            
        }

        //validation comptable

        public function valrecette($ckey, $recet, $ucn)
        {

            $this->company = $this->m_entreprises->get_key($ckey);

            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse');


            $identifiant_sousgare = $this->input->post('idsousgar'); 

            $gare_stop = $this->m_sousgare->sget($this->company->ekey, $identifiant_gare, $identifiant_sousgare);
            $cashboxContext = roleattribut_guard_main_cashbox_validation_context(
                $this->company->ekey,
                $identifiant_gare,
                $ucn
            );
            if ($cashboxContext) {
                roleattribut_guard_assert_main_cashbox_operation(
                    'recette',
                    $recet,
                    $this->company->ekey,
                    $identifiant_gare,
                    $cashboxContext['caissier_ra']
                );
            }
                

            $this->property['gare_stop'] = $gare_stop;
            
                
            $cp = $this->input->post('_compagr');
            $d1 = $this->input->post('datedebutsr');
            $d2 = $this->input->post('datefinsr');
            $con = $this->input->post('iduseconr');

            $arrayrecette = array(
                'commentaire_recet' => $this->input->post('comment'),
                'valid_cptablerecet' => 1,
                'opvalid_cptablerecet' => roleattribut_guard_session_ra(),
            );

            $recette = $this->m_recette->update($recet, $arrayrecette);
            if ($cashboxContext) {
                redirect(
                    'utilisateurs/' . $this->company->ekey
                    . '/caisseprincrecette/' . $identifiant_gare
                    . '/' . $cashboxContext['consultant_ra']
                    . '/' . $identifiant_sousgare
                    . '/' . $cashboxContext['caissier_ra']
                    . '/' . mdate('%d/%m/%Y', now('UTC'))
                );
                return;
            }
            

            $conex = $this->m_compte_user->getusergare($this->company->ekey, $identifiant_gare, $identifiant_use);
                $this->property['conex'] = $conex;

                $connex = $this->m_compte_user->getusergar($this->company->ekey, $identifiant_gare, $con);
                $this->property['connex'] = $connex;

            $this->property['trirecettes'] = $this->m_recette->validget1($this->company->ekey, $identifiant_gare, $cp, $d1, $d2, $con);

            $this->property['compagnies'] = $this->m_compagnies->get();
            
               $this->property['dat1'] = $d1;

                $this->property['dat2'] = $d2;

                $this->property['cpe'] = $cp;

                $this->property['uop'] = $con;

            $this->property['pagetitle'] .= "• VALIDATION DES RECETTES•&nbsp;{$gare_stop->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                return $this->layout->view('_caisse/trivaldrecette', $this->property);


            //redirect('Utilisateurs/recettecaissecptable/'.$this->session->company->ekey. '/'. $identifiant_gare. '/'. $identifiant_use.'/'.$identifiant_sousgare);
        }

        public function valrecettess($ckey)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
                 
                $cp = $this->input->post('compags');
                $d1 = $this->input->post('datedebuts');
                $d2 = $this->input->post('datefins'); 
                $valis = $this->input->post('nameval');
                $ucn = $this->input->post('iduseca');

                $cp1 = $this->input->post('_compagrr');
                $d11 = $this->input->post('datedebutsrr');
                $d21 = $this->input->post('datefinsrr');
                $con1 = $this->input->post('iduseconrr');

                $identifiant_gare = $this->input->post('idgar');
                $identifiant_sousgare = $this->input->post('idsousgar');
                $identifiant_use = $this->input->post('iduse'); 
                
                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $identifiant_gare, $identifiant_sousgare);
                $this->property['gare_stop'] = $gare_stop;

                    if($valis === '1'){
                        $cfrecet10 = $this->db->query("SELECT r.id_recette, r.ferme_caisrecet, r.valid_cptablerecet, r.compkey_recet, r.ferme_caisrecet, r.recetsgid, r.date_recet FROM recette r
                        WHERE r.compkey_recet = '$cp'
                        AND r.operavalid = '$ucn'
                        AND r.ferme_caisrecet = 1
                        AND r.valid_cptablerecet = 0
                        AND r.recetsgid = '$identifiant_sousgare'
                        AND r.date_recet BETWEEN '$d1' AND '$d2'")->result();

                        foreach ($cfrecet10 as $its) {
                            $plarray = array(
                                'valid_cptablerecet' => 1,
                                'opvalid_cptablerecet' => roleattribut_guard_session_ra(),
                            );
                            $vald_recet = $this->m_recette->update($its->id_recette, $plarray);
                        }
                    }
                    if($valis === '0')
                    {

                        $cfrecet10 = $this->db->query("SELECT r.id_recette, r.ferme_caisrecet, r.valid_cptablerecet, r.compkey_recet, r.ferme_caisrecet, r.recetsgid, r.date_recet FROM recette r
                        WHERE r.compkey_recet = '$cp'
                        AND r.operavalid = '$ucn'
                        AND r.ferme_caisrecet = 1
                        AND r.recetsgid = '$identifiant_sousgare'
                        AND r.date_recet BETWEEN '$d1' AND '$d2'")->result();

                        foreach ($cfrecet10 as $its) {
                            $plarray = array(
                                'ferme_caisrecet' => 0,
                                'valid_cptablerecet' => 0,
                            );
                            $vald_recet = $this->m_recette->update($its->id_recette, $plarray);
                        }
                        
                    }

                $this->property['trirecettes'] = $this->m_recette->validget1($this->company->ekey, $identifiant_gare, $cp1, $d11, $d21, $con1);

                $conex = $this->m_compte_user->getusergare($this->company->ekey, $identifiant_gare, $identifiant_use);
                $this->property['conex'] = $conex;

                $connex = $this->m_compte_user->getusergar($this->company->ekey, $identifiant_gare, $con1);
                $this->property['connex'] = $connex;
            
            $this->property['compagnies'] = $this->m_compagnies->get();

            $this->property['dat1'] = $d11;

                $this->property['dat2'] = $d21;

                $this->property['cpe'] = $cp1;

                $this->property['uop'] = $con1;
            
            $this->property['pagetitle'] .= "• VALIDATION DES RECETTES•&nbsp;{$gare_stop->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                return $this->layout->view('_caisse/trivaldrecette', $this->property);
            
           
            //redirect('Utilisateurs/recettecaissecptable/'.$this->session->company->ekey. '/'. $identifiant_gare. '/'. $identifiant_use.'/'.$identifiant_sousgare);
            
        }

        public function rejetrecettes($ckey, $recet, $ucn)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse');
            $identifiant_sousgare = $this->input->post('idsousgar');

            $cp = $this->input->post('_compagr');
            $d1 = $this->input->post('datedebutsr');
            $d2 = $this->input->post('datefinsr');
            $con = $this->input->post('iduseconr');

            $gare_stop = $this->m_sousgare->sget($this->company->ekey, $identifiant_gare, $identifiant_sousgare);
                $this->property['gare_stop'] = $gare_stop;
            $cashboxContext = roleattribut_guard_main_cashbox_validation_context(
                $this->company->ekey,
                $identifiant_gare,
                $ucn
            );
            if ($cashboxContext) {
                roleattribut_guard_assert_main_cashbox_operation(
                    'recette',
                    $recet,
                    $this->company->ekey,
                    $identifiant_gare,
                    $cashboxContext['caissier_ra']
                );
            }

            $arrayrecette = array(
                'commentaire_recet' => $this->input->post('comment'),
                'ferme_caisrecet' => 0,
            );
            $recette = $this->m_recette->update($recet, $arrayrecette);
            if ($cashboxContext) {
                redirect(
                    'utilisateurs/' . $this->company->ekey
                    . '/caisseprincrecette/' . $identifiant_gare
                    . '/' . $cashboxContext['consultant_ra']
                    . '/' . $identifiant_sousgare
                    . '/' . $cashboxContext['caissier_ra']
                    . '/' . mdate('%d/%m/%Y', now('UTC'))
                );
                return;
            }
                       
            $this->property['UPDATE_SUCCESS'] = TRUE;
            
            $this->property['compagnies'] = $this->m_compagnies->get();

            $this->property['trirecettes'] = $this->m_recette->validget1($this->company->ekey, $identifiant_gare, $cp, $d1, $d2, $con);
            
            $this->property['pagetitle'] .= "• VALIDATION DES RECETTES•&nbsp;{$gare_stop->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                return $this->layout->view('_caisse/trivaldrecette', $this->property);

            //redirect('Utilisateurs/recettecaissecptable/'.$this->session->company->ekey. '/'. $identifiant_gare. '/'. $identifiant_use.'/'.$identifiant_sousgare);
        }
       
       
        public function nom_genre($idgr)
        {
            $pnom = $this->m_recette->typenom($idgr);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $pnom));
        }

     
        //tri de recette caisse principale
        public function listegenre($ty)
        {
            $ptype = $this->m_recette->typegenreinterne($this->session->company->ekey, $ty);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $ptype));
        }

        public function listenom($ica, $tprecet, $nom)
        {
            $pnom = $this->m_recette->typenominterne($this->session->company->ekey, $ica, $tprecet, $nom);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $pnom));
        }

    }
    
    /** End of file: Recettes.php **/
    /** File location: application/controllers/Recettes.php **/
