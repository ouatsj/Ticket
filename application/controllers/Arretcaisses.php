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
            $requested_roleattribut = (int) $idcpt;
            $operateur = compte_arret_bind_operateur($this->company->ekey, $g, $idcpt);
            $idcpt = (int) $operateur['roleattribut'];
            if ($idcpt <= 0 || $requested_roleattribut !== $idcpt) {
                show_error('Arrêt de compte non autorisé.', 403);
                return;
            }

            $caisse = $this->db->query(
                'SELECT id_caiss FROM caisse WHERE id_caiss = ? AND gexp_caiss = ? LIMIT 1',
                array((int) $idc, $g)
            )->row();
            if (!$caisse) {
                show_error('La caisse ne correspond pas à la gare active.', 403);
                return;
            }

            // Adjoint : pas d’arrêt/unstop sur un compte chef — validation dédiée uniquement.
            if (caisse_adjoint_blocked_arret_on_chef($idcpt, $this->session->agent->userole)) {
                show_error(
                    'En tant que caissier adjoint, vous ne pouvez pas arrêter le compte d’un chef guichet. '
                    . 'Utilisez l’écran de validation / rejet des arrêts chefs.',
                    403
                );
                return;
            }

            $r=$this->input->post('recettetotal');
            $dpe=$this->input->post('depensetotal');
            $dpo=$this->input->post('totaldepot');
            $gid = $this->input->post('gareconnect');
            $iduser = $idcpt;
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            $is_adjoint = recette_role_is_validateur_adjoint($this->session->agent->userole);
            $depuis_escale = function_exists('caissier_escale_ops_from_request') && caissier_escale_ops_from_request();
            if ($depuis_escale || $is_adjoint) {
                $fr = function_exists('caissier_escale_nom_filtre_sql') ? caissier_escale_nom_filtre_sql('r.nom') : '';
                $fd = function_exists('caissier_escale_nom_filtre_sql') ? caissier_escale_nom_filtre_sql('d.nom_perso') : '';
                $fp = function_exists('caissier_escale_nom_filtre_sql') ? caissier_escale_nom_filtre_sql('d.nom_pre') : '';
                $fv = function_exists('caissier_escale_nom_filtre_sql') ? caissier_escale_nom_filtre_sql('v.nom_beneficiaire') : '';
            } else {
                $fr = function_exists('recette_role_hors_escale_sauf_saisie_sql')
                    ? recette_role_hors_escale_sauf_saisie_sql('r.nom', 'r.idopera', $idcpt) : '';
                $fd = function_exists('recette_role_hors_escale_sauf_saisie_sql')
                    ? recette_role_hors_escale_sauf_saisie_sql('d.nom_perso', 'd.idop_dep', $idcpt) : '';
                $fp = function_exists('recette_role_hors_escale_sauf_saisie_sql')
                    ? recette_role_hors_escale_sauf_saisie_sql('d.nom_pre', 'd.idop_depot', $idcpt) : '';
                $fv = function_exists('recette_role_hors_escale_sauf_saisie_sql')
                    ? recette_role_hors_escale_sauf_saisie_sql('v.nom_beneficiaire', 'v.idop_versement', $idcpt) : '';
            }

            $this->db->trans_start();

                // Adjoint : uniquement ce qu’il a validé (*validad), comme les soldes.
                if ($is_adjoint) {
                    $cfrecet = $this->db->query(
                        "SELECT r.id_recette, r.active_recet, r.idopera FROM recette r
                        WHERE r.operavalidad = ?
                        AND r.is_actifrecetad = 1
                        AND r.is_actifrecet = 0
                        AND IFNULL(r.arret_caisrecet, 0) = 0
                        AND IFNULL(r.ferme_caisrecet, 0) = 0
                        AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
                        {$fr}",
                        array($idcpt)
                    )->result();
                } else {
                    $escale_arret = $depuis_escale && function_exists('caissier_validation_personne_where');
                    $qui_r = $escale_arret
                        ? caissier_validation_personne_where('r.idopera', 'r.operavalidchef', $idcpt)
                        : '(r.idopera = ? OR r.operavalidchef = ?)';
                    $caisse_r = $escale_arret ? '' : 'AND r.idcaisse = ?';
                    $cfrecet = $this->db->query(
                        "SELECT r.id_recette, r.active_recet, r.idopera FROM recette r
                        WHERE {$qui_r}
                        AND r.active_recet = 0
                        {$caisse_r}
                        {$fr}",
                        $escale_arret ? array() : array($idcpt, $idcpt, (int) $idc)
                    )->result();
                }

                    foreach ($cfrecet as $item7) {
                        if ($is_adjoint) {
                            $plarray = caisse_validation_flags_chef_by_validator('18', $idcpt, true);
                            $plarray['valid_recet'] = 'valid';
                            $plarray['arret_caisrecet'] = 1;
                        } else {
                            $plarray = array(
                                'active_recet' => 1,
                                'valid_recet' => 'valid',
                            );
                        }
                        $vald_recet = $this->m_recette->update($item7->id_recette, $plarray);
                    }

                if ($is_adjoint) {
                    $cfdepe = $this->db->query(
                        "SELECT d.id_depense, d.active_dep, d.idop_dep FROM depense d
                        WHERE d.opevalidad = ?
                        AND d.is_actifdepad = 1
                        AND d.is_actifdep = 0
                        AND IFNULL(d.arret_caisdep, 0) = 0
                        AND IFNULL(d.ferme_caisdep, 0) = 0
                        AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
                        {$fd}",
                        array($idcpt)
                    )->result();
                } else {
                    $escale_arret = $depuis_escale && function_exists('caissier_validation_personne_where');
                    $qui_d = $escale_arret
                        ? caissier_validation_personne_where('d.idop_dep', 'd.opevalidchef', $idcpt)
                        : '(d.idop_dep = ? OR d.opevalidchef = ?)';
                    $caisse_d = $escale_arret ? '' : 'AND d.idcaisse_depens = ?';
                    $cfdepe = $this->db->query(
                        "SELECT d.id_depense, d.active_dep, d.idop_dep FROM depense d
                        WHERE {$qui_d}
                        AND d.active_dep = 0
                        {$caisse_d}
                        {$fd}",
                        $escale_arret ? array() : array($idcpt, $idcpt, (int) $idc)
                    )->result();
                }

                    foreach ($cfdepe as $item8) {
                        if ($is_adjoint) {
                            $dplarray = caisse_validation_flags_depense_chef_by_validator('18', $idcpt, true);
                            $dplarray['valid_depens'] = 'valid';
                            $dplarray['arret_caisdep'] = 1;
                        } else {
                            $dplarray = array(
                                'active_dep' => 1,
                                'valid_depens' => 'valid',
                            );
                        }
                        $vald_dep = $this->m_depense->update($item8->id_depense, $dplarray);
                    }

                if ($is_adjoint) {
                    $cfdepo = $this->db->query(
                        "SELECT d.id_depot FROM depot d
                        WHERE d.opvalidad = ?
                        AND " . sous_caisse_predicat('d.idcaisse_depot', $idc) . "
                        AND d.arret_caisdepo = 0
                        AND IFNULL(d.ferme_caisdepo, 0) = 0
                        AND d.is_actifdepoad = 1
                        AND d.is_actifdepo = 0
                        AND d.actif_depo = 0
                        {$fp}",
                        array($idcpt)
                    )->result();
                } else {
                    $escale_arret = $depuis_escale && function_exists('caissier_validation_personne_where');
                    $qui_p = $escale_arret
                        ? caissier_validation_personne_where('d.idop_depot', 'd.opvalidchef', $idcpt)
                        : '(d.idop_depot = ? OR d.opvalidchef = ?)';
                    $caisse_p = $escale_arret ? '' : 'AND d.idcaisse_depot = ?';
                    $cfdepo = $this->db->query(
                        "SELECT d.id_depot FROM depot d
                        WHERE {$qui_p}
                        {$caisse_p}
                        AND d.arret_caisdepo = 0
                        AND d.is_validdepo = 0
                        AND d.is_actifdepo = 0
                        AND d.actif_depo = 0
                        AND COALESCE(d.valid_depo, '') <> 'valid'
                        {$fp}",
                        $escale_arret ? array() : array($idcpt, $idcpt, (int) $idc)
                    )->result();
                }

                foreach ($cfdepo as $item9) {
                    if ($is_adjoint) {
                        $dpoarray = caisse_validation_flags_depot_chef_by_validator('18', $idcpt, true);
                        $dpoarray['valid_depo'] = 'valid';
                        $dpoarray['arret_caisdepo'] = 1;
                    } else {
                        $dpoarray = array(
                            'valid_depo' => 'valid',
                        );
                    }
                    $this->m_depot->update($item9->id_depot, $dpoarray);
                }

                if ($is_adjoint) {
                    $cfvers = $this->db->query(
                        "SELECT v.id_versements FROM versements v
                        WHERE v.validopad = ?
                        AND IFNULL(v.is_actifverserad, 0) = 1
                        AND IFNULL(v.is_actifverser, 0) = 0
                        AND IFNULL(v.arret_caisvers, 0) = 0
                        AND IFNULL(v.ferme_caisvers, 0) = 0
                        AND " . sous_caisse_predicat('v.idcaisse_versement', $idc) . "
                        AND IFNULL(v.type_versement, '') <> 'Courrier'
                        AND IFNULL(v.type_versement, '') <> 'Bordereau_bancairecourrier'
                        {$fv}",
                        array($idcpt)
                    )->result();
                } else {
                    $escale_arret = $depuis_escale && function_exists('caissier_validation_personne_where');
                    $qui_v = $escale_arret
                        ? caissier_validation_personne_where('v.idop_versement', '', $idcpt)
                        : 'v.idop_versement = ?';
                    $caisse_v = $escale_arret ? '' : 'AND v.idcaisse_versement = ?';
                    $cfvers = $this->db->query(
                        "SELECT v.id_versements FROM versements v
                        WHERE {$qui_v}
                        AND IFNULL(v.active_verse, 0) = 0
                        AND IFNULL(v.valider_vers, 0) = 0
                        AND IFNULL(v.is_actifverser, 0) = 0
                        AND IFNULL(v.is_actifverserad, 0) = 0
                        AND IFNULL(v.arret_caisvers, 0) = 0
                        AND IFNULL(v.ferme_caisvers, 0) = 0
                        {$caisse_v}
                        AND IFNULL(v.type_versement, '') <> 'Courrier'
                        AND IFNULL(v.type_versement, '') <> 'Bordereau_bancairecourrier'
                        {$fv}",
                        $escale_arret ? array() : array($idcpt, (int) $idc)
                    )->result();
                }
                foreach ($cfvers as $itemv) {
                    if ($is_adjoint) {
                        $versarray = array('arret_caisvers' => 1);
                    } else {
                        $versarray = array('active_verse' => 1);
                    }
                    $this->m_versements->update($itemv->id_versements, $versarray);
                }

            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                show_error('L’arrêt de compte n’a pas pu être envoyé. Veuillez réessayer.', 500);
                return;
            }

                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('caisses/' . $this->session->company->ekey.'/cais/'.$g. '/'. $idc. '/'. $iduser.'/arretcaisse_adjoint/'. $sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }

        /**
         * Ancienne validation globale des arrêts chefs. Retirée du profil adjoint :
         * chaque arrêt se valide sur la caisse du chef.
         */
        public function unstop_global_adjoint($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!$this->company) {
                show_error('Compagnie introuvable.', 404);
                return;
            }

            show_error(
                'La validation globale des arrêts chefs n’est plus disponible. Validez chaque chef depuis sa caisse.',
                403
            );
        }

        
        //validation globale des recettes, depenses, depots des caisse secondaire par la caissière principale
        
        /*public function validerecette($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.active_recet = 1
                    AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
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
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }

        public function rejetrecette($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get($ckey);
           
            
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse, r.valid_recet FROM recette r
                    WHERE r.idopera = '$idcpt'
                    AND r.active_recet = 1
                    AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
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
            
              redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }

        public function validedepense($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
                $cfdepes = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.active_dep = 1
                    AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
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
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }

        public function rejetdepense($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
                $cfdepe = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.valid_depens, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE d.idop_dep = '$idcpt'
                    AND d.active_dep = 1
                    AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
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
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }
        public function validedepot($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           
                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.idcaisse_depot FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND " . sous_caisse_predicat('d.idcaisse_depot', $idc) . "
                    AND d.is_validdepo = 0")->result();

                    foreach ($cfdepo as $tems) {
                        $dpolarray = array(
                            'is_validdepo' => 1,
                            'opvalid' => $iduser,
                        );
                        $vald_depo = $this->m_depot->update($tems->id_depot, $dpolarray);
                    }
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }

        public function rejetdepot($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
           

                $cfdepo = $this->db->query("SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.valid_depo, d.idcaisse_depot FROM depot d
                    WHERE d.idop_depot = '$idcpt'
                    AND " . sous_caisse_predicat('d.idcaisse_depot', $idc) . "
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
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
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
            // Validation 4/18 uniquement après arrêt chef (active_*=1), jamais sur saisie ouverte.
            $arrete = caisse_validation_chef_arrete_recette_sql('r');

            $escale_sql = caissier_escale_nom_filtre_sql('r.nom');
            $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
            $qui = $escale_page && function_exists('caissier_validation_personne_where')
                ? caissier_validation_personne_where('r.idopera', 'r.operavalidchef', $idcpt)
                : '(r.idopera = ? OR r.operavalidchef = ?)';
            $caisse_sql = $escale_page ? '' : 'AND r.idcaisse = ?';
            $cfrecet = $this->db->query(
                "SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse
                FROM recette r
                WHERE {$qui}
                {$caisse_sql}
                AND {$arrete}
                {$escale_sql}",
                $escale_page ? array() : array($idcpt, $idcpt, (int) $idc)
            )->result();

                    foreach ($cfrecet as $item9) {
                        $plarray = caisse_validation_flags_chef_by_validator(
                            $this->session->agent->userole,
                            $iduser,
                            false
                        );
                        if ((int) $sgid > 0) {
                            $plarray['recetsgid'] = (int) $sgid;
                        }
                        if ($escale_page && $plarray) {
                            $plarray['idcaisse'] = sous_caisse_id_ecriture((int) $idc);
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
            $arrete = caisse_validation_chef_arrete_recette_sql('r');
           
                $escale_sql = caissier_escale_nom_filtre_sql('r.nom');
                $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
                $qui = $escale_page && function_exists('caissier_validation_personne_where')
                    ? caissier_validation_personne_where('r.idopera', 'r.operavalidchef', $idcpt)
                    : '(r.idopera = ? OR r.operavalidchef = ?)';
                $caisse_sql = $escale_page ? '' : 'AND r.idcaisse = ?';
                $cfrecet = $this->db->query(
                    "SELECT r.id_recette, r.active_recet, r.is_validerecet, r.idopera, r.idcaisse, r.valid_recet
                    FROM recette r
                    WHERE {$qui}
                    {$caisse_sql}
                    AND {$arrete}
                    {$escale_sql}",
                    $escale_page ? array() : array($idcpt, $idcpt, (int) $idc)
                )->result();

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
            $arrete = caisse_validation_chef_arrete_depense_sql('d');

            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_perso');
            $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
            $qui = $escale_page && function_exists('caissier_validation_personne_where')
                ? caissier_validation_personne_where('d.idop_dep', 'd.opevalidchef', $idcpt)
                : '(d.idop_dep = ? OR d.opevalidchef = ?)';
            $caisse_sql = $escale_page ? '' : 'AND d.idcaisse_depens = ?';
            $cfdepes = $this->db->query(
                "SELECT d.id_depense, d.active_dep, d.is_validedep, d.idop_dep, d.idcaisse_depens
                FROM depense d
                WHERE {$qui}
                {$caisse_sql}
                AND {$arrete}
                {$escale_sql}",
                $escale_page ? array() : array($idcpt, $idcpt, (int) $idc)
            )->result();

                    foreach ($cfdepes as $cfdep) {
                        $dplarray = caisse_validation_flags_depense_chef_by_validator(
                            $this->session->agent->userole,
                            $iduser,
                            false
                        );
                        if ((int) $sgid > 0) {
                            $dplarray['sousgidepens'] = (int) $sgid;
                        }
                        if ($escale_page && $dplarray) {
                            $dplarray['idcaisse_depens'] = sous_caisse_id_ecriture((int) $idc);
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
            $arrete = caisse_validation_chef_arrete_depense_sql('d');
           
                $escale_sql = caissier_escale_nom_filtre_sql('d.nom_perso');
                $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
                $qui = $escale_page && function_exists('caissier_validation_personne_where')
                    ? caissier_validation_personne_where('d.idop_dep', 'd.opevalidchef', $idcpt)
                    : '(d.idop_dep = ? OR d.opevalidchef = ?)';
                $caisse_sql = $escale_page ? '' : 'AND d.idcaisse_depens = ?';
                $cfdepe = $this->db->query(
                    "SELECT d.id_depense, d.active_dep, d.is_validedep, d.valid_depens, d.idop_dep, d.idcaisse_depens
                    FROM depense d
                    WHERE {$qui}
                    {$caisse_sql}
                    AND {$arrete}
                    {$escale_sql}",
                    $escale_page ? array() : array($idcpt, $idcpt, (int) $idc)
                )->result();

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
            $arrete = caisse_validation_chef_arrete_depot_sql('d');

            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_pre');
            $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
            $qui = $escale_page && function_exists('caissier_validation_personne_where')
                ? caissier_validation_personne_where('d.idop_depot', 'd.opvalidchef', $idcpt)
                : '(d.idop_depot = ? OR d.opvalidchef = ?)';
            $caisse_sql = $escale_page ? '' : 'AND d.idcaisse_depot = ?';
            $cfdepo = $this->db->query(
                "SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.idcaisse_depot
                FROM depot d
                WHERE {$qui}
                {$caisse_sql}
                AND {$arrete}
                {$escale_sql}",
                $escale_page ? array() : array($idcpt, $idcpt, (int) $idc)
            )->result();

                    foreach ($cfdepo as $tems) {
                        $dpolarray = caisse_validation_flags_depot_chef_by_validator(
                            $this->session->agent->userole,
                            $iduser,
                            false
                        );
                        if ((int) $sgid > 0) {
                            $dpolarray['sousgdepot'] = (int) $sgid;
                        }
                        if ($escale_page && $dpolarray) {
                            $dpolarray['idcaisse_depot'] = sous_caisse_id_ecriture((int) $idc);
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
            $arrete = caisse_validation_chef_arrete_depot_sql('d');

            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_pre');
            $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
            $qui = $escale_page && function_exists('caissier_validation_personne_where')
                ? caissier_validation_personne_where('d.idop_depot', 'd.opvalidchef', $idcpt)
                : '(d.idop_depot = ? OR d.opvalidchef = ?)';
            $caisse_sql = $escale_page ? '' : 'AND d.idcaisse_depot = ?';
            $cfdepo = $this->db->query(
                "SELECT d.id_depot, d.is_validdepo, d.idop_depot, d.valid_depo, d.idcaisse_depot
                FROM depot d
                WHERE {$qui}
                {$caisse_sql}
                AND {$arrete}
                {$escale_sql}",
                $escale_page ? array() : array($idcpt, $idcpt, (int) $idc)
            )->result();

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

        public function valideversement($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->_valider_versement_chef($ckey, $g, $idc, $idcpt, $iduser, $sgid, false);
        }

        public function rejetversement($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->_valider_versement_chef($ckey, $g, $idc, $idcpt, $iduser, $sgid, true);
        }

        public function advalideversement($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->_valider_versement_adjoint($ckey, $g, $idc, $idcpt, $iduser, $sgid, false);
        }

        public function adrejetversement($ckey, $g, $idc, $idcpt, $iduser, $sgid)
        {
            $this->_valider_versement_adjoint($ckey, $g, $idc, $idcpt, $iduser, $sgid, true);
        }

        protected function _valider_versement_chef($ckey, $g, $idc, $idcpt, $iduser, $sgid, $rejet)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ctx = caissier_validation_bind_operateurs($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $ctx['chef_ra'];
            $iduser = $ctx['caissier_ra'];
            $arrete = caisse_validation_chef_arrete_versement_sql('v');
            $escale_sql = caissier_escale_nom_filtre_sql('v.nom_beneficiaire');
            $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
            $qui = $escale_page && function_exists('caissier_validation_personne_where')
                ? caissier_validation_personne_where('v.idop_versement', '', $idcpt)
                : 'v.idop_versement = ?';
            $caisse_sql = $escale_page ? '' : 'AND v.idcaisse_versement = ?';
            $rows = $this->db->query(
                "SELECT v.id_versements FROM versements v
                WHERE {$qui}
                {$caisse_sql}
                AND {$arrete}
                {$escale_sql}",
                $escale_page ? array() : array($idcpt, (int) $idc)
            )->result();
            foreach ($rows as $row) {
                if ($rejet) {
                    $flags = array(
                        'active_verse' => 0,
                        'is_actifverser' => 0,
                        'is_actifverserad' => 0,
                        'valider_vers' => 0,
                        'arret_caisvers' => 0,
                    );
                } else {
                    $flags = caisse_validation_flags_versement_by_validator(
                        $this->session->agent->userole,
                        $iduser
                    );
                }
                if ($flags) {
                    if ($escale_page && !$rejet) {
                        $flags['idcaisse_versement'] = sous_caisse_id_ecriture((int) $idc);
                    }
                    $this->m_versements->update($row->id_versements, $flags);
                }
            }
            $this->property['UPDATE_SUCCESS'] = TRUE;
            caissier_validation_viewcaissier_redirect(
                $this->company->ekey, $g, $idc, $idcpt, $iduser, $sgid
            );
        }

        protected function _valider_versement_adjoint($ckey, $g, $idc, $idcpt, $iduser, $sgid, $rejet)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
            $pending = caisse_validation_pending_adjoint_versement_sql($idcpt, 'v');
            $escale_sql = caissier_escale_nom_filtre_sql('v.nom_beneficiaire');
            $rows = $this->db->query(
                "SELECT v.id_versements FROM versements v
                WHERE {$pending}
                AND " . sous_caisse_predicat('v.idcaisse_versement', $idc) . "
                {$escale_sql}",
                array()
            )->result();
            foreach ($rows as $row) {
                if ($rejet) {
                    $flags = array(
                        'is_actifverserad' => 0,
                        'validopad' => null,
                        'arret_caisvers' => 0,
                        'is_actifverser' => 0,
                        'valider_vers' => 0,
                    );
                } else {
                    $flags = caisse_validation_flags_promote_adjoint_versement($iduser);
                }
                if ($flags) {
                    $this->m_versements->update($row->id_versements, $flags);
                }
            }
            $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
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
            $arrete = caisse_validation_chef_arrete_recette_sql('r');
            $escale_sql = caissier_escale_nom_filtre_sql('r.nom');

            $row = $this->db->query(
                "SELECT r.id_recette FROM recette r
                WHERE r.id_recette = ?
                AND (r.idopera = ? OR r.operavalidchef = ?)
                AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
                AND {$arrete}
                {$escale_sql}
                LIMIT 1",
                array((int) $recet, $idcpt, $idcpt)
            )->row();
            if (!$row) {
                $this->session->set_flashdata(
                    'UPDATE_ERROR',
                    'Validation impossible : le chef guichet n’a pas encore arrêté cette opération.'
                );
                caissier_validation_rdd_redirect(
                    $this->company->ekey,
                    $g,
                    $idc,
                    $idcpt,
                    $iduser,
                    $sgid,
                    'validation_recettes'
                );
                return;
            }

            $plarray = caisse_validation_flags_chef_by_validator(
                $this->session->agent->userole,
                $iduser,
                false
            );
            $plarray['commentaire_recet'] = $this->input->post('comment');
            $plarray['idcaisse'] = $idc;
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
            $escale_sql = caissier_escale_nom_filtre_sql('r.nom');
            $row = ($escale_sql === '') ? true : $this->db->query(
                "SELECT r.id_recette FROM recette r
                WHERE r.id_recette = ?
                AND (r.idopera = ? OR r.operavalidchef = ?)
                AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
                {$escale_sql}
                LIMIT 1",
                array((int) $recet, $idcpt, $idcpt)
            )->row();
            if (!$row) {
                $this->session->set_flashdata(
                    'UPDATE_ERROR',
                    'Rejet impossible : cette opération ne fait pas partie de l’escale.'
                );
                caissier_validation_rdd_redirect(
                    $this->company->ekey,
                    $g,
                    $idc,
                    $idcpt,
                    $iduser,
                    $sgid,
                    'validation_recettes'
                );
                return;
            }

                if($this->session->agent->userole === '18')
                {
                    $plarray = array(
                        'commentaire_recet'=> $this->input->post('comment'),
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
                        'active_recet' => 0,
                        'is_actifrecet' => 0,
                        'is_actifrecetad' => 0,
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
            $arrete = caisse_validation_chef_arrete_depense_sql('d');
            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_perso');

            $row = $this->db->query(
                "SELECT d.id_depense FROM depense d
                WHERE d.id_depense = ?
                AND (d.idop_dep = ? OR d.opevalidchef = ?)
                AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
                AND {$arrete}
                {$escale_sql}
                LIMIT 1",
                array((int) $idp, $idcpt, $idcpt)
            )->row();
            if (!$row) {
                $this->session->set_flashdata(
                    'UPDATE_ERROR',
                    'Validation impossible : le chef guichet n’a pas encore arrêté cette opération.'
                );
                caissier_validation_rdd_redirect(
                    $this->company->ekey,
                    $g,
                    $idc,
                    $idcpt,
                    $iduser,
                    $sgid,
                    'validation_depenses'
                );
                return;
            }

            $dplarray = caisse_validation_flags_depense_chef_by_validator(
                $this->session->agent->userole,
                $iduser,
                false
            );
            $dplarray['commentaire'] = $this->input->post('comment');
            $dplarray['idcaisse_depens'] = $idc;
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
            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_perso');
            $row = ($escale_sql === '') ? true : $this->db->query(
                "SELECT d.id_depense FROM depense d
                WHERE d.id_depense = ?
                AND (d.idop_dep = ? OR d.opevalidchef = ?)
                AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
                {$escale_sql}
                LIMIT 1",
                array((int) $idp, $idcpt, $idcpt)
            )->row();
            if (!$row) {
                $this->session->set_flashdata(
                    'UPDATE_ERROR',
                    'Rejet impossible : cette opération ne fait pas partie de l’escale.'
                );
                caissier_validation_rdd_redirect(
                    $this->company->ekey,
                    $g,
                    $idc,
                    $idcpt,
                    $iduser,
                    $sgid,
                    'validation_depenses'
                );
                return;
            }
                if($this->session->agent->userole === '18')
                {
                    $dplarray = array(
                        'commentaire'=> $this->input->post('comment'),
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
            $arrete = caisse_validation_chef_arrete_depot_sql('d');
            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_pre');

            $row = $this->db->query(
                "SELECT d.id_depot FROM depot d
                WHERE d.id_depot = ?
                AND (d.idop_depot = ? OR d.opvalidchef = ?)
                AND " . sous_caisse_predicat('d.idcaisse_depot', $idc) . "
                AND {$arrete}
                {$escale_sql}
                LIMIT 1",
                array((int) $idpo, $idcpt, $idcpt)
            )->row();
            if (!$row) {
                $this->session->set_flashdata(
                    'UPDATE_ERROR',
                    'Validation impossible : le chef guichet n’a pas encore arrêté cette opération.'
                );
                caissier_validation_rdd_redirect(
                    $this->company->ekey,
                    $g,
                    $idc,
                    $idcpt,
                    $iduser,
                    $sgid,
                    'validation_depots'
                );
                return;
            }

            $dpolarray = caisse_validation_flags_depot_chef_by_validator(
                $this->session->agent->userole,
                $iduser,
                false
            );
            $dpolarray['commentaire_depot'] = $this->input->post('comment');
            $dpolarray['idcaisse_depot'] = $idc;
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
            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_pre');
            $row = ($escale_sql === '') ? true : $this->db->query(
                "SELECT d.id_depot FROM depot d
                WHERE d.id_depot = ?
                AND (d.idop_depot = ? OR d.opvalidchef = ?)
                AND " . sous_caisse_predicat('d.idcaisse_depot', $idc) . "
                {$escale_sql}
                LIMIT 1",
                array((int) $idpo, $idcpt, $idcpt)
            )->row();
            if (!$row) {
                $this->session->set_flashdata(
                    'UPDATE_ERROR',
                    'Rejet impossible : cette opération ne fait pas partie de l’escale.'
                );
                caissier_validation_rdd_redirect(
                    $this->company->ekey,
                    $g,
                    $idc,
                    $idcpt,
                    $iduser,
                    $sgid,
                    'validation_depots'
                );
                return;
            }
                if($this->session->agent->userole === '18')
                {
                    $dpolarray = array(
                        'commentaire_depot'=> $this->input->post('comment'),
                        'is_actifdepo' => 0,
                        'is_actifdepoad' => 0,
                        'is_validdepo' => 0,
                        'valid_depo' => 'rejet',
                    );
                }

                else{
                    $dpolarray = array(
                        'commentaire_depot'=> $this->input->post('comment'),
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
        public function advaliderecette($ckey, $g, $idc, $idcpt, $iduser, $sgid, $date = null)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
            $pending = caisse_validation_pending_adjoint_recette_sql($idcpt, 'r');
            $dateSql = caisse_arret_date_filter_sql('r.date_recet', $date);
            $escale_sql = caissier_escale_nom_filtre_sql('r.nom');
            $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
            if ($escale_page && function_exists('caissier_validation_personne_where')) {
                $pending = str_replace(
                    'r.operavalidad = ' . (int) $idcpt,
                    caissier_validation_personne_where('r.operavalidad', '', $idcpt),
                    $pending
                );
            }
            $caisse_sql = $escale_page ? '' : "AND r.idcaisse ='$idc'";

                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.operavalidad, r.idopera, r.idcaisse FROM recette r
                    WHERE {$pending}
                    AND r.active_recet = 1
                    {$caisse_sql}
                    {$dateSql}
                    {$escale_sql}")->result();
                    

                    foreach ($cfrecet as $item9) {
                        // Option A : ajoute operavalid, conserve operavalidad / idopera.
                        $plarray = caisse_validation_flags_promote_adjoint_recette($iduser);
                        if (empty($plarray)) {
                            log_message('error', 'advaliderecette: promote refusé (RA non principal) iduser=' . $iduser);
                            continue;
                        }
                        if ($escale_page) {
                            $plarray['idcaisse'] = sous_caisse_id_ecriture((int) $idc);
                        }
                        $vald_recet = $this->m_recette->update($item9->id_recette, $plarray);
                    }


                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }

        public function adrejetrecette($ckey, $g, $idc, $idcpt, $iduser, $sgid, $date = null)
        {
            $this->company = $this->m_entreprises->get($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
            $pending = caisse_validation_pending_adjoint_recette_sql($idcpt, 'r');
            $dateSql = caisse_arret_date_filter_sql('r.date_recet', $date);
            $escale_sql = caissier_escale_nom_filtre_sql('r.nom');
           
                $cfrecet = $this->db->query("SELECT r.id_recette, r.active_recet, r.is_validerecet, r.operavalidad, r.idopera, r.idcaisse, r.valid_recet FROM recette r
                    WHERE {$pending}
                    AND r.active_recet = 1
                    AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
                    {$dateSql}
                    {$escale_sql}")->result();

                    foreach ($cfrecet as $item10) {
                        // Rejet : n’efface pas idopera (auteur).
                        $plarray = caisse_validation_flags_reject_adjoint_recette();
                        $vald_recet = $this->m_recette->update($item10->id_recette, $plarray);
                    }

                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
              redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }

        public function advalidedepense($ckey, $g, $idc, $idcpt, $iduser, $sgid, $date = null)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
            $pending = caisse_validation_pending_adjoint_depense_sql($idcpt, 'd');
            $dateSql = caisse_arret_date_filter_sql('d.date_depens', $date);
            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_perso');
            $escale_page = function_exists('caissier_escale_page_active') && caissier_escale_page_active();
            if ($escale_page && function_exists('caissier_validation_personne_where')) {
                $pending = str_replace(
                    'd.opevalidad = ' . (int) $idcpt,
                    caissier_validation_personne_where('d.opevalidad', '', $idcpt),
                    $pending
                );
            }
            $caisse_sql = $escale_page ? '' : "AND d.idcaisse_depens = '$idc'";
           
                $cfdepes = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.opevalidad, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE {$pending}
                    AND d.active_dep = 1
                    {$caisse_sql}
                    {$dateSql}
                    {$escale_sql}")->result();

                    foreach ($cfdepes as $cfdep) {
                        $dplarray = caisse_validation_flags_promote_adjoint_depense($iduser);
                        if (empty($dplarray)) {
                            log_message('error', 'advalidedepense: promote refusé (RA non principal) iduser=' . $iduser);
                            continue;
                        }
                        if ($escale_page) {
                            $dplarray['idcaisse_depens'] = sous_caisse_id_ecriture((int) $idc);
                        }
                        $vald_dep = $this->m_depense->update($cfdep->id_depense, $dplarray);
                    }
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }

        public function adrejetdepense($ckey, $g, $idc, $idcpt, $iduser, $sgid, $date = null)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bind = caissier_principale_adjoint_validation_bind($this->company->ekey, $g, $idcpt, $iduser);
            $idcpt = $bind['adjoint_ra'];
            $iduser = $bind['caissier_ra'];
            $pending = caisse_validation_pending_adjoint_depense_sql($idcpt, 'd');
            $dateSql = caisse_arret_date_filter_sql('d.date_depens', $date);
            $escale_sql = caissier_escale_nom_filtre_sql('d.nom_perso');
           
                $cfdepe = $this->db->query("SELECT d.id_depense, d.active_dep, d.is_validedep, d.valid_depens, d.opevalidad, d.idop_dep, d.idcaisse_depens FROM depense d
                    WHERE {$pending}
                    AND d.active_dep = 1
                    AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
                    {$dateSql}
                    {$escale_sql}")->result();

                    foreach ($cfdepe as $teme1) {
                        $dplarray = caisse_validation_flags_reject_adjoint_depense();
                        $vald_dep = $this->m_depense->update($teme1->id_depense, $dplarray);
                    }
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
            
            redirect('utilisateurs/' . $this->session->company->ekey.'/caissier/'.$g. '/'. $idc.'/'.$idcpt.'/'.$iduser.'/'.$sgid.'/'.mdate("%d/%m/%Y", now('UTC')) . caissier_escale_query_suffix());
        }
        public function unstop_caisse($ckey, $g, $idc)
        {
            // ekey métier (ex. 1000) — jamais un roleattribut (ex. 215 Zenabou).
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!$this->company && $this->session->userdata('company')) {
                $this->company = $this->session->company;
            }
            if (!$this->company || empty($this->company->ekey)) {
                $this->session->set_flashdata('error', 'Entreprise invalide pour l\'arrêt de caisse.');
                redirect('home/main');
                return;
            }

            $ekey = $this->company->ekey;
            $g = roleattribut_guard_normalize_gare_id($ekey, $g ? $g : $this->input->post('gareconnect'));
            $idc = (int) $idc;
            $db = trim((string) $this->input->post('date_debut'));
            $df = trim((string) $this->input->post('date_fin'));
            $sgid = (int) $this->input->post('sousgareconnect');
            $iduser = (int) roleattribut_guard_post_hint($ekey);

            $back = 'caisses/' . $ekey . '/gTv/' . $g . '/' . $idc
                . '/arretcaisseprincipale/' . ($iduser > 0 ? $iduser : 0) . '/'
                . $sgid . '/' . mdate('%d/%m/%Y', now('UTC'))
                . caissier_escale_query_suffix();

            if ($this->input->method(true) !== 'POST') {
                $this->session->set_flashdata('error', 'Arrêt de caisse : utilisez le formulaire (dates obligatoires).');
                redirect($back);
                return;
            }
            if ($idc <= 0 || $iduser <= 0) {
                $this->session->set_flashdata('error', 'Arrêt de caisse : opérateur ou caisse manquant.');
                redirect($back);
                return;
            }
            if ($db === '' || $df === ''
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $db)
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $df)
            ) {
                $this->session->set_flashdata('error', 'Arrêt de caisse : renseignez les dates DU et AU (format AAAA-MM-JJ).');
                redirect($back);
                return;
            }
            if ($db > $df) {
                $this->session->set_flashdata('error', 'Arrêt de caisse : la date de début doit précéder la date de fin.');
                redirect($back);
                return;
            }

            $db = $this->db->escape_str($db);
            $df = $this->db->escape_str($df);

            if (caissier_escale_ops_from_request()) {
                $fr = caissier_escale_nom_filtre_sql('r.nom');
                $fd = caissier_escale_nom_filtre_sql('d.nom_perso');
                $fp = caissier_escale_nom_filtre_sql('d.nom_pre');
                $fv = caissier_escale_nom_filtre_sql('v.nom_beneficiaire');
            } else {
                $fr = recette_role_hors_escale_sql('r.nom', 'r.idopera');
                $fd = recette_role_hors_escale_sql('d.nom_perso', 'd.idop_dep');
                $fp = recette_role_hors_escale_sql('d.nom_pre', 'd.idop_depot');
                $fv = recette_role_hors_escale_sql('v.nom_beneficiaire', 'v.idop_versement');
            }

            $rows = function ($sql) {
                $q = $this->db->query($sql);
                return ($q && is_object($q)) ? $q->result() : array();
            };

            $blocage = $this->_arret_caisse_message_blocage($ekey, $g, $idc, $db, $df, $fr, $fd, $fp, $fv);
            if ($blocage !== '') {
                $this->session->set_flashdata('error', $blocage);
                redirect($back);
                return;
            }

            $cfrecet = $rows(
                "SELECT r.id_recette FROM recette r
                WHERE r.is_validerecet = 1
                AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
                AND r.operavalid = '{$iduser}'
                AND r.arret_caisrecet = 1
                AND r.date_recet BETWEEN '{$db}' AND '{$df}'
                {$fr}"
            );
            $date_envoi_arret = date('Y-m-d');

            foreach ($cfrecet as $items2) {
                // date_ferme_* = date à laquelle le caissier a fait l'arrêt (conservée si déjà renseignée)
                $this->db->query(
                    "UPDATE recette SET ferme_caisrecet = 1,
                     date_ferme_caisrecet = IFNULL(date_ferme_caisrecet, ?)
                     WHERE id_recette = ?",
                    array($date_envoi_arret, (int) $items2->id_recette)
                );
            }

            $cfrecetbis = $rows(
                "SELECT r.id_recette FROM recette r
                WHERE r.is_validerecet = 1
                AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
                AND r.idopera = '{$iduser}'
                AND r.operavalid = '{$iduser}'
                AND r.date_recet BETWEEN '{$db}' AND '{$df}'
                {$fr}"
            );
            foreach ($cfrecetbis as $items2bis) {
                $this->db->query(
                    "UPDATE recette SET arret_caisrecet = 1, ferme_caisrecet = 1,
                     date_ferme_caisrecet = IFNULL(date_ferme_caisrecet, ?)
                     WHERE id_recette = ?",
                    array($date_envoi_arret, (int) $items2bis->id_recette)
                );
            }

            // Piste principal : operavalid (même si is_actifrecetad=1 après validation adjoint).
            $cfrecetbisr = $rows(
                "SELECT r.id_recette FROM recette r
                WHERE r.is_validerecet = 1
                AND " . sous_caisse_predicat('r.idcaisse', $idc) . "
                AND r.operavalid = '{$iduser}'
                AND r.arret_caisrecet = 0
                AND r.date_recet BETWEEN '{$db}' AND '{$df}'
                {$fr}"
            );
            foreach ($cfrecetbisr as $items2bisr) {
                $this->db->query(
                    "UPDATE recette SET arret_caisrecet = 1, ferme_caisrecet = 1,
                     date_ferme_caisrecet = IFNULL(date_ferme_caisrecet, ?)
                     WHERE id_recette = ?",
                    array($date_envoi_arret, (int) $items2bisr->id_recette)
                );
            }

            $cfdepe = $rows(
                "SELECT d.id_depense FROM depense d
                WHERE d.is_validedep = 1
                AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
                AND d.opevalid = '{$iduser}'
                AND d.arret_caisdep = 1
                AND d.date_depens BETWEEN '{$db}' AND '{$df}'
                {$fd}"
            );
            foreach ($cfdepe as $items3) {
                $this->db->query(
                    "UPDATE depense SET ferme_caisdep = 1,
                     date_ferme_caisdep = IFNULL(date_ferme_caisdep, ?)
                     WHERE id_depense = ?",
                    array($date_envoi_arret, (int) $items3->id_depense)
                );
            }

            $cfdepebis = $rows(
                "SELECT d.id_depense FROM depense d
                WHERE d.is_validedep = 1
                AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
                AND d.idop_dep = '{$iduser}'
                AND d.opevalid = '{$iduser}'
                AND d.date_depens BETWEEN '{$db}' AND '{$df}'
                {$fd}"
            );
            foreach ($cfdepebis as $items3bis) {
                $this->db->query(
                    "UPDATE depense SET arret_caisdep = 1, ferme_caisdep = 1,
                     date_ferme_caisdep = IFNULL(date_ferme_caisdep, ?)
                     WHERE id_depense = ?",
                    array($date_envoi_arret, (int) $items3bis->id_depense)
                );
            }

            // Piste principal : opevalid (même si is_actifdepad=1 après validation adjoint).
            $cfdepeb = $rows(
                "SELECT d.id_depense FROM depense d
                WHERE d.is_validedep = 1
                AND " . sous_caisse_predicat('d.idcaisse_depens', $idc) . "
                AND d.opevalid = '{$iduser}'
                AND d.arret_caisdep = 0
                AND d.date_depens BETWEEN '{$db}' AND '{$df}'
                {$fd}"
            );
            foreach ($cfdepeb as $items3b) {
                $this->db->query(
                    "UPDATE depense SET arret_caisdep = 1, ferme_caisdep = 1,
                     date_ferme_caisdep = IFNULL(date_ferme_caisdep, ?)
                     WHERE id_depense = ?",
                    array($date_envoi_arret, (int) $items3b->id_depense)
                );
            }

            $cfdepo = $rows(
                "SELECT d.id_depot FROM depot d
                WHERE d.is_validdepo = 1
                AND " . sous_caisse_predicat('d.idcaisse_depot', $idc) . "
                AND d.opvalid = '{$iduser}'
                AND d.datedepot BETWEEN '{$db}' AND '{$df}'
                {$fp}"
            );
            foreach ($cfdepo as $ites5) {
                $this->db->query(
                    "UPDATE depot SET arret_caisdepo = 1, ferme_caisdepo = 1,
                     date_ferme_caisdepo = IFNULL(date_ferme_caisdepo, ?),
                     active_depot = 1, is_actifdepo = 1
                     WHERE id_depot = ?",
                    array($date_envoi_arret, (int) $ites5->id_depot)
                );
            }

            $cfvers = $rows(
                "SELECT v.id_versements FROM versements v
                WHERE v.valider_vers = 1
                AND " . sous_caisse_predicat('v.idcaisse_versement', $idc) . "
                AND v.validop = '{$iduser}'
                AND v.date_versement BETWEEN '{$db}' AND '{$df}'
                {$fv}"
            );
            foreach ($cfvers as $ites6) {
                $this->db->query(
                    "UPDATE versements SET ferme_caisvers = 1, arret_caisvers = 1,
                     date_ferme_caisvers = IFNULL(date_ferme_caisvers, ?)
                     WHERE id_versements = ?",
                    array($date_envoi_arret, (int) $ites6->id_versements)
                );
            }

            $this->property['UPDATE_SUCCESS'] = TRUE;
            $this->session->set_flashdata('success', 'Arrêt de caisse enregistré pour la période sélectionnée.');
            redirect($back);
        }

        /**
         * Opérations de la période encore chez le chef, l'adjoint ou la caissière.
         * Vide si la caissière peut fermer ce qu'elle a déjà validé.
         *
         * @param string $ekey
         * @param string $gare
         * @param int $idc
         * @param string $du
         * @param string $au
         * @param string $fr
         * @param string $fd
         * @param string $fp
         * @param string $fv
         * @return string
         */
        protected function _arret_caisse_message_blocage($ekey, $gare, $idc, $du, $au, $fr, $fd, $fp, $fv)
        {
            $idc = (int) $idc;
            $horsCourrier = " AND IFNULL(%s, '') <> 'Courrier' ";
            $parts = array();

            $push = function ($nature, $lieu, $ra, $nb) use (&$parts) {
                $nb = (int) $nb;
                $ra = (int) $ra;
                if ($nb <= 0) {
                    return;
                }
                $key = $nature . '|' . $lieu . '|' . $ra;
                if (!isset($parts[$key])) {
                    $parts[$key] = array('nature' => $nature, 'lieu' => $lieu, 'ra' => $ra, 'nb' => 0);
                }
                $parts[$key]['nb'] += $nb;
            };

            $run = function ($sql, $binds, $nature, $lieu) use ($push) {
                $q = $this->db->query($sql, $binds);
                if (!$q) {
                    return;
                }
                foreach ($q->result() as $row) {
                    $push($nature, $lieu, isset($row->ra) ? $row->ra : 0, isset($row->nb) ? $row->nb : 0);
                }
            };

            $b = array($du, $au);
            $det_rec = caisse_validation_detenteur_sql('r.idopera', 'r.operavalidchef');
            $det_dep = caisse_validation_detenteur_sql('d.idop_dep', 'd.opevalidchef');
            $det_depo = caisse_validation_detenteur_sql('d.idop_depot', 'd.opvalidchef');
            $run(
                "SELECT {$det_rec} AS ra, COUNT(*) AS nb FROM recette r
                WHERE " . sous_caisse_predicat('r.idcaisse', $idc) . " AND r.date_recet BETWEEN ? AND ?
                AND IFNULL(r.ferme_caisrecet, 0) = 0
                AND IFNULL(r.active_recet, 0) = 0
                AND IFNULL(r.is_actifrecet, 0) = 0
                AND IFNULL(r.is_actifrecetad, 0) = 0
                AND (r.is_validerecet = 0 OR r.is_validerecet IS NULL)
                " . sprintf($horsCourrier, 'r.type_recet') . " {$fr}
                GROUP BY {$det_rec}",
                $b, 'recette', 'chef'
            );
            $run(
                "SELECT {$det_rec} AS ra, COUNT(*) AS nb FROM recette r
                WHERE " . sous_caisse_predicat('r.idcaisse', $idc) . " AND r.date_recet BETWEEN ? AND ?
                AND IFNULL(r.ferme_caisrecet, 0) = 0
                AND IFNULL(r.active_recet, 0) = 1
                AND IFNULL(r.is_actifrecetad, 0) = 0
                AND IFNULL(r.is_actifrecet, 0) = 0
                AND (r.is_validerecet = 0 OR r.is_validerecet IS NULL)
                " . sprintf($horsCourrier, 'r.type_recet') . " {$fr}
                GROUP BY {$det_rec}",
                $b, 'recette', 'attente'
            );
            $run(
                "SELECT IFNULL(r.operavalidad, 0) AS ra, COUNT(*) AS nb FROM recette r
                WHERE " . sous_caisse_predicat('r.idcaisse', $idc) . " AND r.date_recet BETWEEN ? AND ?
                AND IFNULL(r.ferme_caisrecet, 0) = 0
                AND IFNULL(r.is_actifrecetad, 0) = 1
                AND IFNULL(r.is_actifrecet, 0) = 0
                AND IFNULL(r.arret_caisrecet, 0) = 0
                " . sprintf($horsCourrier, 'r.type_recet') . " {$fr}
                GROUP BY IFNULL(r.operavalidad, 0)",
                $b, 'recette', 'adjoint'
            );
            $run(
                "SELECT IFNULL(r.operavalid, 0) AS ra, COUNT(*) AS nb FROM recette r
                WHERE " . sous_caisse_predicat('r.idcaisse', $idc) . " AND r.date_recet BETWEEN ? AND ?
                AND IFNULL(r.ferme_caisrecet, 0) = 0
                AND IFNULL(r.is_actifrecetad, 0) = 1
                AND IFNULL(r.is_actifrecet, 0) = 0
                AND IFNULL(r.arret_caisrecet, 0) = 1
                " . sprintf($horsCourrier, 'r.type_recet') . " {$fr}
                GROUP BY IFNULL(r.operavalid, 0)",
                $b, 'recette', 'principale'
            );

            $run(
                "SELECT {$det_dep} AS ra, COUNT(*) AS nb FROM depense d
                WHERE " . sous_caisse_predicat('d.idcaisse_depens', $idc) . " AND d.date_depens BETWEEN ? AND ?
                AND IFNULL(d.ferme_caisdep, 0) = 0
                AND IFNULL(d.active_dep, 0) = 0
                AND IFNULL(d.is_actifdep, 0) = 0
                AND IFNULL(d.is_actifdepad, 0) = 0
                AND (d.is_validedep = 0 OR d.is_validedep IS NULL)
                " . sprintf($horsCourrier, 'd.type_depense') . " {$fd}
                GROUP BY {$det_dep}",
                $b, 'depense', 'chef'
            );
            $run(
                "SELECT {$det_dep} AS ra, COUNT(*) AS nb FROM depense d
                WHERE " . sous_caisse_predicat('d.idcaisse_depens', $idc) . " AND d.date_depens BETWEEN ? AND ?
                AND IFNULL(d.ferme_caisdep, 0) = 0
                AND IFNULL(d.active_dep, 0) = 1
                AND IFNULL(d.is_actifdepad, 0) = 0
                AND IFNULL(d.is_actifdep, 0) = 0
                AND (d.is_validedep = 0 OR d.is_validedep IS NULL)
                " . sprintf($horsCourrier, 'd.type_depense') . " {$fd}
                GROUP BY {$det_dep}",
                $b, 'depense', 'attente'
            );
            $run(
                "SELECT IFNULL(d.opevalidad, 0) AS ra, COUNT(*) AS nb FROM depense d
                WHERE " . sous_caisse_predicat('d.idcaisse_depens', $idc) . " AND d.date_depens BETWEEN ? AND ?
                AND IFNULL(d.ferme_caisdep, 0) = 0
                AND IFNULL(d.is_actifdepad, 0) = 1
                AND IFNULL(d.is_actifdep, 0) = 0
                AND IFNULL(d.arret_caisdep, 0) = 0
                " . sprintf($horsCourrier, 'd.type_depense') . " {$fd}
                GROUP BY IFNULL(d.opevalidad, 0)",
                $b, 'depense', 'adjoint'
            );
            $run(
                "SELECT IFNULL(d.opevalid, 0) AS ra, COUNT(*) AS nb FROM depense d
                WHERE " . sous_caisse_predicat('d.idcaisse_depens', $idc) . " AND d.date_depens BETWEEN ? AND ?
                AND IFNULL(d.ferme_caisdep, 0) = 0
                AND IFNULL(d.is_actifdepad, 0) = 1
                AND IFNULL(d.is_actifdep, 0) = 0
                AND IFNULL(d.arret_caisdep, 0) = 1
                " . sprintf($horsCourrier, 'd.type_depense') . " {$fd}
                GROUP BY IFNULL(d.opevalid, 0)",
                $b, 'depense', 'principale'
            );

            $run(
                "SELECT {$det_depo} AS ra, COUNT(*) AS nb FROM depot d
                WHERE " . sous_caisse_predicat('d.idcaisse_depot', $idc) . " AND d.datedepot BETWEEN ? AND ?
                AND IFNULL(d.ferme_caisdepo, 0) = 0
                AND IFNULL(d.is_actifdepo, 0) = 0
                AND IFNULL(d.is_actifdepoad, 0) = 0
                AND IFNULL(d.is_validdepo, 0) = 0
                AND IFNULL(d.arret_caisdepo, 0) = 0
                AND COALESCE(d.valid_depo, '') <> 'valid'
                " . sprintf($horsCourrier, 'd.type_depot') . " {$fp}
                GROUP BY {$det_depo}",
                $b, 'depot', 'chef'
            );
            $run(
                "SELECT {$det_depo} AS ra, COUNT(*) AS nb FROM depot d
                WHERE " . sous_caisse_predicat('d.idcaisse_depot', $idc) . " AND d.datedepot BETWEEN ? AND ?
                AND IFNULL(d.ferme_caisdepo, 0) = 0
                AND IFNULL(d.is_actifdepo, 0) = 0
                AND IFNULL(d.is_actifdepoad, 0) = 0
                AND IFNULL(d.is_validdepo, 0) = 0
                AND IFNULL(d.arret_caisdepo, 0) = 0
                AND COALESCE(d.valid_depo, '') = 'valid'
                " . sprintf($horsCourrier, 'd.type_depot') . " {$fp}
                GROUP BY {$det_depo}",
                $b, 'depot', 'attente'
            );
            $run(
                "SELECT IFNULL(d.opvalidad, 0) AS ra, COUNT(*) AS nb FROM depot d
                WHERE " . sous_caisse_predicat('d.idcaisse_depot', $idc) . " AND d.datedepot BETWEEN ? AND ?
                AND IFNULL(d.ferme_caisdepo, 0) = 0
                AND IFNULL(d.is_actifdepoad, 0) = 1
                AND IFNULL(d.is_actifdepo, 0) = 0
                AND IFNULL(d.arret_caisdepo, 0) = 0
                " . sprintf($horsCourrier, 'd.type_depot') . " {$fp}
                GROUP BY IFNULL(d.opvalidad, 0)",
                $b, 'depot', 'adjoint'
            );
            $run(
                "SELECT IFNULL(d.opvalid, 0) AS ra, COUNT(*) AS nb FROM depot d
                WHERE " . sous_caisse_predicat('d.idcaisse_depot', $idc) . " AND d.datedepot BETWEEN ? AND ?
                AND IFNULL(d.ferme_caisdepo, 0) = 0
                AND IFNULL(d.is_actifdepoad, 0) = 1
                AND IFNULL(d.is_actifdepo, 0) = 0
                AND IFNULL(d.arret_caisdepo, 0) = 1
                " . sprintf($horsCourrier, 'd.type_depot') . " {$fp}
                GROUP BY IFNULL(d.opvalid, 0)",
                $b, 'depot', 'principale'
            );

            $horsVers = " AND IFNULL(v.type_versement, '') <> 'Courrier'
                AND IFNULL(v.type_versement, '') <> 'Bordereau_bancairecourrier' ";
            $run(
                "SELECT v.idop_versement AS ra, COUNT(*) AS nb FROM versements v
                WHERE " . sous_caisse_predicat('v.idcaisse_versement', $idc) . " AND v.date_versement BETWEEN ? AND ?
                AND IFNULL(v.ferme_caisvers, 0) = 0
                AND IFNULL(v.is_actifverser, 0) = 0
                AND IFNULL(v.is_actifverserad, 0) = 0
                AND IFNULL(v.active_verse, 0) = 0
                AND IFNULL(v.valider_vers, 0) = 0
                AND IFNULL(v.arret_caisvers, 0) = 0
                {$horsVers} {$fv}
                GROUP BY v.idop_versement",
                $b, 'versement', 'chef'
            );
            $run(
                "SELECT v.idop_versement AS ra, COUNT(*) AS nb FROM versements v
                WHERE " . sous_caisse_predicat('v.idcaisse_versement', $idc) . " AND v.date_versement BETWEEN ? AND ?
                AND IFNULL(v.ferme_caisvers, 0) = 0
                AND IFNULL(v.active_verse, 0) = 1
                AND IFNULL(v.is_actifverser, 0) = 0
                AND IFNULL(v.is_actifverserad, 0) = 0
                AND IFNULL(v.valider_vers, 0) = 0
                AND IFNULL(v.arret_caisvers, 0) = 0
                {$horsVers} {$fv}
                GROUP BY v.idop_versement",
                $b, 'versement', 'attente'
            );
            $run(
                "SELECT IFNULL(v.validopad, 0) AS ra, COUNT(*) AS nb FROM versements v
                WHERE " . sous_caisse_predicat('v.idcaisse_versement', $idc) . " AND v.date_versement BETWEEN ? AND ?
                AND IFNULL(v.ferme_caisvers, 0) = 0
                AND IFNULL(v.is_actifverser, 0) = 0
                AND IFNULL(v.is_actifverserad, 0) = 1
                AND IFNULL(v.arret_caisvers, 0) = 0
                {$horsVers} {$fv}
                GROUP BY IFNULL(v.validopad, 0)",
                $b, 'versement', 'adjoint'
            );
            $run(
                "SELECT IFNULL(v.validop, 0) AS ra, COUNT(*) AS nb FROM versements v
                WHERE " . sous_caisse_predicat('v.idcaisse_versement', $idc) . " AND v.date_versement BETWEEN ? AND ?
                AND IFNULL(v.ferme_caisvers, 0) = 0
                AND IFNULL(v.is_actifverserad, 0) = 1
                AND IFNULL(v.is_actifverser, 0) = 0
                AND IFNULL(v.arret_caisvers, 0) = 1
                {$horsVers} {$fv}
                GROUP BY IFNULL(v.validop, 0)",
                $b, 'versement', 'principale'
            );

            if (!$parts) {
                return '';
            }

            $noms = $this->_arret_caisse_noms_detenteurs($ekey, $gare, $parts);
            $lignes = array();
            $libNature = array(
                'recette' => array('recette', 'recettes'),
                'depense' => array('dépense', 'dépenses'),
                'depot' => array('dépôt', 'dépôts'),
                'versement' => array('versement', 'versements'),
            );
            $libLieu = array(
                'chef' => 'chez le chef de guichet',
                'attente' => 'en attente de validation (adjoint ou caissier)',
                'adjoint' => 'chez le caissier adjoint',
                'principale' => 'chez la caissière principale',
            );
            foreach ($parts as $key => $item) {
                $n = (int) $item['nb'];
                $mot = $libNature[$item['nature']];
                $mot = ($n > 1) ? $mot[1] : $mot[0];
                $ou = isset($libLieu[$item['lieu']]) ? $libLieu[$item['lieu']] : $item['lieu'];
                $qui = isset($noms[$key]) ? $noms[$key] : '';
                $lignes[] = $n . ' ' . $mot . ' ' . $ou . ($qui !== '' ? ' : ' . $qui : '');
                if (count($lignes) >= 12) {
                    $lignes[] = 'D’autres opérations de cette période ne sont pas encore finalisées.';
                    break;
                }
            }

            return "Arrêt de caisse refusé. Il reste des opérations du {$du} au {$au} qui ne sont pas encore finalisées.\n"
                . implode("\n", $lignes);
        }

        /**
         * Nom du détenteur, ou les adjoints / caissières de la gare si la ligne n'est pas encore prise.
         *
         * @param string $ekey
         * @param string $gare
         * @param array $parts
         * @return array
         */
        protected function _arret_caisse_noms_detenteurs($ekey, $gare, array $parts)
        {
            $ids = array();
            $besoinAdjoint = false;
            $besoinPrincipale = false;
            foreach ($parts as $item) {
                $ra = (int) $item['ra'];
                if ($ra > 0) {
                    $ids[$ra] = $ra;
                } elseif ($item['lieu'] === 'adjoint' || $item['lieu'] === 'attente') {
                    $besoinAdjoint = true;
                } elseif ($item['lieu'] === 'principale') {
                    $besoinPrincipale = true;
                }
            }
            $parId = array();
            if ($ids) {
                $in = implode(',', $ids);
                $rows = $this->db->query(
                    "SELECT ar.roleattribut,
                        COALESCE(
                            NULLIF(TRIM(CONCAT(IFNULL(u.first_name, ''), ' ', IFNULL(u.last_name, ''))), ''),
                            NULLIF(TRIM(cu.username), ''),
                            CONCAT(ar.roleattribut, '')
                        ) AS nom
                    FROM attributions_role ar
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    WHERE ar.roleattribut IN ({$in})"
                )->result();
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $parId[(int) $row->roleattribut] = trim((string) $row->nom);
                    }
                }
            }
            $listeRole = function ($userole) use ($ekey, $gare) {
                $rows = $this->db->query(
                    "SELECT DISTINCT COALESCE(
                            NULLIF(TRIM(CONCAT(IFNULL(u.first_name, ''), ' ', IFNULL(u.last_name, ''))), ''),
                            NULLIF(TRIM(cu.username), ''),
                            CONCAT(ar.roleattribut, '')
                        ) AS nom
                    FROM attributions_role ar
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN entreprise e ON u.cle_comp = e.ekey
                    WHERE e.ekey = ?
                    AND ar.userole = ?
                    AND IFNULL(ar.activer_role, 0) = 0
                    AND ul.guser = ?
                    ORDER BY nom ASC",
                    array($ekey, (int) $userole, $gare)
                )->result();
                $noms = array();
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $nom = trim((string) $row->nom);
                        if ($nom !== '') {
                            $noms[$nom] = $nom;
                        }
                    }
                }
                return $noms ? implode(', ', $noms) : '';
            };
            $nomAdjoint = $besoinAdjoint ? $listeRole(18) : '';
            $nomPrincipale = $besoinPrincipale ? $listeRole(4) : '';
            $out = array();
            foreach ($parts as $key => $item) {
                $ra = (int) $item['ra'];
                if ($ra > 0 && isset($parId[$ra]) && $parId[$ra] !== '') {
                    $out[$key] = $parId[$ra];
                } elseif ($item['lieu'] === 'adjoint') {
                    $out[$key] = $nomAdjoint !== '' ? $nomAdjoint : 'caissier adjoint de la gare';
                } elseif ($item['lieu'] === 'principale') {
                    $out[$key] = $nomPrincipale !== '' ? $nomPrincipale : 'caissière principale de la gare';
                } elseif ($item['lieu'] === 'chef') {
                    $out[$key] = 'chef de guichet';
                }
            }

            return $out;
        }
    }
    /* End of file: Arretcaisses */
    /* File localisation: application/controllers/Arretcaisse.php */
