<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Historique_Passagers extends MY_Controller
    {
        public $property = array(
            'title' => 'Historiques',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        
        
        public function __construct()
        {
            parent::__construct();
            $this->load->helper('scripts');
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
            $this->property = array_merge($this->property, scripts_bundle_property('historique', null, true));
        }
        
        /**
         *
         */
        
        public function view($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
            $this->property['conex'] = $conex;
            $this->property['compagnies'] = $this->m_compagnies->get();
            $this->property['pagetitle'] .= "• PASSAGERS<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";

            // daypassagers (getdayad/getday) retiré : jamais utilisé dans la vue index,
            // mais chargeait tous les passagers du jour avec 10+ JOINs.
            $role = $this->session->agent->userole;
            if ($role === '1' OR $role === '2') {
                $this->property['lignes'] = $this->m_lignes->getad($this->company->id_entreprise);
            } else {
                $this->property['lignes'] = $this->m_lignes->get($this->company->id_entreprise, $gd);
            }
            $this->property['heuredeparts'] = $this->m_heure->get();
            $this->property['garedeparts'] = $this->m_sousgare->getes($this->company->ekey, $gd, $sg);

            return $this->layout->view('_historique/index', $this->property);
        }
        

        public function tripassager($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                //$conex = $this->m_compte_user->usget($uid, $gd);
                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;
            $ddbt = $this->input->post('debutdate');
            $dfin = $this->input->post('findate');
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                $this->property['historiques'] = $this->m_passager->alldayarchad($this->company->ekey, $ddbt, $dfin);

            }
            else{

                $this->property['historiques'] = $this->m_passager->alldayarch($this->company->ekey, $ddbt, $dfin, $gd);
            }

                $this->property['garedeparts'] = $this->m_sousgare->get($this->company->id_entreprise, $gd);
                $this->property['typesclients'] = $this->m_type_client->get();
                return $this->layout->view('_historique/tri_passager', $this->property);
         
        }
        

        public function nonreport($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;
            
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                $this->property['nonreports'] = $this->m_report->nongetad($this->company->ekey);

            }
            else{

                $this->property['nonreports'] = $this->m_report->nonget($this->company->ekey, $sg);
            }

                
                return $this->layout->view('_historique/nonget', $this->property);
         
        }

        public function recuetab($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;

                $this->property['allrecu'] = $this->m_passager->grecus($this->company->ekey, $gd);  
            return $this->layout->view('_historique/voirrecu', $this->property);
         
        }

        public function triliste($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ddbt = $this->input->post('debutdate');
            $dfin = $this->input->post('findate');
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                //$conex = $this->m_compte_user->usget($uid, $gd);
                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;
            
			if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                $this->property['passagers'] = $this->m_passager->listedatead($this->company->ekey, $ddbt, $dfin);
			}else{
				$this->property['passagers'] = $this->m_passager->listedate($this->company->ekey, $ddbt, $dfin, $gd);
			}
                return $this->layout->view('_historique/historiqueliste', $this->property);
         
        }

        //tri ticket reprogrammer
        public function trireprogramme($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ddbt = $this->input->post('debutdate');
            $dfin = $this->input->post('findate');
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                //$conex = $this->m_compte_user->usget($uid, $gd);
            $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                $this->property['historiquesrepro'] = $this->m_passager->trireparchad($this->company->ekey, $ddbt, $dfin);
            }
            else
            {

                $this->property['historiquesrepro'] = $this->m_passager->trirep($this->company->ekey, $ddbt, $dfin, $gd);
            }
                return $this->layout->view('_historique/tri_reppassager', $this->property);
         
        }


        public function triconfirme($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $ddbt = $this->input->post('debutdate');
            $dfin = $this->input->post('findate');
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                //$conex = $this->m_compte_user->usget($uid, $gd);
            $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                $this->property['historiquesconfirme'] = $this->m_passager->triconfarch($this->company->ekey, $ddbt, $dfin, $gd, $sg);
            }
            else
                $this->property['historiquesconfirme'] = $this->m_passager->triconf($this->company->ekey, $ddbt, $dfin, $gd, $sg);

                return $this->layout->view('_historique/tri_confpassager', $this->property);
         
        }

        
        public function suprime($ckey, $cdp, $ct, $uid, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $motif = historique_modif_ticket_read_motif_post();
            if (!$motif['ok']) {
                $this->session->set_flashdata('sale_error', $motif['error']);
                return $this->view($ckey, $uid, $g, $sg, $this->property);
            }

            $before = historique_modif_ticket_row_passager($this->db, $cdp, $ct);
            $delarray = array(
                'num_siege_categorie' => NULL,
            );
            $this->m_passager->update($cdp, $ct, $delarray);

            $changes = historique_modif_ticket_diff(
                array('num_siege_categorie' => isset($before['num_siege_categorie']) ? $before['num_siege_categorie'] : null),
                $delarray
            );
            historique_modif_ticket_log($this->db, array(
                'ekey' => $this->company->ekey,
                'gare_id' => $g,
                'type_modif' => 'annulation_siege',
                'code_passager' => $cdp,
                'code_ticket' => $ct,
                'id_client' => isset($before['id_client_pass']) ? (int) $before['id_client_pass'] : null,
                'changes' => $changes,
                'motif' => $motif['motif'],
                'ordre_par' => $motif['ordre_par'],
            ));

               $this->property['UPDATE_SUCCESS'] = TRUE;
               return $this->view($ckey, $uid, $g, $sg, $this->property);
        }

        //desactiver code d'un ticket
        public function desactivecode($ckey, $cdp, $code, $uid, $g, $sg)
        {
            $motif = historique_modif_ticket_read_motif_post();
            if (!$motif['ok']) {
                $this->session->set_flashdata('sale_error', $motif['error']);
                return $this->view($ckey, $uid, $g, $sg, $this->property);
            }

            if($code == 0)
            {
                $actif = 1;
            }
            else
            {
                $actif = 0;
            }

            $before = historique_modif_ticket_row_tampon($this->db, $cdp);
            $annulearray = array(
                'is_activecode' => $actif,
            );
            $this->m_tamponcode->update($cdp, $annulearray);

            $changes = historique_modif_ticket_diff(
                array('is_activecode' => isset($before['is_activecode']) ? $before['is_activecode'] : null),
                $annulearray
            );
            $pass = historique_modif_ticket_row_passager($this->db, $cdp);
            historique_modif_ticket_log($this->db, array(
                'ekey' => $ckey,
                'gare_id' => $g,
                'type_modif' => 'desactivation_code',
                'code_passager' => $cdp,
                'code_ticket' => isset($pass['code_ticket']) ? $pass['code_ticket'] : null,
                'id_client' => isset($pass['id_client_pass']) ? (int) $pass['id_client_pass'] : null,
                'changes' => $changes,
                'motif' => $motif['motif'],
                'ordre_par' => $motif['ordre_par'],
            ));

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $uid, $g, $sg, $this->property);
        }

        //modifier depart
        public function modifdepart($ckey, $cdp, $ct)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $sieges = strpos($this->input->post('siege'), '/');
            $sub_siege = substr($this->input->post('siege'), 0, $sieges);
            $cde_siege = substr($this->input->post('siege'), $sieges + 1, strlen($this->input->post('siege')));
            $g = $this->input->post('stop');
            $uid = $this->_roleattribut_guard_post_id($this->company->ekey, 'stop', 'useridconnected');
            $sg = $this->input->post('sousgd');
            $motif = historique_modif_ticket_read_motif_post();
            if (!$motif['ok']) {
                $this->session->set_flashdata('sale_error', $motif['error']);
                return $this->view($ckey, $uid, $g, $sg, $this->property);
            }

            $before = historique_modif_ticket_row_passager($this->db, $cdp, $ct);
            $delarray2 = array(
                'code_pro'=> $this->input->post('departs'),
                'num_siege_categorie' => $sub_siege,
                'num_cat'=> $cde_siege,
                'departclient_idgare' => $this->input->post('deparsousgareidentif'),
                'quart' => $this->input->post('quartier'),
            );

            $this->m_passager->update($cdp, $ct, $delarray2);

            $slice = array();
            foreach ($delarray2 as $k => $v) {
                $slice[$k] = isset($before[$k]) ? $before[$k] : null;
            }
            historique_modif_ticket_log($this->db, array(
                'ekey' => $this->company->ekey,
                'gare_id' => $g,
                'type_modif' => 'depart',
                'code_passager' => $cdp,
                'code_ticket' => $ct,
                'id_client' => isset($before['id_client_pass']) ? (int) $before['id_client_pass'] : null,
                'changes' => historique_modif_ticket_diff($slice, $delarray2),
                'motif' => $motif['motif'],
                'ordre_par' => $motif['ordre_par'],
            ));

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $uid, $g, $sg, $this->property);
        }

        public function supprimerreprt($ckey, $cdrp, $cdtp, $cdt, $uid, $g, $sg)
        {

            $this->company = $this->m_entreprises->get_key($ckey);

            $delarray = array(
                'num_siege_categorie' => NULL,
                'statut_reprog' => NULL,
            );
            $this->m_passager->update($cdtp, $cdt, $delarray);

            $arrayrep = array(
                'code_report' => $cdrp,
            );
            $arraycoderep = $this->m_report->del($cdrp, $arrayrep);

                $this->property['UPDATE_SUCCESS'] = TRUE;

                return $this->view($ckey, $uid, $g, $sg, $this->property);
        }

        public function reportsup($ckey, $cdrp, $uid, $g, $sg)
        {

            $this->company = $this->m_entreprises->get_key($ckey);

            $arrayrep = array(
                'code_report' => $cdrp,
            );
            $arraycoderep = $this->m_report->del($cdrp, $arrayrep);

                $this->property['UPDATE_SUCCESS'] = TRUE;

                return $this->nonreport($ckey, $uid, $g, $sg, $this->property);
        }
        
        public function updateticket($ckey, $cdp, $ct, $uid, $g, $sg, $nct = FALSE)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $motif = historique_modif_ticket_read_motif_post();
            if (!$motif['ok']) {
                $this->session->set_flashdata('sale_error', $motif['error']);
                return $this->view($ckey, $uid, $g, $sg, $this->property);
            }

            if (sales_price_controls_enabled()) {
                if (strtoupper($this->input->method()) !== 'POST'
                    || (string) $this->session->company->ekey !== (string) $ckey
                    || !super_admin_can('sales.price.free')
                ) {
                    show_error('Modification du prix non autorisée.', 403);
                    return;
                }

                $ticket = $this->db
                    ->from('passager')
                    ->where('code_passager', $cdp)
                    ->where('code_ticket', $ct)
                    ->limit(1)
                    ->get()
                    ->row();
                if (!$ticket) {
                    show_404();
                    return;
                }

                $printed = $this->db
                    ->from('ticket_print_events')
                    ->where('company_ekey', (int) $ckey)
                    ->where('code_passager', $cdp)
                    ->where('code_ticket', $ct)
                    ->limit(1)
                    ->count_all_results() > 0;
                if ($printed
                    && (!sales_setting_bool('sales.post_print_edit_enabled', false)
                        || !super_admin_can('sales.ticket.edit_after_print'))
                ) {
                    show_error('Ce billet a déjà été imprimé et ne peut plus être modifié.', 403);
                    return;
                }

                $pricing = sales_price_validate_or_fail(
                    $ticket->code_pro,
                    $this->input->post('prixticket'),
                    array(
                        'reason' => $motif['motif'],
                        'authorization_type' => 'divers',
                        'zero_confirmed' => $this->input->post('confirmation_zero') === '1',
                    )
                );
                $newPrice = $pricing['sold_price'];
                $oldPrice = (float) $ticket->prixvente;
                $reason = $motif['motif'];
            } else {
                $newPrice = $this->input->post('prixticket');
                $oldPrice = null;
                $reason = $motif['motif'];
            }

            $before = historique_modif_ticket_row_passager($this->db, $cdp, $ct);
            $delarray = array(
                'prixvente' => $newPrice,
            );
            $this->m_passager->update($cdp, $ct, $delarray);

            $codenarray = array(
                'prixretour' => $newPrice,
            );

            $this->m_non_passager->update($cdp, $nct, $codenarray);
            if (sales_price_controls_enabled() && $this->db->table_exists('ticket_audit_log')) {
                $this->db->insert('ticket_audit_log', array(
                    'company_ekey' => (int) $ckey,
                    'code_passager' => (string) $cdp,
                    'code_ticket' => (string) $ct,
                    'actor_cpuser_id' => (int) $this->session->agent->cpuser_id,
                    'action_code' => 'price.update',
                    'old_values_json' => json_encode(array('prixvente' => $oldPrice)),
                    'new_values_json' => json_encode(array('prixvente' => $newPrice)),
                    'reason' => $reason,
                    'created_at' => date('Y-m-d H:i:s'),
                ));
            }

            $changes = historique_modif_ticket_diff(
                array('prixvente' => isset($before['prixvente']) ? $before['prixvente'] : null),
                $delarray
            );
            historique_modif_ticket_log($this->db, array(
                'ekey' => $this->company->ekey,
                'gare_id' => $g,
                'type_modif' => 'prix',
                'code_passager' => $cdp,
                'code_ticket' => $ct,
                'id_client' => isset($before['id_client_pass']) ? (int) $before['id_client_pass'] : null,
                'changes' => $changes,
                'motif' => $motif['motif'],
                'ordre_par' => $motif['ordre_par'],
            ));

            $this->property['UPDATE_SUCCESS'] = TRUE;

                return $this->view($ckey, $uid, $g, $sg, $this->property);
        }
        public function upgarequart($ckey, $cdp, $ct, $uid, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $motif = historique_modif_ticket_read_motif_post();
            if (!$motif['ok']) {
                $this->session->set_flashdata('sale_error', $motif['error']);
                return $this->view($ckey, $uid, $g, $sg, $this->property);
            }

            $before = historique_modif_ticket_row_passager($this->db, $cdp, $ct);
            $delarrays = array(

                'departclient_idgare' => $this->input->post('deparsousgareidentifs'),
                'quart' => $this->input->post('idquarts'),

            );
            $this->m_passager->update($cdp, $ct, $delarrays);

            $slice = array(
                'departclient_idgare' => isset($before['departclient_idgare']) ? $before['departclient_idgare'] : null,
                'quart' => isset($before['quart']) ? $before['quart'] : null,
            );
            historique_modif_ticket_log($this->db, array(
                'ekey' => $this->company->ekey,
                'gare_id' => $g,
                'type_modif' => 'gare_quartier',
                'code_passager' => $cdp,
                'code_ticket' => $ct,
                'id_client' => isset($before['id_client_pass']) ? (int) $before['id_client_pass'] : null,
                'changes' => historique_modif_ticket_diff($slice, $delarrays),
                'motif' => $motif['motif'],
                'ordre_par' => $motif['ordre_par'],
            ));

            $this->property['UPDATE_SUCCESS'] = TRUE;

                return $this->view($ckey, $uid, $g, $sg, $this->property);
        }
        //
        public function suprimeconf($ckey, $cdp, $ct, $uid, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $delconf = array(
                    'num_siege_categorie' => NULL,
                    'statut_confirme' => NULL,
                );
            $this->m_passager->update($cdp, $ct, $delconf);

            $this->property['UPDATE_SUCCESS'] = TRUE;
          return $this->view($ckey, $uid, $g, $sg, $this->property);
        }

        public function viewpass($ckey, $uid, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $this->property['pagetitle'] .= "• PASSAGERS<strong>•&nbsp;{$this->company->nom_entreprise}</strong>";
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

					$this->property['dayspassagers'] = $this->m_passager->dayad($this->company->ekey);
				}
				else{
					$this->property['dayspassagers'] = $this->m_passager->day($this->company->ekey, $g);
				}

                return $this->layout->view('_historique/indexpass', $this->property);
        }

        
        public function sup($ckey, $cdp, $tc, $uid, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $delconf = array(
                'num_siege_categorie' => NULL,
            );
            $this->m_passager->update($cdp, $tc, $delconf);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->viewpass($ckey, $uid, $g, $sg, $this->property);
        }

        public function updateconf($ckey, $clid, $uid, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $motif = historique_modif_ticket_read_motif_post();
            if (!$motif['ok']) {
                $this->session->set_flashdata('sale_error', $motif['error']);
                return $this->view($ckey, $uid, $g, $sg, $this->property);
            }
            
            $argv = array(
                'nom_client' => $this->input->post('rclient'),
                'prenom_client' => $this->input->post('prclient'),
                'contact_client' => $this->input->post('rclient_contact'),
                'num_CNIB' => $this->input->post('cnib'),
                'date_delivre' => $this->input->post('date_cnib'),
                'lieu_delivre' => $this->input->post('lieu'),
            );

            historique_modif_ticket_log_client_fields($this->db, $clid, $argv, array(
                'ekey' => $this->company->ekey,
                'gare_id' => $g,
                'motif' => $motif['motif'],
                'ordre_par' => $motif['ordre_par'],
            ));
            
            $clhid = $this->m_client->update($clid, $argv);
            if ($clhid != FALSE)
            
                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $uid, $g, $sg, $this->property);
        }


        //supprimer ticket
        public function supprimerticket($ckey, $codepas, $idtck, $uid, $g, $sg, $idntck = NULL)
        {
            $this->company = $this->m_entreprises->get_key($ckey);          

            if($idntck === NULL){

                $argrepas = array(
                            'code_passager' => $codepas,
                            'code_ticket' => $idtck,
                        );
                $this->m_passager->del($codepas, $idtck, $argrepas);

                
                $arraytamp = array(
                    'tampncod' => $codepas,
                );
                $this->m_tamponcode->del($codepas, $arraytamp);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $uid, $g, $sg, $this->property);
            }
            else
            {
                $argrepas = array(
                            'code_passager' => $codepas,
                            'code_ticket' => $idtck,
                        );
                $this->m_passager->del($codepas, $idtck, $argrepas);

                $argrenpas = array(
                            'code_non_pass' => $codepas,
                            'codeticket' => $idntck,
                        );
                $this->m_non_passager->del($codepas, $idntck, $argrenpas);

                $arraytamp = array(
                    'tampncod' => $codepas,
                );
                $this->m_tamponcode->del($codepas, $arraytamp);

                $this->property['UPDATE_SUCCESS'] = TRUE;
                return $this->view($ckey, $uid, $g, $sg, $this->property);
            }
            
            
        }

        public function supprimerticketconf($ckey, $codepas, $idtck, $uid, $g, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);          

             $argrepas = array(
                'code_passager' => $codepas,
                'code_ticket' => $idtck,
            );
            $this->m_passager->del($codepas, $idtck, $argrepas);

            $this->property['UPDATE_SUCCESS'] = TRUE;
            return $this->view($ckey, $uid, $g, $sg, $this->property);
        }
            //archivrer les donnees
        public function archivre($ckey, $g)
        {
            $dbenreg = $this->input->post('debutenreg');
            $finenreg = $this->input->post('finenreg');
            $comparch = $this->input->post('archivrecompag');
            $argd = $this->input->post('archivredepargare');
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);

                
                $archdepense = $this->db->query("SELECT * FROM depense d
                    JOIN caisse cs ON d.idcaisse_depens = cs.id_caiss 
                    WHERE d.date_depens BETWEEN '$dbenreg' AND '$finenreg'
                    AND d.compkey_dep = '$comparch'
                    AND cs.gexp_caiss = '$argd'
                    AND d.arret_caisdep = 1
                    AND d.ferme_caisdep = 1
                    AND d.validcptabledep = 1
                    AND d.actif_deps = 0")->result();

                    foreach ($archdepense as $item) {
                        $dpslarray = array(
                            'actif_deps' => 1,
                        );
                        $vald_deparch = $this->m_depense->update($item->id_depense, $dpslarray);
                    }

                    $archdepot = $this->db->query("SELECT * FROM depot dp
                    JOIN caisse cs ON dp.idcaisse_depot = cs.id_caiss 
                    WHERE dp.datedepot BETWEEN '$dbenreg' AND '$finenreg'
                    AND dp.compkey_depo = '$comparch'
                    AND cs.gexp_caiss = '$argd'
                    AND dp.arret_caisdepo = 1
                    AND dp.ferme_caisdepo = 1
                    AND dp.valid_cptabledepo = 1
                    AND dp.actif_depo = 0")->result();

                    foreach ($archdepot as $item1) {
                        $dpolarray = array(
                            'actif_depo' => 1,
                        );
                        $vald_depoarch = $this->m_depot->update($item1->id_depot, $dpolarray);
                    }

                    $archrecette = $this->db->query("SELECT * FROM recette r
                    JOIN caisse cs ON r.idcaisse = cs.id_caiss 
                    WHERE r.date_recet BETWEEN '$dbenreg' AND '$finenreg'
                    AND r.compkey_recet = '$comparch'
                    AND cs.gexp_caiss = '$argd'
                    AND r.arret_caisrecet = 1
                    AND r.ferme_caisrecet = 1
                    AND r.valid_cptablerecet = 1
                    AND r.actif_rect = 0")->result();

                    foreach ($archrecette as $item2) {
                        $recetlarray = array(
                            'actif_rect' => 1,
                        );
                        $vald_recetarch = $this->m_recette->update($item2->id_recette, $recetlarray);
                    }

                    $archvers = $this->db->query("SELECT * FROM versements v
                    JOIN caisse cs ON v.idcaisse_versement = cs.id_caiss 
                    WHERE v.date_versement BETWEEN '$dbenreg' AND '$finenreg'
                    AND v.compkey_vers = '$comparch'
                    AND cs.gexp_caiss = '$argd'
                    AND v.arret_caisvers = 1
                    AND v.ferme_caisvers = 1
                    AND v.valid_cptablevers = 1
                    AND v.actifvers = 0")->result();
                    
                    foreach ($archvers as $item3) {
                        $versemarray = array(
                            'actifvers' => 1,
                        );
                        $vald_versarch = $this->m_versements->update($item3->id_versements, $versemarray);
                    }

                    $archcompte = $this->db->query("SELECT * FROM compte_guichet cg
                    WHERE cg.datearretcompt BETWEEN '$dbenreg' AND '$finenreg'
                    AND cg.comp = '$comparch'
                    AND cg.idsousga = '$argd'
                    AND cg.actifcompt = 0")->result();

                    foreach ($archcompte as $item4) {
                        $guichetarray = array(
                            'actifcompt' => 1,
                        );
                        $vald_comprch = $this->m_comptes_guichet->update($item4->idcpguichet, $guichetarray);
                    }

                    $archprog = $this->db->query("SELECT * FROM programme pr 
                    WHERE pr.date_progr BETWEEN '$dbenreg' AND '$finenreg'
                    AND pr.gareidentif = '$argd'
                    AND pr.actif_prog = 0")->result();

                    foreach ($archprog as $item5) {
                        $progarray = array(
                            'actif_prog' => 1,
                        );
                        $vald_progarch = $this->m_programme->update($item5->code_progr, $progarray);
                    }

                    $archtamp = $this->db->query("SELECT * FROM tamponcode t
                    JOIN passager p ON p.code_passager = t.tamponcod 
                    LEFT JOIN non_passager np ON t.tamponcod = np.code_non_pass
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    WHERE p.datep_create BETWEEN '$dbenreg' AND '$finenreg'
                    AND pr.gareidentif = '$argd'
                    AND p.actif_pas = 0")->result();
                    if($archtamp !== NULL){

                        foreach ($archtamp as $item6) {
                            $tamparray = array(
                                'actif_tamp' => 1,
                            );
                            $vald_tamparch = $this->m_tamponcode->update($item6->tamponcod, $tamparray);
                        }

                        foreach ($archtamp as $item7) {

                            $passarray = array(
                                'actif_pas' => 1,
                            );

                            $vald_passarch = $this->m_passager->update($item7->code_passager, $item7->code_ticket, $passarray);
                            
                        }

                        foreach ($archtamp as $item8) {
                            $nonparray = array(
                                'actif_nonp' => 1,
                            );
                            $vald_nonparch = $this->m_non_passager->update($item8->code_non_pass, $item8->codeticket, $nonparray);
                        }

                        $archtamprep = $this->db->query("SELECT * FROM report rp
                        JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                        JOIN passager p ON p.code_passager = tp.tamponcod 
                        JOIN programme pr ON p.code_pro = pr.code_progr
                        WHERE rp.date BETWEEN '$dbenreg' AND '$finenreg'
                        AND pr.gareidentif = '$argd'
                        AND rp.actifrep = 0")->result();

                        foreach ($archtamprep as $item9) {
                            $tampreparray = array(
                                'actifrep' => 1,
                            );
                            $vald_tampreparch = $this->m_report->update($item9->code_report, $tampreparray);
                        }

                    }
                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        }

        //archivre donnees courrier

        public function archivrecr($ckey, $g)
        {
            $dbenreg = $this->input->post('debutenregcr');
            $finenreg = $this->input->post('finenregcr');
            $comparch = $this->input->post('archivrecompagcr');
            $argd = $this->input->post('archivredepargarecr');
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');

            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->_roleattribut_guard_post_id($this->company->ekey);

                $archcomptecr = $this->db->query("SELECT * FROM compte_courrier cgc
                    JOIN sousgare sg ON cgc.idsousg = sg.idsousgare
                    WHERE cgc.comptdatearret BETWEEN '$dbenreg' AND '$finenreg'
                    AND cgc.compcour = '$comparch'
                    AND sg.gareprinceid = '$argd'
                    AND cgc.compteactif = 0")->result();

                    foreach ($archcomptecr as $item4) {

                        $guichetarraycr = array(
                            'compteactif' => 1,
                        );
                        $vald_comprchcr = $this->m_comptes_courrier->update($item4->idcpcourrier, $guichetarraycr);
                    }

                    $archprogcr = $this->db->query("SELECT * FROM courriers_exp cr 
                    WHERE cr.dateenvoi BETWEEN '$dbenreg' AND '$finenreg'
                    AND cr.departcolis = '$argd'
                    AND cr.actif_cour = 0")->result();

                    foreach ($archprogcr as $item5) {

                        $progarraycr = array(
                            'actif_cour' => 1,
                        );
                        $vald_progarch = $this->m_courrier_expedier->update($item5->courrierexpid, $item5->num_cour , $item5->departcolis, $progarraycr);
                    }

                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
        }
        public function editpdf($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;
            
            $this->layout->view('_tickets/editpdf', $this->property);
        }

        public function print_conf($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->passagersconf = $this->m_passager->passeconfirmead($this->company->ekey, $code_id, $tf, $h);
                    $this->property['item'] = $this->passagersconf;
                  }
                  else{
                        $this->passagersconf = $this->m_passager->passeconfirme($this->company->ekey, $code_id, $tf, $h, $g);
                        $this->property['item'] = $this->passagersconf;
                  }
                    
            $this->layout->view('_tickets/editpdfconf', $this->property);
        }
        
        public function printep_conf($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->passagersconf = $this->m_passager->passeconfirmead($this->company->ekey, $code_id, $tf, $h);
                    if (empty($this->passagersconf)) {
                        $this->passagersconf = $this->m_passager->passeconfirmerad($this->company->ekey, $code_id);
                    }
                    $this->property['item'] = $this->passagersconf;
                  }
                  else{
                        $this->passagersconf = $this->m_passager->passeconfirme($this->company->ekey, $code_id, $tf, $h, $g);
                        if (empty($this->passagersconf)) {
                            $this->passagersconf = $this->m_passager->passeconfirmer($this->company->ekey, $code_id, $g);
                        }
                        $this->property['item'] = $this->passagersconf;
                  }
                    
            $this->layout->view('_tickets/editpdfepconf', $this->property);
        }
        public function editpdftrans($ckey, $code_id, $tf, $h, $co, $lr, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
            $this->property['itemtrans'] = $this->passagerstrans;

            $this->layout->view('_tickets/editpdftrans', $this->property);
        }

        public function editpdftrans2($ckey, $code_id, $tf, $h, $co, $lr, $co1, $lr1, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);

            $this->property['itemtrans'] = $this->passagerstrans;

            $this->passagerstrans2 = $this->m_passager->get($this->company->ekey, $co1, $tf, $lr1);

            $this->property['itemtrans2'] = $this->passagerstrans2;

            $this->layout->view('_tickets/editpdftrans2', $this->property);
        }
        
        public function editpdftrans3($ckey, $code_id, $tf, $h, $co, $lr, $co1, $lr1, $co2, $lr2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
            $this->property['itemtrans'] = $this->passagerstrans;

            $this->passagerstrans2 = $this->m_passager->get($this->company->ekey, $co1, $tf, $lr1);

            $this->property['itemtrans2'] = $this->passagerstrans2;

            $this->passagerstrans3 = $this->m_passager->get($this->company->ekey, $co2, $tf, $lr2);

            $this->property['itemtrans3'] = $this->passagerstrans3;
            $this->layout->view('_tickets/editpdftrans3', $this->property);
        }
        public function editpdfepson($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;
            
            $this->layout->view('_tickets/editpdfepson', $this->property);
        }

        public function editpdfepsontrans($ckey, $code_id, $tf, $h, $co, $lr, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
            $this->property['itemtrans'] = $this->passagerstrans;
            
            $this->layout->view('_tickets/editpdfepsontrans', $this->property);
        }


        public function editpdfepsontrans2($ckey, $code_id, $tf, $h, $co, $lr, $co1, $lr1, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
            $this->property['itemtrans'] = $this->passagerstrans;

            $this->passagerstrans2 = $this->m_passager->get($this->company->ekey, $co1, $tf, $lr1);
            $this->property['itemtrans2'] = $this->passagerstrans2;
            
            $this->layout->view('_tickets/editpdfepsontrans2', $this->property);
        }

        public function editpdfepsontrans3($ckey, $code_id, $tf, $h, $co, $lr, $co1, $lr1, $co2, $lr2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
            $this->property['itemtrans'] = $this->passagerstrans;
            
            $this->passagerstrans2 = $this->m_passager->get($this->company->ekey, $co1, $tf, $lr1);
            $this->property['itemtrans2'] = $this->passagerstrans2;

            $this->passagerstrans3 = $this->m_passager->get($this->company->ekey, $co2, $tf, $lr2);
            $this->property['itemtrans3'] = $this->passagerstrans3;
            $this->layout->view('_tickets/editpdfepsontrans3', $this->property);
        }

        public function epsonalretour($ckey, $code_id, $tf, $cdnp, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
            $this->property['itemar'] = $this->nonpassagers;
            
            $this->layout->view('_tickets/epsonalretour', $this->property);
        }


        public function editpdfar($ckey, $code_id, $tf, $cdnp, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
            $this->property['itemar'] = $this->nonpassagers;
            
            $this->layout->view('_tickets/editpdfar', $this->property);
        }

        public function editpdftransar($ckey, $code_id, $tf, $cdnp, $h, $co, $cdnpr, $lr, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
                $this->property['item'] = $this->passagers;$this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
                $this->property['itemtrans'] = $this->passagerstrans;
                $this->nonpassagerstrans = $this->m_non_passager->getad($this->company->ekey, $cdnpr);
                $this->property['itemartrans'] = $this->nonpassagerstrans;
                $this->layout->view('_tickets/editpdftransar', $this->property);
        }

        public function editpdftransar2($ckey, $code_id, $tf, $cdnp, $h, $co, $cdnpr, $lr, $co1, $cdnpr1, $lr1, $g, $cpus, $idsg)
        {
               $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
                $this->property['item'] = $this->passagers;$this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
                $this->property['itemtrans'] = $this->passagerstrans;

                $this->nonpassagerstrans = $this->m_non_passager->getad($this->company->ekey, $cdnpr);

                $this->property['itemartrans'] = $this->nonpassagerstrans;

                $this->passagerstrans2 = $this->m_passager->get($this->company->ekey, $co1, $tf, $lr1);
                $this->property['itemtrans2'] = $this->passagerstrans2;
                $this->nonpassagerstrans2 = $this->m_non_passager->getad($this->company->ekey, $cdnpr1);
                $this->property['itemartrans2'] = $this->nonpassagerstrans2;
                $this->layout->view('_tickets/editpdftransar2', $this->property);
        }

        public function editpdftransar3($ckey, $code_id, $tf, $cdnp, $h, $co, $cdnpr, $lr, $co1, $cdnpr1, $lr1, $co2, $cdnpr2, $lr2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
                $this->property['item'] = $this->passagers;$this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
                $this->property['itemtrans'] = $this->passagerstrans;

                $this->nonpassagerstrans = $this->m_non_passager->getad($this->company->ekey, $cdnpr);

                $this->property['itemartrans'] = $this->nonpassagerstrans;

                $this->passagerstrans2 = $this->m_passager->get($this->company->ekey, $co1, $tf, $lr1);
                $this->property['itemtrans2'] = $this->passagerstrans2;
                $this->nonpassagerstrans2 = $this->m_non_passager->getad($this->company->ekey, $cdnpr1);
                $this->property['itemartrans2'] = $this->nonpassagerstrans2;

                $this->passagerstrans3 = $this->m_passager->get($this->company->ekey, $co2, $tf, $lr2);
                $this->property['itemtrans3'] = $this->passagerstrans3;
                $this->nonpassagerstrans3 = $this->m_non_passager->getad($this->company->ekey, $cdnpr2);
                $this->property['itemartrans3'] = $this->nonpassagerstrans3;
                $this->layout->view('_tickets/editpdftransar3', $this->property);
        }

        public function editpdfepsontransar($ckey, $code_id, $tf, $cdnp, $h, $co, $cdnpr, $lr, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
                $this->property['item'] = $this->passagers;$this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
                $this->property['itemtrans'] = $this->passagerstrans;
                $this->nonpassagerstrans = $this->m_non_passager->getad($this->company->ekey, $cdnpr);
                $this->property['itemartrans'] = $this->nonpassagerstrans;

            $this->layout->view('_tickets/editpdfepsontransar', $this->property);
        }

        public function editpdfepsontransar2($ckey, $code_id, $tf, $cdnp, $h, $co, $cdnpr, $lr, $co1, $cdnpr1, $lr1, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
                $this->property['item'] = $this->passagers;
                $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
                $this->property['itemtrans'] = $this->passagerstrans;

                $this->nonpassagerstrans = $this->m_non_passager->getad($this->company->ekey, $cdnpr);
                $this->property['itemartrans'] = $this->nonpassagerstrans;

                $this->passagerstrans2 = $this->m_passager->get($this->company->ekey, $co1, $tf, $lr1);
                $this->property['itemtrans2'] = $this->passagerstrans2;
                $this->nonpassagerstrans2 = $this->m_non_passager->getad($this->company->ekey, $cdnpr1);
                $this->property['itemartrans2'] = $this->nonpassagerstrans2;

            $this->layout->view('_tickets/editpdfepsontransar2', $this->property);
        }

        public function editpdfepsontransar3($ckey, $code_id, $tf, $cdnp, $h, $co, $cdnpr, $lr, $co1, $cdnpr1, $lr1, $co2, $cdnpr2, $lr2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
                $this->property['item'] = $this->passagers;$this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->get($this->company->ekey, $co, $tf, $lr);
                $this->property['itemtrans'] = $this->passagerstrans;

                $this->nonpassagerstrans = $this->m_non_passager->getad($this->company->ekey, $cdnpr);
                $this->property['itemartrans'] = $this->nonpassagerstrans;

                $this->passagerstrans2 = $this->m_passager->get($this->company->ekey, $co1, $tf, $lr1);
                $this->property['itemtrans2'] = $this->passagerstrans2;
                $this->nonpassagerstrans2 = $this->m_non_passager->getad($this->company->ekey, $cdnpr1);
                $this->property['itemartrans2'] = $this->nonpassagerstrans2;

                $this->passagerstrans3 = $this->m_passager->get($this->company->ekey, $co2, $tf, $lr2);
                $this->property['itemtrans3'] = $this->passagerstrans3;
                $this->nonpassagerstrans3 = $this->m_non_passager->getad($this->company->ekey, $cdnpr2);
                $this->property['itemartrans3'] = $this->nonpassagerstrans3;
            $this->layout->view('_tickets/editpdfepsontransar3', $this->property);
        }

        public function editprintreport($ckey, $g, $codrep, $tf, $code_id, $h, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                    $this->passagers = $this->m_passager->passereportad($this->company->ekey, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;
            }
            else
            {
                $this->passagers = $this->m_passager->passereport($this->company->ekey, $g, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;
            } 
            $this->layout->view('_tickets/editreport', $this->property);
        }

        public function editprintreportar($ckey, $g, $codrep, $tf, $codenp_id, $code_id, $h, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->passereportad($this->company->ekey, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $codenp_id);
                $this->property['itemar'] = $this->nonpassagers;            }
            else
            {
                $this->passagers = $this->m_passager->passereport($this->company->ekey, $g, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $codenp_id);
                $this->property['itemar'] = $this->nonpassagers;
            } 
            $this->layout->view('_tickets/editreportar', $this->property);
        }

        public function editepsonreport($ckey, $g, $codrep, $tf, $code_id, $h, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->passereportad($this->company->ekey, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;
            }
            else
            {
                $this->passagers = $this->m_passager->passereport($this->company->ekey, $g, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;
            } 
            $this->layout->view('_tickets/editepreport', $this->property);
        }

        public function editepsonreporttr($ckey, $g, $codrep, $tf, $code_id, $h, $cpus, $idsg, $gtr, $idsgtr)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->passereportad($this->company->ekey, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;
            }
            else
            {
                $this->passagers = $this->m_passager->passereport($this->company->ekey, $gtr, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;
            } 
            $this->layout->view('_tickets/editepreport', $this->property);
        }
        public function editepsonreportar($ckey, $g, $codrep, $tf, $codenp_id, $code_id, $h, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                $this->passagers = $this->m_passager->passereportad($this->company->ekey, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $codenp_id);
                $this->property['itemar'] = $this->nonpassagers;            }
            else
            {
                $this->passagers = $this->m_passager->passereport($this->company->ekey, $g, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $codenp_id);
                $this->property['itemar'] = $this->nonpassagers;
            } 
            $this->layout->view('_tickets/editepreportar', $this->property);
        }

        public function editepsonreportartr($ckey, $g, $codrep, $tf, $codenp_id = '', $code_id = null, $h = null, $cpus = null, $idsg = null, $gtr = null, $idsgtr = null)
        {
            // Segment URI vide (ex. //) sauté par CI → paramètres décalés ; renvoyer vers le flux sans non-passager.
            if ($idsgtr === null || $idsgtr === '') {
                $idsgtr = $gtr;
                $gtr = $idsg;
                $idsg = $cpus;
                $cpus = $h;
                $h = $code_id;
                $code_id = $codenp_id;
                return $this->editepsonreporttr($ckey, $g, $codrep, $tf, $code_id, $h, $cpus, $idsg, $gtr, $idsgtr);
            }
            if ($codenp_id === '' || $codenp_id === 'null' || $codenp_id === '_') {
                return $this->editepsonreporttr($ckey, $g, $codrep, $tf, $code_id, $h, $cpus, $idsg, $gtr, $idsgtr);
            }

            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                $this->passagers = $this->m_passager->passereportad($this->company->ekey, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $codenp_id);
                $this->property['itemar'] = $this->nonpassagers;            }
            else
            {
                $this->passagers = $this->m_passager->passereport($this->company->ekey, $gtr, $codrep, $tf, $code_id, $h);

                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $codenp_id);
                $this->property['itemar'] = $this->nonpassagers;
            } 
            $this->layout->view('_tickets/editepreportar', $this->property);
        }
        //fidelite

        public function editpdffi($ckey, $code_id, $h, $hrs, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                               
                   $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                    $this->property['item'] = $this->passagers;
            }else
            {
                 
                 $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;
            }
            
            
            $this->layout->view('_tickets/editpdffi', $this->property);
        }

        public function editpdfepsonfi($ckey, $code_id, $h, $hrs, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                               
                   $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                    $this->property['item'] = $this->passagers;
            }else
            {
                 
                 $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;
            }
            
            $this->layout->view('_tickets/editpdfepsonfi', $this->property);
        }

        public function editpdfarfi($ckey, $code_id, $cdnp, $h, $hrs, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                              
                $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;

            }
            else
            {
                  $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;
            }
            $this->layout->view('_tickets/editpdfarfi', $this->property);
        }

        public function epsonalretourfi($ckey, $code_id, $cdnp, $h, $hrs, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                              
                $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;

            }
            else
            {
                $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;

                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonpassagers;
            }
            
            $this->layout->view('_tickets/epsonalretourfi', $this->property);
        }

        public function editpdftransfi($ckey, $code_id, $h, $co, $lr, $hrs, $hrs1, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
            }
            else
            {
                  $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reduct($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
            }
            $this->layout->view('_tickets/editpdftransfi', $this->property);
        }

        public function editpdfeptransfi($ckey, $code_id, $h, $co, $lr, $hrs, $hrs1, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
            }
            else
            {
                  $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reduct($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
            }
                $this->layout->view('_tickets/editpdfepsontransfi', $this->property);
        }

        public function editpdftransarfi($ckey, $code_id, $cdnp, $h, $co, $cdnpr, $lr, $hrs, $hrs1, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;
                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp, $hrs);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                $this->property['itemtrans'] = $this->passagerstrans;
                $this->nonpassagerstrans = $this->m_non_passager->reduit($this->company->ekey, $cdnpr, $hrs1);
                $this->property['itemartrans'] = $this->nonpassagerstrans;

                $this->layout->view('_tickets/editpdftransarfi', $this->property);
        }
        public function editpdfeptransarfi($ckey, $code_id, $cdnp, $h, $co, $cdnpr, $lr, $hrs, $hrs1, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;
                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp, $hrs);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                $this->property['itemtrans'] = $this->passagerstrans;
                $this->nonpassagerstrans = $this->m_non_passager->reduit($this->company->ekey, $cdnpr, $hrs1);
                $this->property['itemartrans'] = $this->nonpassagerstrans;

                $this->layout->view('_tickets/editpdfepsontransarfi', $this->property);
        }

        public function editpdftransfi2($ckey, $code_id, $h, $co, $lr, $co2, $lr2, $hrs, $hrs1, $hrs2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
                    $this->passagerstrans2 = $this->m_passager->reductad($this->company->ekey, $co2, $lr2, $hrs2);
                    $this->property['itemtrans2'] = $this->passagerstrans2;
            }
            else
            {
                  $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reduct($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
                    $this->passagerstrans2 = $this->m_passager->reduct($this->company->ekey, $co2, $lr2, $hrs2);
                    $this->property['itemtrans2'] = $this->passagerstrans2;
            }
            $this->layout->view('_tickets/editpdftransfi2', $this->property);
        }

        public function editpdftransfi3($ckey, $code_id, $h, $co, $lr, $co2, $lr2, $co3, $lr3, $hrs, $hrs1, $hrs2, $hrs3, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
                    $this->passagerstrans2 = $this->m_passager->reductad($this->company->ekey, $co2, $lr2, $hrs2);
                    $this->property['itemtrans2'] = $this->passagerstrans2;
                    $this->passagerstrans3 = $this->m_passager->reductad($this->company->ekey, $co3, $lr3, $hrs3);
                    $this->property['itemtrans3'] = $this->passagerstrans3;
            }
            else
            {
                  $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reduct($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
                    $this->passagerstrans2 = $this->m_passager->reduct($this->company->ekey, $co2, $lr2, $hrs2);
                    $this->property['itemtrans2'] = $this->passagerstrans2;
                    $this->passagerstrans3 = $this->m_passager->reduct($this->company->ekey, $co3, $lr3, $hrs3);
                    $this->property['itemtrans3'] = $this->passagerstrans3;
            }
            $this->layout->view('_tickets/editpdftransfi3', $this->property);
        }

        public function editeppdftransfi2($ckey, $code_id, $h, $co, $lr, $co2, $lr2, $hrs, $hrs1, $hrs2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
                    $this->passagerstrans2 = $this->m_passager->reductad($this->company->ekey, $co2, $lr2, $hrs2);
                    $this->property['itemtrans2'] = $this->passagerstrans2;
            }
            else
            {
                  $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reduct($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
                    $this->passagerstrans2 = $this->m_passager->reduct($this->company->ekey, $co2, $lr2, $hrs2);
                    $this->property['itemtrans2'] = $this->passagerstrans2;
            }
            $this->layout->view('_tickets/editeppdftransfi2', $this->property);
        }

        public function editeppdftransfi3($ckey, $code_id, $h, $co, $lr, $co2, $lr2, $co3, $lr3, $hrs, $hrs1, $hrs2, $hrs3, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                    $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
                    $this->passagerstrans2 = $this->m_passager->reductad($this->company->ekey, $co2, $lr2, $hrs2);
                    $this->property['itemtrans2'] = $this->passagerstrans2;
                    $this->passagerstrans3 = $this->m_passager->reductad($this->company->ekey, $co3, $lr3, $hrs3);
                    $this->property['itemtrans3'] = $this->passagerstrans3;
            }
            else
            {
                  $this->passagers = $this->m_passager->reduct($this->company->ekey, $code_id, $h, $hrs);
                        $this->property['item'] = $this->passagers;
                    $this->passagerstrans = $this->m_passager->reduct($this->company->ekey, $co, $lr, $hrs1);
                    $this->property['itemtrans'] = $this->passagerstrans;
                    $this->passagerstrans2 = $this->m_passager->reduct($this->company->ekey, $co2, $lr2, $hrs2);
                    $this->property['itemtrans2'] = $this->passagerstrans2;
                    $this->passagerstrans3 = $this->m_passager->reduct($this->company->ekey, $co3, $lr3, $hrs3);
                    $this->property['itemtrans3'] = $this->passagerstrans3;
            }
            $this->layout->view('_tickets/editeppdftransfi3', $this->property);
        }

        public function editpdftransarfi2($ckey, $code_id, $cdnp, $h, $co, $cdnpr, $lr, $co2, $cdnpr2, $lr2, $hrs, $hrs1, $hrs2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;
                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp, $hrs);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                $this->property['itemtrans'] = $this->passagerstrans;
                $this->nonpassagerstrans = $this->m_non_passager->reduit($this->company->ekey, $cdnpr, $hrs1);
                $this->property['itemartrans'] = $this->nonpassagerstrans;
                $this->passagerstrans2 = $this->m_passager->reductad($this->company->ekey, $co2, $lr2, $hrs2);
                $this->property['itemtrans2'] = $this->passagerstrans2;
                $this->nonpassagerstrans2 = $this->m_non_passager->reduit($this->company->ekey, $cdnpr2, $hrs2);
                $this->property['itemartrans2'] = $this->nonpassagerstrans2;
                $this->layout->view('_tickets/editpdftransarfi2', $this->property);
        }

        public function editpdftransarfi3($ckey, $code_id, $cdnp, $h, $co, $cdnpr, $lr, $co2, $cdnpr2, $lr2, $co3, $cdnpr3, $lr3, $hrs, $hrs1, $hrs2, $hrs3, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;
                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp, $hrs);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                $this->property['itemtrans'] = $this->passagerstrans;
                $this->nonpassagerstrans = $this->m_non_passager->reduit($this->company->ekey, $cdnpr, $hrs1);
                $this->property['itemartrans'] = $this->nonpassagerstrans;

                $this->passagerstrans2 = $this->m_passager->reductad($this->company->ekey, $co2, $lr2, $hrs2);
                $this->property['itemtrans2'] = $this->passagerstrans2;
                $this->nonpassagerstrans2 = $this->m_non_passager->reduit($this->company->ekey, $cdnpr2, $hrs2);
                $this->property['itemartrans2'] = $this->nonpassagerstrans2;

                $this->passagerstrans3 = $this->m_passager->reductad($this->company->ekey, $co3, $lr3, $hrs3);
                $this->property['itemtrans3'] = $this->passagerstrans3;
                $this->nonpassagerstrans3 = $this->m_non_passager->reduit($this->company->ekey, $cdnpr3, $hrs3);
                $this->property['itemartrans3'] = $this->nonpassagerstrans3;
                $this->layout->view('_tickets/editpdftransarfi3', $this->property);
        }

        public function editpdfeptransarfi2($ckey, $code_id, $cdnp, $h, $co, $cdnpr, $lr, $co2, $cdnpr2, $lr2, $hrs, $hrs1, $hrs2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;
                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp, $hrs);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                $this->property['itemtrans'] = $this->passagerstrans;
                $this->nonpassagerstrans = $this->m_non_passager->reduit($this->company->ekey, $cdnpr, $hrs1);
                $this->property['itemartrans'] = $this->nonpassagerstrans;
                $this->passagerstrans2 = $this->m_passager->reductad($this->company->ekey, $co2, $lr2, $hrs2);
                $this->property['itemtrans2'] = $this->passagerstrans2;
                $this->nonpassagerstrans2 = $this->m_non_passager->reduit($this->company->ekey, $cdnpr2, $hrs2);
                $this->property['itemartrans2'] = $this->nonpassagerstrans2;
                $this->layout->view('_tickets/editpdfeptransarfi2', $this->property);
        }

        public function editpdfeptransarfi3($ckey, $code_id, $cdnp, $h, $co, $cdnpr, $lr, $co2, $cdnpr2, $lr2, $co3, $cdnpr3, $lr3, $hrs, $hrs1, $hrs2, $hrs3, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                $this->passagers = $this->m_passager->reductad($this->company->ekey, $code_id, $h, $hrs);
                $this->property['item'] = $this->passagers;
                $this->nonpassagers = $this->m_non_passager->reduit($this->company->ekey, $cdnp, $hrs);
                $this->property['itemar'] = $this->nonpassagers;

                $this->passagerstrans = $this->m_passager->reductad($this->company->ekey, $co, $lr, $hrs1);
                $this->property['itemtrans'] = $this->passagerstrans;
                $this->nonpassagerstrans = $this->m_non_passager->reduit($this->company->ekey, $cdnpr, $hrs1);
                $this->property['itemartrans'] = $this->nonpassagerstrans;

                $this->passagerstrans2 = $this->m_passager->reductad($this->company->ekey, $co2, $lr2, $hrs2);
                $this->property['itemtrans2'] = $this->passagerstrans2;
                $this->nonpassagerstrans2 = $this->m_non_passager->reduit($this->company->ekey, $cdnpr2, $hrs2);
                $this->property['itemartrans2'] = $this->nonpassagerstrans2;

                $this->passagerstrans3 = $this->m_passager->reductad($this->company->ekey, $co3, $lr3, $hrs3);
                $this->property['itemtrans3'] = $this->passagerstrans3;
                $this->nonpassagerstrans3 = $this->m_non_passager->reduit($this->company->ekey, $cdnpr3, $hrs3);
                $this->property['itemartrans3'] = $this->nonpassagerstrans3;
                $this->layout->view('_tickets/editpdfeptransarfi3', $this->property);
        }

        public function pdfepsonreduit($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;
            
            $this->layout->view('_tickets/pdfepsonreduit', $this->property);
        }

        public function pdfepsonreduitad($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;
            
            $this->layout->view('_tickets/pdfepsonreduitad', $this->property);
        }
        public function epsonalretourrd($ckey, $code_id, $tf, $cdnp, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->passagers = $this->m_passager->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->passagers;

            $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
            $this->property['itemar'] = $this->nonpassagers;
            
            $this->layout->view('_tickets/epsonalretourrd', $this->property);
        }

        public function editrecus($ckey, $codetp, $cdp, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            $this->recus = $this->m_passager->grecu($this->company->ekey, $codetp);
            $this->property['itemrecu'] = $this->recus;
            
            $this->layout->view('_tickets/editrecus', $this->property);
        }

        public function editrecusar($ckey, $codetp, $cdnp, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            
            $this->recus = $this->m_passager->getrecu($this->company->ekey, $codetp);
            $this->property['itemrecu'] = $this->recus;
            
            $this->layout->view('_tickets/editrecusar', $this->property);
        }


        public function pdfepsonrapport($ckey, $idconnex, $n, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $comp = $this->input->post('_compag');

                $ncomp = $this->m_compagnies->getn($comp);
                $this->property['ncomp'] = $ncomp;
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                $this->property['reponsealler'] = $this->m_passager->rapportaller($this->company->ekey, $idconnex, $comp, $g);

                $this->property['reponseretour'] = $this->m_non_passager->rapportretour($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponserepro'] = $this->m_passager->rapportrep($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponseconf'] = $this->m_passager->rapportconf($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponsealler_rattrapage'] = $this->m_passager->rapportaller_rattrapage($this->company->ekey, $idconnex, $comp, $g);
            
            $this->layout->view('_tickets/pdfepsonrapt', $this->property);
        }

        public function pdfepsonrap($ckey, $idconnex, $n, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $comp = $this->input->post('_compag');

                $ncomp = $this->m_compagnies->getn($comp);
                $this->property['ncomp'] = $ncomp;
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                $this->property['reponsealler'] = $this->m_passager->rapportaller($this->company->ekey, $idconnex, $comp, $g);

                $this->property['reponseretour'] = $this->m_non_passager->rapportretour($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponserepro'] = $this->m_passager->rapportrep($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponseconf'] = $this->m_passager->rapportconf($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponsealler_rattrapage'] = $this->m_passager->rapportaller_rattrapage($this->company->ekey, $idconnex, $comp, $g);
            
            $this->layout->view('_tickets/pdfeprapt', $this->property);
        }

        
        public function pdfepsonrapport2($ckey, $idconnex, $n, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $comp = $this->input->post('_compag');

                $ncomp = $this->m_compagnies->getn($comp);
                $this->property['ncomp'] = $ncomp;
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                $this->property['reponsealler'] = $this->m_passager->rapportaller($this->company->ekey, $idconnex, $comp, $g);

                $this->property['reponseretour'] = $this->m_non_passager->rapportretour($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponserepro'] = $this->m_passager->rapportrep($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponseconf'] = $this->m_passager->rapportconf($this->company->ekey, $idconnex, $comp, $g);
                $this->property['reponsealler_rattrapage'] = $this->m_passager->rapportaller_rattrapage($this->company->ekey, $idconnex, $comp, $g);
            
            $this->layout->view('_tickets/pdfepsonrapt2', $this->property);
        }
        public function pdfepsonrap2($ckey, $idconnex, $n, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $comp = $this->input->post('_compag');

                $ncomp = $this->m_compagnies->getn($comp);
                $this->property['ncomp'] = $ncomp;
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                
                $this->property['reponsebagage'] = $this->m_bagage->rapportbg($this->company->ekey, $idconnex, $comp, $g);
            
            $this->layout->view('_tickets/pdfeprapt2', $this->property);
        }

        public function pdfepsonrapg($ckey, $idconnex, $n, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $comp = $this->input->post('_compag');

                $ncomp = $this->m_compagnies->getn($comp);
                $this->property['ncomp'] = $ncomp;
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                
                $this->property['reponsebagage'] = $this->m_bagage->rapportbg($this->company->ekey, $idconnex, $comp, $g);
            
            $this->layout->view('_tickets/pdfepraptg', $this->property);
        }
        
        public function pdfepsonbag($ckey, $bg_id, $cdbg_id, $lgbg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getesc($this->company->ekey, $g, $bg_id, $cdbg_id, $lgbg_id);
            $this->property['itembag'] = $this->bagages;
            
            $this->layout->view('_tickets/pdfepsonbag', $this->property);
        }

        public function savebagnfact($ckey, $bg_id, $cdbg_id, $lgbg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getnones($this->company->ekey, $g, $bg_id, $cdbg_id, $lgbg_id);
            $this->property['itembag'] = $this->bagages;
            
            $this->layout->view('_tickets/pdfepsonbagnf', $this->property);
        }

        public function pdfepsonbagsuivi($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;
            
            $this->layout->view('_tickets/pdfepsonbagsuivi', $this->property);
        }

        public function pdfepsonbagsuivitrans($ckey, $bg_id, $bg_id2, $g, $g2, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;

            $this->bagages2 = $this->m_bagage->getsuivi($this->company->ekey, $g2, $bg_id2);
            $this->property['itembags2'] = $this->bagages2;
            
            $this->layout->view('_tickets/pdfepsonbagsuivitrans', $this->property);
        }

        public function pdfepsonbagsuivitrans1($ckey, $bg_id, $bg_id2, $bg_id3, $g, $g2, $g3, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;

            $this->bagages2 = $this->m_bagage->getsuivi($this->company->ekey, $g2, $bg_id2);
            $this->property['itembags2'] = $this->bagages2;

            $this->bagages3 = $this->m_bagage->getsuivi($this->company->ekey, $g3, $bg_id3);
            $this->property['itembags3'] = $this->bagages3;
            
            $this->layout->view('_tickets/pdfepsonbagsuivitrans1', $this->property);
        }

        public function pdfepsonbagsuivitrans2($ckey, $bg_id, $bg_id2, $bg_id3, $bg_id4, $g, $g2, $g3, $g4, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;

            $this->bagages2 = $this->m_bagage->getsuivi($this->company->ekey, $g2, $bg_id2);
            $this->property['itembags2'] = $this->bagages2;

            $this->bagages3 = $this->m_bagage->getsuivi($this->company->ekey, $g3, $bg_id3);
            $this->property['itembags3'] = $this->bagages3;
            
            $this->bagages4 = $this->m_bagage->getsuivi($this->company->ekey, $g4, $bg_id4);
            $this->property['itembags4'] = $this->bagages4;
            

            $this->layout->view('_tickets/pdfepsonbagsuivitrans2', $this->property);
        }

        public function pdfepsonbagsuivig($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;
            
            $this->layout->view('_tickets/pdfepsonbagsuivig', $this->property);
        }

        public function pdfepsonbagnfg($ckey, $bg_id, $cdbg_id, $lgbg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getnones($this->company->ekey, $g, $bg_id, $cdbg_id, $lgbg_id);
            $this->property['itembag'] = $this->bagages;
            
            $this->layout->view('_tickets/pdfepsonbagnfg', $this->property);
        }


        public function pdfepsonbaguich($ckey, $bg_id, $cdbg_id, $lgbg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getesc($this->company->ekey, $g, $bg_id, $cdbg_id, $lgbg_id);
            $this->property['itembag'] = $this->bagages;
            
            $this->layout->view('_tickets/pdfepsonbaguich', $this->property);
        }

        public function pdfepsonbagsuivitransg($ckey, $bg_id, $bg_id2, $g, $g2, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;

            $this->bagages2 = $this->m_bagage->getsuivi($this->company->ekey, $g2, $bg_id2);
            $this->property['itembags2'] = $this->bagages2;
            
            $this->layout->view('_tickets/pdfepsonbagsuivitransg', $this->property);
        }

        public function pdfepsonbagsuivitransg1($ckey, $bg_id, $bg_id2, $bg_id3, $g, $g2, $g3, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;

            $this->bagages2 = $this->m_bagage->getsuivi($this->company->ekey, $g2, $bg_id2);
            $this->property['itembags2'] = $this->bagages2;
            
            $this->bagages3 = $this->m_bagage->getsuivi($this->company->ekey, $g3, $bg_id3);
            $this->property['itembags3'] = $this->bagages3;
            $this->layout->view('_tickets/pdfepsonbagsuivitransg1', $this->property);
        }

        public function pdfepsonbagsuivitransg2($ckey, $bg_id, $bg_id2, $bg_id3, $bg_id4, $g, $g2, $g3, $g4, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->getsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;

            $this->bagages2 = $this->m_bagage->getsuivi($this->company->ekey, $g2, $bg_id2);
            $this->property['itembags2'] = $this->bagages2;
            
            $this->bagages3 = $this->m_bagage->getsuivi($this->company->ekey, $g3, $bg_id3);
            $this->property['itembags3'] = $this->bagages3;

            $this->bagages4 = $this->m_bagage->getsuivi($this->company->ekey, $g4, $bg_id4);
            $this->property['itembags4'] = $this->bagages4;

            $this->layout->view('_tickets/pdfepsonbagsuivitransg2', $this->property);
        }
        

        public function editpdfepsonfigr($ckey, $code_id, $h, $hrs, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                              
        
                $this->ordrees = $this->m_ordres->reduct($this->company->ekey, $code_id, $hrs);
                $this->property['item'] = $this->ordrees;
            
            $this->layout->view('_tickets/editpdfepsonfigr', $this->property);
        }
        
        public function epsonalretourfigr($ckey, $code_id, $h, $cdnp, $hrs, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

               $this->ordrees = $this->m_ordres->reduct($this->company->ekey, $code_id, $hrs);
                $this->property['item'] = $this->ordrees;
            
                $this->nonordrees = $this->m_ordres->reduitrt($this->company->ekey, $cdnp);
                $this->property['itemar'] = $this->nonordrees;
            
            $this->layout->view('_tickets/epsonalretourfigr', $this->property);
        }


        public function bagsave($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->sgetuco($this->company->ekey, $g, $bg_id);
            $this->property['itembgs'] = $this->bagages;
            
            $this->layout->view('_tickets/bagsave', $this->property);
        }

        public function bagsaveguich($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->sgetuco($this->company->ekey, $g, $bg_id);
            $this->property['itembag'] = $this->bagages;
            
            $this->layout->view('_tickets/bagsaveguich', $this->property);
        }

        public function bagnfsave($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->sgetnones($this->company->ekey, $g, $bg_id);
            $this->property['itembgs'] = $this->bagages;
            
            $this->layout->view('_tickets/bagnfsave', $this->property);
        }

        public function bagsavenfguich($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->sgetnones($this->company->ekey, $g, $bg_id);
            $this->property['itembag'] = $this->bagages;
            
            $this->layout->view('_tickets/bagnfsaveguich', $this->property);
        }

        public function spdfepsonbagsuivi($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->sgetsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;
            
            $this->layout->view('_tickets/spdfepsonbagsuivi', $this->property);
        }
        
        public function spdfepsonbagsuivig($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->sgetsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;
            
            $this->layout->view('_tickets/spdfepsonbagsuivig', $this->property);
        }

        public function saupdfepsonbagsuivi($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->sgetsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;
            
            $this->layout->view('_tickets/saupdfepsonbagsuivi', $this->property);
        }
        
        public function saupdfepsonbagsuivig($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagages = $this->m_bagage->sgetsuivi($this->company->ekey, $g, $bg_id);
            $this->property['itembags'] = $this->bagages;
            
            $this->layout->view('_tickets/saupdfepsonbagsuivig', $this->property);
        }
        public function listesbagagestpe($ckey)
        {
            $idconnex = $this->input->post('usernameconect');
             
            $g = $this->input->post('gareattribuer');

            $cpus = $this->input->post('usernames');

            $idsg = $this->input->post('sousgareconnect');

            $this->company = $this->m_entreprises->get_key($ckey);
                
            
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                
            $this->layout->view('_tickets/pdfsuivi', $this->property);
        }

        public function listesuivi($ckey, $gd, $sg, $p, $lhr, $datep, $qt, $cpus, $g, $idsg)
        {
                
            $this->company = $this->m_entreprises->get_key($ckey);
            
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                $nam = $this->m_compte_user->cpuseres($cpus);
            
                $this->property['nam'] = $nam;

                $onbord = $this->m_envoibagages->list1($this->company->ekey, $gd, $sg, $p, $lhr, $datep, $qt);
                        
                $this->property['onbord'] = $onbord;
                            
                $this->property['onprogrambordaxe'] = $this->m_bordereaubagage->get($this->company->ekey, $gd, $sg, $p, $datep, $qt); 

            
            $this->layout->view('_tickets/reimpressionsuivi', $this->property);
        }

        public function listesuivi1($ckey, $bg, $op, $p, $cpus, $g, $idsg)
        {
                
            $this->company = $this->m_entreprises->get_key($ckey);
            
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                $nam = $this->m_compte_user->cpuseres($op);
            
                $this->property['nam'] = $nam;

                $this->property['onbord'] = $this->m_envoibagages->list2($this->company->ekey, $p, $idsg);
                        
                
                            
                $onprogrambordaxe = $this->m_bordereaubagage->get2($this->company->ekey, $bg); 

                $this->property['onprogrambordaxe'] = $onprogrambordaxe;
            $this->layout->view('_tickets/reimpressionsuivi', $this->property);
        }

        public function pdfepsonrapportes($ckey, $idconnex, $n, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $comp = $this->input->post('_compag');

                $ncomp = $this->m_compagnies->getn($comp);
                $this->property['ncomp'] = $ncomp;
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                $this->property['reponsealler'] = $this->m_escalclients->rapportaller($this->company->ekey, $idconnex, $comp, $g);

            
            $this->layout->view('_tickets/pdfepescalrapt', $this->property);
        }

        public function pdfepsonescal($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->escalclients = $this->m_escalclients->get($this->company->ekey, $code_id, $tf, $h);
            $this->property['item'] = $this->escalclients;
            
            $this->layout->view('_tickets/pdfepsonescal', $this->property);
        }

        public function pdfepsonbagesc($ckey, $bg_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->bagagesesc = $this->m_bagageesc->get($this->company->ekey, $g, $bg_id);
            $this->property['itemescbag'] = $this->bagagesesc;
            
            $this->layout->view('_tickets/pdfepsonbagesc', $this->property);
        }

        public function pdfepsonrapesc2($ckey, $idconnex, $n, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $comp = $this->input->post('_compag');

                $ncomp = $this->m_compagnies->getn($comp);
                $this->property['ncomp'] = $ncomp;
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

                
                $this->property['reponsebagageesc'] = $this->m_bagageesc->rapportbg($this->company->ekey, $idconnex, $comp, $g);
            
            $this->layout->view('_tickets/pdfepraptesc2', $this->property);
        }
        public function tripassageresc($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
                    $this->property['bus_stop'] = $bus_stop;

                $conex = $this->_roleattribut_guard_bind($uid, $this->company->ekey, $gd);
                $this->property['conex'] = $conex;
            $ddbt = $this->input->post('debutdatees');
            $dfin = $this->input->post('findatees');
            if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){

                $this->property['historiqueses'] = $this->m_escalclients->alldayad($this->company->ekey, $ddbt, $dfin);

            }
            else{

                $this->property['historiqueses'] = $this->m_escalclients->allday($this->company->ekey, $ddbt, $dfin, $gd);
            }

                $this->property['garedeparts'] = $this->m_sousgare->get($this->company->id_entreprise, $gd);
                $this->property['typesclients'] = $this->m_type_client->get();
                return $this->layout->view('_historique/tries_passager', $this->property);
         
        }

        public function epretour($ckey, $cdnp, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

            $this->nonpassagers = $this->m_non_passager->getad($this->company->ekey, $cdnp);
            $this->property['itemar'] = $this->nonpassagers;
            
            $this->layout->view('_tickets/epretour', $this->property);
        }

        /**
         * Ticket confirmation EPSON : get() (vente) puis fallback confirm sans tarif/heure.
         */
        protected function _passager_conf_print_row($ekey, $code, $tf, $h)
        {
            $row = $this->m_passager->get($ekey, $code, $tf, $h);
            if (!empty($row)) {
                return $row;
            }
            $row = $this->m_passager->passeconfirmead($ekey, $code, $tf, $h);
            if (!empty($row)) {
                return $row;
            }
            return $this->m_passager->passeconfirmerad($ekey, $code);
        }

        public function editpdfepsontranscf($ckey, $code_id, $tf, $h, $co, $lr, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->property['item'] = $this->_passager_conf_print_row($this->company->ekey, $code_id, $tf, $h);
            $this->property['itemtrans'] = $this->_passager_conf_print_row($this->company->ekey, $co, $tf, $lr);
            
            $this->layout->view('_tickets/editpdfepsontranscf', $this->property);
        }


        public function editpdfepsontranscf2($ckey, $code_id, $tf, $h, $co, $lr, $co1, $lr1, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->property['item'] = $this->_passager_conf_print_row($this->company->ekey, $code_id, $tf, $h);
            $this->property['itemtrans'] = $this->_passager_conf_print_row($this->company->ekey, $co, $tf, $lr);
            $this->property['itemtrans2'] = $this->_passager_conf_print_row($this->company->ekey, $co1, $tf, $lr1);
            
            $this->layout->view('_tickets/editpdfepsontranscf2', $this->property);
        }

        public function editpdfepsontranscf3($ckey, $code_id, $tf, $h, $co, $lr, $co1, $lr1, $co2, $lr2, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
            $this->property['item'] = $this->_passager_conf_print_row($this->company->ekey, $code_id, $tf, $h);
            $this->property['itemtrans'] = $this->_passager_conf_print_row($this->company->ekey, $co, $tf, $lr);
            $this->property['itemtrans2'] = $this->_passager_conf_print_row($this->company->ekey, $co1, $tf, $lr1);
            $this->property['itemtrans3'] = $this->_passager_conf_print_row($this->company->ekey, $co2, $tf, $lr2);
            $this->layout->view('_tickets/editpdfepsontranscf3', $this->property);
        }

        public function reditpdfepson($ckey, $code_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

            
            $this->property['tritem'] = $this->m_passager->gettr($this->company->ekey, $code_id);
             
            
            $this->layout->view('_tickets/reditpdfepson', $this->property);
        }

        public function repsonalretour($ckey, $code_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

            $this->property['tritem'] = $this->m_passager->gettr($this->company->ekey, $code_id);

            $this->property['rtritem'] = $this->m_non_passager->gettr($this->company->ekey, $code_id);
            
            $this->layout->view('_tickets/repsonalretour', $this->property);
        }

        public function reditpdfepsonfigr($ckey, $code_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;
                              
        
                $this->ordrees = $this->m_ordres->reducttr($this->company->ekey, $code_id);
                $this->property['ritem'] = $this->ordrees;
            
            $this->layout->view('_tickets/reditpdfepsonfigr', $this->property);
        }
        
        public function repsonalretourfigr($ckey, $code_id, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->_roleattribut_guard_bind($cpus, $this->company->ekey, $g);
                $this->property['conex'] = $conex;

               $this->ordrees = $this->m_ordres->reducttr($this->company->ekey, $code_id);
                $this->property['ritem'] = $this->ordrees;
            
                $this->nonordrees = $this->m_ordres->reduitrt($this->company->ekey, $code_id);
                $this->property['ritemar'] = $this->nonordrees;
            
            $this->layout->view('_tickets/repsonalretourfigr', $this->property);
        }
    }
    
    /** End of file: Historique_Passagers.php **/
    /** File location: application/controllers/Historique_Passagers.php **/