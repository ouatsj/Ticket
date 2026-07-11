<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Arretcaisses extends MY_Controller
    {
        public $property = array(
            'title' => 'Chef Guichet',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        
        private $company;
        public $profil;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        
        //cassiere
        public function view($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $icx = $this->session->agent->cpuser_id;

                $this->property['pagetitle'] .= " • ARRÊT COMPTE • <strong>{$this->company->nom_entreprise}</strong>";
                $this->property['recettes'] = $this->m_recette->rget($this->company->ekey);
                $this->property['typedocuments'] = $this->m_typedocument->get();
                $this->property['comptejours'] = $this->m_compte_user->getjours($this->company->ekey, $icx);
                $this->property['depenses'] = $this->m_depense->depget($this->company->ekey, $icx);
                $this->property['depots'] = $this->m_depot->depoget($this->company->ekey, $icx);
                return $this->layout->view('_caisse/index', $this->property);
          
        }
        
        //arret des recettes, depenses, depots par caisse
        public function unstop($ckey, $g, $idc, $idcpt)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $r=$this->input->post('recettetotal');
            $dpe=$this->input->post('depensetotal');
            $dpo=$this->input->post('totaldepot');
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.idopera FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.active_recet = 0
                    AND r.idcaisse ='$idc'")->result();

                    foreach ($cfrecet as $item7) {
                        $plarray = array(
                            'active_recet' => 1,
                            'valid_recet' => 'valid',
                        );
                        $vald_recet = $this->m_recette->update($item7->id_recette, $plarray);
                    }

                $cfdepe = $this->db->query("SELECT d.id_depense, d.active_dep, d.idop_dep FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.active_dep = 0
                    AND d.idcaisse_depens = '$idc'")->result();

                    foreach ($cfdepe as $item8) {
                        $dplarray = array(
                            'active_dep' => 1,
                            'valid_depens' => 'valid',
                        );
                        $vald_dep = $this->m_depense->update($item8->id_depense, $dplarray);
                    }
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('caisses/' . $this->session->company->ekey.'/cais/'.$g. '/'. $idc. '/'. $iduser.'/arretcaisse_adjoint/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }
        
        //validation globale des recettes, depenses, depots des caisse secondaire par la caissière principale
        
        /*public function validerecette($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.active_recet = 1
                    AND r.idcaisse ='$idc'
                    AND r.is_validerecet = 0")->result();

                    foreach ($cfrecet as $item9) {
                        $plarray = array(
                            'is_actifrecet' => 1,
                            'is_validerecet' => 1,
                            'operavalid' => $iduser,
                        );
                        $vald_recet = $this->m_recette->update($item9->id_recette, $plarray);
                    }

                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function rejetrecette($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get($ckey);
           
            
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse, r.valid_recet FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.active_recet = 1
                    AND r.idcaisse ='$idc'
                    AND r.is_validerecet = 0
                    AND r.valid_recet = 'valid'")->result();

                    foreach ($cfrecet as $item10) {
                        $plarray = array(
                            'active_recet' => 0,
                            'is_actifrecet' => 0,
                            'is_validerecet' => 0,
                            'valid_recet' => 'rejet',
                        );
                        $vald_recet = $this->m_recette->update($item10->id_recette, $plarray);
                    }

                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
              redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function validedepense($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
                $cfdepes = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.active_dep = 1
                    AND d.idcaisse_depens = '$idc'
                    AND d.is_validedep = 0")->result();

                    foreach ($cfdepes as $cfdep) {
                        $dplarray = array(
                            'is_validedep' => 1,
							'is_actifdep' => 1,
                            'opevalid' => $iduser,
                        );
                        $vald_dep = $this->m_depense->update($cfdep->id_depense, $dplarray);
                    }
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function rejetdepense($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
                $cfdepe = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.valid_depens, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.active_dep = 1
                    AND d.idcaisse_depens = '$idc'
                    AND d.is_validedep = 0
                    AND d.valid_depens = 'valid'")->result();

                    foreach ($cfdepe as $teme1) {
                        $dplarray = array(
                            'active_dep' => 0,
							'is_actifdep' => 0,
                            'is_validedep' => 0,
                            'valid_depens' => 'rejet',
                        );
                        $vald_dep = $this->m_depense->update($teme1->id_depense, $dplarray);
                    }
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }
        public function validedepot($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.idcaisse_depot FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND d.idcaisse_depot = '$idc'
                    AND d.is_validdepo = 0")->result();

                    foreach ($cfdepo as $tems) {
                        $dpolarray = array(
                            'is_validdepo' => 1,
                            'opvalid' => $iduser,
                        );
                        $vald_depo = $this->m_depot->update($tems->id_depot, $dpolarray);
                    }
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function rejetdepot($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           

                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.valid_depo, d.idcaisse_depot FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND d.idcaisse_depot = '$idc'
                    AND d.is_validdepo = 0
                    AND d.valid_depo = 'valid'")->result();

                    foreach ($cfdepo as $tem) {
                        $dpolarray = array(
                            'is_validdepo' => 1,
                            'valid_depo' => 'rejet',
                        );
                        $vald_depo = $this->m_depot->update($tem->id_depot, $dpolarray);
                    }
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function validrecette($ckey, $g, $idc, $idcpt, $recet)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
        
                        $plarray = array(
                            'commentaire_recet'=> $this->input->post('comment'),
                            'idopera' => $idcpt,
                            'idcaisse' => $idc,
                            'is_actifrecet' => 1,
                            'is_validerecet' => 1,
                            'operavalid' => $iduser,
                        );
                        $vald_recet = $this->m_recette->update($recet, $plarray);
                   
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('caisses/' . $this->session->company->ekey.'/RdD/'.$g. '/'. $idc.'/'.  $idcpt.'/validation_recettes/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function rejetrecet($ckey, $g, $idc, $idcpt, $recet)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                $plarray = array(
                    'commentaire_recet'=> $this->input->post('comment'),
                    'idopera' => $idcpt,
                    'idcaisse' => $idc,
                    'active_recet' => 0,
                    'is_actifrecet' => 0,
                    'is_validerecet' => 0,
                    'valid_recet' => 'rejet',
                );
                $vald_recet = $this->m_recette->update($recet, $plarray);
                   
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('caisses/' . $this->session->company->ekey.'/RdD/'.$g. '/'. $idc.'/'.  $idcpt.'/validation_recettes/'. $iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function validdepense($ckey, $g, $idc, $idcpt, $idp)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                
                        $dplarray = array(
                            'commentaire'=> $this->input->post('comment'),
                            'idop_dep' => $idcpt,
                            'idcaisse_depens' => $idc,
                            'is_actifdep' => 1,
                            'is_validedep' => 1,
                            'opevalid' => $iduser,
                        );
                        $vald_dep = $this->m_depense->update($idp, $dplarray);
                   
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('caisses/' . $this->session->company->ekey.'/RdD/'.$g. '/'. $idc.'/'.  $idcpt.'/validation_depenses/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function rejetdepens($ckey, $g, $idc, $idcpt, $idp)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                
                        $dplarray = array(
                            'commentaire'=> $this->input->post('comment'),
                            'idop_dep' => $idcpt,
                            'idcaisse_depens' => $idc,
                            'active_dep' => 0,
                            'is_actifdep' => 0,
                            'is_validedep' => 0,
                            'valid_depens' => 'rejet',
                        );
                        $vald_dep = $this->m_depense->update($idp, $dplarray);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
                redirect('caisses/' . $this->session->company->ekey.'/RdD/'.$g. '/'. $idc.'/'.  $idcpt.'/validation_depenses/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }
        public function validdepot($ckey, $g, $idc, $idcpt, $idpo)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                        $dpolarray = array(
                            'commentaire_depot'=> $this->input->post('comment'),
                            'idop_depot' => $idcpt,
                            'idcaisse_depot' => $idc,
                            'is_actifdepo' => 1,
                            'is_validdepo' => 1,
                            'opvalid' => $iduser,
                        );
                        $vald_depo = $this->m_depot->update($idpo, $dpolarray);
                    
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
                redirect('caisses/' . $this->session->company->ekey.'/RdD/'.$g. '/'. $idc.'/'.  $idcpt.'/validation_depots/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
            }

        public function rejetdepo($ckey, $g, $idc, $idcpt, $idpo)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
                
                        $dpolarray = array(
                            'commentaire_depot'=> $this->input->post('comment'),
                            'idop_depot' => $idcpt,
                            'idcaisse_depot' => $idc,
                            'is_actifdepo' => 0,
                            'is_validdepo' => 0,
                            'valid_depo' => 'rejet',
                        );
                        $vald_depo = $this->m_depot->update($idpo, $dpolarray);
                    
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('caisses/' . $this->session->company->ekey.'/RdD/'.$g. '/'. $idc.'/'.  $idcpt.'/validation_depots/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }*/
        

        public function validerecette($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ctx = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $ctx['chef_ra'];
            $iduser = $ctx['caissier_ra'];
            $is_saisie = recette_role_is_saisie($ctx['chef_userole']);

            if ($is_saisie) {
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.active_recet = 0
                    AND r.idcaisse ='$idc'
                    AND r.is_validerecet = 0
                    AND r.is_actifrecet = 0
                    AND r.actif_rect = 0")->result();
            } else {
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.active_recet = 1
                    AND r.idcaisse ='$idc'
                    AND r.is_validerecet = 0")->result();
            }

                    foreach ($cfrecet as $item9) {
                        if (recette_role_is_validateur_adjoint($this->session->agent->userole))
                        {
                            $plarray = array(
                                'active_recet' => 1,
                                'is_validerecet' => 1,
                                'is_actifrecetad' => 1,
                                'operavalidad' => $iduser,
                            );
                        }
                        else
                        {
                            $plarray = array(
                                'is_actifrecet' => 1,
                                'is_validerecet' => 1,
                                'operavalid' => $iduser,
                            );
                            if ($is_saisie) {
                                $plarray['active_recet'] = 1;
                            }
                        }
                        if ((int) $sgid > 0) {
                            $plarray['recetsgid'] = (int) $sgid;
                        }

                        $vald_recet = $this->m_recette->update($item9->id_recette, $plarray);
                    }


                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_viewcaissier_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid
            );
        }

        public function rejetrecette($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get($ckey);
            $ctx = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $ctx['chef_ra'];
            $iduser = $ctx['caissier_ra'];
            $is_saisie = recette_role_is_saisie($ctx['chef_userole']);
            $active_filter = $is_saisie ? 0 : 1;
           
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse, r.valid_recet FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.active_recet = $active_filter
                    AND r.idcaisse ='$idc'
                    AND r.is_validerecet = 0
                    AND r.valid_recet = 'valid'" . ($is_saisie ? "
                    AND r.is_actifrecet = 0
                    AND r.actif_rect = 0" : ''))->result();

                    foreach ($cfrecet as $item10) {

                        if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                        {
                            $plarray = array(
                                'active_recet' => 0,
                                'is_actifrecet' => 0,
                                'is_actifrecetad' => 0,
                                'is_validerecet' => 0,
                                'valid_recet' => 'rejet',
                            );
                        }
                        else
                        {
                            $plarray = array(
                                'active_recet' => 0,
                                'is_actifrecet' => 0,
                                'is_validerecet' => 0,
                                'valid_recet' => 'rejet',
                            );
                        }
                        $vald_recet = $this->m_recette->update($item10->id_recette, $plarray);
                    }

                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_viewcaissier_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid
            );
        }

        public function validedepense($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ctx = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $ctx['chef_ra'];
            $iduser = $ctx['caissier_ra'];
            $is_saisie = recette_role_is_saisie($ctx['chef_userole']);

            if ($is_saisie) {
                $cfdepes = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.active_dep = 0
                    AND d.idcaisse_depens = '$idc'
                    AND d.is_validedep = 0
                    AND d.is_actifdep = 0
                    AND d.actif_deps = 0")->result();
            } else {
                $cfdepes = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.active_dep = 1
                    AND d.idcaisse_depens = '$idc'
                    AND d.is_validedep = 0")->result();
            }

                    foreach ($cfdepes as $cfdep) {

                        if (recette_role_is_validateur_adjoint($this->session->agent->userole))
                        {
                            $dplarray = array(
                                'active_dep' => 1,
                                'is_validedep' => 1,
                                'is_actifdepad' => 1,
                                'opevalidad' => $iduser,
                            );
                        }
                        else
                        {
                            $dplarray = array(
                                'is_validedep' => 1,
                                'is_actifdep' => 1,
                                'opevalid' => $iduser,
                            );
                            if ($is_saisie) {
                                $dplarray['active_dep'] = 1;
                            }
                        }
                        if ((int) $sgid > 0) {
                            $dplarray['sousgidepens'] = (int) $sgid;
                        }
                        $vald_dep = $this->m_depense->update($cfdep->id_depense, $dplarray);
                    }
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_viewcaissier_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid
            );
        }

        public function rejetdepense($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ctx = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $ctx['chef_ra'];
            $iduser = $ctx['caissier_ra'];
            $is_saisie = recette_role_is_saisie($ctx['chef_userole']);
            $active_filter = $is_saisie ? 0 : 1;
           
                $cfdepe = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.valid_depens, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.active_dep = $active_filter
                    AND d.idcaisse_depens = '$idc'
                    AND d.is_validedep = 0
                    AND d.valid_depens = 'valid'" . ($is_saisie ? "
                    AND d.is_actifdep = 0
                    AND d.actif_deps = 0" : ''))->result();

                    foreach ($cfdepe as $teme1) {

                        if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                        {

                            $dplarray = array(
                                'active_dep' => 0,
                                'is_actifdepad' => 0,
                                'is_validedep' => 0,
                                'valid_depens' => 'rejet',
                            );
                        }else
                        {
                            $dplarray = array(
                                'active_dep' => 0,
                                'is_actifdep' => 0,
                                'is_validedep' => 0,
                                'valid_depens' => 'rejet',
                            );
                        }
                        $vald_dep = $this->m_depense->update($teme1->id_depense, $dplarray);
                    }
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_viewcaissier_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid
            );
        }
        public function validedepot($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ctx = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $ctx['chef_ra'];
            $iduser = $ctx['caissier_ra'];
            $is_saisie = recette_role_is_saisie($ctx['chef_userole']);

            if ($is_saisie) {
                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.idcaisse_depot FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND d.idcaisse_depot = '$idc'
                    AND d.is_validdepo = 0
                    AND d.is_actifdepo = 0
                    AND d.is_actifdepoad = 0
                    AND d.actif_depo = 0")->result();
            } else {
                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.idcaisse_depot FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND d.idcaisse_depot = '$idc'
                    AND d.is_validdepo = 0")->result();
            }

                    foreach ($cfdepo as $tems) {
                        if (recette_role_is_validateur_adjoint($this->session->agent->userole))
                        {
                            $dpolarray = array(
                                'is_validdepo' => 1,
                                'is_actifdepoad' => 1,
                                'opvalidad' => $iduser,
                            );
                        }
                        else
                        {
                            $dpolarray = array(
                                'is_validdepo' => 1,
                                'is_actifdepo' => 1,
                                'opvalid' => $iduser,
                            );
                        }
                        if ((int) $sgid > 0) {
                            $dpolarray['sousgdepot'] = (int) $sgid;
                        }
                        $vald_depo = $this->m_depot->update($tems->id_depot, $dpolarray);
                    }
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_viewcaissier_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid
            );
        }

        public function rejetdepot($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ctx = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $ctx['chef_ra'];
            $iduser = $ctx['caissier_ra'];
            $is_saisie = recette_role_is_saisie($ctx['chef_userole']);

            if ($is_saisie) {
                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.valid_depo, d.idcaisse_depot FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND d.idcaisse_depot = '$idc'
                    AND d.is_validdepo = 0
                    AND d.is_actifdepo = 0
                    AND d.is_actifdepoad = 0
                    AND d.actif_depo = 0
                    AND d.valid_depo = 'valid'")->result();
            } else {
                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.valid_depo, d.idcaisse_depot FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND d.idcaisse_depot = '$idc'
                    AND d.is_validdepo = 0
                    AND d.valid_depo = 'valid'")->result();
            }

                    foreach ($cfdepo as $tem) {
                        if (recette_role_is_validateur_adjoint($this->session->agent->userole))
                        {
                            $dpolarray = array(
                                'is_validdepo' => 0,
                                'is_actifdepo' => 0,
                                'is_actifdepoad' => 0,
                                'valid_depo' => 'rejet',
                            );
                        }
                        else
                        {
                            $dpolarray = array(
                                'is_validdepo' => 0,
                                'is_actifdepo' => 0,
                                'valid_depo' => 'rejet',
                            );
                        }
                        $vald_depo = $this->m_depot->update($tem->id_depot, $dpolarray);
                    }
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_viewcaissier_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid
            );
        }

        public function validrecette($ckey, $g, $idc, $idcpt, $recet)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $g = roleattribut_guard_normalize_gare_id($this->company->ekey, $g);
            $sgid = (int) $this->input->post('sousgareconnect');
            $caissier_hint = roleattribut_guard_post_hint($this->company->ekey);
            $bind = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $caissier_hint, array(
                'idcai' => $idc,
                'idsg' => $sgid,
                'type' => 'validation_recettes',
            ));
            $idcpt = $bind['chef_ra'];
            $iduser = $bind['caissier_ra'];
                    
                    if($this->session->agent->userole === '18')
                    {
                        $plarray = array(
                            'commentaire_recet'=> $this->input->post('comment'),
                            'idopera' => $idcpt,
                            'idcaisse' => $idc,
                            'active_recet' => 1,
                            'is_actifrecet' => 1,
                            'is_actifrecetad' => 1,
                            'is_validerecet' => 1,
                            'operavalid' => $iduser,
                            'operavalidad' => $iduser,
                        );
                    }
                    else{

                        $plarray = array(
                            'commentaire_recet'=> $this->input->post('comment'),
                            'idopera' => $idcpt,
                            'idcaisse' => $idc,
                            'active_recet' => 1,
                            'is_actifrecet' => 1,
                            'is_validerecet' => 1,
                            'operavalid' => $iduser,
                        );
                    }
                    if ($sgid > 0) {
                        $plarray['recetsgid'] = $sgid;
                    }
                    $vald_recet = $this->m_recette->update($recet, $plarray);
                   
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_rdd_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid,
                'validation_recettes'
            );
        }

        public function rejetrecet($ckey, $g, $idc, $idcpt, $recet)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $g = roleattribut_guard_normalize_gare_id($this->company->ekey, $g);
            $sgid = (int) $this->input->post('sousgareconnect');
            $caissier_hint = roleattribut_guard_post_hint($this->company->ekey);
            $bind = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $caissier_hint, array(
                'idcai' => $idc,
                'idsg' => $sgid,
                'type' => 'validation_recettes',
            ));
            $idcpt = $bind['chef_ra'];
            $iduser = $bind['caissier_ra'];

                if($this->session->agent->userole === '18')
                {
                    $plarray = array(
                        'commentaire_recet'=> $this->input->post('comment'),
                        'idopera' => $idcpt,
                        'idcaisse' => $idc,
                        'active_recet' => 0,
                        'is_actifrecet' => 0,
                        'is_actifrecetad' => 0,
                        'is_validerecet' => 0,
                        'valid_recet' => 'rejet',
                    );
                }
                else
                {
                    $plarray = array(
                        'commentaire_recet'=> $this->input->post('comment'),
                        'idopera' => $idcpt,
                        'idcaisse' => $idc,
                        'active_recet' => 0,
                        'is_actifreceta' => 0,
                        'is_validerecet' => 0,
                        'valid_recet' => 'rejet',
                    );
                }
                    
                $vald_recet = $this->m_recette->update($recet, $plarray);
                   
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_rdd_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid,
                'validation_recettes'
            );
        }

        public function validdepense($ckey, $g, $idc, $idcpt, $idp)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $g = roleattribut_guard_normalize_gare_id($this->company->ekey, $g);
            $sgid = (int) $this->input->post('sousgareconnect');
            $caissier_hint = roleattribut_guard_post_hint($this->company->ekey);
            $bind = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $caissier_hint, array(
                'idcai' => $idc,
                'idsg' => $sgid,
                'type' => 'validation_depenses',
            ));
            $idcpt = $bind['chef_ra'];
            $iduser = $bind['caissier_ra'];
                if (recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                        $dplarray = array(
                            'commentaire'=> $this->input->post('comment'),
                            'idop_dep' => $idcpt,
                            'idcaisse_depens' => $idc,
                            'active_dep' => 1,
                            'is_actifdep' => 1,
                            'is_actifdepad' => 1,
                            'is_validedep' => 1,
                            'opevalid' => $iduser,
                            'opevalidad' => $iduser,
                        );

                }
                else{

                    $dplarray = array(
                            'commentaire'=> $this->input->post('comment'),
                            'idop_dep' => $idcpt,
                            'idcaisse_depens' => $idc,
                            'active_dep' => 1,
                            'is_actifdep' => 1,
                            'is_validedep' => 1,
                            'opevalid' => $iduser, 
                        );
                }
                    if ($sgid > 0) {
                        $dplarray['sousgidepens'] = $sgid;
                    }
                        $vald_dep = $this->m_depense->update($idp, $dplarray);
                   
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_rdd_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid,
                'validation_depenses'
            );
        }

        public function rejetdepens($ckey, $g, $idc, $idcpt, $idp)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $g = roleattribut_guard_normalize_gare_id($this->company->ekey, $g);
            $sgid = (int) $this->input->post('sousgareconnect');
            $caissier_hint = roleattribut_guard_post_hint($this->company->ekey);
            $bind = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $caissier_hint, array(
                'idcai' => $idc,
                'idsg' => $sgid,
                'type' => 'validation_depenses',
            ));
            $idcpt = $bind['chef_ra'];
            $iduser = $bind['caissier_ra'];
                if($this->session->agent->userole === '18')
                {
                    $dplarray = array(
                        'commentaire'=> $this->input->post('comment'),
                        'idop_dep' => $idcpt,
                        'idcaisse_depens' => $idc,
                        'active_dep' => 0,
                        'is_actifdep' => 0,
                        'is_actifdepad' => 0,
                        'is_validedep' => 0,
                        'valid_depens' => 'rejet',
                    );


                }else
                {
                    $dplarray = array(
                        'commentaire'=> $this->input->post('comment'),
                        'idop_dep' => $idcpt,
                        'idcaisse_depens' => $idc,
                        'active_dep' => 0,
                        'is_actifdep' => 0,
                        'is_validedep' => 0,
                        'valid_depens' => 'rejet',
                    );
                }
                        $vald_dep = $this->m_depense->update($idp, $dplarray);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_rdd_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid,
                'validation_depenses'
            );
        }
        public function validdepot($ckey, $g, $idc, $idcpt, $idpo)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $g = roleattribut_guard_normalize_gare_id($this->company->ekey, $g);
            $sgid = (int) $this->input->post('sousgareconnect');
            $caissier_hint = roleattribut_guard_post_hint($this->company->ekey);
            $bind = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $caissier_hint, array(
                'idcai' => $idc,
                'idsg' => $sgid,
                'type' => 'validation_depots',
            ));
            $idcpt = $bind['chef_ra'];
            $iduser = $bind['caissier_ra'];
                if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    $dpolarray = array(
                        'commentaire_depot'=> $this->input->post('comment'),
                        'idop_depot' => $idcpt,
                        'idcaisse_depot' => $idc,
                        'is_actifdepo' => 1,
                        'is_actifdepoad' => 1,
                        'is_validdepo' => 1,
                        'opvalid' => $iduser,
                        'opvalidad' => $iduser,
                    );
                }
                else
                {
                     $dpolarray = array(
                        'commentaire_depot'=> $this->input->post('comment'),
                        'idop_depot' => $idcpt,
                        'idcaisse_depot' => $idc,
                        'is_actifdepo' => 1,
                        'is_validdepo' => 1,
                        'opvalid' => $iduser,
                    );
                }
                    if ($sgid > 0) {
                        $dpolarray['sousgdepot'] = $sgid;
                    }

                        $vald_depo = $this->m_depot->update($idpo, $dpolarray);
                    
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_rdd_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid,
                'validation_depots'
            );
            }

        public function rejetdepo($ckey, $g, $idc, $idcpt, $idpo)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $g = roleattribut_guard_normalize_gare_id($this->company->ekey, $g);
            $sgid = (int) $this->input->post('sousgareconnect');
            $caissier_hint = roleattribut_guard_post_hint($this->company->ekey);
            $bind = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $caissier_hint, array(
                'idcai' => $idc,
                'idsg' => $sgid,
                'type' => 'validation_depots',
            ));
            $idcpt = $bind['chef_ra'];
            $iduser = $bind['caissier_ra'];
                if($this->session->agent->userole === '18')
                {
                    $dpolarray = array(
                        'commentaire_depot'=> $this->input->post('comment'),
                        'idop_depot' => $idcpt,
                        'idcaisse_depot' => $idc,
                        'is_actifdepo' => 0,
                        'is_actifdepoad' => 0,
                        'is_validdepo' => 0,
                        'valid_depo' => 'rejet',
                    );
                }

                else{
                    $dpolarray = array(
                        'commentaire_depot'=> $this->input->post('comment'),
                        'idop_depot' => $idcpt,
                        'idcaisse_depot' => $idc,
                        'is_actifdepo' => 0,
                        'is_validdepo' => 0,
                        'valid_depo' => 'rejet',
                    );
                }
                        $vald_depo = $this->m_depot->update($idpo, $dpolarray);
                    
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            caissier_validation_rdd_redirect(
                $this->company->ekey,
                $g,
                $idc,
                $idcpt,
                $iduser,
                $sgid,
                'validation_depots'
            );
        }
        public function advaliderecette($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
           
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.operavalidad, r.idcaisse FROM recette r
                    WHERE r.operavalidad = '$idcpt'
                    AND r.active_recet = 1
                    AND r.is_actifrecetad = 0
                    AND r.is_validerecet = 0
                    AND r.idcaisse ='$idc'")->result();
                    

                    foreach ($cfrecet as $item9) {
                       
                            $plarray = array(
                                'is_actifrecet' => 1,
                                'is_actifrecetad' => 1,
                                'operavalid' => $iduser,
                            );

                        $vald_recet = $this->m_recette->update($item9->id_recette, $plarray);
                    }


                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            //var_dump($cfrecet)
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function adrejetrecette($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
           
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.operavalidad, r.idcaisse, r.valid_recet FROM recette r
                    WHERE r.operavalidad = '$idcpt'
                    AND r.active_recet = 1
                    AND r.is_actifrecetad = 0
                    AND r.idcaisse ='$idc'
                    AND r.is_validerecet = 0
                    AND r.valid_recet = 'valid'")->result();

                    foreach ($cfrecet as $item10) {

                        
                            $plarray = array(
                                'active_recet' => 0,
                                'is_actifrecet' => 0,
                                'is_validerecet' => 0,
                                'operavalidad' => '',
                                'valid_recet' => 'rejet',
                            );
                        
                        $vald_recet = $this->m_recette->update($item10->id_recette, $plarray);
                    }

                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
              redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function advalidedepense($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
           
                $cfdepes = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.opevalidad, d.idcaisse_depens FROM depense d
                    WHERE d.opevalidad = '$idcpt'
                    AND d.active_dep = 1
                    AND d.is_actifdepad = 0
                    AND d.is_validedep = 0
                    AND d.idcaisse_depens = '$idc'")->result();

                    foreach ($cfdepes as $cfdep) {

                        
                            $dplarray = array(
                                'is_actifdepad' => 1,
                                'is_actifdep' => 1,
                                'opevalid' => $iduser,
                            );
                        
                        $vald_dep = $this->m_depense->update($cfdep->id_depense, $dplarray);
                    }
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }

        public function adrejetdepense($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
           
                $cfdepe = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.valid_depens, d.opevalidad, d.idcaisse_depens FROM depense d
                    WHERE d.opevalidad = '$idcpt'
                    AND d.active_dep = 1
                    AND d.is_actifdepad = 0
                    AND d.idcaisse_depens = '$idc'
                    AND d.is_validedep = 0
                    AND d.valid_depens = 'valid'")->result();

                    foreach ($cfdepe as $teme1) {

                        if($this->session->agent->userole === '18')
                       
                            $dplarray = array(
                                'active_dep' => 0,
                                'is_actifdep' => 0,
                                'is_validedep' => 0,
                                'opevalidad' => '',
                                'valid_depens' => 'rejet',
                            );
                        
                        $vald_dep = $this->m_depense->update($teme1->id_depense, $dplarray);
                    }
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }
        public function unstop_caisse($ckey, $g, $idc)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $db = $this->input->post('date_debut');
            $df = $this->input->post('date_fin');
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

                $cfrecet = $this->db->query("SELECT r.id_recette, r.is_actifrecet, r.idopera, r.is_validerecet, r.arret_caisrecet FROM recette r
                    WHERE r.is_validerecet = 1
                    AND r.idcaisse ='$idc'
                    AND r.operavalid = '$iduser'
                    AND r.arret_caisrecet = 1
                    AND r.date_recet BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfrecet as $items2) {
                        $plarray = array(
                            'ferme_caisrecet' => 1,
                        );
                        $vald_recet = $this->m_recette->update($items2->id_recette, $plarray);
                    }

                    $cfrecetbis = $this->db->query("SELECT r.id_recette, r.is_actifrecet, r.idopera, r.is_validerecet, r.arret_caisrecet FROM recette r
                    WHERE r.is_validerecet = 1
                    AND r.idcaisse ='$idc'
                    AND r.idopera = '$iduser'
                    AND r.operavalid = '$iduser'
                    AND r.date_recet BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfrecetbis as $items2bis) {
                        $plarraybis = array(
                            'arret_caisrecet' => 1,
                            'ferme_caisrecet' => 1,
                        );
                        $vald_recetbis = $this->m_recette->update($items2bis->id_recette, $plarraybis);
                    }

                    $cfrecetbisr = $this->db->query("SELECT r.id_recette, r.is_actifrecet, r.idopera, r.is_validerecet, r.arret_caisrecet FROM recette r
                    WHERE r.is_validerecet = 1
                    AND r.idcaisse ='$idc'
                    AND r.operavalid = '$iduser'
                    AND r.is_actifrecetad = 0
                    AND r.arret_caisrecet = 0
                    AND r.date_recet BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfrecetbisr as $items2bisr) {
                        $plarraybisr = array(
                            'arret_caisrecet' => 1,
                            'ferme_caisrecet' => 1,
                        );
                        $vald_recetbisr = $this->m_recette->update($items2bisr->id_recette, $plarraybisr);
                    }

                  $cfdepe = $this->db->query("SELECT d.id_depense, d.is_actifdep, d.idop_dep, d.is_validedep, d.arret_caisdep FROM depense d
                    WHERE d.is_validedep = 1
                    AND d.idcaisse_depens = '$idc'
                    AND d.opevalid = '$iduser'
                    AND d.arret_caisdep = 1
                    AND d.date_depens BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfdepe as $items3) {
                        $dplarray = array(
                            'ferme_caisdep' => 1,
                        );
                        $vald_dep = $this->m_depense->update($items3->id_depense, $dplarray);
                    }

                    $cfdepebis = $this->db->query("SELECT d.id_depense, d.is_actifdep, d.idop_dep, d.is_validedep, d.arret_caisdep FROM depense d
                    WHERE d.is_validedep = 1
                    AND d.idcaisse_depens = '$idc'
                    AND d.idop_dep = '$iduser'
                    AND d.opevalid = '$iduser'
                    AND d.date_depens BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfdepebis as $items3bis) {
                        $dplarraybis = array(
                            'arret_caisdep' => 1,
                            'ferme_caisdep' => 1,
                        );
                        $vald_depbis = $this->m_depense->update($items3bis->id_depense, $dplarraybis);
                    }

                    $cfdepeb = $this->db->query("SELECT d.id_depense, d.is_actifdep, d.idop_dep, d.is_validedep, d.arret_caisdep FROM depense d
                        WHERE d.is_validedep = 1
                        AND d.idcaisse_depens = '$idc'
                        AND d.opevalid = '$iduser'
                        AND d.is_actifdepad = 0
                        AND d.arret_caisdep = 0
                        AND d.date_depens BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfdepeb as $items3b) {
                        $dplarrayb = array(
                            'arret_caisdep' => 1,
                            'ferme_caisdep' => 1,
                        );
                        $vald_dep = $this->m_depense->update($items3b->id_depense, $dplarrayb);
                    }

                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_actifdepo, d.idop_depot, d.is_validdepo, d.arret_caisdepo FROM depot d
                    WHERE d.is_validdepo = 1
                    AND d.idcaisse_depot = '$idc'
                    AND d.opvalid = '$iduser'
                    AND d.datedepot BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfdepo as $ites5) {
                        $dpolarray = array(
                            'arret_caisdepo' => 1,
                            'ferme_caisdepo' => 1,
							'active_depot' => 1,
							'is_actifdepo' => 1,
                        );
                        $vald_depo = $this->m_depot->update($ites5->id_depot, $dpolarray);
                    }

                    $cfvers = $this->db->query("SELECT v.id_versements, v.idop_versement, v.is_actifverser FROM versements v
                    WHERE v.valider_vers = 1
                    AND v.idcaisse_versement = '$idc'
                    AND v.validop = '$iduser'
                    AND v.date_versement BETWEEN '$db' AND '$df'")->result();

                    foreach ($cfvers as $ites6) {
                        $dpolarray = array(
                            'ferme_caisvers' => 1,
                            'arret_caisvers' => 1,
                        );
                        $vald_verse = $this->m_versements->update($ites6->id_versements, $dpolarray);
                    }
                    
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('caisses/' . $this->session->company->ekey.'/gTv/'.$g. '/'. $idc.'/arretcaisseprincipale/'. $iduser.'/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')));
        }
    }
    /* End of file: Arretcaisses */
    /* File localisation: application/controllers/Arretcaisse.php */
