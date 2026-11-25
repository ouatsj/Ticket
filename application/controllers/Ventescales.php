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
            $this->load->helper('role17_context');
            $depart_code = role17_code_gaexp_lieu($depart_value);
            if ($depart_code === '') {
                $depart_code = $gid;
            }
            $tampon = mdate("%y%d%m", now('UTC')) . ((int) $passecompt->id + 1) . $depart_code . $usen . $iduser;

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
                'departgescal' => $depart_code,
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
            // reimpr=0 : le chef doit autoriser via TRI PASSAGER ESCAL (crayon/redo).
            // Avant : insert à 1 → tous les tickets apparaissaient en réimp, et le
            // bascule chef les retirait au lieu de les envoyer au vendeur.
            if ($this->db->field_exists('reimpr', 'escalclients')) {
                $insert['reimpr'] = 0;
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
            $this->m_entreprises->get_key($ckey);
            // 0 → 1 : envoyer au vendeur pour réimpression.
            // 1 → 0 : retirer l’autorisation.
            $statsrv = ((int) $statutr === 1) ? 0 : 1;
            if ($this->db->field_exists('reimpr', 'escalclients')) {
                $this->m_escalclients->update($id, array('reimpr' => $statsrv));
            }
            if ($statsrv === 1) {
                $this->session->set_flashdata(
                    'sale_success',
                    'Ticket envoyé au vendeur escale pour réimpression.'
                );
            } else {
                $this->session->set_flashdata(
                    'sale_success',
                    'Autorisation de réimpression annulée pour ce ticket.'
                );
            }
            redirect(
                'historique_passagers/tripassageresc/'
                . $this->session->company->ekey . '/' . $uid . '/' . $gd . '/' . $sg
            );
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

            $conex = $this->_reimpri_conex($gd, $uid);
            if (!$conex) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['conex'] = $conex;

            // Rediriger vers le Guichet embarqué (?reimpri=1) : la page autonome
            // s’affichait blanche / lente chez admin et TPE.
            $uid = (int) $conex->roleattribut;
            $hub = !empty($conex->guser) ? (string) $conex->guser : (string) $gd;
            $hubSg = (int) $sg;
            $last = $this->db->query(
                "SELECT departsgescal AS sg FROM escalclients
                 WHERE iduseescal = ? ORDER BY idclescal DESC LIMIT 1",
                array($uid)
            )->row();
            if ($last && (int) $last->sg > 0) {
                $hubSg = (int) $last->sg;
            }
            $tab = $this->input->get('tab');
            $q = array('reimpri' => '1');
            if (in_array($tab, array('ticket', 'bagage', 'courrier'), true)) {
                $q['reimpri_tab'] = $tab;
            }
            // Aperçu admin : conserver le contexte escale du referer.
            $ref = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
            if ($ref !== '' && strpos($ref, 'escale_profil=17') !== false) {
                $parts = parse_url($ref);
                if (!empty($parts['query'])) {
                    parse_str($parts['query'], $rq);
                    foreach (array('escale', 'escale_nom', 'escale_ops', 'escale_profil') as $k) {
                        if (!empty($rq[$k])) {
                            $q[$k] = $rq[$k];
                        }
                    }
                }
                // Compte chef dans le referer si présent.
                if (preg_match('#/compte/(\d+)/(\d+)/#', $ref, $m)) {
                    redirect(
                        'gares/' . $this->company->ekey . '/gTc/' . $hub
                        . '/compte/' . (int) $m[1] . '/' . (int) $m[2]
                        . '/' . mdate('%d/%m/%Y', now('UTC'))
                        . '?' . http_build_query($q)
                    );
                    return;
                }
            }
            redirect(
                'gares/' . $this->company->ekey . '/gTc/' . $hub
                . '/compte/' . $uid . '/' . $hubSg
                . '/' . mdate('%d/%m/%Y', now('UTC'))
                . '?' . http_build_query($q)
            );
        }

        public function pdfepsonescalrp($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            /* Beacon depuis aperçu Guichet : marque reimpr=0, pas de HTML. */
            if ((string) $this->input->get('consume') === '1') {
                if ($this->session->userdata('agent')
                    && $this->db->field_exists('reimpr', 'escalclients')
                ) {
                    $this->db->where('idclescal', $code_id)
                        ->update('escalclients', array('reimpr' => 0));
                }
                $this->output
                    ->set_status_header(204)
                    ->set_header('Cache-Control: no-store')
                    ->set_output('');
                return;
            }

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
            if (!$bus_stop) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['bus_stop'] = $bus_stop;

            $conex = $this->_reimpri_conex($g, $cpus);
            if (!$conex) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['conex'] = $conex;

            /* Toujours afficher l’aperçu dans le Guichet (évite page blanche TPE). */
            if ((string) $this->input->get('embed') !== '1') {
                $hub = !empty($conex->guser) ? (string) $conex->guser : (string) $g;
                $sg_ret = !empty($conex->idsousgare) ? (int) $conex->idsousgare : (int) $idsg;
                $ra_ret = !empty($conex->roleattribut) ? (int) $conex->roleattribut : (int) $cpus;
                $q = array(
                    'reimpri' => '1',
                    'reimpri_tab' => 'ticket',
                    'preview' => (string) $code_id,
                );
                $ref = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
                if ($ref !== '' && strpos($ref, 'escale_profil=17') !== false) {
                    $parts = parse_url($ref);
                    if (!empty($parts['query'])) {
                        parse_str($parts['query'], $rq);
                        foreach (array('escale', 'escale_nom', 'escale_ops', 'escale_profil') as $k) {
                            if (!empty($rq[$k])) {
                                $q[$k] = $rq[$k];
                            }
                        }
                    }
                    if (preg_match('#/compte/(\d+)/(\d+)/#', $ref, $m)) {
                        $ra_ret = (int) $m[1];
                        $sg_ret = (int) $m[2];
                    }
                    if (preg_match('#/gTc/([^/]+)/#', $ref, $m)) {
                        $hub = (string) $m[1];
                    }
                }
                redirect(
                    'gares/' . $this->company->ekey . '/gTc/' . $hub
                    . '/compte/' . $ra_ret . '/' . $sg_ret
                    . '/' . mdate('%d/%m/%Y', now('UTC'))
                    . '?' . http_build_query($q)
                );
                return;
            }

            // Ne pas exiger reimpr=1 ici : Chrome TPE refait un 2e GET après le 1er
            // (qui a déjà mis reimpr=0) → 307 « introuvable » et page d’impression perdue.
            $item = $this->m_escalclients->rget($this->company->ekey, $code_id, $tf, $h);
            $libre = false;
            if (!$item) {
                $item = $this->m_escalclients->get_libre($this->company->ekey, $code_id);
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
                    'Réimpression refusée : ticket introuvable.'
                );
                return $this->_redirect_reimpri_guichet($conex, $g, $idsg);
            }

            $userole = !empty($conex->userole)
                ? (string) $conex->userole
                : (string) $this->session->agent->userole;
            $is_supervisor = function_exists('roleattribut_guard_is_supervisor')
                && roleattribut_guard_is_supervisor();
            // Vendeur : uniquement ses tickets. Admin/superviseur : OK.
            if ((string) $userole === '17'
                && !$is_supervisor
                && (int) $item->iduseescal !== (int) $conex->roleattribut
            ) {
                $this->session->set_flashdata(
                    'error',
                    'Réimpression refusée : ce ticket appartient à un autre opérateur.'
                );
                return $this->_redirect_reimpri_guichet($conex, $g, $idsg);
            }

            // Toujours 57×40 mm (POSPrinter) — libre ou classique.
            if ($libre || (isset($item->quartier_escal) && strpos((string) $item->quartier_escal, '[LIBRE]') === 0)) {
                if (isset($item->quartier_escal)) {
                    $item->quartier_escal = trim(preg_replace('/^\[LIBRE\]\s*/', '', (string) $item->quartier_escal));
                }
            } else {
                $dep = '';
                if (!empty($item->nom_escale)) {
                    $dep = trim((string) $item->nom_escale);
                } elseif (!empty($item->nomsousgare)) {
                    $dep = trim((string) $item->nomsousgare);
                } elseif (!empty($item->nom_gaep)) {
                    $dep = trim((string) $item->nom_gaep);
                }
                $arr = '';
                if (!empty($item->nom_dest_ticket)) {
                    $arr = trim((string) $item->nom_dest_ticket);
                } elseif (!empty($item->nom_ligne_od) && strpos((string) $item->nom_ligne_od, '-') !== false) {
                    $p = explode('-', (string) $item->nom_ligne_od, 2);
                    $arr = isset($p[1]) ? trim($p[1]) : '';
                    if ($dep === '' && !empty($p[0])) {
                        $dep = trim($p[0]);
                    }
                } elseif (!empty($item->nom_gadest)) {
                    $arr = trim((string) $item->nom_gadest);
                }
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

            // Quitte la file réimp (idempotent si déjà 0 — 2e GET TPE).
            if ((int) $item->reimpr === 1) {
                $this->m_escalclients->update($item->idclescal, array('reimpr' => 0));
                $item->reimpr = 0;
            }

            $this->load->helper(array('ticket_escale_libre_print', 'url_safe', 'ticket_prix', 'retour'));
            $this->property['item'] = $item;
            $this->property['bus_stop'] = $bus_stop;
            $this->property['conex'] = $conex;
            $this->property['layout_print'] = TRUE;
            // Toujours mode ops : Accueil après print = Guichet hub (pas compte//).
            $this->property['role17_mode'] = true;
            // Réimp : aperçu + bouton (pas d’auto-print / overlay → page blanche TPE).
            $this->property['print_manual'] = true;
            $hub = !empty($conex->guser) ? (string) $conex->guser : (string) $g;
            $sg_ret = !empty($conex->idsousgare) ? (int) $conex->idsousgare : (int) $idsg;
            $this->property['print_retour_url'] = site_url(
                'gares/' . $this->company->ekey . '/gTc/' . $hub
                . '/compte/' . (int) $conex->roleattribut . '/' . $sg_ret
                . '/' . mdate('%d/%m/%Y', now('UTC'))
                . '?reimpri=1'
            );
            $this->layout->view('_tickets/pdfepsonescal_libre', $this->property);
        }

        /**
         * Retour Guichet réimp après échec print (évite 404 / page autonome).
         */
        private function _redirect_reimpri_guichet($conex, $gd, $sg)
        {
            $uid = ($conex && !empty($conex->roleattribut)) ? (int) $conex->roleattribut : 0;
            $hub = ($conex && !empty($conex->guser)) ? (string) $conex->guser : (string) $gd;
            $q = array('reimpri' => '1');
            $ref = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
            if ($ref !== '' && strpos($ref, 'escale_profil=17') !== false) {
                $parts = parse_url($ref);
                if (!empty($parts['query'])) {
                    parse_str($parts['query'], $rq);
                    foreach (array('escale', 'escale_nom', 'escale_ops', 'escale_profil') as $k) {
                        if (!empty($rq[$k])) {
                            $q[$k] = $rq[$k];
                        }
                    }
                }
                if (preg_match('#/compte/(\d+)/(\d+)/#', $ref, $m)) {
                    redirect(
                        'gares/' . $this->company->ekey . '/gTc/' . $hub
                        . '/compte/' . (int) $m[1] . '/' . (int) $m[2]
                        . '/' . mdate('%d/%m/%Y', now('UTC'))
                        . '?' . http_build_query($q)
                    );
                    return;
                }
            }
            redirect(
                'gares/' . $this->company->ekey . '/gTc/' . $hub
                . '/compte/' . $uid . '/' . (int) $sg
                . '/' . mdate('%d/%m/%Y', now('UTC'))
                . '?' . http_build_query($q)
            );
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

        /**
         * Opérateur réimp / aperçu escale.
         * Login souvent sur le hub (OUA1) alors que le lieu vente est KOM24 :
         * getusergare(KOM24, 196) échoue → 307. On retombe sur la fiche
         * roleattribut sans exiger ul.guser = gare du lieu.
         *
         * @return object|null
         */
        private function _reimpri_conex($gd, $uid)
        {
            $uid = (int) $uid;
            $ekey = $this->company->ekey;
            $conex = null;
            if ($uid > 0) {
                $conex = $this->m_compte_user->getusergare($ekey, $gd, $uid);
            }
            if ((!$conex || empty($conex->roleattribut)) && $uid > 0) {
                $conex = $this->db->query(
                    "SELECT ar.*, ul.*, cu.*, u.*, r.*, g.*
                     FROM attributions_role ar
                     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                     JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                     JOIN utilisateurs u ON cu.userlog_id = u.uid
                     JOIN user_roles r ON ar.userole = r.id_rols
                     JOIN gares g ON ul.guser = g.idengare
                     JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                     JOIN entreprise e ON c.id_entrep = e.id_entreprise
                     WHERE e.ekey = ?
                     AND ar.roleattribut = ?
                     AND ar.activer_role = 0
                     LIMIT 1",
                    array($ekey, $uid)
                )->row();
            }
            if ((!$conex || empty($conex->roleattribut))
                && $this->session->userdata('agent')
                && !empty($this->session->agent->roleattribut)
            ) {
                $self = (int) $this->session->agent->roleattribut;
                $conex = $this->m_compte_user->getusergare($ekey, $gd, $self);
                if ((!$conex || empty($conex->roleattribut)) && $self > 0) {
                    $conex = $this->db->query(
                        "SELECT ar.*, ul.*, cu.*, u.*, r.*, g.*
                         FROM attributions_role ar
                         JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                         JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                         JOIN utilisateurs u ON cu.userlog_id = u.uid
                         JOIN user_roles r ON ar.userole = r.id_rols
                         JOIN gares g ON ul.guser = g.idengare
                         JOIN compagnies c ON g.compagniegare = c.cle_compagnie
                         JOIN entreprise e ON c.id_entrep = e.id_entreprise
                         WHERE e.ekey = ?
                         AND ar.roleattribut = ?
                         AND ar.activer_role = 0
                         LIMIT 1",
                        array($ekey, $self)
                    )->row();
                }
            }
            // Aperçu admin : uid URL suffit pour afficher la page.
            if ((!$conex || empty($conex->roleattribut)) && $uid > 0
                && function_exists('roleattribut_guard_is_supervisor')
                && roleattribut_guard_is_supervisor()
            ) {
                return (object) array(
                    'roleattribut' => $uid,
                    'userole' => '17',
                );
            }
            return ($conex && !empty($conex->roleattribut)) ? $conex : null;
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