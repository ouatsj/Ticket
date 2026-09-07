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

            // Admin / superviseur : toutes les réimpressions de la sous-gare (vendeurs inclus).
            $userole = !empty($conex->userole)
                ? (string) $conex->userole
                : (string) $this->session->agent->userole;
            $scope_gare = in_array($userole, array('1', '2'), true);

            $this->property['reponseallereimp'] = $this->m_escalclients->getrep(
                $this->company->ekey,
                $conex->roleattribut,
                $gd,
                $sg,
                $scope_gare
            );

            $this->property['pagetitle'] .= "REIMPRESSION TICKET• <strong>{$this->company->nom_entreprise}•&nbsp;{$bus_stop->garenom} •&nbsp;{$bus_stop->nomsousgare}</strong>";

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
                $libre = (bool) $item;
            }
            if (!$item) {
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
                redirect('ventescales/voirreimpri/' . $this->company->ekey . '/'
                    . (int) $conex->roleattribut . '/' . rawurlencode($g) . '/' . (int) $idsg);
                return;
            }

            // Une fois chargé pour impression → sort de la file (disparaît de VOIR REIMPRESSION).
            $this->m_escalclients->update($item->idclescal, array('reimpr' => 0));

            $this->property['item'] = $item;
            if ($libre || (isset($item->quartier_escal) && strpos((string) $item->quartier_escal, '[LIBRE]') === 0)) {
                if (isset($item->quartier_escal)) {
                    $item->quartier_escal = trim(preg_replace('/^\[LIBRE\]\s*/', '', (string) $item->quartier_escal));
                    $this->property['item'] = $item;
                }
                // Réimp libre → même PDF 57×40 que la vente
                redirect(
                    'Historique_Passagers/pdfepsonescal_libre/'
                    . $this->company->ekey . '/'
                    . $item->idclescal . '/'
                    . $g . '/'
                    . $cpus . '/'
                    . $idsg
                );
                return;
            }
            $this->layout->view('_tickets/pdfepsonescalrp', $this->property);
        }
    }