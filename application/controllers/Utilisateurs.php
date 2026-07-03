<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Utilisateurs extends CI_Controller
    {
        public $property = array(
            'title' => 'Users',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $fonct;
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        
        
        public function view($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['authusers'] = $this->m_utilisateur->get_use($this->company->ekey);
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;TOUT LES UTILISATEURS<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                $this->property['gares'] = $this->m_gare_depart->get($this->company->id_entreprise);
                return $this->layout->view('_users/compt', $this->property);
        }
        
        public function viewcompte($ckey, $ud, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                
                $this->property['authcompte'] = $this->m_utilisateur->userget($this->company->ekey, $ud);
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;COMPTE <strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                $this->property['garees'] = $this->m_gares->get($this->company->id_entreprise);
                return $this->layout->view('_users/view', $this->property);
        }

        public function trivendeuses($g)
        {
            
            $outg = $this->m_compte_user->get_user5($this->session->company->ekey, $g);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outg));
            
        }

        public function trivendeusesop($g)
        {
            
            $outgop = $this->m_compte_user->get_userop5($this->session->company->ekey, $g);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outgop));
            
        }

        public function trivendeusesesc($g)
        {
            
            $outgesc = $this->m_compte_user->get_useresc5($this->session->company->ekey, $g);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outgesc));
            
        }

        public function trioperateur($g)
        {
            
            $outg = $this->m_compte_user->gverus($this->session->company->ekey, $g);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outg));
            
        }

        public function comptegares($ckey, $ud, $cp, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $conex = $this->m_compte_user->usergt($this->company->ekey, $ud);
                $this->property['conex'] = $conex;
                $this->property['comptegareattrib'] = $this->m_utilisateur->getgare($this->company->ekey, $cp);

                $this->property['pagetitle'] .= "&nbsp;•&nbsp;COMPTE ATTRIBUER DANS LA GARE<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                $this->property['garees'] = $this->m_gares->get($this->company->id_entreprise);
                return $this->layout->view('_users/gareattribuer', $this->property);
        }

        public function compteroles($ckey, $ud, $g, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $conex = $this->m_compte_user->ugare($this->company->ekey, $g, $ud);
                $this->property['conex'] = $conex;
                $this->property['compteroleattrib'] = $this->m_utilisateur->getrole($this->company->ekey, $ud, $g);
                $this->property['dossiers'] = $this->m_dossier->get();
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;ROLE ATTRIBUER AU COMPTE DANS LA GARE<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                $this->property['roles'] = $this->m_users_role->get();
                return $this->layout->view('_users/roleatr', $this->property);
        }
        
        
        //voir le profil des caissieres principal
        public function profilcaisse($ckey, $gid, $iop, $idsg, $us, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;

                $connex = $this->m_compte_user->getusergar($this->company->ekey, $gid, $us);
                    $this->property['connex'] = $connex;

                   $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['bus_stop'] = $bus_stop;

                $this->property['pagetitle'] .= "• CAISSIER • <strong></strong>&nbsp;•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpgetad($this->company->id_entreprise);
                    
                }else
                {
                    $this->property['garedepartcomp'] = $this->m_gare_depart->cmpget($this->company->id_entreprise, $gid);
                    
                }
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['typenoms'] = $this->m_versements->nom($this->company->ekey);
                $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['compagnies'] = $this->m_compagnies->get();
                return $this->layout->view('_caisse/comptecaissier', $this->property);

        }

        public function recettecaisse($ckey, $gid, $ad, $idsg, $uc, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                    $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['gare_stop'] = $gare_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $ad);
                $this->property['conex'] = $conex;
                    $connex = $this->m_compte_user->getusergar($this->company->ekey, $gid, $uc);
                $this->property['connex'] = $connex;

                    $this->property['recettes'] = $this->m_recette->validget($this->company->ekey, $gid, $uc);
                    $this->property['recettesvalid'] = $this->m_recette->validgetmont($this->company->ekey, $gid, $uc);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['pagetitle'] .= "• VALIDATION DES RECETTES•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/valdrecet', $this->property);

        }
        public function depensecaisse($ckey, $gid, $ad, $idsg, $uc, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                $this->property['gare_stop'] = $gare_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $ad);
                $this->property['conex'] = $conex;

                    $connex = $this->m_compte_user->getusergar($this->company->ekey, $gid, $uc);
                $this->property['connex'] = $connex;

                $this->property['depenses'] = $this->m_depense->validget($this->company->ekey, $gid, $uc);
                $this->property['depensesvalid'] = $this->m_depense->validgetmont($this->company->ekey, $gid, $uc);

                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES •&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/valddepens', $this->property);

        }

        public function depensecaissecptable($ckey, $gid, $ad, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $cp = $this->input->post('_compag');
                $d1 = $this->input->post('datedebut');
                $d2 = $this->input->post('datefin');

                $con = $this->input->post('idusecon');

                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                
                $this->property['gare_stop'] = $gare_stop;

                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $ad);
                $this->property['conex'] = $conex;

                $connex = $this->m_compte_user->getusergar($this->company->ekey, $gid, $con);

                $this->property['connex'] = $connex;
                
                $this->property['tridepenses'] = $this->m_depense->validget1($this->company->ekey, $gid, $cp, $d1, $d2, $con);
                    $this->property['compagnies'] = $this->m_compagnies->get();
                    $this->property['typedocuments'] = $this->m_typedocument->get();

                    $this->property['dat1'] = $d1;

                    $this->property['dat2'] = $d2;
                
                    $this->property['cpe'] = $cp;

                    $this->property['uop'] = $con;

                    $this->property['pagetitle'] .= "• VALIDATION DES DEPENSES•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/trivalddepens', $this->property);

        }

        public function recettecaissecptable($ckey, $gid, $ad, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $cp = $this->input->post('_compag');
                $d1 = $this->input->post('datedebuts');
                $d2 = $this->input->post('datefins');

                $con = $this->input->post('idusecon');

                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);

                $this->property['gare_stop'] = $gare_stop;

                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $ad);
                $this->property['conex'] = $conex;

                $connex = $this->m_compte_user->getusergar($this->company->ekey, $gid, $con);
                $this->property['connex'] = $connex;

                $this->property['trirecettes'] = $this->m_recette->validget1($this->company->ekey, $gid, $cp, $d1, $d2, $con);
                
                $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['dat1'] = $d1;

                $this->property['dat2'] = $d2;

                $this->property['cpe'] = $cp;

                $this->property['uop'] = $con;

                $this->property['pagetitle'] .= "• VALIDATION DES RECETTES•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                return $this->layout->view('_caisse/trivaldrecette', $this->property);

        }
       
        public function depotcaisse($ckey, $gid, $ad, $idsg, $uc, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['gare_stop'] = $gare_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $ad);
                $this->property['conex'] = $conex;
                $connex = $this->m_compte_user->getusergar($this->company->ekey, $gid, $uc);
                $this->property['connex'] = $connex;
                    $this->property['depots'] = $this->m_depot->validget($this->company->ekey, $gid, $uc);
                    $this->property['depotsvalid'] = $this->m_depot->validgetmont($this->company->ekey, $gid, $uc);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• VALIDATION DES DEPOTS•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/valddept', $this->property);

        }

        //bagage escal


        public function versemetcaisse($ckey, $gid, $ad, $idsg, $uc, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                    $gare_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
                        $this->property['gare_stop'] = $gare_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $ad);
                $this->property['conex'] = $conex;
                $connex = $this->m_compte_user->getusergar($this->company->ekey, $gid, $uc);
                $this->property['connex'] = $connex;
                $this->property['versements'] = $this->m_versements->validget($this->company->ekey, $gid, $uc);
                    $this->property['versementsvalid'] = $this->m_versements->validgetmont($this->company->ekey, $gid, $uc);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• VALIDATION DES VERSEMENTS•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    
                    return $this->layout->view('_caisse/valdversement', $this->property);

        }

        //voir le profil des caissiers adjoint
        public function viewcaissier($ckey, $gid, $idcai, $idcpus, $idop, $idsg, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                
                    $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gid, $idsg);
            $this->property['bus_stop'] = $bus_stop;
                $user_connect = $this->m_compte_user->usergare($this->company->ekey, $gid, $idcpus);
                $conex = $this->m_compte_user->usget1($idop, $gid);
                    $this->property['conex'] = $conex;
                    
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $idcai);
                    $this->property['caisseident'] = $caisseident;
                    $this->property['user_connect'] = $user_connect;
                $this->property['comptejours'] = $this->m_compte_user->caissejours($this->company->ekey, $gid, $idcai, $idcpus);
                $this->property['typedocuments'] = $this->m_typedocument->get();

                    /*$this->property['recette_stop'] = $this->m_recette->valideget($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depense_stop'] = $this->m_depense->valideget($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depot_stop'] = $this->m_depot->valideget($this->company->ekey, $gid, $idcai, $idcpus);*/

                if($user_connect->userole === '18')
                {
                    $this->property['recette_stop'] = $this->m_recette->validegead($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depense_stop'] = $this->m_depense->validegead($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depot_stop'] = $this->m_depot->validegead($this->company->ekey, $gid, $idcai, $idcpus);
                }
                else
                {
                    $this->property['recette_stop'] = $this->m_recette->valideget($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depense_stop'] = $this->m_depense->valideget($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depot_stop'] = $this->m_depot->valideget($this->company->ekey, $gid, $idcai, $idcpus);
                }
                    $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• VALIDATION COMPTE • <strong>{$user_connect->username}</strong>•&nbsp;{$user_connect->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$user_connect->type_rols}</strong>";
                return $this->layout->view('_caisse/indexcompte', $this->property);

        }

        public function viewcaiss($ckey, $gid, $idcai, $idcpus, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            
                $user_connect = $this->m_compte_user->usergare($this->company->ekey, $gid, $idcpus);
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $idcai);
                    $this->property['caisseident'] = $caisseident;
                    $this->property['user_connect'] = $user_connect;
                    $this->property['recettes'] = $this->m_recette->recetcaisses($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depenses'] = $this->m_depense->depenscaisse($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['depots'] = $this->m_depot->depocaisses($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['versements'] = $this->m_versements->caisseversements($this->company->ekey, $gid, $idcai, $idcpus);
                    $this->property['compagnies'] = $this->m_compagnies->get();
                $this->property['pagetitle'] .= "• VALIDATION ARRET CAISSE • <strong>{$user_connect->username}</strong>•&nbsp;{$user_connect->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}•&nbsp;{$user_connect->type_rols}</strong>";
                return $this->layout->view('_caisse/validationcaisse', $this->property);

        }
        
        public function validerecette($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                        WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                if($sgares->sog == 1){
                    
                    $arrepor = $this->db->query("SELECT rp.code_report, rp.statutreport, rp.is_statutreport, rp.idcpuserconect  FROM report rp
                        WHERE rp.idcpuserconect = '$compt_id'
                        AND rp.statutreport = 1
                        AND rp.is_statutreport = 0")->result();

                        foreach ($arrepor as $iters) {
                            $reparras = array(
                                'is_statutreport' => 1,
                            );
                            $this->m_report->update($iters->code_report, $reparras);
                        }

                    $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.is_valdtick, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$compt_id'
                        AND p.statutvente = 1
                        AND p.is_valdtick = 0")->result();

                        foreach ($arpass as $item1) {
                            $plarras = array(
                                'is_valdtick' => 1,
                            );
                            $this->m_passager->update($item1->code_passager, $item1->code_ticket, $plarras);
                        }

                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.is_valedtick, np.cptus FROM non_passager np
                        WHERE np.cptus = '$compt_id'
                        AND np.statvente = 1
                        AND np.is_valedtick = 0")->result();

                        foreach ($arnonpass as $ites) {
                            $plarrayn = array(
                                'is_valedtick' => 1,
                            );
                            $val = $this->m_non_passager->update($ites->code_non_pass, $ites->codeticket, $plarrayn);
                        }


                }else
                {
                        $arrepor = $this->db->query("SELECT rp.code_report, rp.statutreport, rp.is_statutreport, rp.idcpuserconect  FROM report rp
                        WHERE rp.idcpuserconect = '$compt_id'
                        AND rp.statutreport = 1
                        AND rp.is_statutreport = 0")->result();

                        foreach ($arrepor as $iters) {
                            $reparras = array(
                                'is_statutreport' => 1,
                            );
                            $this->m_report->update($iters->code_report, $reparras);
                        }

                        $arpass = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.is_valdtick, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$compt_id' AND p.departclient_idgare = '$idsoug' 
                        AND p.statutvente = 1
                        AND p.is_valdtick = 0")->result();

                        foreach ($arpass as $item1) {
                            $plarras = array(
                                'is_valdtick' => 1,
                            );
                            $this->m_passager->update($item1->code_passager, $item1->code_ticket, $plarras);
                        }

                        $arnonpass = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.is_valedtick, np.cptus FROM non_passager np
                        WHERE np.cptus = '$compt_id'
                        AND np.statvente = 1
                        AND np.sousgareidentif = '$idsoug'
                        AND np.is_valedtick = 0")->result();

                        foreach ($arnonpass as $ites) {
                            $plarrayn = array(
                                'is_valedtick' => 1,
                            );
                            $val = $this->m_non_passager->update($ites->code_non_pass, $ites->codeticket, $plarrayn);
                        }
                        
                        $arpassbis = $this->db->query("SELECT p.code_passager, p.code_ticket, p.statutvente, p.is_valdtick, p.idcptuser FROM passager p
                        WHERE p.idcptuser = '$compt_id' 
                        AND p.statutvente = 1
                        AND p.is_valdtick = 0
                        AND p.departclient_idgare NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$identifiant_gare')")->result();

                        foreach ($arpassbis as $item1bis) {
                            $plarrasbis = array(
                                'is_valdtick' => 1,
                            );

                        $this->m_passager->update($item1bis->code_passager, $item1bis->code_ticket, $plarrasbis);
                        }

                        $arnonpassbis = $this->db->query("SELECT np.code_non_pass, np.codeticket, np.statvente, np.is_valedtick, np.cptus FROM non_passager np
                        WHERE np.cptus = '$compt_id'
                        AND np.statvente = 1
                        AND np.is_valedtick = 0
                        AND np.sousgareidentif NOT IN (SELECT s.idsousgare FROM sousgare s
                            WHERE s.gareprinceid = '$identifiant_gare')")->result();

                        foreach ($arnonpassbis as $itesbis) {
                            $plarraynbis = array(
                                'is_valedtick' => 1,
                            );
                            $valbis = $this->m_non_passager->update($itesbis->code_non_pass, $itesbis->codeticket, $plarraynbis);
                        }

                }
                
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $iduser,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycomp = array(
                    'is_validcompte'=> 1,

                );
                $this->m_comptes_guichet->update($idcptvers, $arraycomp);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $iduser,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profils/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profils/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }
		
        public function validerecetteesc($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s
                        WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                
                $arpase = $this->db->query("SELECT es.idclescal, es.arrcptescal, es.iduseescal FROM escalclients es
                        WHERE es.iduseescal = '$compt_id'
                        AND es.arrcptescal = 1
                        AND es.arrcptchefgescal = 0")->result();
    
                        foreach ($arpase as $items1) {
                            $plarrase = array(
                                'arrcptchefgescal' => 1,
                            );
                            $this->m_escalclients->update($items1->idclescal, $plarrase);

                        }
                
                
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $iduser,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycomp = array(
                    'is_validcompte'=> 1,

                );
                $this->m_comptes_guichet->update($idcptvers, $arraycomp);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $iduser,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profilsesc/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsesc/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }
        //validation recette bagage

        public function validerecettebagu($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                if($sgares->sog == 1){
                    
                    $arrebags = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.validbag, b.idoperabagage FROM bagages b
                        WHERE b.idoperabagage = '$compt_id'
                        AND b.isvalidbag = 1
                        AND b.validbag = 0")->result();

                        foreach ($arrebags as $iterbg) {
                            $arrebags = array(
                                'validbag' => 1,
                            );
                            $this->m_bagage->update($iterbg->id_bagage, $arrebags);
                        }

                   
                }else
                {
                        $arrebags = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.validbag, b.idoperabagage FROM bagages b
                        WHERE b.idoperabagage = '$compt_id'
                        AND b.idsgarebag = '$idsoug'
                        AND b.isvalidbag = 1
                        AND b.validbag = 0")->result();

                        foreach ($arrebags as $iterbg) {
                            $arrebags = array(
                                'validbag' => 1,
                            );
                            $this->m_bagage->update($iterbg->id_bagage, $arrebags);
                        }

                }
                
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $iduser,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycompb = array(
                    'is_validcomptebg'=> 1,

                );
                $this->m_comptes_bagage->update($idcptvers, $arraycompb);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $iduser,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profils/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profils/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        public function validerecettebag($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                if($sgares->sog == 1){
                    
                    $arrebags = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.validbag, b.idoperabagage FROM bagages b
                        WHERE b.idoperabagage = '$compt_id'
                        AND b.isvalidbag = 1
                        AND b.validbag = 0")->result();

                        foreach ($arrebags as $iterbg) {
                            $arrebags = array(
                                'validbag' => 1,
                            );
                            $this->m_bagage->update($iterbg->id_bagage, $arrebags);
                        }

                   
                }else
                {
                        $arrebags = $this->db->query("SELECT b.id_bagage, b.isvalidbag, b.validbag, b.idoperabagage FROM bagages b
                        WHERE b.idoperabagage = '$compt_id'
                        AND b.idsgarebag = '$idsoug'
                        AND b.isvalidbag = 1
                        AND b.validbag = 0")->result();

                        foreach ($arrebags as $iterbg) {
                            $arrebags = array(
                                'validbag' => 1,
                            );
                            $this->m_bagage->update($iterbg->id_bagage, $arrebags);
                        }

                }
                
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $iduser,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycompb = array(
                    'is_validcomptebg'=> 1,

                );
                $this->m_comptes_bagage->update($idcptvers, $arraycompb);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $iduser,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profilsbagage/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsbagage/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        public function profi($ckey, $gid, $isg, $ad, $cdid, $iop, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                    $user_connect = $this->m_compte_user->getusergare1($this->company->ekey, $gid, $ad);
                    $this->property['user_connect'] = $user_connect;
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $cdid);
                    $this->property['caisseident'] = $caisseident;
                    
                        $this->property['montantversers'] = $this->m_comptes_guichet->getcompte($this->company->ekey, $gid, $isg, $ad);
                        $this->property['versementscourrier'] = $this->m_comptes_courrier->getcompte($this->company->ekey, $gid, $isg, $ad);
                        $this->property['versementsrecettecour'] = $this->m_comptes_courrierrecet->getcompterct($this->company->ekey, $gid, $isg, $ad);
                        $this->property['montantversersbag'] = $this->m_comptes_bagage->getcompte($this->company->ekey, $gid, $isg, $ad);

                        $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• RECETTES •&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_caisse/compteuser', $this->property);

        }
        public function profiesc($ckey, $gid, $isg, $ad, $cdid, $iop, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                    $user_connect = $this->m_compte_user->getusergare1($this->company->ekey, $gid, $ad);
                    $this->property['user_connect'] = $user_connect;
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $cdid);
                    $this->property['caisseident'] = $caisseident;
                    
                        $this->property['montantversers'] = $this->m_comptes_guichet->getcompte($this->company->ekey, $gid, $isg, $ad);
                        $this->property['versementscourrier'] = $this->m_comptes_courrier->getcompte($this->company->ekey, $gid, $isg, $ad);
                        $this->property['montantverbags'] = $this->m_comptes_bagage->getcompte($this->company->ekey, $gid, $isg, $ad);
                        $this->property['versementsrecettecour'] = $this->m_comptes_courrierrecet->getcompterct($this->company->ekey, $gid, $isg, $ad);
                        $this->property['genresguichet'] = $this->m_genre_recette->getrecet();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• RECETTES •&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_caisse/compteuseresc', $this->property);

        }

        public function profibag($ckey, $gid, $isg, $ad, $cdid, $iop, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                    $user_connect = $this->m_compte_user->getusergare1($this->company->ekey, $gid, $ad);
                    $this->property['user_connect'] = $user_connect;
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $cdid);
                    $this->property['caisseident'] = $caisseident;
                    
                        $this->property['montantversers'] = $this->m_comptes_bagage->getcompte($this->company->ekey, $gid, $isg, $ad);
                        
                        $this->property['genresguichet'] = $this->m_genre_recette->getrecetbg();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• RECETTES •&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_caisse/compteuserbag', $this->property);

        }
        public function profideps($ckey, $gid, $isg, $ad, $cdid, $iop, $j, $m, $a)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                    $user_connect = $this->m_compte_user->getusergare1($this->company->ekey, $gid, $ad);
                    $this->property['user_connect'] = $user_connect;
                    $conex = $this->m_compte_user->getusergare($this->company->ekey, $gid, $iop);
                    $this->property['conex'] = $conex;
                    $caisseident = $this->m_caisse->get($this->company->id_entreprise, $gid, $cdid);
                    $this->property['caisseident'] = $caisseident;
                    
                        
                        $this->property['versementsdepensecour'] = $this->m_comptes_courrierdepens->getcomptedep($this->company->ekey, $gid, $isg, $ad);
                        $this->property['genres'] = $this->m_genre_depense->getdeps();
                        $this->property['compagnies'] = $this->m_compagnies->get();
                        $this->property['typedocuments'] = $this->m_typedocument->get();
                    $this->property['pagetitle'] .= "• DEPENSES DU COURRIER•&nbsp;{$conex->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                    return $this->layout->view('_caisse/compteuserdeps', $this->property);

        }
        
        
        public function recettevaliderecet($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

                $iduser = $this->input->post('userconnected');
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');           
                $caisi= $this->input->post('idgar');

            if($this->input->post('daterecep')!= '')
            {
                if($sgares->sog == 1)
                {

                    $arcour = $this->db->query("SELECT e.courrierexpid, e.num_cour, e.departcolis, e.statutcour, e.courrierdepartgare FROM courriers_exp e
                    WHERE e.idoperateur = '$compt_id'
                    AND e.statutcour = 1
                    AND e.validcour = 0")->result();

                        foreach ($arcour as $items1) {
                            $plarras = array(
                                'validcour' => 1,
                        );

                        $this->m_courrier_expedier->update($items1->courrierexpid, $items1->num_cour, $items1->departcolis, $plarras);
                    }

                    



                    $arraytrans = $this->db->query("SELECT rc.recetid, rc.statutargent, rc.idsousgarrecet FROM recettecourriers rc
                    WHERE rc.idoprarecet = '$compt_id'
                    AND rc.statutargent = 1
                    AND rc.validargt = 0")->result();

                    foreach ($arraytrans as $trans) {
                        $trarras = array(
                            'validargt' => 1,
                        );
                        $this->m_courrier_recet->update($trans->recetid, $trarras);
                    }

                    
                    $arrayretrait = $this->db->query("SELECT dc.depenscourid, dc.statutretrait, dc.idsousgaredepens FROM depensescourriers dc
                    WHERE dc.idopradepens = '$compt_id'
                    AND dc.statutretrait = 1
                    AND dc.validretrait = 0")->result();

                    foreach ($arrayretrait as $ret) {
                        $rtarras = array(
                            'validretrait' => 1,
                        );
                        $this->m_courrier_depens->update($ret->depenscourid, $rtarras);
                    }

                    $arraysaudep = $this->db->query("SELECT at.autreid, at.actifautredepense, at.idsgredeps FROM autresdepenses at
                    WHERE at.idoperaconnect = '$compt_id'
                    AND at.actifautredepense = 1
                    AND at.valdautre = 0")->result();

                    if($arraysaudep != NULL)
                    {

                        foreach ($arraysaudep as $aret) {
                            $rtarraus = array(
                                'valdautre' => 1,
                            );

                            $this->m_autredepense->update($aret->autreid, $rtarraus);
                        }
                    
                    }

                }
                else

                {
                        $arcour = $this->db->query("SELECT e.courrierexpid, e.num_cour, e.departcolis, e.statutcour, e.courrierdepartgare FROM courriers_exp e
                        WHERE e.idoperateur = '$compt_id'
                        AND e.statutcour = 1
                        AND e.courrierdepartgare = '$idsoug'
                        AND e.validcour = 0")->result();

                        foreach ($arcour as $items1) {
                            $plarras = array(
                                'validcour' => 1,
                            );
                            $this->m_courrier_expedier->update($items1->courrierexpid, $items1->num_cour, $items1->departcolis, $plarras);
                        }

                        $arcourbs = $this->db->query("SELECT e.courrierexpid, e.num_cour, e.departcolis, e.statutcour, e.courrierdepartgare FROM courriers_exp e
                            WHERE e.idoperateur = '$compt_id'
                            AND e.statutcour = 1
                            AND e.validcour = 0
                            AND e.courrierdepartgare NOT IN (SELECT s.idsousgare FROM sousgare s
                                    WHERE s.gareprinceid = '$identifiant_gare')")->result();


                            foreach ($arcourbs as $itemsb1) {
                                $plarrasb = array(
                                    'validcour' => 1,
                                );
                                $this->m_courrier_expedier->update($itemsb1->courrierexpid, $itemsb1->num_cour, $itemsb1->departcolis, $plarrasb);
                            }
                    
                        $arraytrans = $this->db->query("SELECT rc.recetid, rc.statutargent, rc.idsousgarrecet FROM recettecourriers rc
                        WHERE rc.idoprarecet = '$compt_id'
                        AND rc.statutargent = 1
                        AND rc.idsousgarrecet = '$idsoug'
                        AND rc.validargt = 0")->result();

                        foreach ($arraytrans as $trans) {
                            $trarras = array(
                                'validargt' => 1,
                            );
                            $this->m_courrier_recet->update($trans->recetid, $trarras);
                        }

                        
                        $arrayretrait = $this->db->query("SELECT dc.depenscourid, dc.statutretrait, dc.idsousgaredepens FROM depensescourriers dc
                        WHERE dc.idopradepens = '$compt_id'
                        AND dc.statutretrait = 1
                        AND dc.idsousgaredepens = '$idsoug'
                        AND dc.validretrait = 0")->result();

                        foreach ($arrayretrait as $ret) {
                            $rtarras = array(
                                'validretrait' => 1,
                            );
                            $this->m_courrier_depens->update($ret->depenscourid, $rtarras);
                        }

                        $arraysaudep = $this->db->query("SELECT at.autreid, at.actifautredepense, at.idsgredeps FROM autresdepenses at
                        WHERE at.idoperaconnect = '$compt_id'
                        AND at.actifautredepense = 1
                        AND at.valdautre = 0
                        AND at.idsgredeps = '$idsoug'")->result();

                        if($arraysaudep != NULL)
                        {

                            foreach ($arraysaudep as $aret) {
                                $rtarraus = array(
                                    'valdautre' => 1,
                                );

                                $this->m_autredepense->update($aret->autreid, $rtarraus);
                            }
                        
                        }

                }

                    $arrayrecettecr = array(
                        'idcaisse' => $this->input->post('idgar'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $this->input->post('idcompa'),
                        'recetsgid' => $idsoug,
                        'type_recet' => $this->input->post('interne'),
                        'idopera' => $iduser,
                        'nom' => $this->input->post('nom'),
                        'montant_recet' => $this->input->post('montantvers'),
                        'commentaire_recet' => $this->input->post('comment'),
                        'date_recet' => $this->input->post('daterecep'),
                        'createdrecet_at' => now('UTC'),
                    );
                    $recette = $this->m_recette->create($arrayrecettecr);
                               
                    $arraycompcr = array(
                        'validcompteis' => 1,

                    );
                    $this->m_comptes_courrier->update($idcptvers, $arraycompcr);

                    $arrayretrait = $this->db->query("SELECT dc.depenscourid, dc.statutretrait, dc.idsousgaredepens FROM depensescourriers dc
                    WHERE dc.idopradepens = '$compt_id'
                    AND dc.statutretrait = 1
                    AND dc.idsousgaredepens = '$idsoug'")->result();

                    if($arrayretrait != NULL)
                    {

                        foreach ($arrayretrait as $ret) {
                            $rtarras = array(
                                'validretrait' => 1,
                            );

                            $this->m_courrier_depens->update($ret->depenscourid, $rtarras);
                        }
                    
                    }

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $iduser,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        redirect('utilisateurs/'.$this->session->company->ekey. '/profils/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id. '/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profils/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        public function recettevalidedepens($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            

                $iduser = $this->input->post('userconnected');
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');           
                $caisi= $this->input->post('idgar');
                
            if($this->input->post('daterecepdep')!= '')
            {
                
                $arrayretrait = $this->db->query("SELECT dc.depenscourid, dc.statutretrait, dc.idsousgaredepens FROM depensescourriers dc
                    WHERE dc.idopradepens = '$compt_id'
                    AND dc.statutretrait = 1
                    AND dc.idsousgaredepens = '$idsoug'")->result();

                    foreach ($arrayretrait as $ret) {
                        $rtarras = array(
                            'validretrait' => 1,
                        );
                        $this->m_courrier_depens->update($ret->depenscourid, $rtarras);
                    }


                    $arraysaudep = $this->db->query("SELECT at.autreid, at.actifautredepense, at.idsgredeps FROM autresdepenses at
                    WHERE at.idoperaconnect = '$compt_id'
                    AND at.actifautredepense = 1
                    AND at.valdautre = 0
                    AND at.idsgredeps = '$idsoug'")->result();

                    if($arraysaudep != NULL)
                    {

                        foreach ($arraysaudep as $aret) {
                            $rtarraus = array(
                                'valdautre' => 1,
                            );

                            $this->m_autredepense->update($aret->autreid, $rtarraus);
                        }
                    
                    }
                    $arraydep = array(
                        'idcaisse_depens' => $caisi,
                        'id_genre_depense' => $this->input->post('genredep'),
                        'idop_dep' => $iduser,
                        'sousgidepens' => $idsoug,
                        'type_depense' => $this->input->post('internedep'),
                        'compkey_dep' => $this->input->post('_compagdep'),
                        'typpersonel' => 1,
                        'nom_perso' => $this->input->post('nom'),
                        'montant_depens' => $this->input->post('montantdepens'),
                        'commentaire' => $this->input->post('comments'),
                        'motif' => $this->input->post('motifs'),
                        'date_depens' => $this->input->post('daterecepdep'),
                    );
                    $depens = $this->m_depense->create($arraydep);
                    
                           
                $arraycompcrd = array(
                    'validcompteisdepens'=> 1,

                );
                $this->m_comptes_courrierdepens->update($idcptvers, $arraycompcrd);

                if($this->session->agent->userole === '4')
                {
                    $updeps = array(
                        'active_dep' => 1, 
                        'is_validedep' => 1, 
                        'is_actifdep' => 1,
                        'opevalid' => $iduser,
                    );
                    $this->m_depense->update($depens, $updeps);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

                    redirect('utilisateurs/'.$this->session->company->ekey. '/profilsdep/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id. '/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));

                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsdep/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        //add user
        public function adduse($ckey)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
            $arguser = array(
                'cle_comp' => $company->ekey,
                'first_name' => $this->input->post('firstname'),
                'last_name' => $this->input->post('lastname'),
                'phone' => $this->input->post('phone'),
                'phone2' => $this->input->post('phone2'),
                'email' => $this->input->post('email'),
                'created_atutil' => now('UTC'),
            );
            
            if (!empty($arguser)) {
                $ruserid = $this->m_utilisateur->create($arguser);
                
               
                    $this->property['INSERT_SUCCESS'] = TRUE;
            }
                redirect('utilisateurs/' . $this->session->company->ekey);
        }
        
        public function edit_use($ckey, $id)
        {
            $company = $this->m_entreprises->get_key($ckey);
            $argu = array(
                'cle_comp' => $company->ekey,
                'first_name' => $this->input->post('firstname'),
                'last_name' => $this->input->post('lastname'),
                'phone' => $this->input->post('phone'),
                'phone2' => $this->input->post('phone2'),
                'email' => $this->input->post('email'),
            );
            if ($this->m_utilisateur->update($id, $argu) === TRUE) {
            
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, null, $this->property);
            }
        }
        
        public function add($ckey, $uid)
        {
            $company = $this->m_entreprises->get_key($ckey);
                
                $nu = $this->input->post('username');
                $usp = sha1($this->input->post('pass1'));
                $cusp = sha1($this->input->post('confirm'));

                $fsecl = $this->db->query("SELECT * FROM compte_user cu WHERE cu.userlog_id = '$uid' AND cu.username = '$nu' AND cu.upassword = '$usp' AND cu.confirm_password = '$cusp'")->row();

                if($fsecl == NULL){

                    $comptelogin = array(
                        'userlog_id' => $uid,
                        'username' => $this->input->post('username'),
                        'upassword' => sha1($this->input->post('pass1')),
                        'confirm_password' => sha1($this->input->post('confirm')),
                        'createdcptus_at' => now('UTC'),
                    );
                    
                    $this->m_compte_user->create($comptelogin);
                }
                    $this->property['INSERT_SUCCESS'] = TRUE;
                redirect('utilisateurs/' . $this->session->company->ekey);
        }
        
        public function edit_($ckey, $id, $ul)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
                    $comptelogin = array(
                        'username' => $this->input->post('username'),
                        'upassword' => sha1($this->input->post('pass1')),
                        'confirm_password' => sha1($this->input->post('confirm')),
                        
                    );
                    
                    $this->m_compte_user->update($id, $comptelogin);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$ul.'/compte/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        //active ou désactiver un compte utilisateur
        public function active($ckey, $id, $ul, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    
                    if($statut == 0){
                        $stat = 1;
                        $isc = 0;
                    }
                    else{
                        $stat = 0;
                    }
                    $comptelogin = array(
                        'activer' => $stat,
                        'is_conect' => $isc,
                    );
                    
                    $this->m_compte_user->update($id, $comptelogin);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$ul.'/compte/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function actif($ckey, $id, $uid, $cp, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($statut == 0){
                        $stat = 1;
                    }
                    else
                    {
                        $stat = 0;
                    }
                    $comptelogin = array(
                        'comptactif' => $stat,
                    );
                    
                    $this->m_user_login->update($id, $comptelogin);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$uid.'/'.$cp.'/garecompte/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function actifs($ckey, $id, $idus, $g, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($statut == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $compteat = array(
                        'activer_role' => $stat,
                    );
                    
                    $this->m_roleattribution->update($id, $compteat);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$idus.'/'. $g. '/rolecompte/'. mdate("%d/%m/%Y", now('UTC')));
            
        }
        public function addprofil_($ckey, $id)
        {
            $company = $this->m_entreprises->get_key($ckey);
                
                $dsg = $this->input->post('gareuser');

                $seclg = $this->db->query("SELECT * FROM user_login u WHERE u.uid_usercpte = '$id' AND u.guser = '$dsg'")->row();

                if($seclg == NULL){

                    $comptelogin = array(
                        'uid_usercpte' => $id,
                        'guser' => $this->input->post('gareuser'),
                        'created_atuslg' => now('UTC'),
                    );
                    
                    $this->m_user_login->create($comptelogin);
                }
                    $this->property['INSERT_SUCCESS'] = TRUE;
                
            redirect('utilisateurs/' . $this->session->company->ekey);
            
        }

        public function addattrb($ckey, $ul)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    
                $gf = $this->input->post('fonction');

                $seclgf = $this->db->query("SELECT * FROM attributions_role a WHERE a.idgestcompte = '$ul' AND a.userole = '$gf'")->row();

                if($seclgf == NULL){

                    $comptelogin = array(
                        'idgestcompte' => $ul,
                        'userole' => $this->input->post('fonction'),
                    );
                    
                    $this->m_roleattribution->create($comptelogin);
                }
                    $this->property['INSERT_SUCCESS'] = TRUE;
                redirect('utilisateurs/' . $this->session->company->ekey);
        }

        public function addattrbs($ckey, $at)
        {
            $company = $this->m_entreprises->get_key($ckey);
            
                    $comptelogin = array(

                        'userole' => $this->input->post('fonction'),
                    );
                    
                        $this->m_roleattribution->update($at, $comptelogin);

                    $this->property['INSERT_SUCCESS'] = TRUE;
                redirect('utilisateurs/' . $this->session->company->ekey);
        }

        public function edit_pro($ckey, $uid, $ucp)
        {
            $company = $this->m_entreprises->get_key($ckey);

            
                    $comptelogin = array(
                        'guser' => $this->input->post('gareuser'),
                    );
                    
                    $this->m_user_login->update($ucp, $comptelogin);

                    $this->property['INSERT_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/gTv/'.$uid.'/'.$ucp.'/garecompte/'. mdate("%d/%m/%Y", now('UTC')));
        
        }

        public function affect($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['profilusers'] = $this->m_user_login->get($this->company->ekey);
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;PROFILS<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                $this->property['garees'] = $this->m_gares->get($this->company->id_entreprise);

                $this->property['gares'] = $this->m_gare_depart->get($this->company->id_entreprise);
                return $this->layout->view('_users/afcompt', $this->property);
        }

        public function affectrole($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['profilusers'] = $this->m_roleattribution->get($this->company->ekey);
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;PROFILS<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                return $this->layout->view('_users/afcomptgarerole', $this->property);
        }

        public function editpage_($ckey, $idc, $idr, $idus, $g)
        {
            $company = $this->m_entreprises->get_key($ckey);
            $dos = $this->input->post('roledosattr');

            $secl = $this->db->query("SELECT * FROM appdossierrole ap WHERE ap.iddossrole = '$dos' AND ap.idroleuse = '$idr' AND ap.idcomptrole = '$idc'")->row();
                if($secl == NULL){

                    $attribuerp = array(
                            'iddossrole' => $this->input->post('roledosattr'),
                            'idroleuse' => $idr,
                            'idcomptrole' => $idc,
                            
                        );
                        
                        $this->m_appdossier->create($attribuerp);

                }
                    
                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/voirprofilpage/' . $this->session->company->ekey);
            
        }

        public function affectpage($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $this->property['profiluserspage'] = $this->m_appdossier->get();
                $this->property['pagetitle'] .= "&nbsp;•&nbsp;PAGES<strong>&nbsp;•&nbsp;{$this->company->nom_entreprise}</strong> ";
                
                return $this->layout->view('_users/afcomptpage', $this->property);
        }
        public function activeprofil($ckey, $id, $idcp, $ul, $statut)
        {
            $company = $this->m_entreprises->get_key($ckey);
                    if($statut == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $actifcompte = array(
                        'comptactif' => $stat,
                    );
                    
                    $this->m_user_login->update($id, $actifcompte);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/voirprofil/' . $this->session->company->ekey);
            
        }

        public function updatepage_($ckey, $id, $idcp, $urp)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    
                    $upgcompte = array(
                        'iddossrole' => $this->input->post('roledosattr'),
                    );
                    
                    $this->m_appdossier->update($id, $upgcompte);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/voirprofilpage/' . $this->session->company->ekey);
            
        }

        public function activepagegd($ckey, $id, $idcp, $statutp)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statutp == 0){
                        $stat = 1;
                    }
                    else{
                        $stat = 0;
                    }
                    $actifpcompte = array(
                        'activedosrole' => $stat,
                    );
                    
                    $this->m_appdossier->update($id, $actifpcompte);

                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/voirprofilpage/' . $this->session->company->ekey);
        }

        public function recettevaliderecetesc($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $this->company = $this->m_entreprises->get_key($ckey); 
            
            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

                $iduser = $this->input->post('userconnected');
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');           
                $caisi= $this->input->post('idgar');

            if($this->input->post('daterecep')!= '')
            {
                if($sgares->sog == 1)
                {

                    $arcour = $this->db->query("SELECT ex.courrierexpidesc, ex.num_couresc, ex.departcolisesc, ex.statutcouresc, ex.courrierdepartgareesc FROM courriers_expesc ex
                    WHERE ex.idoperateuresc = '$compt_id'
                    AND ex.statutcouresc = 1
                    AND ex.validcouresc = 0")->result();

                        foreach ($arcour as $items1) {
                            $plarras = array(
                                'validcouresc' => 1,
                        );

                        $this->m_courrier_expedieresc->update($items1->courrierexpidesc, $items1->num_couresc, $items1->departcolisesc, $plarras);
                    }
                }
                else

                {
                        $arcour = $this->db->query("SELECT ex.courrierexpidesc, ex.num_couresc, ex.departcolisesc, ex.statutcouresc, ex.courrierdepartgareesc FROM courriers_expesc ex
                        WHERE ex.idoperateuresc = '$compt_id'
                        AND ex.statutcouresc = 1
                        AND ex.courrierdepartgareesc = '$idsoug'
                        AND ex.validcouresc = 0")->result();

                        foreach ($arcour as $items1) {
                            $plarras = array(
                                'validcouresc' => 1,
                            );
                            $this->m_courrier_expedieresc->update($items1->courrierexpidesc, $items1->num_couresc, $items1->departcolisesc, $plarras);
                        }
                }

                    $arrayrecettecr = array(
                        'idcaisse' => $this->input->post('idgar'),
                        'id_genre_recet' => $this->input->post('genre'),
                        'compkey_recet' => $this->input->post('idcompa'),
                        'recetsgid' => $idsoug,
                        'type_recet' => $this->input->post('interne'),
                        'idopera' => $iduser,
                        'nom' => $this->input->post('nom'),
                        'montant_recet' => $this->input->post('montantvers'),
                        'commentaire_recet' => $this->input->post('comment'),
                        'date_recet' => $this->input->post('daterecep'),
                        'createdrecet_at' => now('UTC'),
                    );
                    $recette = $this->m_recette->create($arrayrecettecr);
                    $arraycompcr = array(
                        'validcompteis' => 1,
                    );
                    $this->m_comptes_courrier->update($idcptvers, $arraycompcr);
                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $iduser,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        
                    redirect('utilisateurs/'.$this->session->company->ekey. '/profilsesc/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id. '/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));
                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsesc/'.$identifiant_gare.'/'.$idsoug. '/'. $compt_id.'/'.$caisi.'/'.$iduser.'/'.mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey.'/gTv/'.$identifiant_gare.'/cais/'.$iduser.'/'.$idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        public function validerecettebagesc($ckey, $identifiant_gare, $idsoug, $compt_id, $idcptvers)
        {

            $sgares = $this->db->query("SELECT count(idsousgare) AS sog FROM sousgare s WHERE s.gareprinceid = '$identifiant_gare'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnected');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');           
            $caisi= $this->input->post('idgar');
            
            if($this->input->post('daterecep')!= '')
            {

                if($sgares->sog == 1){
                    
                    $arrebagsesc = $this->db->query("SELECT be.id_bagageesc, be.isvalidbagesc, be.validbagesc, be.idoperabagageesc FROM bagagesesc be
                        WHERE be.idoperabagageesc = '$compt_id'
                        AND be.isvalidbagesc = 1
                        AND be.validbagesc = 0")->result();

                        foreach ($arrebagsesc as $iterbgesc) {
                            $arrebagsesc = array(
                                'validbagesc' => 1,
                            );
                            $this->m_bagageesc->update($iterbgesc->id_bagageesc, $arrebagsesc);
                        }
                   
                }else
                {
                        $arrebagsesc = $this->db->query("SELECT be.id_bagageesc, be.isvalidbagesc, be.validbagesc, be.idoperabagageesc FROM bagagesesc be
                        WHERE be.idoperabagageesc = '$compt_id'
                        AND be.idsgarebagesc = '$idsoug'
                        AND be.isvalidbagesc = 1
                        AND be.validbagesc = 0")->result();

                        foreach ($arrebagsesc as $iterbgesc) {
                            $arrebagsesc = array(
                                'validbagesc' => 1,
                            );
                            $this->m_bagageesc->update($iterbgesc->id_bagageesc, $arrebagsesc);
                        }

                }
                
                $arrayrecette = array(
                    'idcaisse' => $this->input->post('idgar'),
                    'id_genre_recet' => $this->input->post('genre'),
                    'compkey_recet' => $this->input->post('idcompa'),
                    'recetsgid' => $idsoug,
                    'type_recet' => $this->input->post('interne'),
                    'idopera' => $iduser,
                    'nom' => $this->input->post('nom'),
                    'montant_recet' => $this->input->post('montantverse'),
                    'commentaire_recet' => $this->input->post('comment'),
                    'date_recet' => $this->input->post('daterecep'),
                    'createdrecet_at' => now('UTC'),
                );
                $recette = $this->m_recette->create($arrayrecette);
                           
                $arraycompb = array(
                    'is_validcomptebg'=> 1,

                );
                $this->m_comptes_bagage->update($idcptvers, $arraycompb);

                if($this->session->agent->userole === '4')
                {
                    $array = array(
                        'active_recet' => 1, 
                        'is_validerecet' => 1, 
                        'is_actifrecet' => 1,
                        'operavalid' => $iduser,
                    );
                        $this->m_recette->update($recette, $array);

                        $this->property['UPDATE_SUCCESS'] = TRUE;
                        
                        redirect('utilisateurs/'.$this->session->company->ekey.'/profilsesc/'.$identifiant_gare.'/'.$idsoug.'/'.$compt_id.'/'. $caisi.'/'.$iduser.'/' . mdate("%d/%m/%Y", now('UTC')));
                }
                else 
                redirect('utilisateurs/'.$this->session->company->ekey. '/profilsesc/'. $identifiant_gare. '/'. $idsoug. '/'. $compt_id.'/'. $caisi.'/'.$iduser. '/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $idsoug.'/'. mdate("%d/%m/%Y", now('UTC')));
        }
    }
    /* End of file: Utilisateurs.php */
    /* File location: application/controllers/Utilisateurs.php */