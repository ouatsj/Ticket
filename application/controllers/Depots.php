<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Depots extends MY_Controller
    {
        public $depots;
        public $company;
        protected $property = array(
            'title' => 'Depots',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        
        public function __construct()
        {
            parent::__construct();
            $this->load->helper('scripts');
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
            $this->property = array_merge($this->property, scripts_bundle_property('caisse', null, true));
        }

        /**
         * Date de dépôt envoyée par les formulaires (noms de champs historiques).
         *
         * @param int|null $id_depot  repli sur la date existante en modification
         * @return string
         */
        private function _depot_date_from_post($id_depot = null)
        {
            foreach (array('daterecep', 'date_depot', 'datedepot') as $field) {
                $value = trim((string) $this->input->post($field));
                if ($value !== '') {
                    return $value;
                }
            }

            if ($id_depot !== null) {
                $row = $this->db->where('id_depot', (int) $id_depot)->get('depot')->row();
                if ($row && !empty($row->datedepot)) {
                    $ts = strtotime($row->datedepot);
                    if ($ts !== false) {
                        return date('Y-m-d', $ts);
                    }

                    return $row->datedepot;
                }
            }

            return '';
        }
        
       public function view($ckey, $iddepot = NULL)
       {

            $this->company = $this->m_entreprises->get_key($ckey);

            $this->property['pagetitle'] .= " LES DEPOTS VALIDES &nbsp; <strong>{$this->company->nom_entreprise}</strong>";
            $this->property['depots'] = $this->m_depot->get($this->company->ekey);
            $this->property['genres'] = $this->m_genre_depot->getb();
            $this->property['typedocuments'] = $this->m_typedocument->get();
            $this->property['banque'] = $this->m_banque->get();
            return $this->layout->view('_depot/index', $this->property);
           
       }

        public function addepotbq($ckey)
        {

                $this->company = $this->m_entreprises->get_key($ckey);
                $identifiant_gare = $this->input->post('idgarecode');
                $identifiant_caisse = $this->input->post('idcaisse'); 
                $gid = $this->input->post('gareconnect');
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $sgid = $this->input->post('sousgareconnect');
                $idcmpt = $this->input->post('compconnected');

            if($this->input->post('date_depot')!= '')
            {
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'idop_depot' => $iduser,
                    'sousgdepot' => $sgid,
                    'type_depot' => $this->input->post('typedocs'),
                    'idgenre_depot' => $this->input->post('provenance'),
                    'compkey_depo' => $this->input->post('_compag'),
                    'nom_pre' => $this->input->post('nombq'),
                    'datedepot' => $this->input->post('date_depot'),
                    'montant_depot' => $this->input->post('montantdeposebq'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->create($depoaray);
                
                if(recette_role_is_validateur_principal($this->session->agent->userole))
                {
                    $updepos = array(
                            'is_validdepo' => 1, 
                            'is_actifdepo' => 1,
                            'opvalid' => $iduser,
                        );
                    $this->m_depot->update($depo, $updepos);

                    $this->property['UPDATE_SUCCESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/autredepot/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }

                if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    $updepos = array(
                        'is_validdepo' => 1, 
                        'is_actifdepoad' => 1,
                        'opvalidad' => $iduser,
                    );
                    $this->m_depot->update($depo, $updepos);

                    $this->property['UPDATE_SUCCESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/autredepot/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
                }
                else
                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/autredepot_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
             
        }

        //enregistrement des depots
        public function adddepot($ckey)
        {

            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse'); 

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');            
            if($this->input->post('datedepot')!= '')
            {
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'idop_depot' => $iduser,
                    'sousgdepot' => $sgid,
                    'compkey_depo' => $this->input->post('_compag'),
                    'type_depot' => $this->input->post('autretype'),
                    'idgenre_depot' => $this->input->post('genreautre'),
                    'typersodepot' => $this->input->post('typerson'),
                    'nom_pre' => $this->input->post('nom'),
                    'datedepot' => $this->input->post('datedepot'),
                    'montant_depot' => '-'.$this->input->post('autremontant'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->create($depoaray);
                
                if(recette_role_is_validateur_principal($this->session->agent->userole))
                {
                    $updepos = array(
                        'is_actifdepo' => 1,
                        'opvalid' => $iduser,
                    );
                    $this->m_depot->update($depo, $updepos);

                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depot/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));

                }

                if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    $updepos = array(
                        'is_actifdepoad' => 1,
                        'opvalidad' => $iduser,
                    );
                    $this->m_depot->update($depo, $updepos);

                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depot/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));

                }
                else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/depot_adjoint/' . mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                     
        }

        public function addepotfour($ckey)
        {

            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse'); 

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            if($this->input->post('datedepot')!= '')
            {
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'idop_depot' => $iduser,
                    'sousgdepot' => $sgid,
                    'compkey_depo' => $this->input->post('_compag'),
                    'type_depot' => $this->input->post('autretype'),
                    'idgenre_depot' => $this->input->post('genreautre'),
                    'typersodepot' => $this->input->post('typerson'),
                    'nom_pre' => $this->input->post('nom'),
                    'datedepot' => $this->input->post('datedepot'),
                    'montant_depot' => '-'.$this->input->post('autremontant'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->create($depoaray);
                
                if(recette_role_is_validateur_principal($this->session->agent->userole))
                {
                    $updepos = array(
                        'is_actifdepo' => 1,
                        'opvalid' => $iduser,
                    );
                    $this->m_depot->update($depo, $updepos);

                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depot/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));

                }

                if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    $updepos = array(
                        'is_actifdepoad' => 1,
                        'opvalidad' => $iduser,
                    );
                    $this->m_depot->update($depo, $updepos);

                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depot/'. $iduser.'/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));

                }
                else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/depot_adjoint/'.$sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
        
            
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                     
        }
        //modification
        public function updatedepot($ckey, $idpo)
        {

            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse'); 
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $date_depot = $this->_depot_date_from_post($idpo);
            if ($date_depot !== '')
            {
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'type_depot' => $this->input->post('depot'),
                    'idgenre_depot' => $this->input->post('genre'),
                    'compkey_depo' => $this->input->post('_compag'),
                    'nom_pre' => $this->input->post('nom'),
                    'datedepot' => $date_depot,
                    'montant_depot' => $this->input->post('montantverse'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->update($idpo, $depoaray);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;
                
                if(recette_role_is_validateur_principal($this->session->agent->userole) OR recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depot/'. $idcmpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/depot_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                
        }

        //valide comptable

        public function valdepot($ckey, $idpo, $dop)
        {

            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse');
            $identifiant_sousgare = $this->input->post('idsousgar');
            $cashboxContext = roleattribut_guard_main_cashbox_validation_context(
                $this->company->ekey,
                $identifiant_gare,
                $dop
            );
            if ($cashboxContext) {
                roleattribut_guard_assert_main_cashbox_operation(
                    'depot',
                    $idpo,
                    $this->company->ekey,
                    $identifiant_gare,
                    $cashboxContext['caissier_ra']
                );
            }
            $depoaray = array(
                'commentaire_depot' => $this->input->post('comment'),
                'valid_cptabledepo' => 1,
                'opvalid_cptabledepo' => roleattribut_guard_session_ra(),
            );
            $depo = $this->m_depot->update($idpo, $depoaray);
            
            $this->property['UPDATE_SUCCESS'] = TRUE;

            if ($this->_render_validation_filter_depots($identifiant_gare, $identifiant_sousgare, $identifiant_use, $dop, $cashboxContext)) {
                return;
            }
            
                redirect('utilisateurs/'.$this->session->company->ekey. '/caisseprincdepot/'. $identifiant_gare. '/'. ($cashboxContext ? $cashboxContext['consultant_ra'] : $identifiant_use). '/'.$identifiant_sousgare .'/'.($cashboxContext ? $cashboxContext['caissier_ra'] : $dop). '/' . mdate("%d/%m/%Y", now('UTC')));
            
        }

        public function rejetdepots($ckey, $idpo, $dop)
        {

            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgar');
            $identifiant_use = $this->input->post('iduse');
            $identifiant_sousgare = $this->input->post('idsousgar');
            $cashboxContext = roleattribut_guard_main_cashbox_validation_context(
                $this->company->ekey,
                $identifiant_gare,
                $dop
            );
            if ($cashboxContext) {
                roleattribut_guard_assert_main_cashbox_operation(
                    'depot',
                    $idpo,
                    $this->company->ekey,
                    $identifiant_gare,
                    $cashboxContext['caissier_ra']
                );
            }
            $depoaray = array(
                'commentaire_depot' => $this->input->post('comment'),
                'ferme_caisdepo' => 0,
            );
            $depo = $this->m_depot->update($idpo, $depoaray);
            
            $this->property['UPDATE_SUCCESS'] = TRUE;

            if ($this->_render_validation_filter_depots($identifiant_gare, $identifiant_sousgare, $identifiant_use, $dop, $cashboxContext)) {
                return;
            }
            
                redirect('utilisateurs/'.$this->session->company->ekey. '/caisseprincdepot/'. $identifiant_gare. '/'. ($cashboxContext ? $cashboxContext['consultant_ra'] : $identifiant_use). '/'.$identifiant_sousgare.'/'. ($cashboxContext ? $cashboxContext['caissier_ra'] : $dop). '/' . mdate("%d/%m/%Y", now('UTC')));
            
        }

        /**
         * Recharge la liste filtrée après validation/rejet dépôt (évite de perdre le tri).
         *
         * @return bool true si la vue filtrée a été rendue
         */
        protected function _render_validation_filter_depots($identifiant_gare, $identifiant_sousgare, $identifiant_use, $dop, $cashboxContext)
        {
            $company = $this->input->post('_compag');
            $d1 = $this->input->post('datedebut');
            $d2 = $this->input->post('datefin');
            if (!roleattribut_guard_has_validation_filter_dates($d1, $d2)) {
                return false;
            }

            $viewer_ra = $cashboxContext
                ? (int) $cashboxContext['consultant_ra']
                : (int) $identifiant_use;
            $caissier_ra = $cashboxContext
                ? (int) $cashboxContext['caissier_ra']
                : (int) $dop;
            $query_ra = $caissier_ra;

            $rows = $this->m_depot->validfilter(
                $this->company->ekey,
                $identifiant_gare,
                $query_ra,
                $d1,
                $d2,
                $company
            );
            $this->property['gare_stop'] = $this->m_sousgare->sget($this->company->ekey, $identifiant_gare, $identifiant_sousgare);
            $this->property['conex'] = $this->m_compte_user->getusergare($this->company->ekey, $identifiant_gare, $caissier_ra);
            $this->property['connex'] = $this->m_compte_user->getusergar($this->company->ekey, $identifiant_gare, $viewer_ra);
            $this->property['cashbox_viewer_roleattribut'] = $viewer_ra;
            $this->property['cashbox_target_roleattribut'] = $caissier_ra;
            $this->property['cashbox_list_roleattribut'] = $viewer_ra;
            $this->property['depots'] = $rows;
            $this->property['depotsvalid'] = (object) array(
                'montant_depot' => array_sum(array_map(function ($row) {
                    return (float) $row->montant_depot;
                }, $rows)),
            );
            $this->property['compagnies'] = $this->m_compagnies->get();
            $this->property['typedocuments'] = $this->m_typedocument->get();
            $this->property['filter_date_start'] = $d1;
            $this->property['filter_date_end'] = $d2;
            $this->property['filter_compagnie'] = $company;
            $this->property['pagetitle'] .= "• VALIDATION DES DEPOTS•&nbsp;{$this->property['conex']->garenom}<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
            $this->layout->view('_caisse/valddept', $this->property);
            return true;
        }
        
        //enregistrement autre 
        public function addautre($ckey)
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
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'idop_depot' => $iduser,
                    'sousgdepot' => $sgid,
                    'type_depot' => $this->input->post('autretype'),
                    'idgenre_depot' => $this->input->post('genreautre'),
                    'compkey_depo' => $this->input->post('_compag'),
                    'nom_pre' => $this->input->post('nom'),
                    'datedepot' => $this->input->post('daterecep'),
                    'montant_depot' => $this->input->post('autremontant'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->create($depoaray);
                
                if(recette_role_is_validateur_principal($this->session->agent->userole))
                {
                    $updepos = array(
                            'is_validdepo' => 1, 
                            'is_actifdepo' => 1,
                            'opvalid' => $iduser,
                        );
                    $this->m_depot->update($depo, $updepos);

                    $this->property['UPDATE_SUCCESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/autredepot/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
                }
                if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    $updepos = array(
                        'is_validdepo' => 1, 
                        'is_actifdepoad' => 1,
                        'opvalidad' => $iduser,
                    );
                    $this->m_depot->update($depo, $updepos);

                    $this->property['UPDATE_SUCCESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/autredepot/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
                }
                else
                    $this->property['UPDATE_SUCCESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/autredepot_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
             
        }

        //modification
        public function upautredepot($ckey, $idpo)
        {

                $this->company = $this->m_entreprises->get_key($ckey);
                $identifiant_gare = $this->input->post('idgarecode');
                $identifiant_caisse = $this->input->post('idcaisse'); 
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $date_depot = $this->_depot_date_from_post($idpo);
            if ($date_depot !== '')
            {
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'type_depot' => $this->input->post('depot'),
                    'idgenre_depot' => $this->input->post('genre'),
                    'compkey_depo' => $this->input->post('_compag'),
                    'nom_pre' => $this->input->post('nom'),
                    'datedepot' => $date_depot,
                    'montant_depot' => $this->input->post('montantverse'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->update($idpo, $depoaray);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;

                if(recette_role_is_validateur_principal($this->session->agent->userole) OR recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/autredepot/'. $idcmpt.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/autredepot_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
             

        }

        public function updatefour($ckey, $idpo)
        {

                $this->company = $this->m_entreprises->get_key($ckey);
                $identifiant_gare = $this->input->post('idgarecode');
                $identifiant_caisse = $this->input->post('idcaisse'); 
                $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $date_depot = $this->_depot_date_from_post($idpo);
            if ($date_depot !== '')
            {
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'type_depot' => $this->input->post('depot'),
                    'idgenre_depot' => $this->input->post('genre'),
                    'compkey_depo' => $this->input->post('_compag'),
                    'nom_pre' => $this->input->post('nom'),
                    'datedepot' => $date_depot,
                    'montant_depot' => $this->input->post('montantverse'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->update($idpo, $depoaray);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;

                if(recette_role_is_validateur_principal($this->session->agent->userole) OR recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/autredepot/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/autredepot_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
             

        }

        //depot sous caisse
        public function addsous($ckey)
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
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'idop_depot' => $iduser,
                    'sousgdepot' => $sgid,
                    'type_depot' => $this->input->post('autretype'),
                    'idgenre_depot' => $this->input->post('genreautre'),
                    'compkey_depo' => $this->input->post('_compag'),
                    'typersodepot' => $this->input->post('personnels'),
                    'nom_pre' => $this->input->post('nom'),
                    'datedepot' => $this->input->post('daterecep'),
                    'montant_depot' => $this->input->post('autremontant'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->create($depoaray);
                
                if(recette_role_is_validateur_principal($this->session->agent->userole))
                {

                    $updepos = array(
                        'is_validdepo' => 1, 
                        'is_actifdepo' => 1,
                        'opvalid' => $iduser,
                    );
                    $this->m_depot->update($depo, $updepos);

                    $this->property['UPDATE_SUCCESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depotsous/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        
                }

                if(recette_role_is_validateur_adjoint($this->session->agent->userole))
                {

                    $updepos = array(
                        'is_validdepo' => 1, 
                        'is_actifdepoad' => 1,
                        'opvalidad' => $iduser,
                    );
                    $this->m_depot->update($depo, $updepos);

                    $this->property['UPDATE_SUCCESS'] = TRUE;
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depotsous/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        
                }

                else
                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/depotsous_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        //modification
        public function upsousdepot($ckey, $id)
        {

            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');
            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $date_depot = $this->_depot_date_from_post($id);
            if ($date_depot !== '')
            {
                $depoaray = array(
                    'idcaisse_depot' => $identifiant_caisse,
                    'type_depot' => $this->input->post('typedepot'),
                    'idgenre_depot' => $this->input->post('genreautre'),
                    'typersodepot' => $this->input->post('personn'),
                    'compkey_depo' => $this->input->post('_compag'),
                    'nom_pre' => $this->input->post('nom'),
                    'datedepot' => $date_depot,
                    'montant_depot' => $this->input->post('montantverse'),
                    'commentaire_depot' => $this->input->post('comment'),
                );
                $depo = $this->m_depot->update($id, $depoaray);
                
                $this->property['UPDATE_SUCCESS'] = TRUE;

                if(recette_role_is_validateur_principal($this->session->agent->userole) OR recette_role_is_validateur_adjoint($this->session->agent->userole))
                {
                    redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/depotsous/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                else
                    redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/depotsous_adjoint/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            else
            redirect('gares/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/cais/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            
        }

        //approuve
        public function approuve($ckey, $id)
        {

            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            $prouve = array(
                'commentaire_depot' => $this->input->post('approuvedepot'),
                'approuve' => 1,
            );
            $depo = $this->m_depot->update($id, $prouve);

            $prouveversement = array(
                'idcaisse_versement' => $identifiant_caisse,
                'id_genre_versement' => $this->input->post('autregenrever'),
                'idop_versement' => $iduser,
                'sgareidvers' => $sgid,
                'compkey_vers' => $this->input->post('_compag'),
                'typpersonnel' => $this->input->post('typeperson'),
                'type_versement' => $this->input->post('typedepotvers'),
                'nom_beneficiaire' => $this->input->post('nombf'),
                'montant_verser' => $this->input->post('autrmontverse'),
                'commentaire' => $this->input->post('approuvedepot'),
                'date_versement' => $this->input->post('autreversdate'),
            );
            $versement = $this->m_versements->create($prouveversement);
            
            $this->property['UPDATE_SUCCESS'] = TRUE;
            redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/depotsous_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
        }

        public function approuv($ckey, $id)
        {

            $this->company = $this->m_entreprises->get_key($ckey);
            $identifiant_gare = $this->input->post('idgarecode');
            $identifiant_caisse = $this->input->post('idcaisse');

            $gid = $this->input->post('gareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $prouve = array(
                'commentaire' => $this->input->post('approuvedepot'),
                'approuveversement' => 1,
            );
            $depo = $this->m_versements->update($id, $prouve);

            $prouveversement = array(
                'idcaisse_depot' => $identifiant_caisse,
                'type_depot' => $this->input->post('typeversem'),
                'idop_depot' => $iduser,
                'sousgdepot' => $sgid,
                'compkey_depo' => $this->input->post('_compag'),
                'idgenre_depot' => $this->input->post('autregenrever'),
                'typersodepot' => $this->input->post('idtypeversem'),
                'nom_pre' => $this->input->post('nombf'),
                'datedepot' => $this->input->post('autreversdate'),
                'montant_depot' => $this->input->post('autrmontverse'),
                'commentaire_depot' => $this->input->post('approuvedepot'),
            );
            $versement = $this->m_depot->create($prouveversement);
            
            if(recette_role_is_validateur_principal($this->session->agent->userole))
            {
                $updepos = array(
                        'is_validdepo' => 1, 
                        'is_actifdepo' => 1,
                        'opvalid' => $iduser,
                    );
                $this->m_depot->update($id, $updepos);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versementcaisse/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

            }

            if(recette_role_is_validateur_adjoint($this->session->agent->userole))
            {
                $updepos = array(
                        'is_validdepo' => 1, 
                        'is_actifdepoad' => 1,
                        'opvalidad' => $iduser,
                    );
                $this->m_depot->update($id, $updepos);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                redirect('caisses/'.$this->session->company->ekey. '/gTv/'. $identifiant_gare. '/'. $identifiant_caisse. '/versementcaisse/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

            }
            else
            
                redirect('caisses/'.$this->session->company->ekey. '/cais/'. $identifiant_gare. '/'. $identifiant_caisse. '/'. $iduser. '/versementcaisse_adjoint/'. $sgid.'/'.  mdate("%d/%m/%Y", now('UTC')));
        }
        //tri depots
        public function listegenre($ty)
        {
            $ptype = $this->m_depot->typinternegenre($this->session->company->ekey, $ty);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $ptype));
        }

        
        public function versetribank($gd, $ty)
        {
            $vtype = $this->m_versements->typgenreverse($this->session->company->ekey, $gd, $ty);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $vtype));
        }

        //nombanq
        public function banknom($gd, $typ, $grd)
        {
            $bnom = $this->m_versements->typnombank($this->session->company->ekey, $gd, $typ, $grd);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $bnom));
        }

        public function listenom($grd, $nom)
        {
            $pnom = $this->m_depot->typinternenom($this->session->company->ekey, $grd, $nom);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $pnom));
        }
        
        //tri autre depots
        public function autrelistegenre($ty)
        {
            $typeautre = $this->m_depot->typautregenre($this->session->company->ekey, $ty);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $typeautre));
        }

        public function autrelistenom($grd, $nom)
        {
            $autrenom = $this->m_depot->typautrenom($this->session->company->ekey, $grd, $nom);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $autrenom));
        }

        public function depot_genre($idgr)
        {
            $nom = $this->m_depot->typenom($idgr);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $nom));
        }
    }
    
    /** End of file: Depots.php **/
    /** File location: application/controllers/Depots.php **/
