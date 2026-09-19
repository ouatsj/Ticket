    <?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Ventescales extends MY_Controller
    {
        public $property = array(
            'title' => 'Ventescales',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );

        public $company;
        public $ventescale;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }

        public function passagerescal($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

                $cid = $this->session->company->ekey;

                $gid = $this->input->post('gareconnectescal');
                $sgid = $this->input->post('sousgareconnectescal');
                $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnectescal', 'userconnectedescal');
                if ($msg = compte_arret_guard_sale('ticket', $iduser, $gid)) {
                    compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                    return;
                }

                $imprimeepson = $this->input->post('epsonescal');
                
                $idcmpt = $this->input->post('compconnectedescal');

                $dateclientesca = mdate("%Y-%m-%d", now());

                if($this->input->post('dateclientescal') === '0000-00-00')
                {
                    $dateclientesca = mdate("%Y-%m-%d", now());

                }else{

                    $dateclientesca = $this->input->post('dateclientescal');
                }

                $usen = substr($this->session->agent->username, 0, 1);

                if($this->input->post('datedepartescal') != NULL AND $this->input->post('heuredeptescal') != NULL AND $this->input->post('tarifattribuerescal') != NULL)
                {
                    
                    if($imprimeepson)
                    {
                        
                            
                           $today = mdate("%Y-%m-%d", now('UTC'));
                            
                            $reg = $this->input->post('gareconnectescal');
                            $tf = $this->input->post('tarifattribuerescal');
                            $rcl = $this->input->post('cprclientescal');
                            $rcp = $this->input->post('cpprclientescal');
                            $qua = $this->input->post('quartconfirmeescal');
                            

                            $lghgid = strpos($this->input->post('heuredeptescal'), '/');
                                $lhgides = substr($this->input->post('heuredeptescal'), 0, $lghgid);
                                $hrgidesc = substr($this->input->post('heuredeptescal'), $lghgid + 1, strlen($this->input->post('heuredeptescal')));

                                $cdegid = strpos($this->input->post('depargareescal'), '/');
                                $lhgid = substr($this->input->post('depargareescal'), 0, $cdegid);
                                $hrgid = substr($this->input->post('depargareescal'), $cdegid + 1, strlen($this->input->post('depargareescal')));

                                $destgid = strpos($this->input->post('arrgareescal'), '/');
                                $destlhgid = substr($this->input->post('arrgareescal'), 0, $destgid);
                                $desthrgid = substr($this->input->post('arrgareescal'), $destgid + 1, strlen($this->input->post('arrgareescal')));


                            if($hrgid != '' AND $this->input->post('prixescal') != NULL )
                            {
                                
                                $passecompt = $this->db->query("SELECT COUNT(idclescal) AS id FROM escalclients es WHERE es.dateescal = '$today'")->row();
                                
                                $dernier = $this->db->query("SELECT es.escalpanier FROM escalclients es
                                JOIN lignes lg ON es.lignintescal = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$desthrgid'
                                AND ex.code_gaexp = '$reg'
                                ORDER BY dateheureescal DESC LIMIT 1")->row();

                             
                                $tampon = mdate("%y%d%m", now('UTC')).($passecompt->id + 1).$reg.$usen.$iduser;

                                if($this->input->post('clientcompescal') != '' AND $rcl === $this->input->post('rclientescal') AND $rcp === $this->input->post('prclientescal'))
                                {
                                

                                        $argup = array(
                                            'nom_client' => $this->input->post('rclientescal'),
                                            'type_client' => $this->input->post('typeescal'),
                                            'prenom_client' => $this->input->post('prclientescal'),
                                            'contact_client' => $this->input->post('rclient_contactescal'),
                                            'num_CNIB' => $this->input->post('cnilientescal'),
                                            'date_delivre' => $dateclientesca,
                                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                                            'lieu_delivre' => $this->input->post('cllieuclescal'),
                                        );

                                        $this->m_client->update($this->input->post('clientcompescal'), $argup);

                                        
                                        $passagerarray = array(
                                            'idclescal' => $tampon,
                                            'iduseescal' => $iduser,
                                            'clientescal ' => $this->input->post('clientcompescal'),
                                            'lignintescal' => $lhgid. '-' .$destlhgid,
                                            'departgescal' => $reg,
                                            'departsgescal' => $hrgid,
                                            'id_lgeheur' => $lhgides,
                                            'quartier_escal' => $this->input->post('quartconfirmeescal'),
                                            'typtarifesc' => $this->input->post('tarifattribuerescal'),
                                            'prixescal' => $this->input->post('prixescal'),
                                            'datedepescal' => $this->input->post('datedepartescal'),
                                            'dateescal' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $escalpass = $this->m_escalclients->create($passagerarray);

                                        if ($dernier == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'A' WHERE idclescal = '$tampon'");
                                        }
                                        else
                                        {
                                            if ($dernier->escalpanier == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'B' WHERE idclescal = '$tampon'");
                                            }
                                            elseif ($dernier->escalpanier == 'B')
                                            
                                            {
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'C' WHERE idclescal = '$tampon'");
                                            
                                            }
                                            elseif ($dernier->escalpanier == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'D' WHERE idclescal = '$tampon'");
                                            }
                                            elseif ($dernier->escalpanier == 'D')
                                            
                                            {
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'E' WHERE idclescal = '$tampon'");
                                            
                                            }

                                            else
                                            {
                                                $this->db->query("UPDATE escalclients SET escalpanier = 'A' WHERE idclescal = '$tampon'");
                                            
                                            }
                                        }
                                        
                                            redirect('Historique_Passagers/pdfepsonescal/' . $this->session->company->ekey . '/' . $tampon.'/'.$tf. '/' . $lhgides.'/'.$gid. '/'.$iduser.'/'.$sgid);
                                }

                                else
                                {
                                    $argup = array(
                                        'nom_client' => $this->input->post('rclientescal'),
                                        'type_client' => 'Adulte',
                                        'prenom_client' => $this->input->post('prclientescal'),
                                        'contact_client' => $this->input->post('rclient_contactescal'),
                                        'num_CNIB' => $this->input->post('cnilientescal'),
                                        'date_delivre' => $dateclientesca,
                                        'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                                        'lieu_delivre' => $this->input->post('cllieuclescal'),
                                    );

                                    $clesc = $this->m_client->create($argup);
                                    
                                    $passagerarray = array(
                                        'idclescal' => $tampon,
                                        'iduseescal' => $iduser,
                                        'clientescal ' => $clesc,
                                        'lignintescal' => $lhgid. '-' .$destlhgid,
                                        'departgescal' => $reg,
                                        'departsgescal' => $hrgid,
                                        'id_lgeheur' => $lhgides,
                                        'quartier_escal' => $this->input->post('quartconfirmeescal'),
                                        'typtarifesc' => $this->input->post('tarifattribuerescal'),
                                        'prixescal' => $this->input->post('prixescal'),
                                        'datedepescal' => $this->input->post('datedepartescal'),
                                        'dateescal' => mdate("%Y-%m-%d", now('UTC')),
                                    );
                                    $escalpass = $this->m_escalclients->create($passagerarray);

                                    if ($dernier == NULL)
                                    {
                                                    
                                        $this->db->query("UPDATE escalclients SET escalpanier = 'A' WHERE idclescal = '$tampon'");
                                    }
                                    else
                                    {
                                        if ($dernier->escalpanier == 'A')
                                        {
                                                        
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'B' WHERE idclescal = '$tampon'");
                                        }
                                        elseif ($dernier->escalpanier == 'B')
                                        
                                        {
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'C' WHERE idclescal = '$tampon'");
                                        
                                        }
                                        elseif ($dernier->escalpanier == 'C')
                                        {
                                                        
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'D' WHERE idclescal = '$tampon'");
                                        }
                                        elseif ($dernier->escalpanier == 'D')
                                        
                                        {
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'E' WHERE idclescal = '$tampon'");
                                        
                                        }

                                        else
                                        {
                                            $this->db->query("UPDATE escalclients SET escalpanier = 'A' WHERE idclescal = '$tampon'");
                                        
                                        }
                                    }
                                        redirect('Historique_Passagers/pdfepsonescal/' . $this->session->company->ekey . '/' . $tampon.'/'.$tf. '/' . $lhgides.'/'.$gid. '/'.$iduser.'/'.$sgid);
                                    
                                }
                                    
                            }
                            
                            else
                            {
                                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                            } 
                        
                    }   
                }
        }

        /**
         * Vente escale libre : destination = itineraire_escales, prix = prix_escale, sans programme.
         */
        public function passagerescal_libre($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            if (!isset($this->m_itineraire_escale)) {
                $this->load->model('Itineraire_escale_model', 'm_itineraire_escale');
            }

            $gid = $this->input->post('gareconnectescal');
            $sgid = $this->input->post('sousgareconnectescal');
            $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnectescal', 'userconnectedescal');
            if ($msg = compte_arret_guard_sale('ticket', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }

            if (!$this->input->post('epsonescal')) {
                $this->session->set_flashdata(
                    'error',
                    'Impression non demandée — cliquez IMPRIMER après avoir rempli le formulaire.'
                );
                redirect('gares/' . $this->session->company->ekey . '/gTc/' . $gid . '/compte/' . $iduser . '/' . $sgid . '/' . mdate("%d/%m/%Y", now('UTC')));
                return;
            }

            $depart_value = trim((string) $this->input->post('escale_depart'));
            $destination_vente = trim((string) $this->input->post('destination_vente'));
            if ($destination_vente === '') {
                // Compat ancien champ
                $legacy = (int) $this->input->post('id_escale_dest');
                if ($legacy > 0) {
                    $destination_vente = 'escale~' . $legacy;
                }
            }

            // Rôle 17 : imposer le départ affecté (session ou BDD) — anti-falsification POST.
            if ($this->session->userdata('agent')
                && (string) $this->session->agent->userole === '17') {
                $forced = null;
                $ctx = $this->session->userdata('role17_escale');
                if (is_array($ctx)
                    && !empty($ctx['value'])
                    && (string) $ctx['gare'] === (string) $gid
                ) {
                    $forced = str_replace('|', '~', trim((string) $ctx['value']));
                } else {
                    if (!isset($this->m_roleattribution)) {
                        $this->load->model('Role_attribution_model', 'm_roleattribution');
                    }
                    $aff = $this->m_roleattribution->get_vente_escale((int) $iduser);
                    if ($aff && !empty($aff['value'])) {
                        $forced = str_replace('|', '~', trim((string) $aff['value']));
                    }
                }
                if ($forced !== null && $forced !== '') {
                    $depart_value = $forced;
                }
            }

            $nom = trim((string) $this->input->post('rclientescal'));
            $prenom = trim((string) $this->input->post('prclientescal'));
            $contact = trim((string) $this->input->post('rclient_contactescal'));

            if ($depart_value === '' || $destination_vente === '' || $nom === '' || $prenom === '' || $contact === '') {
                $this->session->set_flashdata('error', 'Destination et identité client obligatoires.');
                redirect('gares/' . $this->company->ekey . '/gTc/' . $gid . '/compte/' . $iduser . '/' . $sgid . '/' . mdate("%d/%m/%Y", now('UTC')));
                return;
            }

            $destinations = $this->m_itineraire_escale->destinations_vente($depart_value);
            $dest = null;
            foreach ($destinations as $row) {
                $row_val = isset($row->value) ? (string) $row->value : ('escale~' . (int) $row->id_escale);
                if ($row_val === $destination_vente) {
                    $dest = $row;
                    break;
                }
            }
            if (!$dest) {
                $this->session->set_flashdata('error', 'Destination invalide pour cette escale de départ.');
                redirect('gares/' . $this->company->ekey . '/gTc/' . $gid . '/compte/' . $iduser . '/' . $sgid . '/' . mdate("%d/%m/%Y", now('UTC')));
                return;
            }

            $prix = (float) $dest->prix_escale;
            $label_od = (string) $dest->label;
            $id_lignes = (string) $dest->id_lignes;
            $today = mdate("%Y-%m-%d", now('UTC'));
            $now_dt = mdate("%Y-%m-%d %H:%i:%s", now('UTC'));
            $usen = substr($this->session->agent->username, 0, 1);

            $passecompt = $this->db->query(
                "SELECT COUNT(idclescal) AS id FROM escalclients es WHERE es.dateescal = ?",
                array($today)
            )->row();
            $tampon = mdate("%y%d%m", now('UTC')) . ((int) $passecompt->id + 1) . $gid . $usen . $iduser;

            $client_id = trim((string) $this->input->post('clientcompescal'));
            $nom_ref = trim((string) $this->input->post('cprclientescal'));
            $prenom_ref = trim((string) $this->input->post('cpprclientescal'));

            if ($client_id !== '' && $nom_ref === $nom && $prenom_ref === $prenom) {
                $this->m_client->update($client_id, array(
                    'nom_client' => $nom,
                    'prenom_client' => $prenom,
                    'contact_client' => $contact,
                    'type_client' => 'Adulte',
                    'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                ));
            } else {
                $client_id = $this->m_client->create(array(
                    'nom_client' => $nom,
                    'prenom_client' => $prenom,
                    'contact_client' => $contact,
                    'type_client' => 'Adulte',
                    'num_CNIB' => '',
                    'date_delivre' => $today,
                    'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                    'lieu_delivre' => '',
                ));
            }

            $lh = $this->db->query(
                "SELECT lh.id_ligneheure
                 FROM ligne_heure lh
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 WHERE lh.ligne_id = ?
                   AND COALESCE(lh.actif_lh, 1) = 1
                   AND COALESCE(h.h_active, 1) = 1
                 ORDER BY lh.id_ligneheure ASC
                 LIMIT 1",
                array($id_lignes)
            )->row();
            if (!$lh) {
                $this->session->set_flashdata('error', 'Aucun horaire actif sur la ligne parent — impossible d\'enregistrer le ticket.');
                redirect('gares/' . $this->company->ekey . '/gTc/' . $gid . '/compte/' . $iduser . '/' . $sgid . '/' . mdate("%d/%m/%Y", now('UTC')));
                return;
            }
            $id_lgeheur = (int) $lh->id_ligneheure;

            $insert = array(
                'idclescal' => $tampon,
                'iduseescal' => $iduser,
                'clientescal' => $client_id,
                'lignintescal' => $id_lignes,
                'departgescal' => $gid,
                'departsgescal' => $sgid,
                'id_lgeheur' => $id_lgeheur,
                // Préfixe pour router réimp / historique vers le ticket 57x40
                'quartier_escal' => '[LIBRE] ' . $label_od,
                'typtarifesc' => 1,
                'prixescal' => $prix,
                'datedepescal' => $today,
                'dateescal' => $today,
                'escalpanier' => 'A',
                'arrcptescal' => 0,
                'cptarrchgescal' => 0,
            );
            // Colonnes optionnelles selon schéma
            if ($this->db->field_exists('reimpr', 'escalclients')) {
                $insert['reimpr'] = 1;
            }
            $this->m_escalclients->create($insert);

            $check = $this->m_escalclients->get_libre($this->company->ekey, $tampon);
            if (!$check) {
                $this->session->set_flashdata('error', 'Échec enregistrement ticket escale.');
                redirect('gares/' . $this->company->ekey . '/gTc/' . $gid . '/compte/' . $iduser . '/' . $sgid . '/' . mdate("%d/%m/%Y", now('UTC')));
                return;
            }

            if ($this->db->field_exists('dateheureescal', 'escalclients')) {
                $this->db->where('idclescal', $tampon)->update('escalclients', array('dateheureescal' => $now_dt));
            }

            redirect('Historique_Passagers/pdfepsonescal_libre/' . $this->company->ekey . '/' . $tampon . '/' . $gid . '/' . $iduser . '/' . $sgid);
        }

        public function reimpri($ckey, $id, $statutr, $idlh, $gd, $uid, $sg)
        {
            $company = $this->m_entreprises->get_key($ckey);

                    if($statutr == 0){

                        $statsrv = 1;
                    }
                    else
                    {
                        $statsrv = 0;
                    }
                    
                    $upreimpr = array(
                        'reimpr' => $statsrv,
                    );
                    
                    $this->m_escalclients->update($id, $upreimpr);

                    $this->property['UPDATE_SUCCESS'] = TRUE;

            redirect('historique_passagers/tripassageresc/'.$this->session->company->ekey.'/'.$uid.'/'.$gd.'/'.$sg);         
        }

        public function voirreimpri($ckey, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $gd, $sg);
            if (!$bus_stop) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $uid);
            if (!$conex && $this->session->userdata('agent')) {
                $conex = $this->m_compte_user->getusergare(
                    $this->company->ekey,
                    $gd,
                    $this->session->agent->roleattribut
                );
            }
            if (!$conex) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['conex'] = $conex;

            // Venteescale : toujours UI onglets + shell autonome (TPE Chrome 64).
            // Tickets : uniquement ceux repositionnés par le chef (reimpr=1).
            $this->property['reponseallereimp'] = $this->m_escalclients->getrep_escale(
                $this->company->ekey,
                $conex->roleattribut,
                $gd,
                $sg
            );
            if (!isset($this->m_bagageesc)) {
                $this->load->model('Bagageesc_model', 'm_bagageesc');
            }
            if (!isset($this->m_courrier_expedieresc)) {
                $this->load->model('Courriers_expesc_model', 'm_courrier_expedieresc');
            }
            // Bagage / courrier : reçus du jour.
            $this->property['reimpri_bagages'] = $this->m_bagageesc->liste_reimpri_jour(
                $this->company->ekey,
                $conex->roleattribut,
                $gd,
                $sg
            );
            $this->property['reimpri_courriers'] = $this->m_courrier_expedieresc->liste_reimpri_jour(
                $this->company->ekey,
                $conex->roleattribut,
                $gd,
                $sg
            );
            if (!function_exists('role17_forced_escale')) {
                $this->load->helper('role17_context');
            }
            // Contexte léger (évite un inject lourd qui peut planter la page).
            $forced = function_exists('role17_forced_escale')
                ? role17_forced_escale($conex->roleattribut, $gd)
                : null;
            $this->property['escale_depart_label'] = $forced
                ? $forced['label']
                : trim(
                    (!empty($bus_stop->garenom) ? $bus_stop->garenom : '')
                    . (!empty($bus_stop->nomsousgare) ? (' / ' . $bus_stop->nomsousgare) : '')
                );
            $this->property['escale_depart_fixed_admin'] = $forced ? !empty($forced['fixed']) : false;
            $this->property['escale_id_lignes'] = $forced ? $forced['id_lignes'] : '';
            $this->property['role17_mode'] = true;
            // Shell autonome : pas de Beagle / PerfectScrollbar / whoami.
            $this->property['layout_reimpri'] = TRUE;
            $this->property['layout_minimal'] = TRUE;
            $this->property['scripts_layout'] = 'scripts_bundle';
            $this->property['bundle_js'] = array();
            $this->property['bundle_optional_js'] = array();
            $this->property['bundle_datatables'] = false;

            $this->property['pagetitle'] .= "REIMPRESSION• <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->garenom} •&nbsp;{$bus_stop->nomsousgare}</strong>";
            $this->property['title'] = 'Réimpression';

            return $this->layout->view('_tickets/indexreimpri', $this->property);
        }

        public function pdfepsonescalrp($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
            if (!$bus_stop) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $cpus);
            if (!$conex && $this->session->userdata('agent')) {
                $conex = $this->m_compte_user->getusergare(
                    $this->company->ekey,
                    $g,
                    $this->session->agent->roleattribut
                );
            }
            if (!$conex) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['conex'] = $conex;

            $item = $this->m_escalclients->rget($this->company->ekey, $code_id, $tf, $h);
            $libre = false;
            if (!$item) {
                $item = $this->m_escalclients->get_libre($this->company->ekey, $code_id);
                // Réimpression : le chef doit avoir repositionné (reimpr=1).
                if ($item && (int) $item->reimpr !== 1) {
                    $item = null;
                }
                $libre = (bool) $item;
            }
            if (!$item) {
                $item = $this->db->query(
                    "SELECT es.*, cl.nom_client, cl.prenom_client, cl.contact_client,
                            sg.nomsousgare, h.heure, lg.nom_ligne, dest.nom_gadest,
                            c.nom_compagnie, c.logo, ge.nom_gaep
                     FROM escalclients es
                     JOIN client cl ON es.clientescal = cl.id_client
                     LEFT JOIN sousgare sg ON es.departsgescal = sg.idsousgare
                     LEFT JOIN ligne_heure lh ON es.id_lgeheur = lh.id_ligneheure
                     LEFT JOIN heures h ON lh.heure_identif = h.id_heure
                     LEFT JOIN lignes lg ON es.lignintescal = lg.ident_ligne
                     LEFT JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
                     LEFT JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                     LEFT JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                     WHERE BINARY es.idclescal = ?
                     AND es.reimpr = 1
                     LIMIT 1",
                    array($code_id)
                )->row();
                if ($item && isset($item->quartier_escal)
                    && strpos((string) $item->quartier_escal, '[LIBRE]') === 0
                ) {
                    $libre = true;
                }
            }
            if (!$item) {
                $this->session->set_flashdata(
                    'error',
                    'Réimpression refusée : ticket introuvable ou non autorisé par le chef (repositionnement requis).'
                );
                redirect('ventescales/voirreimpri/' . $this->company->ekey . '/'
                    . (int) $conex->roleattribut . '/' . rawurlencode($g) . '/' . (int) $idsg);
                return;
            }

            $userole = !empty($conex->userole)
                ? (string) $conex->userole
                : (string) $this->session->agent->userole;
            // Vendeur : uniquement ses tickets. Admin/superviseur : tickets de la gare.
            if ((string) $userole === '17'
                && (int) $item->iduseescal !== (int) $conex->roleattribut
            ) {
                $this->session->set_flashdata(
                    'error',
                    'Réimpression refusée : ce ticket appartient à un autre opérateur.'
                );
                redirect('ventescales/voirreimpri/' . $this->company->ekey . '/'
                    . (int) $conex->roleattribut . '/' . rawurlencode($g) . '/' . (int) $idsg);
                return;
            }

            // Toujours 57×40 mm (POSPrinter) — libre ou classique.
            if ($libre || (isset($item->quartier_escal) && strpos((string) $item->quartier_escal, '[LIBRE]') === 0)) {
                if (isset($item->quartier_escal)) {
                    $item->quartier_escal = trim(preg_replace('/^\[LIBRE\]\s*/', '', (string) $item->quartier_escal));
                }
            } else {
                $dep = '';
                if (!empty($item->nomsousgare)) {
                    $dep = trim((string) $item->nomsousgare);
                } elseif (!empty($item->nom_gaep)) {
                    $dep = trim((string) $item->nom_gaep);
                }
                $arr = !empty($item->nom_gadest) ? trim((string) $item->nom_gadest) : '';
                $quart = !empty($item->quartier_escal) ? trim((string) $item->quartier_escal) : '';
                if ($dep !== '' && $arr !== '') {
                    $item->quartier_escal = $dep . ' - ' . $arr;
                } elseif ($quart !== '') {
                    $item->quartier_escal = $quart;
                } elseif ($arr !== '') {
                    $item->quartier_escal = $arr;
                }
            }
            if (!isset($item->prixescal) && isset($item->prix)) {
                $item->prixescal = $item->prix;
            }
            if (!isset($item->prixescal)) {
                $item->prixescal = 0;
            }

            // Sort de la file seulement quand on a un ticket affichable (évite blanc + perte d’autorisation).
            $this->m_escalclients->update($item->idclescal, array('reimpr' => 0));

            $this->load->helper(array('ticket_escale_libre_print', 'url_safe', 'ticket_prix'));
            $this->property['item'] = $item;
            $this->property['bus_stop'] = $bus_stop;
            $this->property['conex'] = $conex;
            $this->property['layout_print'] = TRUE;
            if (function_exists('role17_is_agent') && role17_is_agent()) {
                $this->property['role17_mode'] = true;
            }
            $this->layout->view('_tickets/pdfepsonescal_libre', $this->property);
        }

        /**
         * Suppression ticket escale (TPE / historique) — hard delete + solde recalculé.
         */
        public function supprimescal($ckey, $code_id, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $conex = $this->_reimpri_conex($gd, $uid);
            if (!$conex) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }

            $item = $this->db->query(
                "SELECT es.idclescal, es.iduseescal, es.dateescal
                 FROM escalclients es
                 WHERE BINARY es.idclescal = ?
                 LIMIT 1",
                array($code_id)
            )->row();

            if (!$item) {
                $this->session->set_flashdata('error', 'Ticket introuvable — suppression impossible.');
                return $this->_redirect_apres_suppression($conex, $gd, $sg, 'ticket');
            }

            $userole = !empty($conex->userole)
                ? (string) $conex->userole
                : (string) $this->session->agent->userole;
            if ($userole === '17' && (int) $item->iduseescal !== (int) $conex->roleattribut) {
                $this->session->set_flashdata('error', 'Suppression refusée : ticket d’un autre opérateur.');
                return $this->_redirect_apres_suppression($conex, $gd, $sg, 'ticket');
            }

            $this->m_escalclients->del($item->idclescal);
            $this->session->set_flashdata('success', 'Ticket escale supprimé.');
            return $this->_redirect_apres_suppression($conex, $gd, $sg, 'ticket');
        }

        /**
         * Annulation reçu bagage escale (soft : annulebagesc=1).
         */
        public function supprimebagesc($ckey, $id_bag, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $conex = $this->_reimpri_conex($gd, $uid);
            if (!$conex) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }

            if (!isset($this->m_bagageesc)) {
                $this->load->model('Bagageesc_model', 'm_bagageesc');
            }
            $row = $this->db->query(
                "SELECT id_bagageesc, idoperabagageesc
                 FROM bagagesesc WHERE id_bagageesc = ? LIMIT 1",
                array((int) $id_bag)
            )->row();
            if (!$row) {
                $this->session->set_flashdata('error', 'Bagage introuvable.');
                return $this->_redirect_apres_suppression($conex, $gd, $sg, 'bagage');
            }

            $op = isset($row->idoperabagageesc) ? (int) $row->idoperabagageesc : 0;
            $userole = !empty($conex->userole)
                ? (string) $conex->userole
                : (string) $this->session->agent->userole;
            if ($userole === '17' && $op > 0 && $op !== (int) $conex->roleattribut) {
                $this->session->set_flashdata('error', 'Suppression refusée : bagage d’un autre opérateur.');
                return $this->_redirect_apres_suppression($conex, $gd, $sg, 'bagage');
            }

            $this->m_bagageesc->update((int) $row->id_bagageesc, array('annulebagesc' => 1));
            if (function_exists('guichet_totaux_cache_invalidate_from_row')) {
                guichet_totaux_cache_invalidate_from_row(array('iduseescal' => $conex->roleattribut));
            }
            $this->session->set_flashdata('success', 'Reçu bagage annulé.');
            return $this->_redirect_apres_suppression($conex, $gd, $sg, 'bagage');
        }

        /**
         * Annulation courrier escale (soft : actif_couresc=1).
         */
        public function supprimecouresc($ckey, $id_cour, $uid, $gd, $sg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $conex = $this->_reimpri_conex($gd, $uid);
            if (!$conex) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }

            if (!isset($this->m_courrier_expedieresc)) {
                $this->load->model('Courriers_expesc_model', 'm_courrier_expedieresc');
            }
            $row = $this->db->query(
                "SELECT courrierexpidesc, idoperateuresc, num_couresc, departcolisesc
                 FROM courriers_expesc WHERE courrierexpidesc = ? LIMIT 1",
                array((int) $id_cour)
            )->row();
            if (!$row) {
                $this->session->set_flashdata('error', 'Courrier introuvable.');
                return $this->_redirect_apres_suppression($conex, $gd, $sg, 'courrier');
            }

            $userole = !empty($conex->userole)
                ? (string) $conex->userole
                : (string) $this->session->agent->userole;
            if ($userole === '17'
                && (int) $row->idoperateuresc !== (int) $conex->roleattribut
            ) {
                $this->session->set_flashdata('error', 'Suppression refusée : courrier d’un autre opérateur.');
                return $this->_redirect_apres_suppression($conex, $gd, $sg, 'courrier');
            }

            $this->m_courrier_expedieresc->update(
                $row->courrierexpidesc,
                $row->num_couresc,
                $row->departcolisesc,
                array('actif_couresc' => 1)
            );
            if (function_exists('guichet_totaux_cache_invalidate_from_row')) {
                guichet_totaux_cache_invalidate_from_row(array('iduseescal' => $conex->roleattribut));
            }
            $this->session->set_flashdata('success', 'Courrier annulé.');
            return $this->_redirect_apres_suppression($conex, $gd, $sg, 'courrier');
        }

        /** @return object|null */
        private function _reimpri_conex($gd, $uid)
        {
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $gd, $uid);
            if (!$conex && $this->session->userdata('agent')) {
                $conex = $this->m_compte_user->getusergare(
                    $this->company->ekey,
                    $gd,
                    $this->session->agent->roleattribut
                );
            }
            return $conex;
        }

        private function _redirect_apres_suppression($conex, $gd, $sg, $tab = 'ticket')
        {
            $ref = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
            if ($ref !== '' && (
                strpos($ref, 'tripassageresc') !== false
                || strpos($ref, 'voirbagage') !== false
                || strpos($ref, 'courrierescal') !== false
            )) {
                redirect($ref);
                return;
            }

            $userole = !empty($conex->userole)
                ? (string) $conex->userole
                : (string) $this->session->agent->userole;
            if (in_array($userole, array('1', '2'), true) && $tab === 'ticket') {
                redirect(
                    'historique_passagers/tripassageresc/' . $this->company->ekey . '/'
                    . (int) $conex->roleattribut . '/' . rawurlencode($gd) . '/' . (int) $sg
                );
                return;
            }

            $tab = in_array($tab, array('ticket', 'bagage', 'courrier'), true) ? $tab : 'ticket';
            redirect(
                'ventescales/voirreimpri/' . $this->company->ekey . '/'
                . (int) $conex->roleattribut . '/' . rawurlencode($gd) . '/' . (int) $sg
                . '?tab=' . $tab
            );
        }
    }