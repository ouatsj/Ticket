<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Caisses extends CI_Controller
    {
        public $caisses;
        public $company;
        public $profil;
        protected $property = array(
            'title' => 'Caisses',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
       

       public function opts($ckey, $cdg, $cid, $type = 'recette', $cpr, $idsg, $d = FALSE, $m = FALSE, $y = FALSE)
       {
           $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $idsg);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $cdg, $cpr);
            $this->property['conex'] = $conex;

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$cdg'")->row();

           switch ($type) 
           {
                case 'arretcaisseprincipale':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['recettes'] = $this->m_recette->recet_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['recettescaisse'] = $this->m_recette->recetcais_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $cpr, $cdg);
                $this->property['depenses'] = $this->m_depense->depens_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['depensescaisse'] = $this->m_depense->depenscais_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['depots'] = $this->m_depot->depo_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['montanttotal'] = $this->m_versements->versecaisse_pr($this->company->ekey, $cid, $cdg, $cpr);
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();

                   if($sgares->sog == 1){
                       $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $cpr, $cdg, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $cpr, $cdg, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $cpr, $cdg);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $cpr, $cdg);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupb($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupb($this->company->ekey, $cpr, $cdg, 5000);

                    $passagersbisss = $this->m_passager->coptb($this->company->ekey, $cpr, $cdg, 5000);
                    
                    $this->property['passagersbisss'] = $passagersbisss;
                }
                else
                {


                    $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $cpr, $cdg, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $cpr, $cdg, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $cpr, $cdg, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $cpr, $cdg);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $cpr, $cdg);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupsbis($this->company->ekey, $cpr, $cdg, $idsg, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupsbis($this->company->ekey, $cpr, $cdg, $idsg, 5000);

                    $passagersbisss = $this->m_passager->coptb($this->company->ekey, $cpr, $cdg, 5000);
                    
                    $this->property['passagersbisss'] = $passagersbisss;
                }
                $this->property['passager_repro'] = $this->m_passager->comptrep($this->company->ekey, $cpr, $cdg);
                $this->property['passager_conf'] = $this->m_passager->comptconf($this->company->ekey, $cpr, $cdg);
                
                $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                $this->property['pagetitle'] .= "• ARRÊT COMPTE ET CAISSE<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
            return $this->layout->view('_caisse/caisseprincipale', $this->property);
            break;
                case 'recette':
                        $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                        $this->property['caisseident'] = $caisseident;
                        $this->property['recettes'] = $this->m_recette->getrecet($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                        $this->property['operateurs'] = $this->m_compte_user->getusercompte($this->company->ekey, $cdg);
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                        $this->property['sommerecettes'] = $this->m_recette->getmontant($this->company->ekey, $cid, $cdg);
                        $this->property['totalrecettes'] = $this->m_recette->getmontant1($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                        $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                        $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                        $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['pagetitle'] .= "• RECETTES INTERNE•&nbsp;<strong>{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/index', $this->property);
                break;

                case 'depense':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['depenses'] = $this->m_depense->getdepen($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                    $this->property['depotcaisse'] = $this->m_depot->depotinterne($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommesdepenses'] = $this->m_versements->totalesdepense($this->company->ekey, $cid, $cdg, $idsg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg, $idsg);
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                    $this->property['operateurs'] = $this->m_compte_user->getusercompte($this->company->ekey, $cdg);
                    $this->property['genres'] = $this->m_genre_depense->get();
                    $this->property['caisseident'] = $caisseident;

                    $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['caissemontant'] = $this->m_caisse->vers($this->company->id_entreprise, $cdg, $cid);
                    $this->property['pagetitle'] .= "• DEPENSES INTERNE• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depense/index', $this->property);
                break;

                case 'autredepense':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['autredepenses'] = $this->m_depense->getautre($this->company->ekey, $cid, $cdg);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepenses'] = $this->m_versements->totaldepenses($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['depotcaisse'] = $this->m_depot->depotinterne($this->company->ekey, $cid, $cdg, $cpr);   
                    $this->property['typedocuments'] = $this->m_typedocument->get();         
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg, $idsg);
                    $this->property['genres'] = $this->m_genre_depense->get();
                    $this->property['caisseident'] = $caisseident;
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• DEPENSES EXTERNE• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depense/autreindex', $this->property);
                break;

                case 'versement':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['versements'] = $this->m_versements->get($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
					$this->property['montantvervesbank'] = $this->m_versements->totalversementbank($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['genres'] = $this->m_genre_depot->getb();
                    $this->property['banque'] = $this->m_banque->get();
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['depotcaisse'] = $this->m_depot->ad_deptinterne($this->company->ekey, $cdg, $cid, $cpr);    
                    $this->property['typedocuments'] = $this->m_typedocument->get();                    
                    $this->property['caisseident'] = $caisseident;
                    $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• VERSEMENTS BANQUE<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_caisse/versement', $this->property);
                break;

                case 'autreversement':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['versements'] = $this->m_versements->getverpart($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['genresv'] = $this->m_genre_depot->geta();
                    $this->property['depotcaisse'] = $this->m_depot->ad_deptinterne($this->company->ekey, $cdg, $cid, $cpr); 
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();                       
                    $this->property['caisseident'] = $caisseident;
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• VERSEMENTS CLIENT<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_caisse/autreversement', $this->property);

                break;

                case 'versementfournisseur':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['versements'] = $this->m_versements->getverpart($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['genres'] = $this->m_genre_depense->get();
                    $this->property['depotcaisse'] = $this->m_depot->ad_deptinterne($this->company->ekey, $cdg, $cid, $cpr); 
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();                       
                    $this->property['caisseident'] = $caisseident;
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• VERSEMENTS FOURNISSEURS<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_caisse/versementfour', $this->property);

                case 'versementcaisse':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['versements'] = $this->m_versements->getvercais($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                    $this->property['genrespersonnels'] = $this->m_type_personnel->getsc();
                    $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                    $this->property['genrespersonnel'] = $this->m_type_personnel->getusercpg($this->company->ekey, $cdg);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['depotcaisse'] = $this->m_depot->ad_deptinterne1($this->company->ekey, $cdg, $cid, $cpr); 	
                    $this->property['typedocuments'] = $this->m_typedocument->get();			
                    $this->property['caisseident'] = $caisseident;
                    $this->property['genres'] = $this->m_genre_depot->geta();
                    $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• DEPOTS DES CAISSES <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";

                    return $this->layout->view('_caisse/versementcaisse', $this->property);
                break;

                
                case 'depot':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['depots'] = $this->m_depot->getdepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['operateurs'] = $this->m_compte_user->getusercompte($this->company->ekey, $cdg);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    //$this->property['sommesdepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg, $idsg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);

                    $this->property['genres'] = $this->m_genre_depot->getb();
                    $this->property['banque'] = $this->m_banque->get();
                    $this->property['caisseident'] = $caisseident;
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• DEPOTS INTERNE• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depot/index', $this->property);
                break;

                

                case 'depotsous':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['sousdepots'] = $this->m_depot->getsous($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['genrespersonnel'] = $this->m_type_personnel->getusercp($this->company->ekey, $cdg);
                    $this->property['genrespersonnels'] = $this->m_type_personnel->getsc();
                    $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                    $this->property['genres'] = $this->m_genre_depot->get();
                    $this->property['caisseident'] = $caisseident;
                    $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• DEPOTS SOUS CAISSE <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depot/sousindex', $this->property);
                break;

                case 'autredepot':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['autredepots'] = $this->m_depot->getautre($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['sommesdepots'] = $this->m_depot->getmontantget($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['genres'] = $this->m_genre_depot->geta();
                    $this->property['caisseident'] = $caisseident;
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• DEPOTS CLIENT• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depot/autreindex', $this->property);
                break;
            
                case 'autredepotfournisseur':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['autredepots'] = $this->m_depot->getautre($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['montantverves'] = $this->m_versements->totalversement($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['sommedepenses'] = $this->m_versements->totaldepense($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommerecettes'] = $this->m_versements->totalrecette($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepot'] = $this->m_versements->totaldepot($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['sommedepots'] = $this->m_depot->getmontant($this->company->ekey, $cid, $cpr, $cdg);
                    $this->property['genres'] = $this->m_genre_depot->geta();
                    $this->property['caisseident'] = $caisseident;
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• DEPOTS FOURNISSEURS• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                    return $this->layout->view('_depot/fourdepot', $this->property);
                break;
                case 'recetteguichet':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['vendeuses'] = $this->m_compte_user->get_user2ad($this->company->ekey);
                    }else{
                        $this->property['vendeuses'] = $this->m_compte_user->get_user2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES DU GUICHET<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/view_vendeuse', $this->property);
                break;

                case 'recetteguichetesc':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['vendeuseses'] = $this->m_compte_user->get_es2ad($this->company->ekey);
                    }else{
                        $this->property['vendeuseses'] = $this->m_compte_user->get_es2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES DU GUICHET ESCAL<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/view_vendeusees', $this->property);
                break;
                case 'recettebagage':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['ecrivainbagages'] = $this->m_compte_user->get_userbg2ad($this->company->ekey);
                    }else{
                        $this->property['ecrivainbagages'] = $this->m_compte_user->get_userbg2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES BAGAGES<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/view_bagage', $this->property);
                break;
                case 'depensecourrier':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['vendeuses'] = $this->m_compte_user->get_user2ad($this->company->ekey);
                    }else{
                        $this->property['vendeuses'] = $this->m_compte_user->get_user2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES DU COURRIER<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_depense/dep_view_vendeuse', $this->property);
                break;
                case 'validation':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['recettes'] = $this->m_recette->recet($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['depenses'] = $this->m_depense->depens($this->company->ekey, $cid, $cdg, $cpr);
                    $this->property['caisseident'] = $caisseident;
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['usercomptes'] = $this->m_compte_user->get_cuser($this->company->ekey, $cdg);
                    }else{
                        $this->property['usercomptes'] = $this->m_compte_user->get_cuser($this->company->ekey, $cdg);
                    }
                    $this->property['pagetitle'] .= "• VALIDATION COMPTE<strong•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}></strong>";
                return $this->layout->view('_caisse/caissevalide', $this->property);
                break;

               default:
                   return -1;
           }

           
       }


        public function add($ckey)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');
            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            
            if($this->input->post('daterecep')!= '')
            {
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idcaisse'),
                    'idopera' => $iduser,
                    'recetsgid' => $sgid,
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('_compag'),
                    'type_recet' => $this->input->post('interne'),
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                          
                if($this->session->agent->userole === '4')
                {
                    $uprecette = array(
                    'active_recet' => 1,
                    'is_validerecet' => 1,
                    'is_actifrecet' => 1,
                    'operavalid' => $iduser,
                );
                    $this->m_recette->update($recette, $uprecette);

                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
                }
                else

                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/recette_adjoint/'. $sgid.'/' . mdate("%d/%m/%Y", now('UTC')));
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
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            if($this->input->post('daterecep')!= '')
            {
                $arrayrecette = array(
                'idcaisse' => $this->input->post('idcaisse'),
                'id_genre_recet' => $this->input->post('genre'),
                'compkey_recet' => $this->input->post('_compag'),
                'type_recet' => $this->input->post('interne'),
                'nom' => $this->input->post('nom'),
                'montant_recet' => $this->input->post('montantverse'),
                'commentaire_recet' => $this->input->post('comment'),
                'date_recet' => $this->input->post('daterecep'),
                );
                $recette = $this->m_recette->update($recet, $arrayrecette);
                           
                $this->property['UPDATE_SUCCESS'] = TRUE;
                if($this->session->agent->userole === '4')
                {
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/recette_adjoint/'. $sgid.'/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    
        }

        public function updatrecette($ckey, $recet)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');
            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
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
                
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/recette/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    
        }        


        //arret des caisses
        public function unstop($ckey, $idcpt, $d)
        {
                $this->company = $this->m_entreprises->get_key($ckey);
                $identifiant_gare = $this->input->post('idgarecode');
                $identifiant_caisse = $this->input->post('idcaisse');
                $gid = $this->input->post('gareconnect');
                $iduser = $this->input->post('userconnected');
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');

                $s=$this->input->post('solde');
                
                $db = $this->input->post('datedebut');
                $df = $this->input->post('datefin');
                $cfrecet = $this->db->query("SELECT r.id_recette, r.arret_caisrecet, r.active_recet, r.idopera, r.is_validerecet FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.is_validerecet = 1
                    AND r.arret_caisrecet = 0
                    AND r.idcaisse ='$identifiant_caisse'
                    AND r.date_recet BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfrecet as $item) {
                        $plarray = array(
                            'arret_caisrecet' => 1,
                        );
                        $vald_recet = $this->m_recette->update($item->id_recette, $plarray);
                    }

                $cfdepe = $this->db->query("SELECT d.id_depense, d.active_dep, d.arret_caisdep, d.idop_dep, d.is_validedep FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.is_validedep = 1
                    AND d.arret_caisdep = 0
                    AND d.idcaisse_depens = '$identifiant_caisse'
                    AND d.date_depens BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfdepe as $item1) {
                        $dplarray = array(
                            'arret_caisdep' => 1,
                        );
                        $vald_dep = $this->m_depense->update($item1->id_depense, $dplarray);
                    }
                $cfdepo = $this->db->query("SELECT d.id_depot, d.idop_depot, d.arret_caisdepo, d.is_validdepo FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND d.is_validdepo = 0
                    AND d.arret_caisdepo = 0
                    AND d.idcaisse_depot = '$identifiant_caisse'
                    AND d.datedepot BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfdepo as $item2) {
                        $dpolarray = array(
                            'arret_caisdepo' => 1,
                            'is_validdepo' => 1,
                        );
                        $vald_depo = $this->m_depot->update($item2->id_depot, $dpolarray);
                    }

                    $cfvers = $this->db->query("SELECT v.id_versements, v.arret_caisvers, v.idop_versement, v.valider_vers FROM versements v
                    WHERE v.valider_vers = 0
                    AND v.idop_versement = '$idcpt'
                    AND v.arret_caisvers = 0
                    AND v.idcaisse_versement = '$identifiant_caisse'
                    AND v.date_versement BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfvers as $item3) {
                        $versarray = array(
                            'arret_caisvers' => 1,
                            'valider_vers' => 1,
                        );
                        $vald_verse = $this->m_versements->update($item3->id_versements, $versarray);
                    }
                    $this->property['UPDATE_SUCCESS'] = TRUE;
                
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/arretcaisse_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
        }
        
        public function indexversement($ckey, $g)
        {
            $ddbt = $this->input->post('dated');
            $dfin = $this->input->post('datef');
            $comp = $this->input->post('_compag');
            $ivd = $this->input->post('vendeuseid');
            $gid = $this->input->post('departgar');
            $gidc = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['pagetitle'] .= " • VERSEMENT DES GUICHETS• <strong>{$this->company->nom_entreprise}•&nbsp;</strong>";
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gidc, $sgid);
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gidc, $iduser);
            $this->property['conex'] = $conex;
            $this->property['triversements'] = $this->m_comptes_guichet->versfiltre($this->company->ekey, $gid, $ddbt, $dfin, $comp, $ivd);

                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/viewversement', $this->property);
          
        }

        //modifier le versement des guichets
        public function modifierversement($ckey, $idcpt, $g)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
        
                
                        $arrayvalid = array(

                            'montcomtpte' => $this->input->post('montantenvoyer'),
                        );
                        $this->m_comptes_guichet->update($idcpt, $arrayvalid);

                    
                    
                        
            redirect('caisses/indexversement/'.$this->session->company->ekey.'/'. $g);
        }

             //guichet
        public function arcompte($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();
            

            $this->company = $this->m_entreprises->get_key($ckey);
                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->usget1($idc, $gd);
                    $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE • <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->nom_gaep}•{$bus_stop->nomsousgare}</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
                if($sgares->sog == 1){
                   $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $idc, $gd, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $idc, $gd, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $idc, $gd, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $idc, $gd, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $idc, $gd);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $idc, $gd);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupb($this->company->ekey, $idc, $gd, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupb($this->company->ekey, $idc, $gd, 5000);

                    $passagersbisss = $this->m_passager->coptb($this->company->ekey, $idc, $gd, 5000);
                    
                    $this->property['passagersbisss'] = $passagersbisss;
                }
                else
                {


                    $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $idc, $gd, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $idc, $gd, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $idc, $gd, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $idc, $gd, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $idc, $gd);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $idc, $gd);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupsbis($this->company->ekey, $idc, $gd, $sg, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupsbis($this->company->ekey, $idc, $gd, $sg, 5000);

                    $passagersbisss = $this->m_passager->coptb($this->company->ekey, $idc, $gd, 5000);
                    
                    $this->property['passagersbisss'] = $passagersbisss;
                }
                $this->property['passager_repro'] = $this->m_passager->comptrep($this->company->ekey, $idc, $gd);
                $this->property['passager_conf'] = $this->m_passager->comptconf($this->company->ekey, $idc, $gd);
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/indexguichet', $this->property);
          
        }

        public function arcompteescal($ckey, $idc, $gd, $sg)
        {
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();
            
            $this->company = $this->m_entreprises->get_key($ckey);
                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->usget1($idc, $gd);
                    $this->property['conex'] = $conex;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE ESCAL • <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->nom_gaep}•{$bus_stop->nomsousgare}</strong>";
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $idc, $gd);
                   
                $this->property['escalclient'] = $this->m_escalclients->comptes($this->company->ekey, $idc, $gd, $sg);

                $this->property['escalclientgroup'] = $this->m_escalclients->comptegroups($this->company->ekey, $idc, $gd, $sg);

                $this->property['compagnies'] = $this->m_compagnies->get();
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
                }
                else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gd);
                    
                }
                return $this->layout->view('_caisse/indexguichetesc', $this->property);
          
        }

        public function valide($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
        
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                    WHERE s.gareprinceid = '$gd'")->row();
               if($sgares->sog == 1){

                    $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$idcpt'
                        AND p.statutvente = 0")->result();
    
                        foreach ($arpass as $items1) {
                            $plarras = array(
                                'statutvente' => 1,
                            );

                            $arpasss =  $this->m_passager->update($items1->code_passager, $items1->code_ticket, $plarras);
                        }
    
                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                        WHERE np.cptus = '$idcpt'
                        AND np.statvente = 0")->result();
    
                        foreach ($arnonpass as $items2) {
                            $plarrayn = array(
                                'statvente' => 1,
                            );
                            $val = $this->m_non_passager->update($items2->code_non_pass, $items2->codeticket, $plarrayn);
                        }
               }else{
                       $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$idcpt'
                        AND departclient_idgare = '$isg'
                        AND p.statutvente = 0")->result();
    
                        foreach ($arpass as $items1) {
                            $plarras = array(
                                'statutvente' => 1,
                            );
                            $arpasss = $this->m_passager->update($items1->code_passager, $items1->code_ticket, $plarras);

                        }
    
                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                        WHERE np.cptus = '$idcpt'
                        AND sousgareidentif = '$isg'
                        AND np.statvente = 0")->result();
    
                        foreach ($arnonpass as $items2) {
                            $plarrayn = array(
                                'statvente' => 1,
                            );
                            $val = $this->m_non_passager->update($items2->code_non_pass, $items2->codeticket, $plarrayn);
                        }
                           $arpassbis = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                            WHERE p.idcptuser = '$idcpt'
                            AND p.statutvente = 0
                            AND p.departclient_idgare NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                            foreach ($arpassbis as $items1bis) {
                            $plarrasbis = array(
                                'statutvente' => 1,
                            );
                            $this->m_passager->update($items1bis->code_passager, $items1bis->code_ticket, $plarrasbis);
                        }
    
                            $arnonpassbis = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                            WHERE np.cptus = '$idcpt'
                            AND np.statvente = 0
                            AND np.sousgareidentif NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                        foreach ($arnonpassbis as $items2bis) {
                            $plarraynbis = array(
                                'statvente' => 1,
                            );
                        $valbis = $this->m_non_passager->update($items2bis->code_non_pass, $items2bis->codeticket, $plarraynbis);
                        }

                }
                        
                    $arnonreport = $this->db->query("SELECT rp.code_report, rp.idcpuserconect, rp.statutreport FROM report rp
                    WHERE rp.idcpuserconect = '$idcpt'
                    AND rp.statutreport = 0")->result();

                    foreach ($arnonreport  as $items3) {
                        $plarrayrpro = array(
                            'statutreport' => 1,
                        );
                        
                        $valrepro = $this->m_report->update($items3->code_report, $plarrayrpro);
                    }

                    $cd = $this->input->post('comppremier');
                    $mt = $this->input->post('montaller');
                    $cdr = $this->input->post('compsecond');
                    $mtr = $this->input->post('montretour');
                    $sg = $this ->input->post('sousga');
                    $sgr = $this ->input->post('sousgr');

                    $cdb = $this->input->post('comppremierbis');
                    $mtb = $this->input->post('montallerbis');
                    $cdrb = $this->input->post('compsecondbis');
                    $mtrb = $this->input->post('montretourbis');
                    $sgb = $this ->input->post('sousgabis');
                    $sgrb = $this ->input->post('sousgrbis');

                    $cdse = $this->input->post('compcted');

                    //$i = count($cd);

                    //$j = count($cdb);

                    
                    if($cdse !== NULL)
                    {
                        $i = count($cdse);
                    }
                    else
                    {
                        $i = 0;
                    }

                    if($cdb !== NULL)
                    {
                        $j = count($cdb);
                    }
                    else
                    {
                        $j = 0;
                    }
                    
                    if($arpass != NULL)
                    {
                        if($i === 1 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];
                            $sg1 = $sg[0];

                            $mt1 = $mt[0] + $mtr[0];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'montcomtpte' => $mt1,
                                'idsousga' => $sg1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                        }
                        if($i === 1 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                                $sg2 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                                $sg1 = $sg[0];
                            }


                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                        }
                        if($i === 1 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $isg;
                                $sg2 = $isg;
                                $sg3 = $isg;
                            }else
                            {
                                $sg1 = $sg[0];
                                $sg2 = $sg[0];
                                $sg3 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                        }

                        if($i === 1 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];
                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            $sg4 = $sg[0];
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){

                                $sg1 = $isg;
                                $sg2 = $isg;
                                $sg3 = $isg;
                                $sg4 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                                $sg2 = $sg[0];
                                $sg3 = $sg[0];
                                $sg4 = $sg[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arraycompt4 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde4,
                                'idsousga' => $sg4,
                                'montcomtpte' => $mt4,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt4);
                        }

                        if($i === 0 AND $j === 1)
                        {

                            $cde1 = $cdb[0];

                            $mt1 = $mtb[0] + $mtrb[0];

                            $sg1 = $isg;
                            

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $this->m_comptes_guichet->create($arraycompt);
                            
                        }
                        if($i === 0 AND $j === 2)
                        {
                            
                            $sg1 = $isg;
                            $sg2 = $isg;
                            
                            
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];

                            if($cdb[1] === $cdrb[1]){

                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);
                        }

                        if($i === 0 AND $j === 3)
                        {
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];
                            $cde3 = $cdb[2];

                            $sg1 = $isg;
                            $sg2 = $isg;
                            $sg3 = $isg;
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){
                                
                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];
                                $mt3 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                                $mt3 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + 0;
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                                $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);
                        }
                    }

                //var_dump($j, 0, $arpass);
            redirect('caisses/compte/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
        }
        
        public function valideesc($ckey, $idcpt, $d, $gd, $isg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
        
            $cus = $this->input->post('compconnected');
            $today = mdate("%Y-%m-%d", now());

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                    WHERE s.gareprinceid = '$gd'")->row();
               
                    $arpass = $this->db->query("SELECT es.idclescal, es.arrcptescal, es.iduseescal FROM escalclients es
                        WHERE es.iduseescal = '$idcpt'
                        AND es.departsgescal = '$isg'
                        AND es.arrcptescal = 0")->result();
    
                        foreach ($arpass as $items1) {
                            $plarras = array(
                                'arrcptescal' => 1,
                            );
                            $this->m_escalclients->update($items1->idclescal, $plarras);

                        }
    
                        /*$scode = $this->db->select('*')
                              ->from('verifcompte_user v')
                              ->where('v.verifis_conect', 0)
                              ->where('v.verifuserlog_id', $cus)
                              ->get()
                              ->row();


                            $act_array = array(
                                'verifis_conect' => 1,
                            );

                        $this->m_verifcompte_user->update($scode->verifcpuser_id, $act_array);*/

                    $cd = $this->input->post('comppremier');
                    $mt = $this->input->post('montaller');
                    $sg = $this ->input->post('sousga');

                    $i = count($cd);

                    
                    if($arpass != NULL)
                    {
                        if($i === 1)
                        {

                            $cde1 = $cd[0];
                            $sg1 = $sg[0];
                            $mt1 = $mt[0];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            
                            if($sgares->sog == 1)
                            {
                                
                                $sg1 = $isg;
                            }
                            else
                            {
                                $sg1 = $sg[0];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'montcomtpte' => $mt1,
                                'idsousga' => $sg1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                        }
                        
                    }
            redirect('caisses/compteescal/'.$this->session->company->ekey. '/' . $idcpt.'/'.$gd.'/'.$isg);
        }
        //arret chefguichet

        public function validerec($ckey, $idcpt, $d, $gd)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

             $idc = $this->input->post('idcaisse');
             
             $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$gd'")->row();
                if($sgares->sog == 1){            
                    $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                    WHERE p.idcptuser = '$idcpt'
                    AND p.statutvente = 0
                    AND p.code_ticket != 'R'")->result();

                    foreach ($arpass as $ite1) {
                        $plarras = array(
                            'statutvente' => 1,
                            'is_valdtick' => 1,
                        );
                        $this->m_passager->update($ite1->code_passager, $ite1->code_ticket, $plarras);
                    }

                    $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                    WHERE np.cptus = '$idcpt'
                    AND np.statvente = 0")->result();

                    foreach ($arnonpass as $ite2) {
                        $plarrayn = array(
                            'statvente' => 1,
                            'is_valedtick' => 1,
                        );
                        $val = $this->m_non_passager->update($ite2->code_non_pass, $ite2->codeticket, $plarrayn);
                    }
                }else
                {
                        $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$idcpt'
                        AND p.departclient_idgare = '$sgid'
                        AND p.statutvente = 0
                        AND p.code_ticket != 'R'")->result();
    
                        foreach ($arpass as $ite1) {
                            $plarras = array(
                                'statutvente' => 1,
                                'is_valdtick' => 1,
                            );
                            $this->m_passager->update($ite1->code_passager, $ite1->code_ticket, $plarras);
                        }
    
                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                        WHERE np.cptus = '$idcpt'
                        AND np.sousgareidentif = '$sgid'
                        AND np.statvente = 0")->result();
    
                        foreach ($arnonpass as $ite2) {
                            $plarrayn = array(
                                'statvente' => 1,
                                'is_valedtick' => 1,
                            );
                            $val = $this->m_non_passager->update($ite2->code_non_pass, $ite2->codeticket, $plarrayn);
                        }
                          $arpassbis = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.idcptuser FROM passager p
                            WHERE p.idcptuser = '$idcpt'
                            AND p.statutvente = 0
                            AND p.code_ticket != 'R'
                            AND p.departclient_idgare NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                            foreach ($arpassbis as $items1bis) {
                            $plarrasbis = array(
                                'statutvente' => 1,
                            );
                            $this->m_passager->update($items1bis->code_passager, $items1bis->code_ticket, $plarrasbis);
                        }
    
                            $arnonpassbis = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.cptus FROM non_passager np
                            WHERE np.cptus = '$idcpt'
                            AND np.statvente = 0
                            AND np.sousgareidentif NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$gd')")->result();
        
                        foreach ($arnonpassbis as $items2bis) {
                            $plarraynbis = array(
                                'statvente' => 1,
                            );
                            $valbis = $this->m_non_passager->update($items2bis->code_non_pass, $items2bis->codeticket, $plarraynbis);
                        }
                }    
                
                    $arnonreport = $this->db->query("SELECT rp.code_report, rp.idcpuserconect, rp.statutreport FROM report rp
                    WHERE rp.idcpuserconect = '$idcpt'
                    AND rp.statutreport = 0")->result();

                    foreach ($arnonreport  as $ite3) {
                        $plarrayrpro = array(
                            'statutreport' => 1,
                        );
                        $valrepro = $this->m_report->update($ite3->code_report, $plarrayrpro);
                    }
                    
                    $cd = $this->input->post('comppremier');
                    $mt = $this->input->post('montaller');
                    $cdr = $this->input->post('compsecond');
                    $mtr = $this->input->post('montretour');
                    $sg = $this ->input->post('sousga');
                    $sgr = $this ->input->post('sousgr');

                    $cdb = $this->input->post('comppremierbis');
                    $mtb = $this->input->post('montallerbis');
                    $cdrb = $this->input->post('compsecondbis');
                    $mtrb = $this->input->post('montretourbis');
                    $sgb = $this ->input->post('sousgabis');
                    $sgrb = $this ->input->post('sousgrbis');

                    $cdse = $this->input->post('compcted');

                    //$i = count($cd);

                    //$j = count($cdb);

                    
                    if($cdse !== NULL)
                    {
                        $i = count($cdse);
                    }
                    else
                    {
                        $i = 0;
                    }

                    if($cdb !== NULL)
                    {
                        $j = count($cdb);
                    }
                    else
                    {
                        $j = 0;
                    }

                    $nm = $this->input->post('nom');

                    $nmbis = $this->input->post('nombis');
                    
                    $sgar = $sgid;

                    $nomcais = $this->session->agent->username;
                    
                if($arpass != NULL)
                {
                        if($i === 1 AND $j === 0)
                        {

                            $cde1 = $cd[0];
                            $cder1 = $cdr[0];

                            $sg1 = $sg[0];
                            $mt1 = $mt[0] + $mtr[0];

                            $cm1 = $nm[0];
                                
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                                $arraycompt = array(
                                    'idusercompt' => $idcpt,
                                    'comp' => $cde1,
                                    'idsousga' => $sg1,
                                    'montcomtpte' => $mt1,
                                    'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                                );
                                $this->m_comptes_guichet->create($arraycompt);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                               redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                            redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

                        }
                        if($i === 1 AND $j === 1)
                        {

                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            

                            $mt1 = $mt[0] + $mtr[0];
                            $mt2 = $mtb[0] + $mtrb[0];

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];

                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 1 AND $j === 2)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                            }

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];
                            $cm3 = $nmbis[1];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $sgid;
                            }else{
                                $sg3 = $sg[2];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 1 AND $j === 3)
                        {
                            $cde1 = $cd[0];
                            $cde2 = $cdb[0];
                            $cde3 = $cdb[1];
                            $cde4 = $cdb[2];

                            $sg1 = $sg[0];
                            $sg2 = $sg[0];
                            $sg3 = $sg[0];
                            $sg4 = $sg[0];
                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){

                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + $mtrb[0];
                                $mt3 = $mtb[1] + $mtrb[1];
                                $mt4 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + $mtrb[0];
                                $mt4 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mt[0] + $mtr[0];
                                $mt2 = $mtb[0] + 0;
                                $mt3 = $mtb[1] + 0;
                                $mt4 = $mtb[2] + 0;
                            }

                            $cm1 = $nm[0];
                            $cm2 = $nmbis[0];
                            $cm3 = $nmbis[1];
                            $cm4 = $nmbis[2];
                            
                            $sgares = $this->db->query("SELECT count(idsousgare) AS sog  FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares->sog == 1){
                                
                                $sg1 = $sgid;
                            }else{
                                $sg1 = $sg[0];
                            }
                            $sgares1 = $this->db->query("SELECT count(idsousgare) AS sog1 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares1->sog1 == 1){
                                
                                $sg2 = $sgid;
                            }else{
                                $sg2 = $sg[1];
                            }
                            $sgares2 = $this->db->query("SELECT count(idsousgare) AS sog2 FROM sousgare sg
                            WHERE sg.gareprinceid = '$gd'")->row();
                            if($sgares2->sog2 == 1){
                                
                                $sg3 = $sgid;
                            }else{
                                $sg3 = $sg[2];
                            }

                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sg1,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sg2,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            $arraycompt3 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde3,
                                'idsousga' => $sg3,
                                'montcomtpte' => $mt3,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt3);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg1,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg2,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg3,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            $arrayrecette4 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde4,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sg4,
                                'nom' => $nomcais,
                                'montant_recet' => $mt4,
                                'commentaire_recet' => $cm4,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette4 = $this->m_recette->create($arrayrecette4);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->m_recette->update($recette4, $uprecette);

                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 1)
                        {

                            
                            $cde1 = $cdb[0];

                            $mt1 = $mtb[0] + $mtrb[0];

                            $cm1 = $nmbis[0];

                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);

                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            
                            $recette = $this->m_recette->create($arrayrecette);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);

                                
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 2)
                        {
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];

                            if($cdb[1] === $cdrb[1]){

                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];

                            }
                            
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                            }
                            
                            $cm1 = $nmbis[0];
                            $cm2 = $nmbis[1];
                            
                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            
                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                    $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                        if($i === 0 AND $j === 3)
                        {
                            $cde1 = $cdb[0];
                            $cde2 = $cdb[1];
                            $cde3 = $cdb[2];

                            
                            if($cdb[0] === $cdrb[0] OR $cdb[1] === $cdrb[1] OR $cdb[2] === $cdrb[2]){
                                
                                $mt1 = $mtb[0] + $mtrb[0];
                                $mt2 = $mtb[1] + $mtrb[1];
                                $mt3 = $mtb[2] + $mtrb[2];

                            }
                            elseif($cdb[1] === $cdrb[0] OR $cdb[2] === $cdrb[1])
                            {
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + $mtrb[0];
                                $mt3 = $mtb[2] + $mtrb[1];
                            }
                            
                            elseif($cdb[2] === $cdrb[0])
                            {
                                
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + $mtrb[0];
                            }
                            else
                            {
    
                                $mt1 = $mtb[0] + 0;
                                $mt2 = $mtb[1] + 0;
                                $mt3 = $mtb[2] + 0;
                            }

                            $cm1 = $nmbis[0];
                            $cm2 = $nmbis[1];
                            $cm3 = $nmbis[2];
                            
                            
                            $arraycompt = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde1,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt1,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt);
                            
                            $arraycompt2 = array(
                                'idusercompt' => $idcpt,
                                'comp' => $cde2,
                                'idsousga' => $sgar,
                                'montcomtpte' => $mt2,
                                'datearretcompt' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            $this->m_comptes_guichet->create($arraycompt2);

                            
                            $arrayrecette = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde1,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt1,
                                'commentaire_recet' => $cm1,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette = $this->m_recette->create($arrayrecette);

                            $arrayrecette2 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde2,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt2,
                                'commentaire_recet' => $cm2,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette2 = $this->m_recette->create($arrayrecette2);

                            $arrayrecette3 = array(
                                'idcaisse' => $this->input->post('idcaisse'),
                                'id_genre_recet' => $this->input->post('genre'),
                                'compkey_recet' => $cde3,
                                'type_recet' => 'Ticket',
                                'idopera' => $idcpt,
                                'recetsgid' => $sgar,
                                'nom' => $nomcais,
                                'montant_recet' => $mt3,
                                'commentaire_recet' => $cm3,
                                'date_recet' => mdate("%Y/%m/%d", now('UTC')),
                                'createdrecet_at' => now('UTC'),
                            );
                            $recette3 = $this->m_recette->create($arrayrecette3);

                            if($this->session->agent->userole === '4')
                            {
                                $uprecette = array(
                                    'active_recet' => 1,
                                    'is_validerecet' => 1,
                                    'is_actifrecet' => 1,
                                    'operavalid' => $iduser,
                                );
                                $this->m_recette->update($recette, $uprecette);
                                    
                                    $this->m_recette->update($recette2, $uprecette);

                                    $this->m_recette->update($recette3, $uprecette);
                                    
                                    $this->property['UPDATE_SUCCESS'] = TRUE;

                                redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$gd. '/'. $idc. '/arretcaisseprincipale/'. $idcpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC'))); 
                            }
                            else                    
                            
                                 redirect('caisses/' . $this->session->company->ekey.'/cais/'.$gd. '/'. $idc. '/'. $idcpt.'/arretcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }
                }
                   
        }

        public function addbank($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            if($this->input->post('versedate')!= '')
            {
                $arrayvers = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('genrevers'),
                    'idop_versement' => $iduser,
                    'sgareidvers' => $sgid,
                    'compkey_vers' => $this->input->post('_compag'),
                    'type_versement' => $this->input->post('typeverse'),
                    'nom_beneficiaire' => $this->input->post('nom'),
                    'montant_verser' => $this->input->post('montverse'),
                    'commentaire' => $this->input->post('comment'),
                    'bordereau_verser' => $this->input->post('bordereau'),
                    'date_versement' => $this->input->post('versedate'),
                    'active_verse' => 1,
                    'is_actifverser' => 1,
                    'valider_vers' => 1,
                    'validop' => $iduser,
                );
                $ver = $this->m_versements->create($arrayvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function updatebank($ckey, $idversbank)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('dateversements')!= '')
            {
                $upvers = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('genreverse'),
                    'idop_versement' => $iduser,
                    'compkey_vers' => $this->input->post('_compag'),
                    'type_versement' => $this->input->post('interneverse'),
                    'nom_beneficiaire' => $this->input->post('nombank'),
                    'montant_verser' => $this->input->post('montantversem'),
                    'commentaire' => $this->input->post('commentverse'),
                    'bordereau_verser' => $this->input->post('bordereau'),
                    'date_versement' => $this->input->post('dateversements'),
                );
                $ver = $this->m_versements->update($idversbank, $upvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
            

        }
        //versement des clients

        public function addverseautre($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autreversedate')!= '')
            {
                    $arrayvers = array(
                        'idcaisse_versement' => $identifiant_caisse,
                        'id_genre_versement' => $this->input->post('autregenrevers'),
                        'idop_versement' => $iduser,
                        'sgareidvers' => $sgid,
                        'compkey_vers' => $this->input->post('_compag'),
                        'typpersonnel' => $this->input->post('typerson'),
                        'type_versement' => $this->input->post('autretypeverse'),
                        'nom_beneficiaire' => $this->input->post('nom'),
                        'montant_verser' => $this->input->post('autremontverse'),
                        'commentaire' => $this->input->post('autrecomment'),
                        'bordereau_verser' => $this->input->post('autrebordereau'),
                        'date_versement' => $this->input->post('autreversedate'),
                    );
                    $ver = $this->m_versements->create($arrayvers);
                if($this->session->agent->userole === '4')
                {

                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validop' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);
                
                    $this->property['UPDATE_SUSSESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/' . mdate("%d/%m/%Y", now('UTC')));
                }else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function addverseautrefour($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

                $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autreversedate')!= '')
            {
                    $arrayvers = array(
                        'idcaisse_versement' => $identifiant_caisse,
                        'id_genre_versement' => $this->input->post('autregenrevers'),
                        'idop_versement' => $iduser,
                        'sgareidvers' => $sgid,
                        'compkey_vers' => $this->input->post('_compag'),
                        'type_versement' => $this->input->post('autretypeverse'),
                        'typpersonnel' => $this->input->post('typerson'),
                        'nom_beneficiaire' => $this->input->post('nom'),
                        'montant_verser' => $this->input->post('autremontverse'),
                        'commentaire' => $this->input->post('autrecomment'),
                        'bordereau_verser' => $this->input->post('autrebordereau'),
                        'date_versement' => $this->input->post('autreversedate'),
                    );
                    $ver = $this->m_versements->create($arrayvers);
                if($this->session->agent->userole === '4')
                {

                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validop' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);
                
                    $this->property['UPDATE_SUSSESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
                }else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint//'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function upautreversement($ckey, $idvers)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autredateversements')!= '')
            {
                $upvers = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('autregenreverse'),
                    'idop_versement' => $iduser,
                    'compkey_vers' => $this->input->post('compags'),
                    'type_versement' => $this->input->post('externeverse'),
                    'nom_beneficiaire' => $this->input->post('autrenom'),
                    'montant_verser' => $this->input->post('autremontantversem'),
                    'bordereau_verser' => $this->input->post('autrebordereau'),
                    'commentaire' => $this->input->post('autrecommentverse'),
                    'date_versement' => $this->input->post('autredateversements'),
                );
                $ver = $this->m_versements->update($idvers, $upvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                if($this->session->agent->userole === '4')
                {
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
                }else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            

        }

        public function upfourversement($ckey, $idvers)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autredateversements')!= '')
            {
                $upvers = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('autregenreverse'),
                    'idop_versement' => $iduser,
                    'compkey_vers' => $this->input->post('_compag'),
                    'typpersonnel' => $this->input->post('typerson'),
                    'type_versement' => $this->input->post('externeverse'),
                    'nom_beneficiaire' => $this->input->post('autrenom'),
                    'montant_verser' => $this->input->post('autremontantversem'),
                    'commentaire' => $this->input->post('autrecommentverse'),
                    'date_versement' => $this->input->post('autredateversements'),
                );
                $ver = $this->m_versements->update($idvers, $upvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                if($this->session->agent->userole === '4')
                {
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            

        }
        //versement dans une caisse
        public function adverscaisse($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');
            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('caisseversedate')!= '')
            {
                $arrayver = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('caissegenrevers'),
                    'idop_versement' => $iduser,
                    'sgareidvers' => $sgid,
                    'compkey_vers' => $this->input->post('_compag'),
                    'type_versement' => $this->input->post('caissetypeverse'),
                    'typpersonnel' => $this->input->post('personnels'),
                    'nom_beneficiaire' => $this->input->post('nom'),
                    'montant_verser' => $this->input->post('caismontantmontverse'),
                    'commentaire' => $this->input->post('caisseautrecomment'),
                    'date_versement' => $this->input->post('caisseversedate'),
                );
                $ver = $this->m_versements->create($arrayver);
                if($this->session->agent->userole === '4')
                {
                    $upvers = array(
                        'active_verse' => 1,
                        'valider_vers' => 1, 
                        'is_actifverser' => 1,
                        'validop' => $iduser,
                    );
                    $this->m_versements->update($ver, $upvers);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versementcaisse/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }else
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versementcaisse_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        //modification versement caisse
        public function upautreversment($ckey, $idver)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('autredateversements')!= '')
            {
                $arrayver = array(
                    'idcaisse_versement' => $identifiant_caisse,
                    'id_genre_versement' => $this->input->post('caissegenrevers'),
                    'idop_versement' => $iduser,
                    'compkey_vers' => $this->input->post('_compag'),
                    'type_versement' => $this->input->post('interneverse'),
                    'typpersonnel' => $this->input->post('personnels'),
                    'nom_beneficiaire' => $this->input->post('nom'),
                    'montant_verser' => $this->input->post('autremontantversem'),
                    'commentaire' => $this->input->post('autrecommentverse'),
                    'date_versement' => $this->input->post('autredateversements'),
                );
                $ver = $this->m_versements->update($idver, $arrayver);
                $this->property['UPDATE_SUSSESS'] = TRUE;
                if($this->session->agent->userole === '4')
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versement/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versement_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                

        }
        public function viewcaisprinc($ckey, $idcpus, $cdg, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $idsg);
                        $this->property['gare_stop'] = $gare_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $cdg, $idcpus);
                $this->property['conex'] = $conex;
           
                
                    $this->property['usercomptes'] = $this->m_compte_user->gets_usercp($this->company->ekey, $cdg);
               
                $this->property['pagetitle'] .= "• VALIDATION COMPTE •<strong>{$this->company->nom_entreprise}</strong>";
                return $this->layout->view('_caisse/viewcompte', $this->property);
                

        }

        // triencaissement
        public function triversement($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    
            $ivd = $this->input->post('vendeuseid');
            $ddbt = $this->input->post('dated');
            $dfin = $this->input->post('datef');
            $gid = $this->input->post('departgar');

            redirect('Rapport/triencaissement/' . $this->session->company->ekey.'/'. $gid .'/'. $ivd.'/'. $ddbt.'/'. $dfin); 
        }

        //validation comptable
        public function valversement($ckey, $id)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse');   
                $identifiant_sousgare = $this->input->post('idsousgar');
            $array = array(
                'commentaire' => $this->input->post('autrecommentverse'),
                'valid_cptablevers' => 1,
                'opvalid_cptablevers' => roleattribut_guard_session_ra(),
            );
            $this->m_versements->update($id, $array);
                       
            $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/'.$this->session->company->ekey. '/caisseprincversement/'. $identifiant_gare. '/'. $identifiant_use.'/'.$identifiant_sousgare. '/' . mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function rejetversement($ckey, $id)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse');   
                $identifiant_sousgare = $this->input->post('idsousgar');
            $array = array(
                'commentaire' => $this->input->post('autrecommentverse'),
                'ferme_caisvers' => 0,

            );
            $this->m_versements->update($id, $array);
                       
            $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/'.$this->session->company->ekey. '/caisseprincversement/'. $identifiant_gare. '/'. $identifiant_use.'/'.$identifiant_sousgare. '/' . mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function options($ckey, $cdg, $cid, $icx, $type = 'recette_adjoint', $idsg, $d = FALSE, $m = FALSE, $y = FALSE)
        {
           $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $cdg, $icx);
                $this->property['conex'] = $conex;

                $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                            WHERE s.gareprinceid = '$cdg'")->row();

           // All the departures
           switch ($type) 
           {
                
                case 'recette_adjoint':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['recettes'] = $this->m_recette->ad_getrecet($this->company->ekey, $cdg, $idsg, $cid, $icx);
                    $this->property['sommerecettes'] = $this->m_recette->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['sommesrecettes'] = $this->m_recette->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                    $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                    $this->property['caisseident'] = $caisseident;
                    $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• RECETTES INTERNE• <strong>&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
               return $this->layout->view('_recette/ad_index', $this->property);
            break;  
            case 'autreversement_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['versements'] = $this->m_versements->ad_get($this->company->ekey, $cdg, $cid, $icx);
                $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['genres'] = $this->m_genre_depense->get();
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);   $this->property['genrespersonnels'] = $this->m_type_personnel->get();                     
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• VERSEMENTS CLIENTS <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_caisse/ad_autreversement', $this->property);

            break;

            case 'versementfourni_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['versements'] = $this->m_versements->ad_get($this->company->ekey, $cdg, $cid, $icx);
                $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['genres'] = $this->m_genre_depense->get();
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• VERSEMENTS FOURNISSEURS<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";

                return $this->layout->view('_caisse/fouradjoint', $this->property);

            break;
            case 'versementcaisse_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['versements'] = $this->m_versements->ad_getcais($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisses'] = $this->m_caisse->getcaisse($this->company->ekey);
                $this->property['genrespersonnels'] = $this->m_type_personnel->getsc();
                $this->property['genrespersonnel'] = $this->m_type_personnel->getusercpg($this->company->ekey, $cdg);
                $this->property['genres'] = $this->m_genre_depot->geta();
                $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);                        
                $this->property['caisseident'] = $caisseident;
                $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS DES CAISSES <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";

                return $this->layout->view('_caisse/ad_versementcaisse', $this->property);
            break;

            case 'depense_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['depenses'] = $this->m_depense->ad_getdepen($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepenses'] = $this->m_versements->ad_totalesdepense($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                $this->property['caisseident'] = $caisseident;
                $this->property['genres'] = $this->m_genre_depense->get();
                $this->property['caissemontant'] = $this->m_caisse->ad_vers($this->company->id_entreprise, $cdg, $cid, $icx);
                $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPENSES INTERNE• <strong>{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depense/ad_index', $this->property);
            break;
            case 'depot_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['depots'] = $this->m_depot->adgetdepot($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['genres'] = $this->m_genre_depot->getb();
                $this->property['banque'] = $this->m_banque->get();
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS INTERNE• <strong>{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depot/ad_index', $this->property);
            break;

            case 'autredepense_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['autredepenses'] = $this->m_depense->ad_getautre($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepenses($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depotcaisse'] = $this->m_depot->ad_depotinterne($this->company->ekey, $cdg, $cid, $icx);                        
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['genres'] = $this->m_genre_depense->get();
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPENSES EXTERNE• <strong>{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depense/ad_autreindex', $this->property);
            break;
            
            case 'depotsous_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['sousdepots'] = $this->m_depot->ad_getdepot($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->getsc();
                $this->property['personnels'] = $this->m_personnels->get($this->company->ekey);
                $this->property['caisseident'] = $caisseident;
                $this->property['genres'] = $this->m_genre_depot->get();
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['typesclients'] = $this->m_type_client->get();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS SOUS CAISSE <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depot/ad_sousindex', $this->property);
            break;

            case 'autredepot_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['autredepots'] = $this->m_depot->ad_getautre($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);

                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);

                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['genres'] = $this->m_genre_depot->geta();
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS CLIENT• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depot/ad_autreindex', $this->property);
            break;

           

            case 'autredepotfour_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['autredepots'] = $this->m_depot->ad_getautre($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['montantverves'] = $this->m_versements->ad_totalversement($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepenses'] = $this->m_versements->ad_totaldepense($this->company->ekey, $cdg, $cid, $icx);
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['sommerecettes'] = $this->m_versements->ad_totalrecette($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepot'] = $this->m_versements->ad_totaldepot($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommedepots'] = $this->m_depot->ad_getmontant($this->company->ekey, $cdg, $cid, $icx);
                $this->property['sommesdepots'] = $this->m_depot->ad_getmontant1($this->company->ekey, $cdg, $idsg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['genres'] = $this->m_genre_depot->geta();
                $this->property['caisseident'] = $caisseident;
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• DEPOTS CLIENT• <strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                return $this->layout->view('_depot/ad_fourdepot', $this->property);
            break;

            case 'arretcaisse_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                $this->property['recettes'] = $this->m_recette->ad_recet($this->company->ekey, $cdg, $cid, $icx);
                $this->property['recettecaisses'] = $this->m_recette->ad_recetcais($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depenses'] = $this->m_depense->ad_depens($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depensecaisses'] = $this->m_depense->ad_depenscais($this->company->ekey, $cdg, $cid, $icx);
                $this->property['depotcaisses'] = $this->m_depot->ad_depocais($this->company->ekey, $cdg, $cid, $icx);
                $this->property['montanttotalcaisses'] = $this->m_versements->versecaiss($this->company->ekey, $cdg, $cid, $icx);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['genrespersonnels'] = $this->m_type_personnel->get();
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $icx, $cdg);
                if($sgares->sog == 1){
                       $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $icx, $cdg, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $icx, $cdg, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $icx, $cdg);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $icx, $cdg);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupb($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupb($this->company->ekey, $icx, $cdg, 5000);
                    
                }
                else
                {


                    $this->property['passagerallerbis'] = $this->m_passager->comptebis($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourbis'] = $this->m_non_passager->comptebis($this->company->ekey, $icx, $cdg, 5000);
                
                    $this->property['passagerallergroupbis'] = $this->m_passager->comptegroupbis($this->company->ekey, $icx, $cdg, 5000);
                    $this->property['passagerretourgroupbis'] = $this->m_non_passager->comptegroupbis($this->company->ekey, $icx, $cdg, 5000);
                    
                    $this->property['passageraller'] = $this->m_passager->compte($this->company->ekey, $icx, $cdg);
                    $this->property['passagerretour'] = $this->m_non_passager->compte($this->company->ekey, $icx, $cdg);
                
                    $this->property['passagerallergroup'] = $this->m_passager->comptegroupsbis($this->company->ekey, $icx, $cdg, $idsg, 5000);
                    $this->property['passagerretourgroup'] = $this->m_non_passager->comptegroupsbis($this->company->ekey, $icx, $cdg, $idsg, 5000);

                }
                
                $this->property['passager_repro'] = $this->m_passager->comptrep($this->company->ekey, $icx, $cdg);
                $this->property['passager_conf'] = $this->m_passager->comptconf($this->company->ekey, $icx, $cdg);
                
                $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                $this->property['pagetitle'] .= "• ARRÊT COMPTE ET CAISSE<strong>&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
            return $this->layout->view('_caisse/ad_indexcaisse', $this->property);
            break;

            case 'recetteguichet_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['vendeuses'] = $this->m_compte_user->get_user2ad($this->company->ekey);
                }else{
                    $this->property['vendeuses'] = $this->m_compte_user->get_user2($this->company->ekey, $cdg);
                }
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['pagetitle'] .= "• VALIDATION DES RECETTES DU GUICHET<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
               return $this->layout->view('_recette/ad_view_vendeuse', $this->property);
            break;

            case 'recetteguichetesc_adjoint':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                        $this->property['vendeuseses'] = $this->m_compte_user->get_es2ad($this->company->ekey);
                    }else{
                        $this->property['vendeuseses'] = $this->m_compte_user->get_es2($this->company->ekey, $cdg);
                    }
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES DU GUICHET ESCAL<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
                   return $this->layout->view('_recette/ad_view_vendeusees', $this->property);
            break;

            case 'recettebagage_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['ecrivainbagages'] = $this->m_compte_user->get_userbg2ad($this->company->ekey);
                }else{
                    $this->property['ecrivainbagages'] = $this->m_compte_user->get_userbg2($this->company->ekey, $cdg);
                }
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['pagetitle'] .= "• VALIDATION DES RECETTES BAGAGES<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
               return $this->layout->view('_recette/ad_view_bagage', $this->property);
            break;

            case 'depensecourrier_adjoint':
                $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->property['vendeuses'] = $this->m_compte_user->get_user2ad($this->company->ekey);
                }else{
                    $this->property['vendeuses'] = $this->m_compte_user->get_user2($this->company->ekey, $cdg);
                }
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['caisseident'] = $caisseident;
                $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES DU COURRIER<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$conex->type_rols}</strong>";
               return $this->layout->view('_depense/addep_view_vendeuse', $this->property);
            break;
               default:
                   return -1;
           }

           
        }

        public function optionscaisse($ckey, $cdg, $cid, $icx, $type = 'validation_recettes', $op, $idsg, $d = FALSE, $m = FALSE, $y = FALSE)
        {
           $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $cdg, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $cdg, $icx);
                $this->property['conex'] = $conex;
                $connex = $this->m_compte_user->usget1($op, $cdg);
                    $this->property['connex'] = $connex;

           // All the departures
           switch ($type) 
           {
                
                case 'validation_recettes':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['recettes'] = $this->m_recette->recetnonvalide($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $user_connect = $this->m_compte_user->usergare($this->company->ekey, $cdg, $icx);
                    $this->property['user_connect'] = $user_connect;
                    $this->property['caisseident'] = $caisseident;
                    $this->property['pagetitle'] .= "• RECETTES NON VALIDE<strong•&nbsp;{$this->company->nom_entreprise}•></strong>";
                return $this->layout->view('_caisse/recetnonvalide', $this->property);
                break;

                case 'validation_depenses':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['depenses'] = $this->m_depense->depensnonvalide($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['caisseident'] = $caisseident;
                    $user_connect = $this->m_compte_user->usergare($this->company->ekey, $cdg, $icx);
                    $this->property['user_connect'] = $user_connect;
                    $this->property['pagetitle'] .= "• DEPENSES NON VALIDE<strong•&nbsp;{$this->company->nom_entreprise}•</strong>";
                return $this->layout->view('_caisse/depensnonvalid', $this->property);
                break;

                case 'validation_depots':
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $cdg, $cid);
                    $this->property['depots'] = $this->m_depot->depotnonvalide($this->company->ekey, $cdg, $cid, $icx);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['caisseident'] = $caisseident;
                    $user_connect = $this->m_compte_user->usergare($this->company->ekey, $cdg, $icx);
                    $this->property['user_connect'] = $user_connect;
                    $this->property['pagetitle'] .= "• DEPOTS NON VALIDE<strong•&nbsp;{$this->company->nom_entreprise}•></strong>";
                return $this->layout->view('_caisse/depotnonvalid', $this->property);
                break;

               default:
                   return -1;
           }

           
        }

    }
    
    /** End of file: Caisses.php **/
    /** File location: application/controllers/Caisses.php **/
