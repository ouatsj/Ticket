<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Depenses extends MY_Controller
    {
        public $depenses;
        public $company;
        protected $property = array(
            'title' => 'Depenses',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
       public function view($ckey, $iddepense = NULL)
       {

            $this->company = $this->m_entreprises->get_key($ckey);

            $this->property['pagetitle'] .= " LES DEPENSES NON VALIDEES &nbsp; <strong>{$this->company->nom_entreprise}</strong>";
            $this->property['depenses'] = $this->m_depense->getdepense($this->company->ekey);
            $this->property['genres'] = $this->m_genre_depense->get();
            $this->property['genrespersonnels'] = $this->m_type_personnel->get();
            $this->property['typedocuments'] = $this->m_typedocument->get();
            $this->property['fournisseurs'] = $this->m_client->get();
            $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
            return $this->layout->view('_depense/index', $this->property);
           
       }

       public function tripardate($ckey, $idgd, $idcais)
       {

            $this->company = $this->m_entreprises->get_key($ckey);
            $d = $this->input->post('datedebut');
            $f = $this->input->post('datefin');
            $cop = $this->input->post('_compag');
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $this->property['pagetitle'] .= " LES DEPENSES &nbsp; <strong>{$d} au &nbsp;{$f}</strong>";
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $idgd, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $idgd, $iduser);
            $this->property['conex'] = $conex;
            $this->property['depenses'] = $this->m_depense->getsdepen($this->company->ekey, $idcais, $idgd, $iduser, $d, $f, $cop);
			$caisseident = $this->m_caisse->get($this->company->id_entreprise, $idgd, $idcais);
              $this->property['caisseident'] = $caisseident;
			  $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $idcais, $idgd, $iduser);
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $idcais, $idgd, $iduser);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $idcais, $idgd, $iduser);
                    $this->property['fournisseurs'] = $this->m_client->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $idcais, $idgd, $iduser);
                    $this->property['genres'] = $this->m_genre_depense->get();
            $this->property['depotcaisse'] = $this->m_depot->depotinterne($this->company->ekey, $idcais, $idgd, $iduser);
            $this->property['genrespersonnels'] = $this->m_type_personnel->get();
			$this->property['compagnies'] = $this->m_compagnies->get();
            $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
            return $this->layout->view('_depense/indexpardate', $this->property);
           
       }
       public function triadjointdate($ckey, $idgd, $idcais)
       {

            $this->company = $this->m_entreprises->get_key($ckey);
            $d = $this->input->post('datedebut');
            $f = $this->input->post('datefin');
            $cop = $this->input->post('_compag');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $this->property['pagetitle'] .= " LES DEPENSES &nbsp; <strong>{$d} au &nbsp;{$f}</strong>";
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $idgd, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $idgd, $iduser);
            $this->property['conex'] = $conex;
            $this->property['depenses'] = $this->m_depense->getadjointdepen($this->company->ekey, $idcais, $gid, $iduser, $d, $f, $cop);
			$caisseident = $this->m_caisse->get($this->company->id_entreprise, $idgd, $idcais);
              $this->property['caisseident'] = $caisseident;
			  $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $idcais, $idgd);
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $idcais, $idgd);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $idcais, $idgd);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $idcais, $idgd);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $idcais, $iduser, $idgd);
            $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne1($this->company->ekey, $idgd, $idcais, $iduser);
            $this->property['genrespersonnels'] = $this->m_type_personnel->get();
			$this->property['compagnies'] = $this->m_compagnies->get();
            $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
            return $this->layout->view('_depense/indexadjoint', $this->property);
           
       }

       //enregistrement des depenses
       public function add($ckey)
       {

            $this->company = $this->m_entreprises->get_key($ckey);   
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse'); 
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('datereception')!= '')
            {
                if( $this->input->post('genredep') != '' AND $this->input->post('_compag') != '' AND $this->input->post('typerson') != '' AND $this->input->post('internedep')!= '')
                {
                    if($this->input->post('client_infos')!= ''){
                        $arraydeps = array(
                            'idcaisse_depens' => $identifiant_caisse,
                            'id_genre_depense' => $this->input->post('genredep'),
                            'compkey_dep' => $this->input->post('_compag'),
                            'typpersonel' => $this->input->post('typerson'),
                            'idop_dep' => $iduser,
                            'sousgidepens' => $sgid,
                            'type_depense' => $this->input->post('internedep'),
                            'nom_perso' => $this->input->post('client_infos'),
                            'montant_depens' => $this->input->post('montantversedep'),
                            'commentaire' => $this->input->post('commentdep'),
                            'motif' => $this->input->post('motifs'),
                            'date_depens' => $this->input->post('datereception'),
                            'createddep_at' => now('UTC'),
                        );
                        $depense = $this->m_depense->create($arraydeps);
                    }
                    if($this->input->post('personnel_infos')!= ''){
                        $arraydeps = array(
                            'idcaisse_depens' => $identifiant_caisse,
                            'id_genre_depense' => $this->input->post('genredep'),
                            'compkey_dep' => $this->input->post('_compag'),
                            'typpersonel' => $this->input->post('typerson'),
                            'idop_dep' => $iduser,
                            'sousgidepens' => $sgid,
                            'type_depense' => $this->input->post('internedep'),
                            'nom_perso' => $this->input->post('personnel_infos'),
                            'montant_depens' => $this->input->post('montantversedep'),
                            'commentaire' => $this->input->post('commentdep'),
                            'motif' => $this->input->post('motifs'),
                            'date_depens' => $this->input->post('datereception'),
                            'createddep_at' => now('UTC'),
                        );
                        $depense = $this->m_depense->create($arraydeps);
                    }
                    if($this->input->post('client_perso') === 'perso' AND $this->input->post('personnel_infos') === '')
                    {

                            $arrayperso = array(
                                'matricule' => $this->input->post('_matperso'),
                                'type_perso' => $this->input->post('typerson'),
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


                            $arraydeps = array(
                                'idcaisse_depens' => $identifiant_caisse,
                                'id_genre_depense' => $this->input->post('genredep'),
                                'compkey_dep' => $this->input->post('_compag'),
                                'typpersonel' => $this->input->post('typerson'),
                                'idop_dep' => $iduser,
                                'sousgidepens' => $sgid,
                                'type_depense' => $this->input->post('internedep'),
                                'nom_perso' => $this->input->post('perso_nom'),
                                'montant_depens' => $this->input->post('montantversedep'),
                                'commentaire' => $this->input->post('commentdep'),
                                'motif' => $this->input->post('motifs'),
                                'date_depens' => $this->input->post('datereception'),
                                'createddep_at' => now('UTC'),
                            );
                            if($perso != NULL){

                                $depense = $this->m_depense->create($arraydeps);
                            }
                     
                    }
                    if($this->input->post('client_perso') === 'client' AND $this->input->post('client_infos') === ''){
                            $arraydeps = array(
                                'idcaisse_depens' => $identifiant_caisse,
                                'id_genre_depense' => $this->input->post('genredep'),
                                'compkey_dep' => $this->input->post('_compag'),
                                'typpersonel' => $this->input->post('typerson'),
                                'idop_dep' => $iduser,
                                'type_depense' => $this->input->post('internedep'),
                                'nom_perso' => $this->input->post('ruclient').' '.$this->input->post('prclient'),
                                'montant_depens' => $this->input->post('montantversedep'),
                                'commentaire' => $this->input->post('commentdep'),
                                'motif' => $this->input->post('motifs'),
                                'date_depens' => $this->input->post('datereception'),
                                'createddep_at' => now('UTC'),
                            );
                        
                            $argv = array(
                                'nom_client' => $this->input->post('ruclient'),
                                'type_client' => $this->input->post('client_perso'),
                                'prenom_client' => $this->input->post('prclient'),
                                'contact_client' => $this->input->post('_matperso'),
                                'date_delivre' => mdate("%Y-%m-%d", now('UTC')),
                                'lieu_delivre' => $this->input->post('lieu'),
                                'datedoc' => mdate("%Y-%m-%d", now('UTC')),
                            );
                            $clhid = $this->m_client->create($argv);
                            
                            if($clhid != NULL){

                                $depense = $this->m_depense->create($arraydeps);
                            }        
                    }
                    if($this->input->post('client_perso') === 'autrepersonnel' AND $this->input->post('client_infos') === ''){

                            $argv1 = array(
                                'nom_client' => $this->input->post('ruclient'),
                                'type_client' => $this->input->post('client_perso'),
                                'prenom_client' => $this->input->post('prclient'),
                                'contact_client' => $this->input->post('_matperso'),
                                'date_delivre' => mdate("%Y-%m-%d", now('UTC')),
                                'lieu_delivre' => $this->input->post('lieu'),
                                'datedoc' => mdate("%Y-%m-%d", now('UTC')),
                            );
                            $clhid1 = $this->m_client->create($argv1);

                            $arraydeps = array(
                                'idcaisse_depens' => $identifiant_caisse,
                                'id_genre_depense' => $this->input->post('genredep'),
                                'compkey_dep' => $this->input->post('_compag'),
                                'typpersonel' => $this->input->post('typerson'),
                                'idop_dep' => $iduser,
                                'sousgidepens' => $sgid,
                                'type_depense' => $this->input->post('internedep'),
                                'nom_perso' => $this->input->post('ruclient').' '.$this->input->post('prclient'),
                                'montant_depens' => $this->input->post('montantversedep'),
                                'commentaire' => $this->input->post('commentdep'),
                                'motif' => $this->input->post('motifs'),
                                'date_depens' => $this->input->post('datereception'),
                                'createddep_at' => now('UTC'),
                            );
                        
                            if($clhid1 != NULL){
                                $depense = $this->m_depense->create($arraydeps);
                            }
                    }
                    if(recette_role_is_validateur_principal($this->session->agent->userole))
                    {
                        $updeps = array(
                            'active_dep' => 1, 
                            'is_validedep' => 1, 
                            'is_actifdep' => 1,
                            'opevalid' => $iduser,
                        );
                        $this->m_depense->update($depense, $updeps);

                            $this->property['UPDATE_SUSSESS'] = TRUE;
                        redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depense/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        
                    }

                    if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                    {
                        $updeps = array(
                            'active_dep' => 1, 
                            'is_validedep' => 1, 
                            'is_actifdepad' => 1,
                            'opevalidad' => $iduser,
                        );
                        $this->m_depense->update($depense, $updeps);

                            $this->property['UPDATE_SUSSESS'] = TRUE;
                        redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depense/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        
                    }
                    else
                       
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser.'/depense_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
         
        }
        //mofification depense
        public function updatedepense($ckey, $dep)
        {
                $this->company = $this->m_entreprises->get_key($ckey);  
                $identifiant_gare = $this->input->post('idgarecode');
                $identifiant_caisse = $this->input->post('idcaisse');   

                $gid = $this->input->post('gareconnect');
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');

                if($this->input->post('datereception')!= '')
                {
                    $arraydep = array(
                        'id_genre_depense' => $this->input->post('genredep'),
                        'type_depense' => $this->input->post('internedep'),
                        'compkey_dep' => $this->input->post('_compag'),
                        'typpersonel' => $this->input->post('typerson'),
                        'nom_perso' => $this->input->post('nomdepmodifier'),
                        'montant_depens' => $this->input->post('montantversedep'),
                        'commentaire' => $this->input->post('commentdep'),
                        'motif' => $this->input->post('motifs'),
                        'date_depens' => $this->input->post('datereception'),
                    );
                    $depens = $this->m_depense->update($dep, $arraydep);
                    
                    if($this->session->agent->userole === '4' OR $this->session->agent->userole === '18')
                    {
                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depense/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    }
                    else
                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/depense_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
        
                }
                else
                redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                
        }

        public function up_datedepenses($ckey, $dep)
        {
                $this->company = $this->m_entreprises->get_key($ckey);  
                $identifiant_gare = $this->input->post('idgarecode');
                $identifiant_caisse = $this->input->post('idcaisse');   
                $gid = $this->input->post('gareconnect');
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');
                if($this->input->post('datereception')!= '')
                {
                        $arraydep = array(
                        'id_genre_depense' => $this->input->post('genredep'),
                        'type_depense' => $this->input->post('internedep'),
                        'compkey_dep' => $this->input->post('_compag'),
                        'typpersonel' => $this->input->post('typerson'),
                        'nom_perso' => $this->input->post('nomdepmodifier'),
                        'montant_depens' => $this->input->post('montantversedep'),
                        'commentaire' => $this->input->post('commentdep'),
                        'motif' => $this->input->post('motifs'),
                        'date_depens' => $this->input->post('datereception'),
                    );
                    $depens = $this->m_depense->update($dep, $arraydep);
                    
                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depense/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
                }
                else
                redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                 
        }
        //valide comptable
        

         public function valdepense($ckey, $dep, $dop)
        {
                $this->company = $this->m_entreprises->get_key($ckey);  
                $identifiant_gare = $this->input->post('idgar');
                $identifiant_sousgare = $this->input->post('idsousgar');
                $identifiant_use = $this->input->post('iduse');


                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $identifiant_gare, $identifiant_sousgare);
                $this->property['gare_stop'] = $gare_stop;
                $cashboxContext = roleattribut_guard_main_cashbox_validation_context(
                    $this->company->ekey,
                    $identifiant_gare,
                    $dop
                );
                if ($cashboxContext) {
                    roleattribut_guard_assert_main_cashbox_operation(
                        'depense',
                        $dep,
                        $this->company->ekey,
                        $identifiant_gare,
                        $cashboxContext['caissier_ra']
                    );
                }
                
                $cp = $this->input->post('_compagd');
                $d1 = $this->input->post('datedebutsd');
                $d2 = $this->input->post('datefinsd');
                $con = $this->input->post('idusecond');
                  
                    $arraydep = array(
                        'commentaire' => $this->input->post('commentdep'),
                        'validcptabledep' => 1,
                        'opvalid_cptabledep' => roleattribut_guard_session_ra(),
                    );
                $depens = $this->m_depense->update($dep, $arraydep);
                if ($cashboxContext && !roleattribut_guard_has_validation_filter_dates($d1, $d2)) {
                    redirect(
                        'utilisateurs/' . $this->company->ekey
                        . '/caisseprincdepense/' . $identifiant_gare
                        . '/' . $cashboxContext['consultant_ra']
                        . '/' . $identifiant_sousgare
                        . '/' . $cashboxContext['caissier_ra']
                        . '/' . mdate('%d/%m/%Y', now('UTC'))
                    );
                    return;
                }
                
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $identifiant_gare, $identifiant_use);
                $this->property['conex'] = $conex;
                $connex = $this->m_compte_user->getusergar($this->company->ekey, $identifiant_gare, $dop);
                $this->property['connex'] = $connex;

                if ($cashboxContext) {
                    $this->property['cashbox_viewer_roleattribut'] = (int) $cashboxContext['consultant_ra'];
                    $this->property['cashbox_list_roleattribut'] = (int) $cashboxContext['consultant_ra'];
                    $this->property['cashbox_target_roleattribut'] = (int) $cashboxContext['caissier_ra'];
                }

                $this->property['compagnies'] = $this->m_compagnies->get();

                

                $this->property['dat1'] = $d1;

                $this->property['dat2'] = $d2;

                $this->property['cpe'] = $cp;

                $this->property['uop'] = $con;

                $this->property['tridepenses'] = $this->m_depense->validget1($this->company->ekey, $identifiant_gare, $cp, $d1, $d2, $dop);

                $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES•&nbsp;{$gare_stop->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                return $this->layout->view('_caisse/trivalddepens', $this->property);

            //redirect('Utilisateurs/depensecaissecptable/'.$this->session->company->ekey. '/'. $identifiant_gare. '/'. $identifiant_use.'/'.$identifiant_sousgare);
        }

        public function valdepensess($ckey)
        {
                $this->company = $this->m_entreprises->get_key($ckey);
                $cp = $this->input->post('_compags');
                $d1 = $this->input->post('datedebuts');
                $d2 = $this->input->post('datefins'); 
                $isval = $this->input->post('nameval');

                $ucn = $this->input->post('iduseca');

                $cp1 = $this->input->post('_compagrd');
                $d11 = $this->input->post('datedebutsrd');
                $d21 = $this->input->post('datefinsrd');
                $con1 = $this->input->post('iduseconrd');

                $identifiant_gare = $this->input->post('idgar');
                $identifiant_sousgare = $this->input->post('idsousgar');
                $identifiant_use = $this->input->post('iduse'); 
                
                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $identifiant_gare, $identifiant_sousgare);
                $this->property['gare_stop'] = $gare_stop;

                    if($isval === '1'){

                       
                        $cfdepe10 = $this->db->query("SELECT d.id_depense, d.validcptabledep, d.ferme_caisdep, d.compkey_dep, d.ferme_caisdep, d.sousgidepens, d.date_depens FROM depense d
                        WHERE d.compkey_dep = '$cp'
                        AND d.opevalid = '$ucn'
                        AND d.ferme_caisdep = 1
                        AND d.validcptabledep = 0
                        AND d.sousgidepens = '$identifiant_sousgare'
                        AND d.date_depens BETWEEN '$d1' AND '$d2'")->result();

                        foreach ($cfdepe10 as $ite1) {
                            $dplarray = array(
                                'validcptabledep' => 1,
                                'opvalid_cptabledep' => roleattribut_guard_session_ra(),
                            );
                            $vald_dep = $this->m_depense->update($ite1->id_depense, $dplarray);
                        }
                    }
                    if($isval === '0')
                    {

                        $cfdepe10 = $this->db->query("SELECT d.id_depense, d.validcptabledep, d.ferme_caisdep, d.compkey_dep, d.ferme_caisdep, d.sousgidepens, d.date_depens FROM depense d
                        WHERE d.compkey_dep = '$cp'
                        AND d.opevalid = '$ucn'
                        AND d.ferme_caisdep = 1
                        AND d.sousgidepens = '$identifiant_sousgare'
                        AND d.date_depens BETWEEN '$d1' AND '$d2'")->result();

                        foreach ($cfdepe10 as $ite1) {
                            $dplarray = array(
                                'validcptabledep' => 0,
                                'ferme_caisdep' => 0,
                            );
                            $vald_dep = $this->m_depense->update($ite1->id_depense, $dplarray);
                        }
                        
                    }
                
                
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $identifiant_gare, $identifiant_use);
                $this->property['conex'] = $conex;
                $connex = $this->m_compte_user->getusergar($this->company->ekey, $identifiant_gare, $ucn);
                $this->property['connex'] = $connex;

                $this->property['tridepenses'] = $this->m_depense->validget1($this->company->ekey, $identifiant_gare, $cp1, $d11, $d21, $ucn);

                $this->property['compagnies'] = $this->m_compagnies->get();

                $this->property['dat1'] = $d11;

                $this->property['dat2'] = $d21;

                $this->property['cpe'] = $cp1;

                $this->property['uop'] = $con1;

                $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES•&nbsp;{$gare_stop->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                return $this->layout->view('_caisse/trivalddepens', $this->property);

                //redirect('Utilisateurs/depensecaissecptable/'.$this->session->company->ekey. '/'. $identifiant_gare. '/'. $identifiant_use.'/'.$identifiant_sousgare);
        }

        public function rejetsdepense($ckey, $dep, $dop)
        {
            $this->company = $this->m_entreprises->get_key($ckey);  
            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse'); 
            $identifiant_sousgare = $this->input->post('idsousgar');


            $gare_stop = $this->m_sousgare->sget($this->company->ekey, $identifiant_gare, $identifiant_sousgare);
                $this->property['gare_stop'] = $gare_stop;
            $cashboxContext = roleattribut_guard_main_cashbox_validation_context(
                $this->company->ekey,
                $identifiant_gare,
                $dop
            );
            if ($cashboxContext) {
                roleattribut_guard_assert_main_cashbox_operation(
                    'depense',
                    $dep,
                    $this->company->ekey,
                    $identifiant_gare,
                    $cashboxContext['caissier_ra']
                );
            }

            $cp = $this->input->post('_compagd');
            $d1 = $this->input->post('datedebutsd');
            $d2 = $this->input->post('datefinsd');
            $con = $this->input->post('idusecond');

                $arraydep = array(
                    
                    'commentaire' => $this->input->post('commentdep'),
                    'ferme_caisdep' => 0,
                );
                $depens = $this->m_depense->update($dep, $arraydep);
                if ($cashboxContext && !roleattribut_guard_has_validation_filter_dates($d1, $d2)) {
                    redirect(
                        'utilisateurs/' . $this->company->ekey
                        . '/caisseprincdepense/' . $identifiant_gare
                        . '/' . $cashboxContext['consultant_ra']
                        . '/' . $identifiant_sousgare
                        . '/' . $cashboxContext['caissier_ra']
                        . '/' . mdate('%d/%m/%Y', now('UTC'))
                    );
                    return;
                }
                
                    
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $identifiant_gare, $identifiant_use);
                $this->property['conex'] = $conex;
                $connex = $this->m_compte_user->getusergar($this->company->ekey, $identifiant_gare, $dop);
                $this->property['connex'] = $connex;

                if ($cashboxContext) {
                    $this->property['cashbox_viewer_roleattribut'] = (int) $cashboxContext['consultant_ra'];
                    $this->property['cashbox_list_roleattribut'] = (int) $cashboxContext['consultant_ra'];
                    $this->property['cashbox_target_roleattribut'] = (int) $cashboxContext['caissier_ra'];
                }

                $this->property['compagnies'] = $this->m_compagnies->get();

                $this->property['tridepenses'] = $this->m_depense->validget1($this->company->ekey, $identifiant_gare, $cp, $d1, $d2, $dop);

                $this->property['dat1'] = $d1;

                $this->property['dat2'] = $d2;

                $this->property['cpe'] = $cp;

                $this->property['uop'] = $con;

                $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES•&nbsp;{$gare_stop->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                return $this->layout->view('_caisse/trivalddepens', $this->property);

               //redirect('Utilisateurs/depensecaissecptable/'.$this->session->company->ekey. '/'. $identifiant_gare. '/'. $identifiant_use.'/'.$identifiant_sousgare);
        }    
        
       //
        public function depense_genre($idgr)
        {
            $pnoms = $this->m_depense->typenom($idgr);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $pnoms));
        }
        
        public function versetrifour($ty)
        {
            $vtype = $this->m_versements->typgenrefourverse($this->session->company->ekey, $ty);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $vtype));
        }

        //nomclientfour
        public function fournom($typ, $grd)
        {
            $bnom = $this->m_versements->fournom($this->session->company->ekey, $typ, $grd);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $bnom));
        }

        //tri depenses
        public function listegenre($ty)
        {
            $ptype = $this->m_depense->typinternegenre1($this->session->company->ekey, $ty);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $ptype));
        }

        public function listenom($ica, $grd, $nom)
        {
            $pnom = $this->m_depense->typinternenom($this->session->company->ekey, $ica, $grd, $nom);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $pnom));
        }

        public function listesnom($nom)
        {
            $prenom = $this->m_client->getclt($nom);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $prenom));
        }

        //autre tri depense
        public function autrelistegenre($ty)
        {
            $typeautre = $this->m_depense->typautregenre($this->session->company->ekey, $ty);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $typeautre));
        }

        public function autrelistenom($grd, $nom)
        {
            $autrenom = $this->m_depense->typautrenom($this->session->company->ekey, $grd, $nom);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $autrenom));
        }
        
    }
    
    /** End of file: Depenses.php **/
    /** File location: application/controllers/Depenses.php **/
