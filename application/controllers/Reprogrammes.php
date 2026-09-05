<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Reprogrammes extends MY_Controller
    {
        public $property = array(
            'title' => 'Reprogrammation',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        public $reprogramme;
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }

        /**
         * Admin + chef guichet : reprogrammation depuis n’importe quelle gare.
         */
        protected function _reprog_roles_any_gare()
        {
            $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
            return in_array($role, array('1', '2', '5', '15'), true);
        }

        /**
         * Gare session (code_gaexp typique agent.guser).
         */
        protected function _reprog_session_gare_code()
        {
            if (isset($this->session->agent->guser) && trim((string) $this->session->agent->guser) !== '') {
                return trim((string) $this->session->agent->guser);
            }
            $post = $this->input->post('gareconnect');
            return ($post !== false && $post !== null) ? trim((string) $post) : '';
        }

        /**
         * Codes gare liés à la vente / départ du ticket (pour contrôle d’accès).
         *
         * @param object $row
         * @return string[]
         */
        protected function _reprog_ticket_vente_gare_codes($row)
        {
            $codes = array();
            if (!empty($row->gareidentif)) {
                $codes[] = trim((string) $row->gareidentif);
            }
            if (!empty($row->gaexp_lg)) {
                $codes[] = trim((string) $row->gaexp_lg);
            }
            if (!empty($row->departclient_idgare)) {
                $sg = $this->db->query(
                    "SELECT gareprinceid FROM sousgare WHERE idsousgare = ? LIMIT 1",
                    array((int) $row->departclient_idgare)
                )->row();
                if ($sg && !empty($sg->gareprinceid)) {
                    $codes[] = trim((string) $sg->gareprinceid);
                }
            }
            $out = array();
            foreach ($codes as $c) {
                if ($c !== '' && !in_array($c, $out, true)) {
                    $out[] = $c;
                }
            }
            return $out;
        }

        /**
         * Accès reprogrammation ticket.
         * Temporaire : toutes gares / tous rôles (restriction gare de vente désactivée).
         *
         * @param object $row
         * @param string|null $sessionGareOverride ex. gareconnect POST
         * @return bool
         */
        protected function _reprog_may_reprog_ticket($row, $sessionGareOverride = null)
        {
            return (bool) $row;
        }

        /**
         * Garde commit gare — temporairement désactivée (toutes gares autorisées).
         */
        protected function _reprog_guard_gare_commit_or_refuse($gidc, $iduser, $sgid)
        {
            return true;
        }

        /**
         * Pré-contrôle siège (stock partagé, bloqués, quota).
         *
         * @param string $code_pro
         * @param int|string $num_siege
         * @return object|null
         */
        protected function _sale_siege_occupe_legacy($code_pro, $num_siege)
        {
            if (!isset($this->sale_svc)) {
                $this->load->library('sale_passager_service', null, 'sale_svc');
            }
            return $this->sale_svc->occupe_legacy_row($code_pro, $num_siege);
        }

        protected function _sale_sieges_sont_libres(array $pairs)
        {
            if (!isset($this->sale_svc)) {
                $this->load->library('sale_passager_service', null, 'sale_svc');
            }
            return $this->sale_svc->sieges_sont_vendables($pairs, array('allow_tampon' => true));
        }

        protected function _sale_siegepassager_json($code_pro, $num_siege)
        {
            if (!isset($this->sale_svc)) {
                $this->load->library('sale_passager_service', null, 'sale_svc');
            }
            return $this->sale_svc->siegepassager_payload($code_pro, $num_siege);
        }

        /**
         * True si le ticket a déjà été reprogrammé (report actif ou statut_reprog).
         *
         * @param string $tamponcod
         * @param string|null $code_passager
         * @param string|null $code_ticket
         * @return bool
         */
        protected function _reprog_deja_effectuee($tamponcod, $code_passager = null, $code_ticket = null)
        {
            $tamp = trim((string) $tamponcod);
            if ($tamp !== '') {
                $row = $this->db->query(
                    "SELECT COUNT(*) AS n FROM report
                     WHERE BINARY code_tick_tamp = " . $this->db->escape($tamp) . "
                     AND actifrep = 0"
                )->row();
                if ($row && (int) $row->n > 0) {
                    return true;
                }
                // Tampon = code_passager : bloquer aussi si statut déjà repor (jambes correspondance).
                $pasT = $this->db->query(
                    "SELECT statut_reprog FROM passager
                     WHERE code_passager = " . $this->db->escape($tamp) . "
                     LIMIT 1"
                )->row();
                if ($pasT && isset($pasT->statut_reprog) && (string) $pasT->statut_reprog === 'repor') {
                    return true;
                }
            }

            $cdpa = trim((string) $code_passager);
            $cdpt = trim((string) $code_ticket);
            if ($cdpa !== '' && $cdpt !== '') {
                $pas = $this->db->query(
                    "SELECT statut_reprog FROM passager
                     WHERE code_passager = " . $this->db->escape($cdpa) . "
                     AND BINARY code_ticket = " . $this->db->escape($cdpt) . "
                     LIMIT 1"
                )->row();
                if ($pas && isset($pas->statut_reprog) && (string) $pas->statut_reprog === 'repor') {
                    return true;
                }
            } elseif ($cdpa !== '') {
                $pas = $this->db->query(
                    "SELECT statut_reprog FROM passager
                     WHERE code_passager = " . $this->db->escape($cdpa) . "
                     LIMIT 1"
                )->row();
                if ($pas && isset($pas->statut_reprog) && (string) $pas->statut_reprog === 'repor') {
                    return true;
                }
            }

            return false;
        }

        /**
         * Refuse une 2ᵉ reprogrammation et renvoie à l’accueil sous-gare.
         */
        protected function _reprog_refuse_redirect($gidc, $iduser, $sgid)
        {
            if (isset($this->session) && method_exists($this->session, 'set_flashdata')) {
                $this->session->set_flashdata(
                    'reprog_error',
                    'Ce ticket a déjà été reprogrammé (une seule reprogrammation autorisée).'
                );
            }
            redirect(
                'gares/' . $this->session->company->ekey
                . '/gTc/' . $gidc
                . '/compte/' . $iduser
                . '/' . $sgid
                . '/' . mdate('%d/%m/%Y', now('UTC'))
            );
        }

        /**
         * Parse les segments transit postés par le modal unifié.
         * @return array|null|false null=pas multi, false=incomplet, array=OK
         */
        protected function _reprog_parse_multiseg()
        {
            $mode = strtolower(trim((string) $this->input->post('reprog_mode')));
            $n = (int) $this->input->post('reprog_nbr_seg');
            if ($mode !== 'transit' || $n < 2) {
                return null;
            }
            if ($n > 4) {
                $n = 4;
            }
            $segs = array();
            for ($i = 0; $i < $n; $i++) {
                $prog = trim((string) $this->input->post('reprog_seg_prog_' . $i));
                $siege = trim((string) $this->input->post('reprog_seg_siege_' . $i));
                if ($prog === '' || $siege === '' || strpos($prog, '/') === false) {
                    return false;
                }
                $parts = explode('/', $prog);
                $segs[] = array(
                    'code_progr' => isset($parts[0]) ? trim($parts[0]) : '',
                    'id_ligneheure' => isset($parts[1]) ? trim($parts[1]) : '',
                    'typetarif' => (isset($parts[2]) && $parts[2] !== '') ? trim($parts[2]) : '1',
                    'siege' => $siege,
                    'compaga' => trim((string) $this->input->post('reprog_seg_compaga_' . $i)),
                    'cat' => trim((string) $this->input->post('reprog_seg_cat_' . $i)),
                    'prix' => trim((string) $this->input->post('reprog_seg_prix_' . $i)),
                );
                if ($segs[$i]['code_progr'] === '') {
                    return false;
                }
            }
            return $segs;
        }

        /**
         * Billets nouvellement émis au report : hors encaissement agent (CA / arrêt).
         * 0 = à arrêter, 1 = arrêté, 2 = report gratuit (jamais facturé à l’agent).
         */
        const REPROG_STATUTVENTE_HORS_CA = 2;

        /**
         * Ticket transit N codes : invalide les jambes d’origine 2..N (POST).
         */
        protected function _reprog_invalidate_transit_leg2_if_needed()
        {
            if ((string) $this->input->post('reprog_is_transit_ticket') !== '1') {
                return;
            }
            $nOrig = (int) $this->input->post('reprog_nbr_jambes_origine');
            if ($nOrig < 2) {
                $nOrig = 2;
            }
            if ($nOrig > 4) {
                $nOrig = 4;
            }
            for ($i = 2; $i <= $nOrig; $i++) {
                $suffix = ($i === 2) ? '2' : (string) $i;
                $cdpa = $this->input->post('passeridtransit' . $suffix);
                $cdpt = $this->input->post('codeclienttransit' . $suffix);
                if (!$cdpa || !$cdpt) {
                    continue;
                }
                $this->m_passager->update($cdpa, $cdpt, array(
                    'num_siege_categorie' => null,
                    'actif_pas' => 1,
                    'statut_reprog' => 'repor',
                ));
                $tamp = $this->input->post('codeticketsclienttransit' . $suffix);
                if ($tamp) {
                    $this->m_tamponcode->update($tamp, array('actif_tamp' => 1));
                }
            }
        }

        /**
         * Résumé d’une jambe pour détection transit (lookup).
         */
        protected function _reprog_jambe_summary($row)
        {
            if (!$row || !is_object($row)) {
                return null;
            }
            $dest = '';
            if (function_exists('ticket_destination_label')) {
                $dest = ticket_destination_label($row, isset($row->gadest_lg) ? $row->gadest_lg : '');
            } else {
                $dest = !empty($row->nom_dest_vente) ? $row->nom_dest_vente
                    : (isset($row->gadest_lg) ? $row->gadest_lg : '');
            }
            return array(
                'code_ticket' => isset($row->code_ticket) ? (string) $row->code_ticket : '',
                'code_passager' => isset($row->code_passager) ? (string) $row->code_passager : '',
                'tamponcod' => isset($row->tamponcod) ? (string) $row->tamponcod : '',
                'gaexp_lg' => isset($row->gaexp_lg) ? (string) $row->gaexp_lg : '',
                'gadest_lg' => isset($row->gadest_lg) ? (string) $row->gadest_lg : '',
                'dest_affiche' => (string) $dest,
                'heure' => isset($row->heure) ? (string) $row->heure : '',
                'date_progr' => isset($row->date_progr) ? (string) $row->date_progr : '',
                'prixvente' => isset($row->prixvente) ? $row->prixvente : null,
                'nom_ligne' => isset($row->nom_ligne) ? (string) $row->nom_ligne : '',
                'nom_compagnie' => isset($row->nom_compagnie) ? (string) $row->nom_compagnie : '',
                'id_escale_vente' => isset($row->id_escale_vente) ? $row->id_escale_vente : null,
                'nom_dest_vente' => isset($row->nom_dest_vente) ? (string) $row->nom_dest_vente : '',
                'code_gadest_vente' => isset($row->code_gadest_vente) ? (string) $row->code_gadest_vente : '',
            );
        }

        /**
         * Après lookup : détecte ticket transit via tamponcodtr et liste les jambes actives.
         */
        protected function _reprog_enrich_transit_meta($out)
        {
            if (!$out || !is_object($out)) {
                return $out;
            }
            $out->est_transit = 0;
            $out->nbr_jambes = 1;
            $out->jambes = array();
            $sum = $this->_reprog_jambe_summary($out);
            if ($sum) {
                $out->jambes[] = $sum;
            }

            $tr = isset($out->tamponcodtr) ? trim((string) $out->tamponcodtr) : '';
            if ($tr === '') {
                return $out;
            }

            $ek = $this->db->escape($this->session->company->ekey);
            $trEsc = $this->db->escape($tr);
            $rows = $this->db->query(
                "SELECT ctp.tamponcod, ctp.tamponcodtr,
                        p.code_passager, p.code_ticket, p.prixvente, p.id_escale_vente,
                        p.nom_dest_vente, p.code_gadest_vente,
                        h.heure, pr.date_progr, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                        ca.nom_compagnie AS nom_compagnie
                 FROM tamponcode ctp
                 JOIN passager p ON p.code_passager = ctp.tamponcod
                 JOIN programme pr ON p.code_pro = pr.code_progr
                 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                 JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                 JOIN entreprise e ON ca.id_entrep = e.id_entreprise
                 WHERE e.ekey = {$ek}
                 AND ctp.tamponcodtr = {$trEsc}
                 AND ctp.actif_tamp = 0
                 AND p.actif_pas = 0
                 AND p.num_siege_categorie IS NOT NULL
                 AND (p.statut_reprog IS NULL OR p.statut_reprog = '' OR p.statut_reprog != 'repor')
                 ORDER BY pr.date_progr ASC, h.heure ASC, p.code_passager ASC"
            )->result();

            if (count($rows) < 2) {
                return $out;
            }

            $out->est_transit = 1;
            $out->nbr_jambes = min(4, count($rows));
            $out->jambes = array();
            $n = 0;
            foreach ($rows as $r) {
                if ($n >= 4) {
                    break;
                }
                $js = $this->_reprog_jambe_summary($r);
                if ($js) {
                    $out->jambes[] = $js;
                    $n++;
                }
            }
            $out->nbr_jambes = count($out->jambes);
            return $out;
        }

        /**
         * Après report : rattacher l'escale déjà vendue au nouveau programme (destination ticket = escale).
         */
        protected function _reprog_apply_preserved_escale($code_passager, $code_ticket, $code_pro)
        {
            $idEsc = (int) $this->input->post('id_escale_vente_reprog');
            $codeG = trim((string) $this->input->post('code_gadest_vente_reprog'));
            $nomD = trim((string) $this->input->post('nom_dest_vente_reprog'));
            if ($idEsc <= 0 && $codeG === '' && $nomD === '') {
                return;
            }
            if (!isset($this->sale_svc)) {
                $this->load->library('sale_passager_service', null, 'sale_svc');
            }
            $fields = $this->sale_svc->preserve_escale_on_programme(
                $code_pro,
                $idEsc,
                $codeG,
                $nomD,
                false
            );
            if (!empty($fields)) {
                $this->m_passager->update($code_passager, $code_ticket, $fields);
            }
        }

        /**
         * Reprogrammation multi-correspondances : désactive l’ancien billet,
         * crée N passagers liés (tamponcodtr), imprime via editpdfepsontrans*.
         */
        protected function _reprog_commit_multiseg_epson(array $segs)
        {
            $today = mdate('%Y-%m-%d', now('UTC'));
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $iduser = roleattribut_guard_post_hint($this->company->ekey);
            $usen = substr($this->session->agent->username, 0, 1);
            $reg = $gidc;

            $cdpa_old = $this->input->post('passeridtransit');
            $cdpt_old = $this->input->post('codeclienttransit');
            $client_id = $this->input->post('client_idtransit');
            $sgidtr = $this->input->post('departclientidgaretr');
            if ($sgidtr === null || $sgidtr === '') {
                $sgidtr = $sgid;
            }
            $prix_base = $this->input->post('prixventeunifie');
            if ($prix_base === null || $prix_base === false) {
                $prix_base = '';
            }
            // Reprogrammation gratuite : prix segments informatifs (impression), hors CA agent.
            // Guichetier autorisé sur correspondances sans égalité de somme.

            if ($this->_reprog_deja_effectuee($this->input->post('codeticketsclienttransit'), $cdpa_old, $cdpt_old)) {
                $this->_reprog_refuse_redirect($gidc, $iduser, $sgid);
                return;
            }
            $is_tr_ticket = ((string) $this->input->post('reprog_is_transit_ticket') === '1');
            if ($is_tr_ticket) {
                $nOrigChk = (int) $this->input->post('reprog_nbr_jambes_origine');
                if ($nOrigChk < 2) {
                    $nOrigChk = 2;
                }
                if ($nOrigChk > 4) {
                    $nOrigChk = 4;
                }
                for ($ii = 2; $ii <= $nOrigChk; $ii++) {
                    $sfx = ($ii === 2) ? '2' : (string) $ii;
                    $cdpaX = $this->input->post('passeridtransit' . $sfx);
                    $cdptX = $this->input->post('codeclienttransit' . $sfx);
                    if ($cdpaX && $cdptX
                        && $this->_reprog_deja_effectuee(
                            $this->input->post('codeticketsclienttransit' . $sfx),
                            $cdpaX,
                            $cdptX
                        )
                    ) {
                        $this->_reprog_refuse_redirect($gidc, $iduser, $sgid);
                        return;
                    }
                }
            }

            $pairs = array();
            foreach ($segs as $s) {
                $pairs[] = array($s['code_progr'], $s['siege']);
            }
            if (!$this->_sale_sieges_sont_libres($pairs)) {
                if (isset($this->session) && method_exists($this->session, 'set_flashdata')) {
                    $this->session->set_flashdata(
                        'reprog_error',
                        'Un ou plusieurs sièges de correspondance ne sont plus disponibles.'
                    );
                }
                redirect(
                    'gares/' . $this->session->company->ekey
                    . '/gTc/' . $gidc . '/compte/' . $iduser . '/' . $sgid . '/'
                    . mdate('%d/%m/%Y', now('UTC'))
                );
                return;
            }

            $repors = $this->db->query(
                "SELECT COUNT(code_report) AS id FROM report r WHERE r.date = " . $this->db->escape($today)
            )->row();
            $codrep = $reg . mdate('%y%m%d', now('UTC')) . ($repors->id + 1) . $usen . $iduser;

            $tampo_pref = mdate('%y%m%d', now('UTC')) . 'TR' . $usen . $iduser;
            $tampo = $this->m_tamponcodetr->create(array('codtampon' => $tampo_pref));

            $this->m_passager->update($cdpa_old, $cdpt_old, array(
                'num_siege_categorie' => null,
                'actif_pas' => 1,
                'statut_reprog' => 'repor',
            ));
            $old_tamp = $this->input->post('codeticketsclienttransit');
            if ($old_tamp) {
                $this->m_tamponcode->update($old_tamp, array('actif_tamp' => 1));
            }
            // Jambes 2..N d’un ticket transit d’origine
            if ($is_tr_ticket) {
                $this->_reprog_invalidate_transit_leg2_if_needed();
            }

            $created = array();
            $escaleIdPost = (int) $this->input->post('id_escale_vente_reprog');
            $escaleCodePost = trim((string) $this->input->post('code_gadest_vente_reprog'));
            $escaleNomPost = trim((string) $this->input->post('nom_dest_vente_reprog'));
            $lastSegIdx = count($segs) - 1;
            $si = -1;
            foreach ($segs as $s) {
                $si++;
                $passecompter = $this->db->query(
                    "SELECT COUNT(code_passager) AS id FROM passager p
                     WHERE p.datep_create = " . $this->db->escape($today) . "
                     AND p.idcptuser = " . $this->db->escape($iduser) . "
                     AND p.code_ticket != 'R' AND p.statut_code = 'vendu'"
                )->row();
                $passecompt = $this->db->query(
                    "SELECT COUNT(code_passager) AS id FROM passager p
                     WHERE p.datep_create = " . $this->db->escape($today)
                )->row();

                $cdtick = mdate('%m%d', now('UTC')) . ($passecompt->id + 1) . $usen . $iduser;
                $tampon = mdate('%y%m%d', now('UTC')) . ($passecompter->id + 1) . $gidc . $usen . $iduser;

                $this->m_tamponcode->create(array(
                    'tamponcod' => $tampon,
                    'tamponcodtr' => $tampo,
                ));

                $cat = $s['cat'];
                if ($cat === '') {
                    $meta = $this->m_programme->indexprog($this->session->company->ekey, $s['code_progr']);
                    if (!empty($meta) && isset($meta[0]->categori)) {
                        $cat = $meta[0]->categori;
                    }
                }
                // Chaque jambe conserve son prix programme ; la somme a été contrôlée (= ticket vérifié).
                $prix = ($s['prix'] !== '') ? $s['prix'] : $prix_base;

                $pas = array(
                    'code_passager' => $tampon,
                    'code_ticket' => $cdtick,
                    'idcptuser' => $iduser,
                    'id_client_pass' => $client_id,
                    'code_pro' => $s['code_progr'],
                    'departclient_idgare' => $sgidtr,
                    'num_siege_categorie' => $s['siege'],
                    'num_cat' => $cat,
                    'statut_reprog' => 'repor',
                    'statut_code' => 'vendu',
                    // Hors encaissement agent (report gratuit, même multi-compagnie).
                    'statutvente' => self::REPROG_STATUTVENTE_HORS_CA,
                    'quart' => 'Marche',
                    'createpas_at' => now('UTC'),
                    'datep_create' => mdate('%Y-%m-%d', now('UTC')),
                );
                if ($prix !== '' && $prix !== null) {
                    $pas['prixvente'] = $prix;
                }
                $this->m_passager->create($pas);
                // Dernière jambe : préserver l'escale déjà vendue (après create pour ne pas écraser le prix jambe).
                if ($si === $lastSegIdx && $escaleIdPost > 0) {
                    if (!isset($this->sale_svc)) {
                        $this->load->library('sale_passager_service', null, 'sale_svc');
                    }
                    $escFields = $this->sale_svc->preserve_escale_on_programme(
                        $s['code_progr'],
                        $escaleIdPost,
                        $escaleCodePost,
                        $escaleNomPost,
                        false
                    );
                    if (!empty($escFields)) {
                        $this->m_passager->update($tampon, $cdtick, $escFields);
                    }
                }

                $rowTs = $this->db->query(
                    "SELECT * FROM tampon_siege t WHERE t.codepro = "
                    . $this->db->escape($s['code_progr'])
                    . " AND t.numsieg = " . $this->db->escape($s['siege'])
                )->row();
                if ($rowTs && isset($rowTs->idtamp)) {
                    $this->m_tampon_siege->del($rowTs->idtamp, array(
                        'codepro' => $s['code_progr'],
                        'numsieg' => $s['siege'],
                    ));
                }

                $created[] = array(
                    'tampon' => $tampon,
                    'typetarif' => $s['typetarif'],
                    'id_ligneheure' => $s['id_ligneheure'],
                );
            }

            $this->m_report->create(array(
                'code_report' => $codrep,
                'code_tick_tamp' => $created[0]['tampon'],
                'idcpuserconect' => $iduser,
                'date' => mdate('%Y/%m/%d', now('UTC')),
            ));
            // Chaque jambe = non reprogrammable (report + statut_reprog déjà 'repor').
            for ($ri = 1; $ri < count($created); $ri++) {
                $this->m_report->create(array(
                    'code_report' => $codrep . 'S' . $ri,
                    'code_tick_tamp' => $created[$ri]['tampon'],
                    'idcpuserconect' => $iduser,
                    'date' => mdate('%Y/%m/%d', now('UTC')),
                ));
            }

            $this->property['UPDATE_SUCCESS'] = true;
            $tf = $created[0]['typetarif'];
            $base = 'Historique_Passagers/';
            $tail = '/' . $gidc . '/' . $iduser . '/' . $sgid;
            $ek = $this->session->company->ekey;

            if (count($created) === 2) {
                redirect(
                    $base . 'editpdfepsontrans/' . $ek
                    . '/' . $created[0]['tampon'] . '/' . $tf . '/' . $created[0]['id_ligneheure']
                    . '/' . $created[1]['tampon'] . '/' . $created[1]['id_ligneheure']
                    . $tail
                );
                return;
            }
            if (count($created) === 3) {
                redirect(
                    $base . 'editpdfepsontrans2/' . $ek
                    . '/' . $created[0]['tampon'] . '/' . $tf . '/' . $created[0]['id_ligneheure']
                    . '/' . $created[1]['tampon'] . '/' . $created[1]['id_ligneheure']
                    . '/' . $created[2]['tampon'] . '/' . $created[2]['id_ligneheure']
                    . $tail
                );
                return;
            }
            redirect(
                $base . 'editpdfepsontrans3/' . $ek
                . '/' . $created[0]['tampon'] . '/' . $tf . '/' . $created[0]['id_ligneheure']
                . '/' . $created[1]['tampon'] . '/' . $created[1]['id_ligneheure']
                . '/' . $created[2]['tampon'] . '/' . $created[2]['id_ligneheure']
                . '/' . $created[3]['tampon'] . '/' . $created[3]['id_ligneheure']
                . $tail
            );
        }

        /**
         *
         */

        /**
         * Programmes d’une correspondance (ligne + date) pour le modal reprog unifié.
         * GET /reprogrammes/seg_progs/{ligne}/{date}/{tarif?}
         */
        public function seg_progs($ligne, $date, $tarif = null)
        {
            $ekey = $this->session->company->ekey;
            session_release_lock();
            $ligne = rawurldecode((string) $ligne);
            $date = rawurldecode((string) $date);
            if ($tarif === null || $tarif === '' || $tarif === '0') {
                $tarif = $this->input->get('tarif');
            }
            if ($tarif === false || $tarif === null || trim((string) $tarif) === '') {
                $tarif = null;
            }
            $cie = $this->input->get('cie');
            if ($cie === false || $cie === null || trim((string) $cie) === '') {
                $cie = $this->input->get('compaga');
            }
            if ($cie === false || $cie === null || trim((string) $cie) === '') {
                $cie = null;
            }
            $gadest = $this->input->get('gadest');
            if ($gadest === false || $gadest === null || trim((string) $gadest) === '') {
                $gadest = null;
            }
            $rows = $this->m_programme->getch_seg_reprog($ekey, $ligne, $date, $tarif, $cie, $gadest);
            // Si filtre tarif trop strict → retenter sans tarif (garde cie + gadest).
            if (empty($rows) && $tarif !== null) {
                $rows = $this->m_programme->getch_seg_reprog($ekey, $ligne, $date, null, $cie, $gadest);
            }
            // Si filtre gadest trop strict → retenter sans gadest (garde cie).
            if (empty($rows) && $gadest !== null) {
                $rows = $this->m_programme->getch_seg_reprog($ekey, $ligne, $date, $tarif, $cie, null);
                if (empty($rows) && $tarif !== null) {
                    $rows = $this->m_programme->getch_seg_reprog($ekey, $ligne, $date, null, $cie, null);
                }
            }
            // Dernier recours : sans cie (mais ne devrait pas arriver si l’étape a id_compaga).
            if (empty($rows) && $cie !== null) {
                $rows = $this->m_programme->getch_seg_reprog($ekey, $ligne, $date, $tarif, null, $gadest);
                if (empty($rows) && $tarif !== null) {
                    $rows = $this->m_programme->getch_seg_reprog($ekey, $ligne, $date, null, null, $gadest);
                }
                if (empty($rows) && $gadest !== null) {
                    $rows = $this->m_programme->getch_seg_reprog($ekey, $ligne, $date, $tarif, null, null);
                    if (empty($rows) && $tarif !== null) {
                        $rows = $this->m_programme->getch_seg_reprog($ekey, $ligne, $date, null, null, null);
                    }
                }
            }
            return $this->load->view('beagle/pages/_programme/json', array('json' => $rows));
        }

        public function siegdispo($cd)
        {
            
            $outcd = $this->m_programme->indexprog($this->session->company->ekey, $cd);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outcd));
            
        }

        public function siegepassager($d, $prog_id)
        {
            return $this->load->view('beagle/pages/_programme/json', array(
                'json' => $this->_sale_siegepassager_json($d, $prog_id),
            ), FALSE);
        }
        //information du client repro pour vendeuse
        public function adminverifcodecl($code)
        {
            $out = $this->m_tamponcode->verifirepadmin($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $out));
        }

        public function verifcodecl($code)
        {
            session_release_lock();
            $out = $this->m_tamponcode->verifirep($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $out));
        }

        public function verifcodetransit($code)
        {
            $outr = $this->m_tamponcode->verifireptra($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outr));
        }

        /**
         * Lookup unifié : code billet (tous) ou tamponcod (admin / chef seulement).
         * GET ?mode=ticket|tampon&code=...
         */
        public function lookup_unifie()
        {
            session_release_lock();
            $mode = strtolower(trim((string) $this->input->get_post('mode')));
            $code = trim((string) $this->input->get_post('code'));
            if ($code === '') {
                return $this->load->view('beagle/pages/_programme/json', array('json' => null));
            }

            $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
            $allow_tampon = in_array($role, array('1', '2', '5', '15'), true);

            if ($mode === 'tampon') {
                if (!$allow_tampon) {
                    return $this->output->set_status_header(403)
                        ->set_content_type('application/json')
                        ->set_output(json_encode(null));
                }
                // Admin/chef : lookup tampon sans filtre gare (garde any-gare ci-dessous).
                $out = $this->m_tamponcode->verifirepadmin($this->session->company->ekey, $code);
            } else {
                // Ticket : entreprise entière puis garde gare de vente.
                $out = $this->m_tamponcode->verifireptra($this->session->company->ekey, $code);
            }

            if (!$out) {
                return $this->load->view('beagle/pages/_programme/json', array('json' => null));
            }
            if (!$this->_reprog_may_reprog_ticket($out)) {
                return $this->load->view('beagle/pages/_programme/json', array(
                    'json' => array(
                        'ok' => false,
                        'error' => 'gare_refuse',
                        'reason' => 'Reprogrammation possible uniquement dans la gare ayant effectué la vente. Admin / chef guichet : toutes gares.',
                    ),
                ));
            }
            if (is_object($out)) {
                $out->ok = true;
                $hasEsc = !empty($out->id_escale_vente) || !empty($out->nom_dest_vente) || !empty($out->code_gadest_vente);
                $out->est_escale_vente = $hasEsc ? 1 : 0;
                if (function_exists('ticket_destination_label')) {
                    $out->dest_affiche = ticket_destination_label($out, isset($out->gadest_lg) ? $out->gadest_lg : '');
                } else {
                    $out->dest_affiche = !empty($out->nom_dest_vente) ? $out->nom_dest_vente : (isset($out->gadest_lg) ? $out->gadest_lg : '');
                }
                $out = $this->_reprog_enrich_transit_meta($out);
            }

            return $this->load->view('beagle/pages/_programme/json', array('json' => $out));
        }

        /**
         * Heures unifiées même OD. GET prix= optionnel ; GET id_escale= si ticket escale.
         * Vendeur : même prix obligatoire. Admin/chef : prix libre (toutes heures OD).
         */
        public function heures_unifie($gaexp, $gadest, $exclude)
        {
            session_release_lock();
            $role = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
            $is_prive = in_array($role, array('1', '2', '5', '15'), true);
            $prix = $this->input->get_post('prix');
            if ($prix === false || $prix === null) {
                $prix = '';
            }
            $prix = trim((string) $prix);
            $id_escale = (int) $this->input->get_post('id_escale');

            $prixFilter = null;
            if (!$is_prive) {
                if ($prix === '') {
                    return $this->load->view('beagle/pages/_programme/json', array('json' => array()));
                }
                $prixFilter = $prix;
            }

            $rows = $this->m_programme->heurereprog_unifie(
                $this->session->company->ekey,
                $gaexp,
                $gadest,
                $exclude,
                $prixFilter,
                $id_escale > 0 ? $id_escale : null
            );
            return $this->load->view('beagle/pages/_programme/json', array('json' => $rows));
        }
        
        public function verifretcodecl($gid, $codert, $u)
        {
            $outrt = $this->m_tamponcode->verifireprt($this->session->company->ekey, $gid, $codert, $u);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outrt));
        }

        public function verifcoderecu($gd, $cod)
        {
            $outrecu = $this->m_tamponcode->verifirecu($this->session->company->ekey, $gd, $cod);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outrecu));
        }

        public function verifcoderetour($coder)
        {
            $outr = $this->m_tamponcode->verificdretour($this->session->company->ekey, $coder);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outr));
        }

		public function verifcodeclgare($code)
        {
            $outs = $this->m_tamponcode->verifirepgare($this->session->company->ekey, $code);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outs));
        }
        //heure reprog
        public function hdepartprepro($axedp, $hcl, $lgh)
        {
            $outph = $this->m_programme->heurereprog($this->session->company->ekey, $axedp, $hcl, $lgh);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outph));
        }

        //verification code pour enregistrer bagages
        public function codeclientverif($cod, $gd, $sgd)
        {
            $outbag = $this->m_passager->verifcodbag($this->session->company->ekey, $cod, $gd, $sgd);

            return $this->load->view('beagle/pages/_tarif/json', array('json' => $outbag));
        }
        public function verifinfos($n)
        {

            $contcl = $this->m_client->infocl($n);
            return $this->load->view('beagle/pages/_tarif/json', array('json' => $contcl));
        }

        public function codeclientveriftr($cod = '')
        {
            if ($cod === '' || $cod === 'undefined') {
                return $this->load->view('beagle/pages/_programme/json', array('json' => null));
            }
            $outbagt = $this->m_passager->verifcodbagt($this->session->company->ekey, $cod);

            return $this->load->view('beagle/pages/_programme/json', array('json' => $outbagt));
        }

        public function codeclientveriftr2($cod2 = '')
        {
            if ($cod2 === '' || $cod2 === 'undefined') {
                return $this->load->view('beagle/pages/_programme/json', array('json' => null));
            }
            $outbagt2 = $this->m_passager->verifcodbagt2($this->session->company->ekey, $cod2);

            return $this->load->view('beagle/pages/_programme/json', array('json' => $outbagt2));
        }

        public function verifprog($axe, $dt)
        {
            
            $outh = $this->m_programme->getchcour($this->session->company->ekey, $axe, $dt);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outh));
            
        }
        public function hdepartpreprotr($axedp, $hcl)
        {
            $outpht = $this->m_programme->heurereprogtr($this->session->company->ekey, $axedp, $hcl);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outpht));
        }

        public function hdepartpreprotrt($axedp, $cpg, $hcl)
        {
            $outpht = $this->m_programme->heurereprogtrt($this->session->company->ekey, $axedp, $cpg, $hcl);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outpht));
        }
        public function verifconfquart($axe)
        {
            $qous= $this->m_quartier->qartligne($this->session->company->ekey, $axe);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $qous));
        }

        public function verifdepartcourrier($axe, $dt)
        {
            $outverfis = $this->m_programme->timeconf($this->session->company->ekey, $axe, $dt);
            return $this->load->view('beagle/pages/_programme/json', array('json' => $outverfis));
        }

        public function codeclientverifesc($cod, $gd, $sgd)
        {
            $outbagesc = $this->m_escalclients->verifcodbag($this->session->company->ekey, $cod, $gd, $sgd);

            return $this->load->view('beagle/pages/_tarif/json', array('json' => $outbagesc));
        }
       
        public function update($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $today = mdate("%Y-%m-%d", now('UTC'));
            $imprimeordinaire = $this->input->post('ordinaire');
            $imprimeepson = $this->input->post('epson');
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            if($imprimeordinaire)
            {
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $usen = substr($this->session->agent->username, 0, 1);           
                $reg = $this->input->post('gareconnect');
                $codet = $this->input->post('rpcode');
                
                $cderep = strpos($this->input->post('heuredepart'), '/');
                $dpclient = substr($this->input->post('heuredepart'), 0, $cderep);
                $hrp = substr($this->input->post('heuredepart'), $cderep + 1, strlen($this->input->post('heuredepart')));

                $cderep1 = strpos($hrp, '/');
                $dpclient1 = substr($hrp, 0, $cderep1);
                $hrp1 = substr($hrp, $cderep1 + 1, strlen($hrp));
                
                $p_sieg = $this->input->post('numsiege');
                $ct = $this->input->post('catreprogram');
                $cdpa = $this->input->post('passerid');
                $cdpt = $this->input->post('codeclient');
                $rt = 'repor';
                $codnonpass = $this->input->post('codenonpassager');
                $conf = $this->input->post('statconfirm');
                $repr = $this->input->post('statrepro');
                if ($this->_reprog_deja_effectuee($this->input->post('codeticketsclient'), $cdpa, $cdpt)) {
                    $this->_reprog_refuse_redirect($gidc, $iduser, $sgid);
                    return;
                }
                if($this->input->post('numsiege')!= '')
                {
                    $repors = $this->db->query("SELECT COUNT(code_report) AS id FROM report r WHERE r.date = '$today'")->row();

                    $codrep = $reg.mdate("%y%m%d", now('UTC')).($repors->id + 1).$usen.$iduser;

                            if ($this->_sale_sieges_sont_libres(array(array($dpclient, $p_sieg))))
                        {

                                $passagerarray = array(
                                    'id_client_pass' => $this->input->post('client_id'),
                                    'code_pro' => $dpclient,
                                    'num_siege_categorie' => $this->input->post('numsiege'),
                                    'num_cat' => $this->input->post('catreprogram'),
                                    'statut_reprog' => 'repor',
                                );
                                $passrid = $this->m_passager->update($cdpa, $cdpt, $passagerarray);

                                if ($passrid != FALSE) {
                
                                    $this->property['UPDATE_SUCCESS'] = TRUE;
                                        $arrayrep = array(
                                            'code_report' => $codrep,
                                            'code_tick_tamp' => $this->input->post('codeticketsclient'),
                                            'idcpuserconect' => $iduser,
                                            'date' => mdate("%Y/%m/%d", now('UTC')),
                                        );
                                        $arraycoderep = $this->m_report->create($arrayrep);
                                }

                            
                                
                                    $dte = date('H:i', time('H:i')+3600);
                                        $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif = h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                        
                                        foreach ($result as $rew) {
                                            $pasarrays = array(
                                                'num_siege_categorie' => NULL,
                                                'prixvente' => NULL,
                                                'num_cat' => NULL,
                                            );
                                            $this->m_passager->update($rew->code_passager, $rew->code_ticket, $pasarrays);
                                            
                                        }
                            
                                        $cp = $dpclient;
                                        $d = $this->input->post('numsiege');

                                        $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                    
                                            $delarray = array(
                                                'codepro' => $dpclient,
                                                'numsieg' => $this->input->post('numsiege'),
                                            );
                                            $this->m_tampon_siege->del($results->idtamp, $delarray);

                                if($codnonpass == "null" OR $conf == 'confirm'){
                                    
                                    redirect('Historique_Passagers/editprintreport/' . $this->session->company->ekey.'/'. $gidc . '/' . $codrep.'/'.$hrp1. '/' .$cdpt.'/' .$dpclient1.'/'.$iduser.'/'.$sgid);      
                                }
                                else
                                {                     
                                    
                                    redirect('Historique_Passagers/editprintreportar/' . $this->session->company->ekey.'/'. $gidc.'/' . $codrep .'/'. $hrp1.'/' .$codnonpass. '/' .$cdpt. '/' . $dpclient1.'/'.$iduser.'/'.$sgid);          
                                }
                                
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }   
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }  
            }

            if($imprimeepson)
            {
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $usen = substr($this->session->agent->username, 0, 1);           
                $reg = $this->input->post('gareconnect');

                $codet = $this->input->post('rpcode');
               
                $cderep = strpos($this->input->post('heuredepart'), '/');
                $dpclient = substr($this->input->post('heuredepart'), 0, $cderep);
                $hrp = substr($this->input->post('heuredepart'), $cderep + 1, strlen($this->input->post('heuredepart')));
                $cderep1 = strpos($hrp, '/');
                $dpclient1 = substr($hrp, 0, $cderep1);
                $hrp1 = substr($hrp, $cderep1 + 1, strlen($hrp));

                $p_sieg = $this->input->post('numsiege');
                $ct = $this->input->post('catreprogram');
                $cdpa = $this->input->post('passerid');
                $cdpt = $this->input->post('codeclient');
                $rt = 'repor';
                $codnonpass = $this->input->post('codenonpassager');
                $conf = $this->input->post('statconfirm');
                $repr = $this->input->post('statrepro');
                if ($this->_reprog_deja_effectuee($this->input->post('codeticketsclient'), $cdpa, $cdpt)) {
                    $this->_reprog_refuse_redirect($gidc, $iduser, $sgid);
                    return;
                }
                if($this->input->post('numsiege')!= '')
                {
                    $repors = $this->db->query("SELECT COUNT(code_report) AS id FROM report r WHERE r.date = '$today'")->row();

                    $codrep = $reg.mdate("%y%m%d", now('UTC')).($repors->id + 1).$usen.$iduser;

                            if ($this->_sale_sieges_sont_libres(array(array($dpclient, $p_sieg))))
                        {

                            

                            $passagerarray = array(
                                    'id_client_pass' => $this->input->post('client_id'),
                                    'code_pro' => $dpclient,
                                    'num_siege_categorie' => $this->input->post('numsiege'),
                                    'num_cat' => $this->input->post('catreprogram'),
                                    'statut_reprog' => 'repor',
                                );

                                $passrid = $this->m_passager->update($cdpa, $cdpt, $passagerarray);

                                if ($passrid != FALSE) {
                
                                    $this->property['UPDATE_SUCCESS'] = TRUE;
                                        $arrayrep = array(
                                            'code_report' => $codrep,
                                            'code_tick_tamp' => $this->input->post('codeticketsclient'),
                                            'idcpuserconect' => $iduser,
                                            'date' => mdate("%Y/%m/%d", now('UTC')),
                                        );
                                        $arraycoderep = $this->m_report->create($arrayrep);
                                }

                                
                                $dte = date('H:i', time('H:i')+3600);
                                        $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif = h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                        
                                        foreach ($result as $rew) {
                                            $pasarrays = array(
                                                'num_siege_categorie' => NULL,
                                                'prixvente' => NULL,
                                                'num_cat' => NULL,
                                            );
                                            $this->m_passager->update($rew->code_passager, $rew->code_ticket, $pasarrays);
                                            
                                        }
                            
                                        $cp = $dpclient;
                                        $d = $this->input->post('numsiege');

                                        $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                
                                        $delarray = array(
                                            'codepro' => $dpclient,
                                            'numsieg' => $this->input->post('numsiege'),
                                        );
                                        $this->m_tampon_siege->del($results->idtamp, $delarray);

                                if($codnonpass == "null" OR $conf == 'confirm'){
                                    
                                     
                                    redirect('Historique_Passagers/editepsonreport/' . $this->session->company->ekey.'/'. $gidc . '/' . $codrep.'/'. $hrp1. '/' .$cdpt.'/' . $dpclient1.'/'.$iduser.'/'.$sgid);      
                                }
                                else
                                {                     
                                   

                                    redirect('Historique_Passagers/editepsonreportar/' . $this->session->company->ekey.'/'. $gidc.'/' . $codrep .'/'. $hrp1.'/' .$codnonpass. '/' .$cdpt. '/' . $dpclient1.'/'.$iduser.'/'.$sgid);            
                                }
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }   
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }  
            }
                    
        }

        public function updatetransit($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $today = mdate("%Y-%m-%d", now('UTC'));
            $imprimeordinaire = $this->input->post('ordinairetransit');
            $imprimeepson = $this->input->post('epsontransit');
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            $iduser_gate = roleattribut_guard_post_hint($this->company->ekey);
            if (!$this->_reprog_guard_gare_commit_or_refuse($gidc, $iduser_gate, $sgid)) {
                return;
            }

            // Multi-segments : EPSON uniquement (ORDINAIRE désactivé dans le modal unifié).
            $multiseg_probe = $this->_reprog_parse_multiseg();
            if ($multiseg_probe === false
                || (is_array($multiseg_probe) && count($multiseg_probe) >= 2 && !$imprimeepson)
            ) {
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                if (isset($this->session) && method_exists($this->session, 'set_flashdata')) {
                    $msg = ($multiseg_probe === false)
                        ? 'Itinéraire incomplet : renseignez compagnie, heure et siège pour chaque correspondance.'
                        : 'Impression multi-correspondances : utilisez EPSON.';
                    $this->session->set_flashdata('reprog_error', $msg);
                }
                redirect(
                    'gares/' . $this->session->company->ekey
                    . '/gTc/' . $gidc . '/compte/' . $iduser . '/' . $sgid . '/'
                    . mdate('%d/%m/%Y', now('UTC'))
                );
                return;
            }

            if($imprimeepson)
            {
                if (is_array($multiseg_probe) && count($multiseg_probe) >= 2) {
                    $this->_reprog_commit_multiseg_epson($multiseg_probe);
                    return;
                }

                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $usen = substr($this->session->agent->username, 0, 1);           
                $reg = $this->input->post('gareconnect');

                $codet = $this->input->post('rpcodetransit');
                
                $gidctr = $this->input->post('gareidentiftrans');

                $sgidtr = $this->input->post('departclientidgaretr');
                
                $cderep = strpos($this->input->post('heuredeparttransit'), '/');
                $dpclient = substr($this->input->post('heuredeparttransit'), 0, $cderep);
                $hrp = substr($this->input->post('heuredeparttransit'), $cderep + 1, strlen($this->input->post('heuredeparttransit')));
                $cderep1 = strpos($hrp, '/');
                $dpclient1 = substr($hrp, 0, $cderep1);
                $hrp1 = substr($hrp, $cderep1 + 1, strlen($hrp));

                $p_sieg = $this->input->post('numsiegetransit');
                $ct = $this->input->post('catreprogramtransit');
                $cdpa = $this->input->post('passeridtransit');
                $cdpt = $this->input->post('codeclienttransit');

                $cdpa1 = $this->input->post('passeridtransit');
                $cdpt1 = $this->input->post('codeclienttransit');
                
                $rt = 'repor';
                $codnonpass = $this->input->post('codenonpassagertransit');
                if ($codnonpass === false || trim((string) $codnonpass) === '' || $codnonpass === 'null') {
                    $codnonpass = null;
                }
                $conf = $this->input->post('statconfirmtransit');
                $repr = $this->input->post('statreprotransit');
                if ($this->_reprog_deja_effectuee($this->input->post('codeticketsclienttransit'), $cdpa, $cdpt)) {
                    $this->_reprog_refuse_redirect($gidc, $iduser, $sgid);
                    return;
                }
                if ((string) $this->input->post('reprog_is_transit_ticket') === '1') {
                    $nOrigChk = (int) $this->input->post('reprog_nbr_jambes_origine');
                    if ($nOrigChk < 2) {
                        $nOrigChk = 2;
                    }
                    if ($nOrigChk > 4) {
                        $nOrigChk = 4;
                    }
                    for ($ii = 2; $ii <= $nOrigChk; $ii++) {
                        $sfx = ($ii === 2) ? '2' : (string) $ii;
                        $cdpa2chk = $this->input->post('passeridtransit' . $sfx);
                        $cdpt2chk = $this->input->post('codeclienttransit' . $sfx);
                        if ($cdpa2chk && $cdpt2chk
                            && $this->_reprog_deja_effectuee(
                                $this->input->post('codeticketsclienttransit' . $sfx),
                                $cdpa2chk,
                                $cdpt2chk
                            )
                        ) {
                            $this->_reprog_refuse_redirect($gidc, $iduser, $sgid);
                            return;
                        }
                    }
                }
                if($this->input->post('numsiegetransit')!= '')
                {
                    $repors = $this->db->query("SELECT COUNT(code_report) AS id FROM report r WHERE r.date = '$today'")->row();

                    $codrep = $reg.mdate("%y%m%d", now('UTC')).($repors->id + 1).$usen.$iduser;

                        if ($this->_sale_sieges_sont_libres(array(array($dpclient, $p_sieg))))
                        {

                            $cdrpo = $this->input->post('compgcftranst');

                             $pcdrpo = $this->input->post('trid_compaga');

                             
                            if($pcdrpo == $cdrpo)
                            {

                                $passagerarray = array(
                                    'id_client_pass' => $this->input->post('client_idtransit'),
                                    'code_pro' => $dpclient,
                                    'num_siege_categorie' => $this->input->post('numsiegetransit'),
                                    'num_cat' => $this->input->post('catreprogramtransit'),
                                    'statut_reprog' => 'repor',
                                );

                                $passrid = $this->m_passager->update($cdpa, $cdpt, $passagerarray);

                                if ($passrid != FALSE) {
                                    $this->_reprog_apply_preserved_escale($cdpa, $cdpt, $dpclient);
                
                                    $this->property['UPDATE_SUCCESS'] = TRUE;
                                    $arrayrep = array(
                                        'code_report' => $codrep,
                                        'code_tick_tamp' => $this->input->post('codeticketsclienttransit'),
                                        'idcpuserconect' => $iduser,
                                        'date' => mdate("%Y/%m/%d", now('UTC')),
                                    );
                                    $arraycoderep = $this->m_report->create($arrayrep);
                                    $this->_reprog_invalidate_transit_leg2_if_needed();
                                }

                            }
                            if($pcdrpo != $cdrpo)
                            {
                                $passecompterrp = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket !='R' AND p.statut_code = 'vendu'")->row();
                                
                                $passecomptrp = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();
                                

                                $cdtickrp = mdate("%m%d", now('UTC')).($passecomptrp->id + 1).$usen.$iduser;

                                $tamponrp = mdate("%y%m%d", now('UTC')).($passecompterrp->id + 1).$gidc.$usen.$iduser;

                                    $antamponrp = $this->input->post('lgecodeticketstransit');

                                    $cdpt = $cdtickrp;
                                
                                    $arraycodetamponrp = array(
                                        'tamponcod' => $tamponrp,
                                        'tamponcodtr' => $antamponrp,
                                    );
                                        
                                    $this->m_tamponcode->create($arraycodetamponrp);

                                       
                                        $pasarray = array(
                                            'code_passager' => $tamponrp,
                                            'code_ticket' => $cdtickrp,
                                            'idcptuser' => $iduser,
                                            'id_client_pass' => $this->input->post('client_idtransit'),
                                            'code_pro' => $dpclient,
                                            'departclient_idgare' => $sgidtr,
                                            'num_siege_categorie' => $this->input->post('numsiegetransit'),
                                            'num_cat' => $this->input->post('catreprogramtransit'),
                                            'statut_reprog' => 'repor',
                                            'statut_code' => 'vendu',
                                            // Hors encaissement agent (changement de compagnie = report gratuit).
                                            'statutvente' => self::REPROG_STATUTVENTE_HORS_CA,
                                            'quart' => 'Marche',
                                            'createpas_at' => now('UTC'),
                                            'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                        $prixOrigCie = $this->input->post('prixventeunifie');
                                        if ($prixOrigCie !== false && $prixOrigCie !== null && trim((string) $prixOrigCie) !== '') {
                                            $pasarray['prixvente'] = $prixOrigCie;
                                        }
                                    $passrid = $this->m_passager->create($pasarray);
                                    if ($passrid != FALSE) {
                                        $this->_reprog_apply_preserved_escale($tamponrp, $cdtickrp, $dpclient);
                                    }
                                    
                                        $arrayrep = array(
                                            'code_report' => $codrep,
                                            'code_tick_tamp' => $tamponrp,
                                            'idcpuserconect' => $iduser,
                                            'date' => mdate("%Y/%m/%d", now('UTC')),
                                        );
                                        
                                    $arraycoderep = $this->m_report->create($arrayrep);

                                    $passagerarray1 = array(
                                        'num_siege_categorie' => NULL,
                                        'actif_pas' => 1,
                                    );

                                    $passrid1 = $this->m_passager->update($cdpa1, $cdpt1, $passagerarray1);

                                    $arraycodetamponrp1 = array(
                                        'actif_tamp' => 1,
                                    );
                                    
                                    $this->m_tamponcode->update($cdpa1, $arraycodetamponrp1);
                                    $this->_reprog_invalidate_transit_leg2_if_needed();
                            }
                            
                            /*if($pcdrpo != $cdrpo){

                                if($codnonpass !== null){
                                    $passecompterrp = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today' AND p.idcptuser = '$iduser' AND p.code_ticket !='R' AND p.statut_code = 'vendu'")->row();
                                    
                                    $passecomptrp = $this->db->query("SELECT COUNT(code_passager) AS id FROM passager p WHERE p.datep_create = '$today'")->row();

                                    $comptnp = $this->db->query("SELECT COUNT(code_non_pass) AS id FROM non_passager np WHERE np.datevente = '$today' AND np.cptus = '$iduser'")->row();

                                    $compnp = $this->db->query("SELECT COUNT(code_non_pass) AS id FROM non_passager np WHERE np.datevente = '$today'")->row();
                                        $idemt = 'N';

                                    $cpastick = mdate("%m%d", now('UTC')).($passecomptrp->id + 1).$usen.$iduser;
                                        
                                    $cdnpas = mdate("%m%d", now('UTC')).$idemt.($compnp->id + 1).$usen.$iduser; 
                                        
                                    $tpcdpas = mdate("%y%m%d", now('UTC')).($passecompterrp->id + 1).$gidc.$usen.$iduser;

                                    $antamponrp = $this->input->post('lgecodetickbagsanstr');

                                    $arraycodetamponrp = array(
                                        'tamponcod' => $tpcdpas,
                                        'tamponcodtr' => $antamponrp,
                                    );
                                    
                                    $this->m_tamponcode->create($arraycodetamponrp);
                                   
                                    $pasarray = array(
                                        'code_passager' => $tpcdpas,
                                        'code_ticket' => $cpastick,
                                        'idcptuser' => $iduser,
                                        'id_client_pass' => $this->input->post('client_idtransit'),
                                        'code_pro' => $dpclient,
                                        'departclient_idgare' => $sgidtr,
                                        'num_siege_categorie' => $this->input->post('numsiegetransit'),
                                        'num_cat' => $this->input->post('catreprogramtransit'),
                                        'statut_reprog' => 'repor',
                                        'statut_code' => 'vendu',
                                        'quart' => 'Marche',
                                        'createpas_at' => now('UTC'),
                                        'datep_create' => mdate("%Y-%m-%d", now('UTC')),
                                    );
                                    $passrid = $this->m_passager->create($pasarray);

                                    $nonarray = array(
                                        'code_non_pass' => $tpcdpas,
                                        'codeticket' => $cdnpas,
                                        'cptus' => $iduser,
                                        'sousgareidentif' => $sgidtr,
                                        'id_client_npass' => $this->input->post('client_idtransit'),
                                        'id_ligne_pass' => $this->input->post('idrpligntransit'),
                                        'nom_ligne' => $this->input->post('repligntransit'),
                                        'datevente' => mdate("%Y/%m/%d", now('UTC')),
                                        'creatednp_at' => now('UTC'),
                                    );
                                    $nonrid = $this->m_non_passager->create($nonarray);

                                    $codnonpass = $cdnpas;
                                    $cdpt = $cpastick;
                                    
                                        $arrayrep = array(
                                            'code_report' => $codrep,
                                            'code_tick_tamp' => $tpcdpas,
                                            'idcpuserconect' => $iduser,
                                            'date' => mdate("%Y/%m/%d", now('UTC')),
                                        );
                                        
                                    $arraycoderep = $this->m_report->create($arrayrep);
                        
                                    $passagerarray1 = array(
                                        'num_siege_categorie' => NULL,
                                        'actif_pas' => 1,
                                    );

                                    $passrid1 = $this->m_passager->update($cdpa1, $cdpt1, $passagerarray1);

                                    $arraycodetamponrp1 = array(
                                        'actif_tamp' => 1,
                                    );
                                    
                                    $this->m_tamponcode->update($cdpa1, $arraycodetamponrp1);
                                }
                            }*/
                                
                            $dte = date('H:i', time('H:i')+3600);
                                
                            $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                JOIN programme pr ON p.code_pro = pr.code_progr
                                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                JOIN heures h ON lh.heure_identif = h.id_heure
                                WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                
                                foreach ($result as $rew) {
                                    $pasarrays = array(
                                        'num_siege_categorie' => NULL,
                                        'prixvente' => NULL,
                                        'num_cat' => NULL,
                                    );
                                    $this->m_passager->update($rew->code_passager, $rew->code_ticket, $pasarrays);
                                }
                            
                                        $cp = $dpclient;
                                        $d = $this->input->post('numsiegetransit');

                                        $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                
                                        $delarray = array(
                                            'codepro' => $dpclient,
                                            'numsieg' => $this->input->post('numsiegetransit'),
                                        );
                                        
                                    $this->m_tampon_siege->del($results->idtamp, $delarray);

                                if ($codnonpass === null || $codnonpass === '' || $codnonpass === 'null' || $conf == 'confirm') {
                                     
                                    redirect('Historique_Passagers/editepsonreporttr/' . $this->session->company->ekey.'/'. $gidc . '/' . $codrep.'/'. $hrp1. '/' .$cdpt.'/' . $dpclient1.'/'.$iduser.'/'.$sgid.'/'.$gidctr.'/'.$sgidtr);
                                }
                                else
                                {                     
                                   
                                    redirect('Historique_Passagers/editepsonreportartr/' . $this->session->company->ekey.'/'. $gidc.'/' . $codrep .'/'. $hrp1.'/' .$codnonpass. '/' .$cdpt. '/' . $dpclient1.'/'.$iduser.'/'.$sgid.'/'.$gidctr.'/'.$sgidtr);
                                }
                        }
                        else
                        {
                            
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }   
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }  
            }
                    
        }
        public function adupdate($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $today = mdate("%Y-%m-%d", now('UTC'));
            $imprimeordinaire = $this->input->post('adordinaire');
            $imprimeepson = $this->input->post('adepson');
            $gidc = $this->input->post('gareconnect');
            $sgid = $this->input->post('sousgareconnect');
            $idcmpt = $this->input->post('compconnected');
            if($imprimeordinaire)
            {
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $usen = substr($this->session->agent->username, 0, 1);           
                $reg = $this->input->post('gareconnect');
                $codet = $this->input->post('adminrpcode');
                
                $cderep = strpos($this->input->post('adminheuredepart'), '/');
                $dpclient = substr($this->input->post('adminheuredepart'), 0, $cderep);
                $hrp = substr($this->input->post('adminheuredepart'), $cderep + 1, strlen($this->input->post('adminheuredepart')));

                $cderep1 = strpos($hrp, '/');
                $dpclient1 = substr($hrp, 0, $cderep1);
                $hrp1 = substr($hrp, $cderep1 + 1, strlen($hrp));
                
                $p_sieg = $this->input->post('adminnumsiege');
                $ct = $this->input->post('admincatreprogram');
                $cdpa = $this->input->post('adminpasserid');
                $cdpt = $this->input->post('admincodeclient');
                $rt = 'repor';
                $codnonpass = $this->input->post('admincodenonpassager');
                $conf = $this->input->post('adminstatconfirm');
                $repr = $this->input->post('adminstatrepro');
                if ($this->_reprog_deja_effectuee($this->input->post('admincodeticketsclient'), $cdpa, $cdpt)) {
                    $this->_reprog_refuse_redirect($gidc, $iduser, $sgid);
                    return;
                }
                if($this->input->post('adminnumsiege')!= '')
                {
                    $repors = $this->db->query("SELECT COUNT(code_report) AS id FROM report r WHERE r.date = '$today'")->row();

                    $codrep = $reg.mdate("%y%m%d", now('UTC')).($repors->id + 1).$usen.$iduser;

                            if ($this->_sale_sieges_sont_libres(array(array($dpclient, $p_sieg))))
                        {

                                $passagerarray = array(
                                    'id_client_pass' => $this->input->post('adminclient_id'),
                                    'code_pro' => $dpclient,
                                    'num_siege_categorie' => $this->input->post('adminnumsiege'),
                                    'num_cat' => $this->input->post('admincatreprogram'),
                                    'statut_reprog' => 'repor',
                                );
                                $passrid = $this->m_passager->update($cdpa, $cdpt, $passagerarray);

                                if ($passrid != FALSE) {
                
                                    $this->property['UPDATE_SUCCESS'] = TRUE;
                                        $arrayrep = array(
                                            'code_report' => $codrep,
                                            'code_tick_tamp' => $this->input->post('admincodeticketsclient'),
                                            'idcpuserconect' => $iduser,
                                            'date' => mdate("%Y/%m/%d", now('UTC')),
                                        );
                                        $arraycoderep = $this->m_report->create($arrayrep);
                                }

                            
                                
                                $dte = date('H:i', time('H:i')+3600);
                                        $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif = h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                        
                                        foreach ($result as $rew) {
                                            $pasarrays = array(
                                                'num_siege_categorie' => NULL,
                                                'prixvente' => NULL,
                                                'num_cat' => NULL,
                                            );
                                            $this->m_passager->update($rew->code_passager, $rew->code_ticket, $pasarrays);
                                            
                                        }
                            
                                        $cp = $dpclient;
                                        $d = $this->input->post('adminnumsiege');

                                        $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                    
                                            $delarray = array(
                                                'codepro' => $dpclient,
                                                'numsieg' => $this->input->post('adminnumsiege'),
                                            );
                                            $this->m_tampon_siege->del($results->idtamp, $delarray);

                                if($codnonpass == "null" OR $conf == 'confirm'){
                                    
                                    redirect('Historique_Passagers/editprintreport/' . $this->session->company->ekey.'/'. $gidc . '/' . $codrep.'/'.$hrp1. '/' .$cdpt.'/' .$dpclient1.'/'.$iduser.'/'.$sgid);      
                                }
                                else
                                {                     
                                    
                                    redirect('Historique_Passagers/editprintreportar/' . $this->session->company->ekey.'/'. $gidc.'/' . $codrep .'/'. $hrp1.'/' .$codnonpass. '/' .$cdpt. '/' . $dpclient1.'/'.$iduser.'/'.$sgid);          
                                }
                                
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }   
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }  
            }

            if($imprimeepson)
            {
                $iduser = roleattribut_guard_post_hint($this->company->ekey);
                $usen = substr($this->session->agent->username, 0, 1);           
                $reg = $this->input->post('gareconnect');

                $codet = $this->input->post('adminrpcode');
                
                
                $cderep = strpos($this->input->post('adminheuredepart'), '/');
                $dpclient = substr($this->input->post('adminheuredepart'), 0, $cderep);
                $hrp = substr($this->input->post('adminheuredepart'), $cderep + 1, strlen($this->input->post('adminheuredepart')));
                $cderep1 = strpos($hrp, '/');
                $dpclient1 = substr($hrp, 0, $cderep1);
                $hrp1 = substr($hrp, $cderep1 + 1, strlen($hrp));

                $p_sieg = $this->input->post('adminnumsiege');
                $ct = $this->input->post('admincatreprogram');
                $cdpa = $this->input->post('adminpasserid');
                $cdpt = $this->input->post('admincodeclient');
                $rt = 'repor';
                $codnonpass = $this->input->post('admincodenonpassager');
                $conf = $this->input->post('adminstatconfirm');
                $repr = $this->input->post('adminstatrepro');
                if ($this->_reprog_deja_effectuee($this->input->post('admincodeticketsclient'), $cdpa, $cdpt)) {
                    $this->_reprog_refuse_redirect($gidc, $iduser, $sgid);
                    return;
                }
                if($this->input->post('adminnumsiege')!= '')
                {
                    $repors = $this->db->query("SELECT COUNT(code_report) AS id FROM report r WHERE r.date = '$today'")->row();

                    $codrep = $reg.mdate("%y%m%d", now('UTC')).($repors->id + 1).$usen.$iduser;

                            if ($this->_sale_sieges_sont_libres(array(array($dpclient, $p_sieg))))
                        {

                            

                            $passagerarray = array(
                                    'id_client_pass' => $this->input->post('adminclient_id'),
                                    'code_pro' => $dpclient,
                                    'num_siege_categorie' => $this->input->post('adminnumsiege'),
                                    'num_cat' => $this->input->post('admincatreprogram'),
                                    'statut_reprog' => 'repor',
                                );

                                $passrid = $this->m_passager->update($cdpa, $cdpt, $passagerarray);

                                if ($passrid != FALSE) {
                
                                    $this->property['UPDATE_SUCCESS'] = TRUE;
                                        $arrayrep = array(
                                            'code_report' => $codrep,
                                            'code_tick_tamp' => $this->input->post('admincodeticketsclient'),
                                            'idcpuserconect' => $iduser,
                                            'date' => mdate("%Y/%m/%d", now('UTC')),
                                        );
                                        $arraycoderep = $this->m_report->create($arrayrep);
                                }

                                
                                $dte = date('H:i', time('H:i')+3600);
                                        $result = $this->db->query("SELECT p.code_passager, p.code_ticket, p.code_pro, pr.code_progr, pr.id_heur, lh.heure_identif, h.heure, pr.date_progr FROM passager p
                                        JOIN programme pr ON p.code_pro = pr.code_progr
                                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                                        JOIN heures h ON lh.heure_identif = h.id_heure
                                        WHERE h.heure <= '$dte' AND pr.date_progr = '$today' AND p.code_ticket = 'R'")->result();
                        
                                        foreach ($result as $rew) {
                                            $pasarrays = array(
                                                'num_siege_categorie' => NULL,
                                                'prixvente' => NULL,
                                                'num_cat' => NULL,
                                            );
                                            $this->m_passager->update($rew->code_passager, $rew->code_ticket, $pasarrays);
                                            
                                        }
                            
                                        $cp = $dpclient;
                                        $d = $this->input->post('adminnumsiege');

                                        $results = $this->db->query("SELECT * FROM tampon_siege t WHERE t.codepro = '$cp' AND t.numsieg = '$d'")->row();
                                                
                                        $delarray = array(
                                            'codepro' => $dpclient,
                                            'numsieg' => $this->input->post('adminnumsiege'),
                                        );
                                        $this->m_tampon_siege->del($results->idtamp, $delarray);

                                if($codnonpass == "null" OR $conf == 'confirm'){
                                    
                                     
                                    redirect('Historique_Passagers/editepsonreport/' . $this->session->company->ekey.'/'. $gidc . '/' . $codrep.'/'. $hrp1. '/' .$cdpt.'/' . $dpclient1.'/'.$iduser.'/'.$sgid);      
                                }
                                else
                                {                     
                                   

                                    redirect('Historique_Passagers/editepsonreportar/' . $this->session->company->ekey.'/'. $gidc.'/' . $codrep .'/'. $hrp1.'/' .$codnonpass. '/' .$cdpt. '/' . $dpclient1.'/'.$iduser.'/'.$sgid);            
                                }
                        }
                        else
                        {
                            redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                        }   
                }
                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gidc.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }  
            }
                    
        }

        public function recuclient($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
            $this->company = $this->m_entreprises->get_key($ckey);
            $today = mdate("%Y-%m-%d", now('UTC'));
            $gidc = $this->input->post('gareconnectrecu');
            $sgid = $this->input->post('sousgareconnectrecu');
            $idcmpt = $this->input->post('compconnectedrecu');
        
            $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnect', 'userconnectedrecu');
            
            $cdtamp = $this->input->post('codetamponrecu');
            $npas = $this->input->post('passnomrecu');
            $prpas = $this->input->post('passprenomrecu');
            $lignerecu = $this->input->post('passaxerecu');
            $prixrecu = $this->input->post('pventerecu');
            $cdtck = $this->input->post('passeridrecu');
            $codnonpass = $this->input->post('codenonpassagerrecu');

            $td = mdate("%y%m%d", now('UTC'));

            $rec = $this->db->query("SELECT COUNT(idrecup) AS id FROM recupassager re WHERE re.datecreatrecu = '$td'")->row();

            $codrec = $gidc.mdate("%y%m%d", now('UTC')).($rec->id + 1);
            
                $recuarray = array(
                    'recuactif' => 1,
                );
                $passrecu = $this->m_tamponcode->update($cdtamp, $recuarray);

                if ($passrecu != FALSE) {
                
                    $this->property['UPDATE_SUCCESS'] = TRUE;

                    $arrayrecu = array(
                        'numrecu' => $codrec,
                        'operaidrecu' => $iduser,
                        'idtampcodpass' => $this->input->post('codetamponrecu'),
                        'nomsociete' => $this->input->post('structurenom'),
                        'datecreatrecu' => mdate("%Y/%m/%d", now('UTC')),
                    );
                    $this->m_recupassager->create($arrayrecu);

                }
                else
                {
                    $recuarray = array(
                        'recuactif' => 0,
                    );
                    $passrecu = $this->m_tamponcode->update($cdtamp, $recuarray);
                }

                if($codnonpass == "null"){
                        
                    redirect('Historique_Passagers/editrecus/' . $this->session->company->ekey.'/' .$cdtamp.'/'.$cdtck.'/'.$gidc.'/'.$iduser.'/'.$sgid);      
                }
                else
                {                     
                                
                    redirect('Historique_Passagers/editrecusar/' . $this->session->company->ekey.'/' .$cdtamp.'/'.$codnonpass.'/'. $gidc.'/'.$iduser.'/'.$sgid);          
                }            
        }

        public function savebag($ckey){

            $this->company = $this->m_entreprises->get_key($ckey);

            $gid = $this->input->post('gareconnectbagsans');
            $sgid = $this->input->post('sousgareconnectbagsans');
            $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnect', 'userconnectedbagsans');
            if ($msg = compte_arret_guard_sale('bagage', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }

            $cd = (int)$this->input->post('idcompagad');

            $today = mdate("%Y-%m-%d", now('UTC'));

            $cid = (int)$this->session->company->ekey;

            $bagepson = $this->input->post('epsonbagsans');
            $idcmpt = $this->input->post('compconnectedbagsans');

            $garec = $this->db->query("SELECT gr.codegares FROM gares gr WHERE gr.idengare = '$gid'")->row();
            
            $nugd = $garec->codegares;

                /*$lettre = $garec->codegares;

                $numeo = ord($lettre) - 64;
                $nugd = $numeo;*/

            $lgcd = $this->input->post('lgcodetickbagsans');

            $lgcdtr = $this->input->post('lgecodetickbagsanstr');

            $cdcd = (string) $this->input->post('codeticketbags');

            $ch = $this->input->post('types_bagsans');

            $chr = implode(",", $ch);

            $quart3 = $this->input->post('quartpasses');

            
            $arcod1 = strpos($this->input->post('lignespasses'), '/');
            $arcod2 = substr($this->input->post('lignespasses'), 0, $arcod1);

            $arcod3 = substr($this->input->post('lignespasses'), $arcod1 + 1, strlen($this->input->post('lignespasses')));

            $arcode1 = strpos($arcod2, '-');
            $arcode2 = substr($arcod2, 0, $arcode1);

            $arcode3 = substr($arcod2, $arcode1 + 1, strlen($arcod2));

                $arecomp = $this->db->query("SELECT d.id_compaga FROM gare_dest d WHERE d.code_gadest = '$arcode3'")->row();
                    
            
            if($arcod3 == NULL)
            {

                $argde = '';
            }
            else
            {

                $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcode3'")->row();

                $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                $argde = $aregid->idgaresdest;
            }
            
            if($quart3 == NULL)
            {
                $sargde = '';
            }

            else
            {

                $quart3 = $this->input->post('quartpasses');

                $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcode3'")->row();

                $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                $sargde = $sousgar_id->idsousgare;
            }


            

            if($this->input->post('passcontactbagsans') != NULL AND $this->input->post('cprclientbagsans') != NULL AND $this->input->post('progcodbagsans') != NULL AND $this->input->post('siegebagasans') != NULL AND $this->input->post('buscodebagsans') != NULL AND $this->input->post('codetickbagsans') != NULL AND $this->input->post('types_bagsans') != NULL AND $this->input->post('naturebagagesans') != NULL AND $this->input->post('nombrebagsans') != NULL AND $this->input->post('fraisbagsans') != NULL)
            {
                $nbrs = $this->input->post('nombrebagsans');

                if($nbrs === '' OR $nbrs === '0' OR $nbrs === '-1'){

                    $nbres = '1';

                }else
                {
                    $nbres = $this->input->post('nombrebagsans');
                }

                    
                    $cd = (int)$this->input->post('idcompagad');

                    $today = mdate("%Y-%m-%d", now('UTC'));

                    $cid = (int)$this->session->company->ekey;

                    $aenc = date("y");

                    $annee = date("Y");


                    $pascompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM
                     bagages b WHERE b.idoperabagage = '$iduser' AND YEAR(b.lastbag_update) = '$annee'")->row();

                    
                    $codecptr = $idcmpt.$nugd.($pascompt->id + 1)."-".$aenc;

                    $derniercrn = $this->db->query("SELECT b.couleurcarnet FROM bagages b
                        INNER JOIN lignes lg ON b.lgidbagage = lg.ident_ligne 
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '$cid'
                        AND dest.id_compaga = '$cd'
                        AND b.idgarebag = '$gid'
                        AND b.date_create = '$today'
                        ORDER BY b.lastbag_update DESC LIMIT 1")->row();
                        
                        if($bagepson)
                        {
                                                  
                            $argupgab = array(
                                'id_bagage' => $codecptr,
                                'idoperabagage' => (int)$iduser,
                                'lgidbagage' => $gid. '-' .$arcode3,
                                'clientbag' => (int)$this->input->post('cprclientbagsans'),
                                'idgarebag' => $gid,
                                'idsgarebag' => (int)$sgid,
                                'gidarrbag' => $argde,
                                'sgidarrbag' => (int)$sargde,
                                'quartarr_bg' => $this->input->post('quartpasses'),
                                'codebag' => $this->input->post('lgecodetickbagsanstr'),
                                'contactexpedi' => $this->input->post('passcontactbagsans'),
                                'genrebagage' => 'sans_suivi',
                                'typebagages' => $chr,
                                'nombrebagage' => (int)$nbres,
                                'contenubagage' => $this->input->post('naturebagagesans'),
                                'valeurbagage' => (double)$this->input->post('valeurbagagesans'),
                                'prix_bagage' => (double)$this->input->post('fraisbagsans'),
                                'transistbag' => 'pas_transit',
                                'date_create' => mdate("%Y-%m-%d", now('UTC')),
                            );
                    if($arcode3 != NULL AND $lgcdtr != NULL AND $gid != $argde)
                    {
                        $this->m_bagage->create($argupgab);
                        
                        
                    
                        if($derniercrn == NULL)
                        {
                            $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                        }

                        else
                        {
                            if($derniercrn->couleurcarnet == 'A')
                            {       
                                $this->db->query("UPDATE bagages SET couleurcarnet = 'B' WHERE id_bagage = '$codecptr'");
                            }
                            elseif($derniercrn->couleurcarnet == 'B')
                            {        
                                $this->db->query("UPDATE bagages SET couleurcarnet = 'C' WHERE id_bagage = '$codecptr'");
                            }
                            elseif ($derniercrn->couleurcarnet == 'C')
                            {       
                                $this->db->query("UPDATE bagages SET couleurcarnet = 'D' WHERE id_bagage = '$codecptr'");
                            }
                            elseif ($derniercrn->couleurcarnet == 'D')
                            {  
                                $this->db->query("UPDATE bagages SET couleurcarnet = 'E' WHERE id_bagage = '$codecptr'");
                            }

                            else
                            {       
                                $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                            }
                        }
                     
                        if($this->session->agent->userole === '1' OR $this->session->agent->userole === '12' OR $this->session->agent->userole === '10')
                        {
                            redirect('Historique_Passagers/bagsave/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                        }else
                        {

                            redirect('Historique_Passagers/bagsaveguich/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                        }

                    }else{
                        redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    }

                }
            }              
            else
            {
                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
        }

        ///enregistrement bagage avec ticket non facturable

        public function savebagnfact($ckey){

            $this->company = $this->m_entreprises->get_key($ckey);

            $gid = $this->input->post('gareconnectbagsansn');
            $sgid = $this->input->post('sousgareconnectbagsansn');
            $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnect', 'userconnectedbagsansn');
            if ($msg = compte_arret_guard_sale('bagage', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }

            $today = mdate("%Y-%m-%d", now('UTC'));

            $bagepson = $this->input->post('epsonbagsansn');
            $idcmpt = $this->input->post('compconnectedbagsansn');
                //strval();

            $garec = $this->db->query("SELECT gr.codegares FROM gares gr WHERE gr.idengare = '$gid'")->row();

            $nugd = $garec->codegares;

                /*$lettre = $garec->codegares;

                $numeo = ord($lettre) - 64;
                $nugd = $numeo;*/



            $lgcd = $this->input->post('lgcodetickbagsansntr');

            $cdcd = (string) $this->input->post('codetickbagsansn');

            $chs = $this->input->post('types_bagsansn');

            $chrn = implode(",",$chs);

            $quart3 = $this->input->post('quartpassesn');

            
            $arcod1 = strpos($this->input->post('lignespassesn'), '/');
            $arcod2 = substr($this->input->post('lignespassesn'), 0, $arcod1);

            $arcod3 = substr($this->input->post('lignespassesn'), $arcod1 + 1, strlen($this->input->post('lignespassesn')));

            $arcode1 = strpos($arcod2, '-');
            $arcode2 = substr($arcod2, 0, $arcode1);

            $arcode3 = substr($arcod2, $arcode1 + 1, strlen($arcod2));

            $aenc = date("y");

            $annee = date("Y");


            $pascompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM
                     bagages b WHERE b.idoperabagage = '$iduser' AND YEAR(b.lastbag_update) = '$annee'")->row();

            $codecptr = $idcmpt.$nugd.($pascompt->id + 1) . "-" . $aenc;
            
            if($arcod3 == NULL)
            {

                $argde = '';

            }
            else
            {

                    $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcode3'")->row();

                    $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                    $argde = $aregid->idgaresdest;
            }
            
            if($quart3 == NULL)
            {
                $sargde = '';
            }
            else
            {

                $quart3 = $this->input->post('quartpassesn');

                $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcode3'")->row();

                $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                $sargde = $sousgar_id->idsousgare;
            }

            if($this->input->post('passcontactbagsansn') != NULL AND $this->input->post('cprclientbagsansn') != NULL AND $this->input->post('progcodbagsansn') != NULL AND $this->input->post('siegebagasansn') != NULL AND $this->input->post('buscodebagsansn') != NULL AND $this->input->post('codetickbagsansn') != NULL AND $this->input->post('types_bagsansn') != NULL AND $this->input->post('naturebagagesansn') != NULL AND $this->input->post('nombrebagsansn') != NULL)
            {
                $nbrs = $this->input->post('nombrebagsansn');

                if($nbrs === '' OR $nbrs === '0' OR $nbrs === '-1'){

                    $nbres = '1';

                }else
                {
                    $nbres = $this->input->post('nombrebagsansn');;
                }

                
                if($bagepson)
                {
                      
                    $argupgab = array(
                        'id_bagage' => $codecptr,
                        'idoperabagage' => (int)$iduser,
                        'lgidbagage' => $gid. '-' .$arcode3,
                        'clientbag' => (int)$this->input->post('cprclientbagsansn'),
                        'idgarebag' => $gid,
                        'idsgarebag' => (int)$sgid,
                        'gidarrbag' => $argde,
                        'sgidarrbag' => (int)$sargde,
                        'quartarr_bg' => $this->input->post('quartpassesn'),
                        'codebag' => $this->input->post('lgcodetickbagsansntr'),
                        'contactexpedi' => $this->input->post('passcontactbagsansn'),
                        'genrebagage' => 'save',
                        'typebagages' => $chrn,
                        'nombrebagage' => (int)$nbres,
                        'contenubagage' => $this->input->post('naturebagagesansn'),
                        'valeurbagage' => (double)$this->input->post('valeurbagagesansn'),
                        'transistbag' => 'pas_transit',
                        'date_create' => mdate("%Y-%m-%d", now('UTC')),
                    );

                    if($arcode3 != NULL AND $lgcd != NULL AND $gid != $argde)
                    {
                        $this->m_bagage->create($argupgab);
                        
                    
                        if($this->session->agent->userole === '1' OR $this->session->agent->userole === '12' OR $this->session->agent->userole === '10')
                        {
                            redirect('Historique_Passagers/bagnfsave/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                        }else{

                            redirect('Historique_Passagers/bagsavenfguich/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                        }

                    }else{
                        redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                    }
                }

                
            }        
                        
            else
            {
                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
        }
        
        ///enregistrement bagage envoi

        public function savebagsuivi($ckey){

            $this->company = $this->m_entreprises->get_key($ckey);

            $gid = $this->input->post('gareconnectbag');
            $sgid = $this->input->post('sousgareconnectbag');
            $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnect', 'userconnectedbag');
            if ($msg = compte_arret_guard_sale('bagage', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }

            $today = mdate("%Y-%m-%d", now('UTC'));
            $imprimeepson = $this->input->post('epsonbag');
            $idcmpt = $this->input->post('compconnectedbag');
            $usen = substr($this->session->agent->username, 0, 1);


            $garec = $this->db->query("SELECT gr.codegares FROM gares gr WHERE gr.idengare = '$gid'")->row();

            $nugd = $garec->codegares;

                /*$lettre = $garec->codegares;

                $numeo = ord($lettre) - 64;
                $nugd = $numeo;*/

            $ches = $this->input->post('types_bagage');

            $chrnf = implode(",",$ches);

            $depgd = strpos($this->input->post('deparcourrierbag'), '/');
            $reg = substr($this->input->post('deparcourrierbag'), 0, $depgd);
            $dpgdp = substr($this->input->post('deparcourrierbag'), $depgd + 1, strlen($this->input->post('deparcourrierbag')));

            $depgd1 = strpos($dpgdp, '/');
            $reg1 = substr($dpgdp, 0, $depgd1);
            $dpgdp1 = substr($dpgdp, $depgd1 + 1, strlen($dpgdp));

            $argd = strpos($this->input->post('lignebag'), '/');
            $arreg = substr($this->input->post('lignebag'), 0, $argd);
            $argdp = substr($this->input->post('lignebag'), $argd + 1, strlen($this->input->post('lignebag')));
                
            $argd1 = strpos($argdp, '/');
            $arreg2 = substr($argdp, 0, $argd1);
            $argdp3 = substr($argdp, $argd1 + 1, strlen($argdp));

            $arcod1 = strpos($arreg, '-');
            $arcod2 = substr($arreg, 0, $arcod1);

            $arcod3 = substr($arreg, $arcod1 + 1, strlen($arreg));

            $arecomp = $this->db->query("SELECT d.id_compaga FROM gare_dest d WHERE d.code_gadest = '$arreg2'")->row();

                $cid = (int)$this->session->company->ekey;

                $cd = (int)$arecomp->id_compaga;               


            $quart3 = $this->input->post('quartconfirmebag');
           
            $rcl = $this->input->post('cprclientbag');
            $rcp = $this->input->post('cpprclientbag');
            $tycl = $this->input->post('typemobbag');

            $aenc = date("y");

            $annee = date("Y");

            $pascompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM
                     bagages b WHERE b.idoperabagage = '$iduser' AND YEAR(b.lastbag_update) = '$annee'")->row();


            $codecptr = $idcmpt.$nugd.($pascompt->id + 1) . "-" . $aenc;

            if($this->input->post('lignebag') != NULL AND $this->input->post('expclient_contactbag') != NULL AND $this->input->post('rclient_contactbag') != NULL AND $this->input->post('types_bagage') != NULL AND $this->input->post('naturebagage') != NULL AND $this->input->post('nombrebag') != NULL AND $this->input->post('fraisbag') != NULL)
            {
                    $nbrs = $this->input->post('nombrebag');

                    if($nbrs === '' OR $nbrs === '0' OR $nbrs === '-1'){

                        $nbres = '1';

                    }else
                    {
                        $nbres = $this->input->post('nombrebag');;
                    }
                    

                    if($this->input->post('clientcompbag') != '' AND $this->input->post('rclientbag') != '' AND $this->input->post('prclientbag') != '')
                    {
                        $argen = array(
                            'nom_client' => $this->input->post('rclientbag'),
                            'prenom_client' => $this->input->post('prclientbag'),
                            'contact_client' => $this->input->post('rclient_contactbag'),
                            'date_delivre' => mdate("%Y/%m/%d", now('UTC')),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        
                        $this->m_client->update((int)$this->input->post('clientcompbag'), $argen);

                
                            $today = mdate("%Y-%m-%d", now('UTC'));
                                            
                                           
                            $passecompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM bagages b WHERE b.date_create = '$today' AND b.idoperabagage = '$iduser'")->row();
                                
                                    
                                   if($arcod3 == NULL)
                                    {

                                        $argde = '';

                                    }
                                    else
                                    {

                                            $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                                            $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                                            $argde = $aregid->idgaresdest;
                                    }
                                    
                                        $derniercrn = $this->db->query("SELECT b.couleurcarnet FROM bagages b
                                            INNER JOIN lignes lg ON b.lgidbagage = lg.ident_ligne 
                                            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                            WHERE e.ekey = '$cid'
                                            AND dest.id_compaga = '$cd'
                                            AND b.idgarebag = '$gid'
                                            AND b.date_create = '$today'
                                            ORDER BY b.lastbag_update DESC LIMIT 1")->row();

                                    if($quart3 == NULL)
                                    {
                                        $sargde = '';
                                    }
                                    else
                                    {

                                        $quart3;

                                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                                            AND sg.nomsousgare = '$quart3'")->row();

                                        $sargde = $sousgar_id->idsousgare;

                                    }

                                    $cdbag = mdate("%m%d", now('UTC')).($passecompt->id + 1).$gid.$sgid.$iduser.$usen;

                                            $tr = 'pas_transit';

                                            $cid = (int)$this->session->company->ekey;
                                            $cd = (int)$arecomp->id_compaga;

                                            $argupgab = array(
                                                'id_bagage' => $codecptr,
                                                'idoperabagage' => (int)$iduser,
                                                'lgidbagage' => $arreg,
                                                'clientbag' => (int)$this->input->post('clientcompbag'),
                                                'idgarebag' => $reg,
                                                'idsgarebag' => (int)$reg1,
                                                'gidarrbag' => $argde,
                                                'sgidarrbag' => (int)$sargde,
                                                'quartarr_bg' => $quart3,
                                                'codebag' => (string) $cdbag,
                                                'contactexpedi' => $this->input->post('expclient_contactbag'),
                                                'genrebagage' => 'suivi',
                                                'typebagages' => $chrnf,
                                                'nombrebagage' => (int)$nbres,
                                                'contenubagage' => $this->input->post('naturebagage'),
                                                'valeurbagage' => (double)$this->input->post('valeurbagage'),
                                                'prix_bagage' => (double)$this->input->post('fraisbag'),
                                                'transistbag' => $tr,
                                                'date_create' => mdate("%Y-%m-%d", now('UTC')),
                                            );
                                    

                                            $this->m_bagage->create($argupgab);

                                           
                                            if($derniercrn == NULL)
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                                            }

                                            else
                                            {
                                                if($derniercrn->couleurcarnet == 'A')
                                                {
                                                                
                                                    $this->db->query("UPDATE bagages SET couleurcarnet = 'B' WHERE id_bagage = '$codecptr'");
                                                }
                                                elseif($derniercrn->couleurcarnet == 'B')
                                                {
                                                                
                                                    $this->db->query("UPDATE bagages SET couleurcarnet = 'C' WHERE id_bagage = '$codecptr'");
                                                }
                                                elseif ($derniercrn->couleurcarnet == 'C')
                                                {
                                                                
                                                    $this->db->query("UPDATE bagages SET couleurcarnet = 'D' WHERE id_bagage = '$codecptr'");
                                                }
                                                elseif ($derniercrn->couleurcarnet == 'D')
                                                {
                                                                
                                                    $this->db->query("UPDATE bagages SET couleurcarnet = 'E' WHERE id_bagage = '$codecptr'");
                                                }

                                                else
                                                {
                                                                
                                                    $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                                                }

            
                                            }
                                                
                                            if($imprimeepson)
                                            {
                                                
                                                
                                                if($this->session->agent->userole === '1' OR $this->session->agent->userole === '12' OR $this->session->agent->userole === '10')
                                                {
                                                    redirect('Historique_Passagers/spdfepsonbagsuivi/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                                                }else{

                                                    redirect('Historique_Passagers/spdfepsonbagsuivig/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                                                }

                                            }
                                    
                    }
                    if($rcl != $this->input->post('rclientbag') AND $rcp != $this->input->post('prclientbag'))
                    {
                            $argen = array(
                                'type_client' => 'Adulte',
                                'nom_client' => $this->input->post('rclientbag'),
                                'prenom_client' => $this->input->post('prclientbag'),
                                'contact_client' => $this->input->post('rclient_contactbag'),
                                'date_delivre' => mdate("%Y/%m/%d", now('UTC')),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $cld = $this->m_client->create($argen);

                    
                            $today = mdate("%Y-%m-%d", now('UTC'));
                                            
                                           
                            $passecompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM bagages b WHERE b.date_create = '$today' AND b.idoperabagage = '$iduser'")->row();
                                
                                    
                                   if($arcod3 == NULL)
                                    {

                                        $argde = '';

                                    }
                                    else
                                    {

                                            $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                                            $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                                            $argde = $aregid->idgaresdest;
                                    }
                                    
                                    if($quart3 == NULL)
                                    {
                                        $sargde = '';
                                    }
                                    else
                                    {

                                        $quart3;
                                        
                                       
                                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                                            AND sg.nomsousgare = '$quart3'")->row();

                                        $sargde = $sousgar_id->idsousgare;
                                    }

                                    $derniercrn = $this->db->query("SELECT b.couleurcarnet FROM bagages b
                                        INNER JOIN lignes lg ON b.lgidbagage = lg.ident_ligne 
                                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                        WHERE e.ekey = '$cid'
                                        AND dest.id_compaga = '$cd'
                                        AND b.idgarebag = '$gid'
                                        AND b.date_create = '$today'
                                        ORDER BY b.lastbag_update DESC LIMIT 1")->row();
                                    
                                    $cdbag = mdate("%m%d", now('UTC')).($passecompt->id + 1).$gid.$sgid.$iduser.$usen;

                                   
                                        $tr = 'pas_transit';

                                        $argupgab = array(
                                            'id_bagage' => $codecptr,
                                            'idoperabagage' => (int)$iduser,
                                            'lgidbagage' => $arreg,
                                            'clientbag' => $cld,
                                            'idgarebag' => $reg,
                                            'idsgarebag' => (int)$reg1,
                                            'gidarrbag' => $argde,
                                            'sgidarrbag' => (int)$sargde,
                                            'quartarr_bg' => $quart3,
                                            'codebag' => (string) $cdbag,
                                            'contactexpedi' => $this->input->post('expclient_contactbag'),
                                            'genrebagage' => 'suivi',
                                            'typebagages' => $chrnf,
                                            'nombrebagage' => (int)$nbres,
                                            'contenubagage' => $this->input->post('naturebagage'),
                                            'valeurbagage' => (double)$this->input->post('valeurbagage'),
                                            'prix_bagage' => (double)$this->input->post('fraisbag'),
                                            'transistbag' => $tr,
                                            'date_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                

                                        $this->m_bagage->create($argupgab);
                                       

                                        if ($derniercrn == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                                        }

                                        else
                                        {
                                            if ($derniercrn->couleurcarnet == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'B' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif ($derniercrn->couleurcarnet == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'C' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif ($derniercrn->couleurcarnet == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'D' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif ($derniercrn->couleurcarnet == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'E' WHERE id_bagage = '$codecptr'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                                            }
                                        }

                                        
                                        if($imprimeepson)
                                        {
                                            
                                            
                                            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '12' OR $this->session->agent->userole === '10')
                                            {
                                                redirect('Historique_Passagers/spdfepsonbagsuivi/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                                            }else{

                                                redirect('Historique_Passagers/spdfepsonbagsuivig/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                                           }

                                        }        
                    } 
            }
            else
            {
                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
        }

        public function autresave($ckey){

            $this->company = $this->m_entreprises->get_key($ckey);
            $today = mdate("%Y-%m-%d", now('UTC'));
            $imprimeepson = $this->input->post('auepson');
            $gid = $this->input->post('augareconnect');
            $sgid = $this->input->post('ausousgareconnect');
            $idcmpt = $this->input->post('aucompconnected');
            $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnect', 'auuserconnected');
            $usen = substr($this->session->agent->username, 0, 1);

            $garec = $this->db->query("SELECT gr.codegares FROM gares gr WHERE gr.idengare = '$gid'")->row();

            $nugd = $garec->codegares;

                /*$lettre = $garec->codegares;

                $numeo = ord($lettre) - 64;
                $nugd = $numeo;*/
                
            $ches = $this->input->post('autypes_bagsans');

            $chrnf = implode(",",$ches);

            $depgd = strpos($this->input->post('audepargare'), '/');
            $reg = substr($this->input->post('audepargare'), 0, $depgd);
            $dpgdp = substr($this->input->post('audepargare'), $depgd + 1, strlen($this->input->post('audepargare')));

            $depgd1 = strpos($dpgdp, '/');
            $reg1 = substr($dpgdp, 0, $depgd1);
            $dpgdp1 = substr($dpgdp, $depgd1 + 1, strlen($dpgdp));

            $argd = strpos($this->input->post('auaxeconfirme'), '/');
            $arreg = substr($this->input->post('auaxeconfirme'), 0, $argd);
            $argdp = substr($this->input->post('auaxeconfirme'), $argd + 1, strlen($this->input->post('auaxeconfirme')));
                
            $argd1 = strpos($argdp, '/');
            $arreg2 = substr($argdp, 0, $argd1);
            $argdp3 = substr($argdp, $argd1 + 1, strlen($argdp));

            $arcod1 = strpos($arreg, '-');
            $arcod2 = substr($arreg, 0, $arcod1);

            $arcod3 = substr($arreg, $arcod1 + 1, strlen($arreg));

            $arecomp = $this->db->query("SELECT d.id_compaga FROM gare_dest d WHERE d.code_gadest = '$arreg2'")->row();


            $quart3 = $this->input->post('auquartconfirmebag');

            $rcl = $this->input->post('aucppasnompconf');
            $rcp = $this->input->post('aucppasprenompconf');


            if($this->input->post('auaxeconfirme') != NULL AND $this->input->post('aurcfclient_contact') != NULL AND $this->input->post('autypes_bagsans') != NULL AND $this->input->post('aunaturebagagesans') != NULL AND $this->input->post('aunombrebagsans') != NULL AND $this->input->post('aufraisbagsans') != NULL)
            {
                    $nbrs = $this->input->post('aunombrebagsans');

                    if($nbrs === '' OR $nbrs === '0' OR $nbrs === '-1'){

                        $nbres = '1';

                    }else
                    {
                        $nbres = $this->input->post('aunombrebagsans');;
                    }
                    

                    if($this->input->post('auclientconfirme') != '' AND $rcl === $this->input->post('aurcfclient') AND $rcp === $this->input->post('auprcfclient'))
                    {
                        $argen = array(
                            'nom_client' => $this->input->post('aurcfclient'),
                            'prenom_client' => $this->input->post('auprcfclient'),
                            'contact_client' => $this->input->post('aurcfclient_contact'),
                            'num_CNIB' => $this->input->post('cnibcf'),
                            'date_delivre' => $this->input->post('aucfdate_cnib'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        
                        $this->m_client->update((int)$this->input->post('auclientconfirme'), $argen);

                    
                            $today = mdate("%Y-%m-%d", now('UTC'));
                                            
                            $aenc = date("y");

                            $annee = date("Y");


                            $pascompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM
                                bagages b WHERE b.idoperabagage = '$iduser' AND YEAR(b.lastbag_update) = '$annee'")->row();

                            $codecptr = $idcmpt.$nugd.($pascompt->id + 1)."-" . $aenc;

                            $passecompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM bagages b WHERE b.date_create = '$today' AND b.idoperabagage = '$iduser'")->row();
                                
                                    
                                   if($arcod3 == NULL)
                                    {
                                        $argde = '';
                                    }
                                    else
                                    {

                                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                                        $argde = $aregid->idgaresdest;
                                    }
                                    
                                    if($quart3 == NULL)
                                    {
                                        $sargde = '';
                                    }
                                    else
                                    {
                                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                                            AND sg.nomsousgare = '$quart3'")->row();

                                        $sargde = $sousgar_id->idsousgare;
                                    }

                                    $cdbag = mdate("%m%d", now('UTC')).($passecompt->id + 1).$gid.$sgid.$iduser.$usen;

                                    
                                        $tr = 'pas_transit';

                                        $cid = (int)$this->session->company->ekey;
                                        $cd = (int)$arecomp->id_compaga;
                                        
                                        $derniercrn = $this->db->query("SELECT b.couleurcarnet FROM bagages b
                                            INNER JOIN lignes lg ON b.lgidbagage = lg.ident_ligne 
                                            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                            WHERE e.ekey = '$cid'
                                            AND dest.id_compaga = '$cd'
                                            AND b.idgarebag = '$gid'
                                            AND b.date_create = '$today'
                                            ORDER BY b.lastbag_update DESC LIMIT 1")->row();

                                        $argupgab = array(
                                            'id_bagage' => $codecptr,
                                            'idoperabagage' => (int)$iduser,
                                            'lgidbagage' => $arreg,
                                            'clientbag' => (int)$this->input->post('auclientconfirme'),
                                            'idgarebag' => $reg,
                                            'idsgarebag' => (int)$reg1,
                                            'gidarrbag' => $argde,
                                            'sgidarrbag' => (int)$sargde,
                                            'quartarr_bg' => $quart3,
                                            'codebag' => (string) $cdbag,
                                            'contactexpedi' => $this->input->post('aurcfclient_contact'),
                                            'genrebagage' => 'sans_suivi',
                                            'typebagages' => $chrnf,
                                            'nombrebagage' => (int)$nbres,
                                            'contenubagage' => $this->input->post('aunaturebagagesans'),
                                            'valeurbagage' => (double)$this->input->post('auvaleurbagagesans'),
                                            'prix_bagage' => (double)$this->input->post('aufraisbagsans'),
                                            'transistbag' => $tr,
                                            'date_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                

                                        $this->m_bagage->create($argupgab);

                                        if($derniercrn == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                                        }

                                        else
                                        {
                                            if($derniercrn->couleurcarnet == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'B' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif($derniercrn->couleurcarnet == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'C' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif ($derniercrn->couleurcarnet == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'D' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif ($derniercrn->couleurcarnet == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'E' WHERE id_bagage = '$codecptr'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                                            }
                                        }
                                        if($imprimeepson)
                                        {
                                            
                                           if($this->session->agent->userole === '1' OR $this->session->agent->userole === '12' OR $this->session->agent->userole === '10')
                                            {
                                                //redirect('Historique_Passagers/pdfepsonbagsuivitrans/'.$this->session->company->ekey.'/'.$idbag.'/'.$idbag1.'/'.$gid.'/'.$reg.'/'.$iduser.'/'.$sgid);

                                                redirect('Historique_Passagers/saupdfepsonbagsuivi/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                                            }else{

                                                //redirect('Historique_Passagers/pdfepsonbagsuivitransg/'.$this->session->company->ekey.'/'.$idbag.'/'.$idbag1.'/'.$gid.'/'.$reg.'/'.$iduser.'/'.$sgid);
                                                redirect('Historique_Passagers/saupdfepsonbagsuivig/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                                            }
                                        }   
                    }
                    if($rcl != $this->input->post('aurcfclient') AND $rcp != $this->input->post('auprcfclient'))
                    {
                            $argen = array(
                                'type_client' => 'Adulte',
                                'nom_client' => $this->input->post('aurcfclient'),
                                'prenom_client' => $this->input->post('auprcfclient'),
                                'contact_client' => $this->input->post('aurcfclient_contact'),
                                'num_CNIB' => $this->input->post('cnibcf'),
                                'date_delivre' => $this->input->post('aucfdate_cnib'),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );
                            
                            $cld = $this->m_client->create($argen);

                    
                            $today = mdate("%Y-%m-%d", now('UTC'));
                                            
                            $aenc = date("y");

                            $annee = date("Y");


                            $pascompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM
                                bagages b WHERE b.idoperabagage = '$iduser' AND YEAR(b.lastbag_update) = '$annee'")->row();

                            $codecptr = $idcmpt.$nugd.($pascompt->id + 1)."-".$aenc;               
                            $passecompt = $this->db->query("SELECT COUNT(b.id_bagage) AS id FROM bagages b WHERE b.date_create = '$today' AND b.idoperabagage = '$iduser'")->row();
                                
                                    
                                   if($arcod3 == NULL)
                                    {
                                        $argde = '';
                                    }
                                    else
                                    {

                                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                                        $argde = $aregid->idgaresdest;
                                    }
                                    
                                    if($quart3 == NULL)
                                    {
                                        $sargde = '';
                                    }
                                    else
                                    {
                                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                                            AND sg.nomsousgare = '$quart3'")->row();

                                        $sargde = $sousgar_id->idsousgare;
                                    }

                                    $derniercrn = $this->db->query("SELECT b.couleurcarnet FROM bagages b
                                    INNER JOIN lignes lg ON b.lgidbagage = lg.ident_ligne 
                                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                    WHERE e.ekey = '$cid'
                                    AND dest.id_compaga = '$cd'
                                    AND b.idgarebag = '$gid'
                                    AND b.date_create = '$today'
                                    ORDER BY b.lastbag_update DESC LIMIT 1")->row();
                                    
                                    $cdbag = mdate("%m%d", now('UTC')).($passecompt->id + 1).$gid.$sgid.$iduser.$usen;

                                    
                                        $tr = 'pas_transit';

                                        $argupgab = array(
                                            'id_bagage' => $codecptr,
                                            'idoperabagage' => (int)$iduser,
                                            'lgidbagage' => $arreg,
                                            'clientbag' => $cld,
                                            'idgarebag' => $reg,
                                            'idsgarebag' => (int)$reg1,
                                            'gidarrbag' => $argde,
                                            'sgidarrbag' => (int)$sargde,
                                            'quartarr_bg' => $quart3,
                                            'codebag' => (string) $cdbag,
                                            'contactexpedi' => $this->input->post('aurcfclient_contact'),
                                            'genrebagage' => 'sans_suivi',
                                            'typebagages' => $chrnf,
                                            'nombrebagage' => (int)$nbres,
                                            'contenubagage' => $this->input->post('aunaturebagagesans'),
                                            'valeurbagage' => (double)$this->input->post('auvaleurbagagesans'),
                                            'prix_bagage' => (double)$this->input->post('aufraisbagsans'),
                                            'transistbag' => $tr,
                                            'date_create' => mdate("%Y-%m-%d", now('UTC')),
                                        );
                                

                                        $this->m_bagage->create($argupgab);
                                       
                                       
                                        if ($derniercrn == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                                        }

                                        else
                                        {
                                            if ($derniercrn->couleurcarnet == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'B' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif ($derniercrn->couleurcarnet == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'C' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif ($derniercrn->couleurcarnet == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'D' WHERE id_bagage = '$codecptr'");
                                            }
                                            elseif ($derniercrn->couleurcarnet == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'E' WHERE id_bagage = '$codecptr'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE bagages SET couleurcarnet = 'A' WHERE id_bagage = '$codecptr'");
                                            }
                                        }
                                        
                                        if($imprimeepson)
                                        {
                                            
                                            if($this->session->agent->userole === '1' OR $this->session->agent->userole === '12' OR $this->session->agent->userole === '10')
                                            {
                                                redirect('Historique_Passagers/saupdfepsonbagsuivi/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                                            }else{

                                                redirect('Historique_Passagers/saupdfepsonbagsuivig/'.$this->session->company->ekey.'/'.$codecptr.'/'.$gid.'/'.$iduser.'/'.$sgid);
                                            }

                                        }
                    }
            }
            else
            {
                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
        }
        

        public function retour($ckey)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                $gid = $this->input->post('retgareconnects');
                $sgid = $this->input->post('retsousgareconnect');
                $idcmpt = $this->input->post('retcompconnected');
                $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnect', 'retuserconnecteds');

                $usrets= $this->input->post('usrets');
                $retsgds = $this->input->post('retsgds');

                $cid = $this->session->company->ekey;

                $cd = $this->input->post('compcd');
                $reg = $this->input->post('retgareconnects');

                $usen = substr($this->session->agent->username, 0, 1);
                $today = mdate("%Y-%m-%d", now('UTC'));

                $retimprimeepson = $this->input->post('retepson');

                if($this->input->post('retcodeclient') != NULL AND $this->input->post('retnomprenomcl') != NULL)
                {
                    if($retimprimeepson)
                    { 
                            $compnp = $this->db->query("SELECT COUNT(code_non_pass) AS id FROM non_passager np WHERE np.datevente = '$today'")->row();
                            $idemt = 'N';

                            $cdnpas = mdate("%m%d", now('UTC')).$idemt.($compnp->id + 1).$usen.$iduser; 
                            $derniernp = $this->db->query("SELECT np.verifnonpassager FROM non_passager np 
                            JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne 
                            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                            WHERE e.ekey = '$cid'
                            AND dest.id_compaga ='$cd'
                            AND ex.code_gaexp = '$reg'
                            AND np.datevente = '$today' ORDER BY datenp_create DESC LIMIT 1")->row();

                            $tpcdpas1 = $this->input->post('retcodetickets');
                            $tpcdpas = $this->input->post('retcodeclient');
                            
                            $nonarray = array(
                                'code_non_pass' => $this->input->post('retcodetickets'),
                                'codeticket' => $cdnpas,
                                'cptus' => $usrets,
                                'sousgareidentif' => $retsgds,
                                'id_client_npass' => $this->input->post('retcles'),
                                'id_ligne_pass' => $this->input->post('retligneids'),
                                'nom_ligne' => $this->input->post('retnomlignes'),
                                'prixretour' => $this->input->post('retprixvents'),
                                'datevente' => mdate("%Y/%m/%d", now('UTC')),
                                'creatednp_at' => now('UTC'),
                            );

                            $nonclid = $this->m_non_passager->create($nonarray);


                                if ($derniernp == NULL)
                                {
                                                
                                    $this->db->query("UPDATE non_passager SET verifnonpassager = 'A' WHERE code_non_pass = '$tpcdpas1' AND codeticket = '$cdnpas'");
                                }
                                else
                                {
                                    if ($derniernp->verifnonpassager == 'A')
                                    {
                                                    
                                        $this->db->query("UPDATE non_passager SET verifnonpassager = 'B' WHERE code_non_pass = '$tpcdpas1' AND codeticket = '$cdnpas'");
                                    }
                                    elseif ($derniernp->verifnonpassager == 'B')
                                    {
                                                    
                                        $this->db->query("UPDATE non_passager SET verifnonpassager = 'C' WHERE code_non_pass = '$tpcdpas1' AND codeticket = '$cdnpas'");
                                    }
                                    elseif($derniernp->verifnonpassager == 'C')
                                    {
                                        $this->db->query("UPDATE non_passager SET verifnonpassager = 'D' WHERE code_non_pass = '$tpcdpas1' AND codeticket = '$cdnpas'");
                                    }
                                    elseif($derniernp->verifnonpassager == 'D')
                                    {
                                        $this->db->query("UPDATE non_passager SET verifnonpassager = 'E' WHERE code_non_pass = '$tpcdpas1' AND codeticket = '$cdnpas'");
                                    }
                                    else
                                    {
                                        $this->db->query("UPDATE non_passager SET verifnonpassager = 'A' WHERE code_non_pass = '$tpcdpas1' AND codeticket = '$cdnpas'");
                                    }
                                }

                                redirect('Historique_Passagers/epretour/' . $this->session->company->ekey . '/' . $tpcdpas1.'/'.$gid.'/'.$iduser. '/'.$sgid);   
                                
                    }
                }

                else
                {
                    redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
                }
                
        }

        public function savebagesc($ckey){

            $this->company = $this->m_entreprises->get_key($ckey);

            $gid = $this->input->post('gareconnectescalbag');
            $sgid = $this->input->post('sousgareconnectescalbag');
            $iduser = roleattribut_guard_post_hint($this->company->ekey, 'gareconnect', 'userconnectedescalbag');
            if ($msg = compte_arret_guard_sale('bagage', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }

            $today = mdate("%Y-%m-%d", now('UTC'));

            $bagepsonesc = $this->input->post('epsonbagsansesc');
            $idcmpt = $this->input->post('compconnectedescalbag');

            $ch = $this->input->post('types_bagsansesc');

            $chr = implode(",",$ch);

            $quart3 = $this->input->post('quartpassesesc');
            
            $arcod1 = strpos($this->input->post('lignedepaescalbag'), '-');
            $arcod2 = substr($this->input->post('lignedepaescalbag'), 0, $arcod1);

            $arcod3 = substr($this->input->post('lignedepaescalbag'), $arcod1 + 1, strlen($this->input->post('lignedepaescalbag')));

            $arecomp = $this->db->query("SELECT d.id_compaga FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();
            
            $cid = (int)$this->session->company->ekey;

            $cd = (int)$arecomp->id_compaga;

            if($arcod3 == NULL)
            {

                $argde = '';

            }
            else
            {

                    $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                    $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                    $argde = $aregid->idgaresdest;
            }
            
            if($quart3 == NULL)
            {
                $sargde = '';
            }

            else
            {

                $quart3 = $this->input->post('quartpassesesc');

                $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arcod3'")->row();

                $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                $sargde = $sousgar_id->idsousgare;
            }

            if($this->input->post('passcontactbagsansescbg') != NULL AND $this->input->post('nclientescalbag') != NULL AND $this->input->post('cpprclientescalbag') != NULL AND $this->input->post('idlgeheurescalbag') != NULL  AND $this->input->post('codeticketbagsesc') != NULL AND $this->input->post('types_bagsansesc') != NULL AND $this->input->post('naturebagagesansesc') != NULL AND $this->input->post('nombrebagsansesc') != NULL AND $this->input->post('fraisbagsansesc') != NULL)
            {
                $nbrs = $this->input->post('nombrebagsansesc');

                if($nbrs === '' OR $nbrs === '0' OR $nbrs === '-1'){

                    $nbres = '1';

                }else
                {
                    $nbres = $this->input->post('nombrebagsansesc');
                }

                $derniercrnesc = $this->db->query("SELECT be.couleurcarnetesc FROM bagagesesc be
                    JOIN ligne_heure lh ON be.id_lgeheuresc = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND dest.id_compaga ='$cd'
                    AND ex.code_gaexp = '$gid'
                    ORDER BY be.lastbag_updateesc DESC LIMIT 1")->row();
                
                
                if($bagepsonesc)
                {
                    $argupgabesc = array(
                        'idoperabagageesc' => $iduser,
                        'id_lgeheuresc' => $this->input->post('idlgeheurescalbag'),
                        'clientbagesc' => $this->input->post('cprclientescalbag'),
                        'idgarebagesc' => $gid,
                        'idsgarebagesc' => $sgid,
                        'gidarrbagesc' => $argde,
                        'sgidarrbagesc' => $sargde,
                        'codebagesc' => $this->input->post('codeticketbagsesc'),
                        'contactexpediesc' => $this->input->post('passcontactbagsansescbg'),
                        'genrebagageesc' => 'sans_suivi',
                        'typebagagesesc' => $chr,
                        'nombrebagageesc' => $nbres,
                        'contenubagageesc' => $this->input->post('naturebagagesansesc'),
                        'valeurbagageesc' => $this->input->post('valeurbagagesansesc'),
                        'prix_bagageesc' => $this->input->post('fraisbagsansesc'),
                        'transistbagesc' => 'pas_transit',
                        'date_createesc' => mdate("%Y-%m-%d", now('UTC')),
                    );

                    $idbagesc = $this->m_bagageesc->create($argupgabesc);

                    if ($derniercrnesc == NULL)
                    {
                                    
                        $this->db->query("UPDATE bagagesesc SET couleurcarnetesc = 'A' WHERE id_bagageesc = '$idbagesc' AND prix_bagageesc != '0.00'");
                    }

                    else
                    {
                        if ($derniercrnesc->couleurcarnetesc == 'A')
                        {
                                        
                            $this->db->query("UPDATE bagagesesc SET couleurcarnetesc = 'B' WHERE id_bagageesc = '$idbagesc' AND prix_bagageesc != '0.00'");
                        }
                        elseif ($derniercrnesc->couleurcarnetesc == 'B')
                        {
                                        
                            $this->db->query("UPDATE bagagesesc SET couleurcarnetesc = 'C' WHERE id_bagageesc = '$idbagesc' AND prix_bagageesc != '0.00'");
                        }
                        elseif ($derniercrnesc->couleurcarnetesc == 'C')
                        {
                                        
                            $this->db->query("UPDATE bagagesesc SET couleurcarnetesc = 'D' WHERE id_bagageesc = '$idbagesc' AND prix_bagageesc != '0.00'");
                        }
                        elseif ($derniercrnesc->couleurcarnetesc == 'D')
                        {
                                        
                            $this->db->query("UPDATE bagagesesc SET couleurcarnetesc = 'E' WHERE id_bagageesc = '$idbagesc' AND prix_bagageesc != '0.00'");
                        }

                        else
                        {
                                        
                            $this->db->query("UPDATE bagagesesc SET couleurcarnetesc = 'A' WHERE id_bagageesc = '$idbagesc' AND prix_bagageesc != '0.00'");
                        }
                    }

                    redirect('Historique_Passagers/pdfepsonbagesc/'.$this->session->company->ekey.'/'.$idbagesc.'/'.$gid.'/'.$iduser.'/'.$sgid);
                }
                
            }              
            else
            {
                
                redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
        }


        public function addordesc($ckey)
        {
            $cid = $this->session->company->ekey;

            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnect');
            $sgid = $this->input->post('sousgareconnect');
            $gid = $this->input->post('gareattribuer');
            if ($msg = compte_arret_guard_sale('courrier', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }

            $imprimepson = $this->input->post('epsonesc');
            $idcmpt = $this->input->post('compconnected');
            $usen = substr($this->session->agent->username, 0, 1);

            if($this->input->post('heuredpcouresc') != NULL AND $this->input->post('quartconfirmeesc') != NULL AND $this->input->post('datedepartesc') != NULL AND $this->input->post('types_couresc') != NULL AND $this->input->post('naturecolesc') != NULL OR $this->input->post('persopassdestesc') != NULL OR $this->input->post('compagniepassdestesc') != NULL)
            {
                $nbrs = $this->input->post('nombrecolesc');

                if($nbrs === '' OR $nbrs === '0' OR $nbrs === '-1'){

                    $nbres = '1';

                }else
                {
                    $nbres = $this->input->post('nombrecolesc');
                }

                
                $depgd = strpos($this->input->post('deparcourrieresc'), '/');
                $reg = substr($this->input->post('deparcourrieresc'), 0, $depgd);
                $dpgdp = substr($this->input->post('deparcourrieresc'), $depgd + 1, strlen($this->input->post('deparcourrieresc')));

                $depgd1 = strpos($dpgdp, '/');
                $reg1 = substr($dpgdp, 0, $depgd1);
                $dpgdp1 = substr($dpgdp, $depgd1 + 1, strlen($dpgdp));

                
                $argd = strpos($this->input->post('arricouresc'), '/');
                $arreg = substr($this->input->post('arricouresc'), 0, $argd);
                $argdp = substr($this->input->post('arricouresc'), $argd + 1, strlen($this->input->post('arricouresc')));
                
                $argd1 = strpos($argdp, '/');
                $arreg2 = substr($argdp, 0, $argd1);
                $argdp3 = substr($argdp, $argd1 + 1, strlen($argdp));

                $arecomp = $this->db->query("SELECT d.id_compaga FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                $cd = $arecomp->id_compaga;
                
                $typecl = strpos($this->input->post('type_persoesc'), '/');
                $typeclid = substr($this->input->post('type_persoesc'), 0, $typecl);
                $idtypecl = substr($this->input->post('type_persoesc'), $typecl + 1, strlen($this->input->post('type_persoesc')));
                
                $natu = strpos($this->input->post('types_couresc'), '/');
                $natid = substr($this->input->post('types_couresc'), 0, $natu);
                $nat = substr($this->input->post('types_couresc'), $natu + 1, strlen($this->input->post('types_couresc')));
                
                $natu1 = strpos($nat, '/');
                $natid1 = substr($nat, 0, $natu1);
                $nat1 = substr($nat, $natu1 + 1, strlen($nat));

                $quart1 = strpos($this->input->post('quartconfirmeesc'), '/');
                $quart2 = substr($this->input->post('quartconfirmeesc'), 0, $quart1);
                $quart3 = substr($this->input->post('quartconfirmeesc'), $quart1 + 1, strlen($this->input->post('quartconfirmeesc')));
               

                $rcl = $this->input->post('cprclientexpesc');
                $rcp = $this->input->post('cpprclientexpesc');
                $rcn = $this->input->post('cpcnibexpesc');
                $rcd = $this->input->post('cpdate_cnibexpesc');
                $rl = $this->input->post('cplieudelivrexpesc');
                $tycl = $this->input->post('clientypeexpesc');
                
                $rcldest = $this->input->post('cprclientdestesc');
                $rcpdest = $this->input->post('cpprclientdestesc');
                
                $tycldest = $this->input->post('clientypedestesc');
                $typcldest = $this->input->post('typeclientsesc');


                    if($this->input->post('clientpasscompesc') != '' AND $rcl === $this->input->post('nomexpesc') AND $rcp === $this->input->post('prenomexpesc') AND $rcn === $this->input->post('cnibesc') AND $tycl === $idtypecl AND $rcd === $this->input->post('date_cnibesc') AND $rl === $this->input->post('lieuetabesc') AND $this->input->post('clientcompassdestesc') != '' AND $rcldest === $this->input->post('nomdestesc') AND $rcpdest === $this->input->post('prenomdestesc'))
                    {
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                            $this->m_client->update($this->input->post('clientpasscompesc'), $argex);

                            $argc = array(
                                'clientexpedit' => $this->input->post('clientpasscompesc'),
                                'dateexpedition' => $this->input->post('datedepartesc'),
                            );
                            
                            $exp = $this->m_expediteur->create($argc);
                      
                            $argd = array(
                                'nom_client' => $this->input->post('nomdestesc'),
                                'type_client' => $this->input->post('typeclientsesc'),
                                'prenom_client' => $this->input->post('prenomdestesc'),
                                'contact_client' => $this->input->post('contact_destesc'),
                                'date_delivre' => $this->input->post('date_cnibdestesc'),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $this->m_client->update($this->input->post('clientcompassdestesc'), $argd);

                            
                            $argd1 = array(
                                'client_recept' => $this->input->post('clientcompassdestesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);

                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                       
                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                ORDER BY dateemis_recuesc DESC LIMIT 1")->row();
                        
                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');

                                        
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }
                                        if($imprimepson)
                                        {
                                            redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid);
                                        }

                                     
                            
                                
                    }

                    
                    if($this->input->post('clientpasscompesc') === '' AND $rcl != $this->input->post('nomexpesc') AND $rcp != $this->input->post('prenomexpesc') AND $rcn != $this->input->post('cnibesc') AND $tycl != $idtypecl AND $rcd != $this->input->post('date_cnibesc') AND $rl != $this->input->post('lieuetabesc') AND $this->input->post('clientcompassdestesc') === '' AND $rcldest != $this->input->post('nomdestesc') AND $rcpdest != $this->input->post('prenomdestesc'))
                    {


                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        $clcour = $this->m_client->create($argex);

                        $argc = array(
                            'clientexpedit' => $clcour,
                            'dateexpedition' => $this->input->post('datedepartesc'),
                        );
                            
                            $exp = $this->m_expediteur->create($argc);

                        $argd = array(
                            'nom_client' => $this->input->post('nomdestesc'),
                            'type_client' => $this->input->post('typeclientsesc'),
                            'prenom_client' => $this->input->post('prenomdestesc'),
                            'contact_client' => $this->input->post('contact_destesc'),
                            'date_delivre' => $this->input->post('date_cnibdestesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );

                            
                            $cldest = $this->m_client->create($argd);

                            
                            $argd1 = array(
                                'client_recept' => $cldest,
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);

                        if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();


                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid' 
                                ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }
                            
                                        if($imprimepson)
                                        {
                                            redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                        }

                                
                                
                    }

                    if($this->input->post('clientpasscompesc') != '' AND $rcl === $this->input->post('nomexpesc') AND $rcp === $this->input->post('prenomexpesc') AND $rcn === $this->input->post('cnibesc') AND $tycl === $idtypecl AND $rcd === $this->input->post('date_cnibesc') AND $rl === $this->input->post('lieuetabesc') AND $this->input->post('clientcompassdestesc') === '' AND $rcldest != $this->input->post('nomdestesc') AND $rcpdest != $this->input->post('prenomdestesc')) 
                    {
                        
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                            $this->m_client->update($this->input->post('clientpasscompesc'), $argex);

                            $argc = array(
                                'clientexpedit' => $this->input->post('clientpasscompesc'),
                                'dateexpedition' => $this->input->post('datedepartesc'),
                            );
                            
                            $exp = $this->m_expediteur->create($argc);
                            
                            $argd = array(
                            'nom_client' => $this->input->post('nomdestesc'),
                            'type_client' => $this->input->post('typeclientsesc'),
                            'prenom_client' => $this->input->post('prenomdestesc'),
                            'contact_client' => $this->input->post('contact_destesc'),
                            'date_delivre' => $this->input->post('date_cnibdestesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );

                            
                            $cldest = $this->m_client->create($argd);

                            
                            $argd1 = array(
                                'client_recept' => $cldest,
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);
                      
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpid) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();


                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        
                                    if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }

                            
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }

                                   
                               
                                
                    }
                    
                    if($this->input->post('clientpasscompesc') === '' AND $rcl != $this->input->post('nomexpesc') AND $rcp != $this->input->post('prenomexpesc') AND $rcn != $this->input->post('cnibesc') AND $tycl != $idtypecl AND $rcd != $this->input->post('date_cnibesc') AND $rl != $this->input->post('lieuetabesc') AND $this->input->post('clientcompassdestesc') != '' AND $rcldest === $this->input->post('nomdestesc') AND $rcpdest === $this->input->post('prenomdestesc'))
                    {
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        $clcour = $this->m_client->create($argex);

                        $argc = array(
                            'clientexpedit' => $clcour,
                            'dateexpedition' => $this->input->post('datedepartesc'),
                        );
                            
                            $exp = $this->m_expediteur->create($argc);

                            $argd = array(
                                'nom_client' => $this->input->post('nomdestesc'),
                                'type_client' => $this->input->post('typeclientsesc'),
                                'prenom_client' => $this->input->post('prenomdestesc'),
                                'contact_client' => $this->input->post('contact_destesc'),
                                'date_delivre' => $this->input->post('date_cnibdestesc'),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $this->m_client->update($this->input->post('clientcompassdestesc'), $argd);

                            
                            $argd1 = array(
                                'client_recept' => $this->input->post('clientcompassdestesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();


                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;
                                
                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                    if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }
                            
                                        if($imprimepson)
                                        {
                                            redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                        }

                                
                                
                    }



                    if($this->input->post('clientpasscompesc') != '' AND $this->input->post('persopassdestesc') != '') 
                    {
                        
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                            $this->m_client->update($this->input->post('clientpasscompesc'), $argex);

                            $argc = array(
                                'clientexpedit' => $this->input->post('clientpasscompesc'),
                                'dateexpedition' => $this->input->post('datedepartesc'),
                            );
                            
                            $exp = $this->m_expediteur->create($argc);
                            
                            
                            
                            $argd1 = array(
                                'persorecep' => $this->input->post('persopassdestesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);
                      
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();


                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                
                               
                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }

                            
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }

                                
                    }

                    if($this->input->post('clientpasscompesc') === '' AND $rcl != $this->input->post('nomexpesc') AND $rcp != $this->input->post('prenomexpesc') AND $rcn != $this->input->post('cnibesc') AND $tycl != $idtypecl AND $rcd != $this->input->post('date_cnibesc') AND $rl != $this->input->post('lieuetabesc') AND $this->input->post('persopassdestesc') != '')
                    {
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        $clcour = $this->m_client->create($argex);

                        $argc = array(
                            'clientexpedit' => $clcour,
                            'dateexpedition' => $this->input->post('datedepartesc'),
                        );
                            
                            $exp = $this->m_expediteur->create($argc);

                            $argd1 = array(
                                'persorecep' => $this->input->post('persopassdestesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);                         
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                        

                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;

                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;

                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }

                            
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }

                                
                                
                    }

                    if($this->input->post('clientpasscompesc') != '' AND $this->input->post('clientcompassdestesc') != '')
                    {
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                            $this->m_client->update($this->input->post('clientpasscompesc'), $argex);

                            $argc = array(
                                'clientexpedit' => $this->input->post('clientpasscompesc'),
                                'dateexpedition' => $this->input->post('datedepartesc'),
                            );
                            
                            $exp = $this->m_expediteur->create($argc);
                      
                            $argd = array(
                                'nom_client' => $this->input->post('nomdestesc'),
                                'type_client' => $this->input->post('typeclientsesc'),
                                'prenom_client' => $this->input->post('prenomdestesc'),
                                'contact_client' => $this->input->post('contact_destesc'),
                                'date_delivre' => $this->input->post('date_cnibdestesc'),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $this->m_client->update($this->input->post('clientcompassdestesc'), $argd);

                            
                            $argd1 = array(
                                'client_recept' => $this->input->post('clientcompassdestesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);

                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();


                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }

                            
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }

                                
                    }
                    if($this->input->post('clientpasscompesc') === '' AND $this->input->post('persopassdestesc') != '')
                    {
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        $clcour = $this->m_client->create($argex);

                        $argc = array(
                            'clientexpedit' => $clcour,
                            'dateexpedition' => $this->input->post('datedepartesc'),
                        );
                            
                            $exp = $this->m_expediteur->create($argc);

                            $argd1 = array(
                                'persorecep' => $this->input->post('persopassdestesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);                         
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();


                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                            
                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }
                                        if($imprimepson)
                                        {
                                            redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                        }

                            
                    }
                    
                    if($this->input->post('clientpasscompesc') != '' AND $this->input->post('clientcompassdestesc') === '') 
                    {
                        
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                            $this->m_client->update($this->input->post('clientpasscompesc'), $argex);

                            $argc = array(
                                'clientexpedit' => $this->input->post('clientpasscompesc'),
                                'dateexpedition' => $this->input->post('datedepartesc'),
                            );
                            
                            $exp = $this->m_expediteur->create($argc);
                            
                            $argd = array(
                            'nom_client' => $this->input->post('nomdestesc'),
                            'type_client' => $this->input->post('typeclientsesc'),
                            'prenom_client' => $this->input->post('prenomdestesc'),
                            'contact_client' => $this->input->post('contact_destesc'),
                            'date_delivre' => $this->input->post('date_cnibdestesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );

                            
                            $cldest = $this->m_client->create($argd);

                            
                            $argd1 = array(
                                'client_recept' => $cldest,
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);
                      
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1esc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                        

                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }

                                        if($imprimepson)
                                        {
                                            redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                        }

                                
                    }


                    if($this->input->post('clientpasscompesc') === '' AND $this->input->post('clientcompassdestesc') === '')
                    {
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        $clcour = $this->m_client->create($argex);

                        $argc = array(
                            'clientexpedit' => $clcour,
                            'dateexpedition' => $this->input->post('datedepartesc'),
                        );
                            
                            $exp = $this->m_expediteur->create($argc);

                        $argd = array(
                            'nom_client' => $this->input->post('nomdestesc'),
                            'type_client' => $this->input->post('typeclientsesc'),
                            'prenom_client' => $this->input->post('prenomdestesc'),
                            'contact_client' => $this->input->post('contact_destesc'),
                            'date_delivre' => mdate("%Y/%m/%d", now('UTC')),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );

                            
                            $cldest = $this->m_client->create($argd);

                            
                            $argd1 = array(
                                'client_recept' => $cldest,
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);

                        if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                        

                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                               $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }
                                        if($imprimepson)
                                        {
                                            redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                        }

                                
                    }
                
                    if($this->input->post('clientpasscompesc') === '' AND $this->input->post('clientcompassdestesc') != '')
                    {
                        $argex = array(
                            'nom_client' => $this->input->post('nomexpesc'),
                            'type_client' => $idtypecl,
                            'prenom_client' => $this->input->post('prenomexpesc'),
                            'contact_client' => $this->input->post('contact_expesc'),
                            'num_CNIB' => $this->input->post('cnibesc'),
                            'date_delivre' => $this->input->post('date_cnibesc'),
                            'lieu_delivre' => $this->input->post('lieuetabesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );
                        $clcour = $this->m_client->create($argex);

                        $argc = array(
                            'clientexpedit' => $clcour,
                            'dateexpedition' => $this->input->post('datedepartesc'),
                        );
                            
                            $exp = $this->m_expediteur->create($argc);

                            $argd = array(
                                'nom_client' => $this->input->post('nomdestesc'),
                                'type_client' => $this->input->post('typeclientsesc'),
                                'prenom_client' => $this->input->post('prenomdestesc'),
                                'contact_client' => $this->input->post('contact_destesc'),
                                'date_delivre' => $this->input->post('date_cnibdestesc'),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $this->m_client->update($this->input->post('clientcompassdestesc'), $argd);

                            
                            $argd1 = array(
                                'client_recept' => $this->input->post('clientcompassdestesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbres,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                        

                        $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest'
                            AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                
                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcouresc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcouresc');
                                        if ($derniercr == NULL)
                                        {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }
                                        if($imprimepson)
                                        {
                                            redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                        }
                                
                    }

            }
            else
            {
               redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            
        }

        public function addpersoesc($ckey)
        {
            
            $this->company = $this->m_entreprises->get_key($ckey);
            $iduser = $this->input->post('userconnectperso');
            $sgid = $this->input->post('sousgareconnectperso');
            $gid = $this->input->post('gareattribuerperso');
            if ($msg = compte_arret_guard_sale('courrier', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }

            $imprimepson = $this->input->post('epsonesc');
            $idcmpt = $this->input->post('compconnectedperso');
            $usen = substr($this->session->agent->username, 0, 1);

            if($this->input->post('heuredpcourpersoesc') != NULL AND $this->input->post('quartconfirmepersoesc') != NULL AND $this->input->post('datedepartpersoesc') != NULL AND $this->input->post('types_courpersoesc') != NULL AND $this->input->post('naturecolpersoesc') != NULL OR $this->input->post('persopassdestpersoesc') != NULL OR $this->input->post('compagniepassdestpersoesc') != NULL)
            {

                $nbrsp = $this->input->post('nombrecolpersoesc');

                if($nbrsp === '' OR $nbrsp === '0' OR $nbrsp === '-1'){

                    $nbresp = '1';

                }else
                {
                    $nbresp = $this->input->post('nombrecolpersoesc');
                }
                
                
                $depgd = strpos($this->input->post('deparcourrierpersoesc'), '/');
                $reg = substr($this->input->post('deparcourrierpersoesc'), 0, $depgd);
                $dpgdp = substr($this->input->post('deparcourrierpersoesc'), $depgd + 1, strlen($this->input->post('deparcourrierpersoesc')));

                $depgd1 = strpos($dpgdp, '/');
                $reg1 = substr($dpgdp, 0, $depgd1);
                $dpgdp1 = substr($dpgdp, $depgd1 + 1, strlen($dpgdp));

                
                $argd = strpos($this->input->post('arricourpersoesc'), '/');
                $arreg = substr($this->input->post('arricourpersoesc'), 0, $argd);
                $argdp = substr($this->input->post('arricourpersoesc'), $argd + 1, strlen($this->input->post('arricourpersoesc')));
                
                $argd1 = strpos($argdp, '/');
                $arreg2 = substr($argdp, 0, $argd1);
                $argdp3 = substr($argdp, $argd1 + 1, strlen($argdp));

                
                $typecl = strpos($this->input->post('type_persopersoesc'), '/');
                $typeclid = substr($this->input->post('type_persopersoesc'), 0, $typecl);
                $idtypecl = substr($this->input->post('type_persopersoesc'), $typecl + 1, strlen($this->input->post('type_persopersoesc')));
                
                $natu = strpos($this->input->post('types_courpersoesc'), '/');
                $natid = substr($this->input->post('types_courpersoesc'), 0, $natu);
                $nat = substr($this->input->post('types_courpersoesc'), $natu + 1, strlen($this->input->post('types_courpersoesc')));
                
                $natu1 = strpos($nat, '/');
                $natid1 = substr($nat, 0, $natu1);
                $nat1 = substr($nat, $natu1 + 1, strlen($nat));

                $quart1 = strpos($this->input->post('quartconfirmepersoesc'), '/');
                $quart2 = substr($this->input->post('quartconfirmepersoesc'), 0, $quart1);
                $quart3 = substr($this->input->post('quartconfirmepersoesc'), $quart1 + 1, strlen($this->input->post('quartconfirmepersoesc')));
                
               
                $rcl = $this->input->post('cprclientexppersoesc');
                $rcp = $this->input->post('cpprclientexppersoesc');
                $rcn = $this->input->post('cpcnibexppersoesc');
                $rcd = $this->input->post('cpdate_cnibexppersoesc');
                $rl = $this->input->post('cplieudelivrexppersoesc');
                $tycl = $this->input->post('clientypeexppersoesc');
                
                $rcldest = $this->input->post('cprclientdestpersoesc');
                $rcpdest = $this->input->post('cpprclientdestpersoesc');
                
                $tyctdest = $this->input->post('clientcontpersoesc');
                $typcldest = $this->input->post('typeclientspersoesc');


                    if($this->input->post('persopasscomppersoesc') != '' AND $this->input->post('persopassdestpersoesc') != '')
                    {
                        $argc = array(
                            'persoexp' => $this->input->post('persopasscomppersoesc'),
                            'dateexpedition' => $this->input->post('datedepartpersoesc'),
                        );
                        
                        $exp = $this->m_expediteur->create($argc);

                        $argd = array(
                            'persorecep' => $this->input->post('persopassdestpersoesc'),
                        );
                        
                        $dest = $this->m_recepteur->create($argd);

                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1persoesc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbresp,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();

                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                       
                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;


                                  $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcourpersoesc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexpersoesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolpersoesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                            
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcourpersoesc');
                                
                                    
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }

                    }
                    
                    if($this->input->post('persopasscomppersoesc') != '' AND $this->input->post('clientcompassdestpersoesc') != '')
                    {
                        $argc = array(
                            'persoexp' => $this->input->post('persopasscomppersoesc'),
                            'dateexpedition' => $this->input->post('datedepartpersoesc'),
                        );
                        
                        $exp = $this->m_expediteur->create($argc);

                            
                            $argd1 = array(
                                'client_recept' => $this->input->post('clientcompassdestpersoesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1persoesc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbresp,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                       
                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcourpersoesc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexpersoesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolpersoesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcourpersoesc');
                                
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }

                    }

                    if($this->input->post('persopasscomppersoesc') != '' AND $this->input->post('persopassdestpartoesc') != '')
                    {
                        $argc = array(
                            'persoexp' => $this->input->post('persopasscomppersoesc'),
                            'dateexpedition' => $this->input->post('datedepartpersoesc'),
                        );
                        
                        $exp = $this->m_expediteur->create($argc);

                            $argd = array(
                                'nom_client' => $this->input->post('nomdestpersoesc'),
                                'type_client' => $this->input->post('typeclientspersoesc'),
                                'prenom_client' => $this->input->post('prenomdestpersoesc'),
                                'contact_client' => $this->input->post('contact_destpersoesc'),
                                'date_delivre' => $this->input->post('date_cnibdestpersoesc'),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $this->m_client->update($this->input->post('clientcompassdestpersoesc'), $argd);

                            
                            $argd1 = array(
                                'client_recept' => $this->input->post('clientcompassdestpersoesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1persoesc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbresp,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                            
                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcourpersoesc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexpersoesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolpersoesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcourpersoesc');
                                    
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }

                    }

                    //personnel adulte
                    if($this->input->post('persopasscomppersoesc') != '' AND $this->input->post('clientcompassdestpersoesc') === '')
                    {


                        $argc = array(
                            'persoexp' => $this->input->post('persopasscomppersoesc'),
                            'dateexpedition' => $this->input->post('datedepartpersoesc'),
                        );
                        
                        $exp = $this->m_expediteur->create($argc);


                        $argd = array(
                            'nom_client' => $this->input->post('nomdestpersoesc'),
                            'type_client' => $this->input->post('typeclientspersoesc'),
                            'prenom_client' => $this->input->post('prenomdestpersoesc'),
                            'contact_client' => $this->input->post('contact_destpersoesc'),
                            'date_delivre' => $this->input->post('date_cnibdestpersoesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );

                            
                            $cldest = $this->m_client->create($argd);

                            
                            $argd1 = array(
                                'client_recept' => $cldest,
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);

                        if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1persoesc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbresp,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                            
                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();


                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;


                                
                                    $tr = 'pas_transit';

                                    $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcourpersoesc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexpersoesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolpersoesc'),
                                        'statuscourrieresc' => $tr,                     
                                        'dateenvoicour_atesc' => now('UTC'),
                                    
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);
                                    
                                    $cdcourdp = $this->input->post('heuredpcourpersoesc');
                                    
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }

                                                    
                    }

            }
            else
            {
               redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));

            }
            
        }

        public function addpartoesc($ckey)
        {
            
            $this->company = $this->m_entreprises->get_key($ckey);

            $cid = $this->session->company->ekey;

            $iduser = $this->input->post('userconnectparto');
            $sgid = $this->input->post('sousgareconnectparto');
            $gid = $this->input->post('gareattribuerparto');
            if ($msg = compte_arret_guard_sale('courrier', $iduser, $gid)) {
                compte_arret_redirect_guichet($iduser, $gid, $sgid, $msg);
                return;
            }

            $imprimepson = $this->input->post('epsonesc');
            $idcmpt = $this->input->post('compconnectedparto');
            $usen = substr($this->session->agent->username, 0, 1);

            if($this->input->post('heuredpcourpartoesc') != NULL AND $this->input->post('quartconfirmepartoesc') != NULL AND $this->input->post('datedepartpartoesc') != NULL AND $this->input->post('types_courpartoesc') != NULL AND $this->input->post('naturecolpartoesc') != NULL OR $this->input->post('persopassdestpartoesc') != NULL OR $this->input->post('compagniepassdestpartoesc') != NULL)
            
            {

                $nbrspa = $this->input->post('nombrecolpartoesc');

                if($nbrspa === '' OR $nbrspa === '0' OR $nbrspa === '-1'){

                    $nbrespa = '1';

                }else
                {
                    $nbrespa = $this->input->post('nombrecolpartoesc');
                }
                
                $depgd = strpos($this->input->post('deparcourrierpartoesc'), '/');
                $reg = substr($this->input->post('deparcourrierpartoesc'), 0, $depgd);
                $dpgdp = substr($this->input->post('deparcourrierpartoesc'), $depgd + 1, strlen($this->input->post('deparcourrierpartoesc')));

                $depgd1 = strpos($dpgdp, '/');
                $reg1 = substr($dpgdp, 0, $depgd1);
                $dpgdp1 = substr($dpgdp, $depgd1 + 1, strlen($dpgdp));

                
                $argd = strpos($this->input->post('arricourpartoesc'), '/');
                $arreg = substr($this->input->post('arricourpartoesc'), 0, $argd);
                $argdp = substr($this->input->post('arricourpartoesc'), $argd + 1, strlen($this->input->post('arricourpartoesc')));
                
                $argd1 = strpos($argdp, '/');
                $arreg2 = substr($argdp, 0, $argd1);
                $argdp3 = substr($argdp, $argd1 + 1, strlen($argdp));

                $arecomp = $this->db->query("SELECT d.id_compaga FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                $cd = $arecomp->id_compaga;
                
                $typecl = strpos($this->input->post('type_persopartoesc'), '/');
                $typeclid = substr($this->input->post('type_persopartoesc'), 0, $typecl);
                $idtypecl = substr($this->input->post('type_persopartoesc'), $typecl + 1, strlen($this->input->post('type_persopartoesc')));
                
                $natu = strpos($this->input->post('types_courpartoesc'), '/');
                $natid = substr($this->input->post('types_courpartoesc'), 0, $natu);
                $nat = substr($this->input->post('types_courpartoesc'), $natu + 1, strlen($this->input->post('types_courpartoesc')));
                
                $natu1 = strpos($nat, '/');
                $natid1 = substr($nat, 0, $natu1);
                $nat1 = substr($nat, $natu1 + 1, strlen($nat));

                $quart1 = strpos($this->input->post('quartconfirmepartoesc'), '/');
                $quart2 = substr($this->input->post('quartconfirmepartoesc'), 0, $quart1);
                $quart3 = substr($this->input->post('quartconfirmepartoesc'), $quart1 + 1, strlen($this->input->post('quartconfirmepartoesc')));
            

                $rcl = $this->input->post('cprclientexppartoesc');
                $rcp = $this->input->post('cpprclientexppartoesc');
                $rcn = $this->input->post('cpcnibexppartoesc');
                $rcd = $this->input->post('cpdate_cnibexpparto');
                $rl = $this->input->post('cplieudelivrexppartoesc');
                $tycl = $this->input->post('clientypeexppartoesc');
                
                $rcldest = $this->input->post('cprclientdestpartoesc');
                $rcpdest = $this->input->post('cpprclientdestpartoesc');
                
                $tyctdest = $this->input->post('clientcontdestpartoesc');
                $typcldest = $this->input->post('typeclientspartoesc');
                
                if($this->input->post('nompartenairepartoesc') != '' AND $this->input->post('typepartespartoesc') != ''){
                        $argc = array(
                            'clientexpedit' => $this->input->post('clientpasscomppartoesc'),
                            'dateexpedition' => $this->input->post('datedepartpartoesc'),
                        );
                        
                        $exp = $this->m_expediteur->create($argc);

                        $argd = array(
                            'client_recept' => $this->input->post('clientcompassdestpartoesc'),
                        );
                        
                        $dest = $this->m_recepteur->create($argd);

                        if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1partoesc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbrespa,
                        
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();



                         $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();
                        
                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();


                            $argare_ar = $quartar->codegares;
                            $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                            $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                
                                    $tr = 'pas_transit';

                                     $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcourpartoesc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexpartoesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolpartoesc'),
                                        'statuscourrieresc' => $tr,
                                        'partocouresc' => $this->input->post('statenvoipartoesc'),                   
                                        'dateenvoicour_atesc' => now('UTC'),
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcourpartoesc');
                                    if ($derniercr == NULL)
                                    {
                                                        
                                            $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                        }

                                        else
                                        {
                                            if ($derniercr->verifcouresc == 'A')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'B')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'C')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                            elseif ($derniercr->verifcouresc == 'D')
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }

                                            else
                                            {
                                                            
                                                $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                            }
                                        }
                                    if($imprimepson)
                                    {
                                        redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                    }
                }

                if($this->input->post('nompartenairepartoesc') != '' AND $this->input->post('clientcompassdestpartoesc') === '')
                {

                        $argc = array(
                            'clientexpedit' => $this->input->post('clientpasscomppartoesc'),
                            'dateexpedition' => $this->input->post('datedepartpartoesc'),
                        );
                        
                        $exp = $this->m_expediteur->create($argc);


                        $argd = array(
                            'nom_client' => $this->input->post('nomdestpartoesc'),
                            'type_client' => $this->input->post('typeclientspartoesc'),
                            'prenom_client' => $this->input->post('prenomdestpartoesc'),
                            'contact_client' => $this->input->post('contact_destpartoesc'),
                            'date_delivre' => $this->input->post('date_cnibdestpartoesc'),
                            'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                        );

                            
                            $cldest = $this->m_client->create($argd);

                            
                            $argd1 = array(
                                'client_recept' => $cldest,
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);

                        if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1partoesc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbrespa,
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();

                       
                         $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();
                        
                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();


                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;
                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                     $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcourpartoesc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexpartoesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolpartoesc'),
                                        'statuscourrieresc' => $tr,
                                        'partocouresc' => $this->input->post('statenvoipartoesc'),                   
                                        'dateenvoicour_atesc' => now('UTC'),
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcourpartoesc');
                                    
                                if ($derniercr == NULL)
                                {
                                                
                                    $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                }

                                else
                                {
                                    if ($derniercr->verifcouresc == 'A')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'B')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'C')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'D')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }

                                    else
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                }
                            if($imprimepson)
                            {
                                redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                            }

                }

                   
                if($this->input->post('nompartenairepartoesc') != '' AND $this->input->post('clientcompassdestpartoesc') != '')
                {
                        $argc = array(
                            'clientexpedit' => $this->input->post('clientpasscomppartoesc'),
                            'dateexpedition' => $this->input->post('datedepartpartoesc'),
                        );
                        
                        $exp = $this->m_expediteur->create($argc);

                            $argd = array(
                                'nom_client' => $this->input->post('nomdestpartoesc'),
                                'prenom_client' => $this->input->post('prenomdestpartoesc'),
                                'contact_client' => $this->input->post('contact_destpartoesc'),
                                'date_delivre' => $this->input->post('date_cnibdestpartoesc'),
                                'datedoc' => mdate("%Y/%m/%d", now('UTC')),
                            );

                            $this->m_client->update($this->input->post('clientcompassdestpartoesc'), $argd);

                            
                            $argd1 = array(
                                'client_recept' => $this->input->post('clientcompassdestpartoesc'),
                            );
                            
                            $dest = $this->m_recepteur->create($argd1);
                            if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1partoesc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbrespa,
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();


                         $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();
                        
                        $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                        $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                        
                        $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();

                            $argare_ar = $quartar->codegares;

                        $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                        $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                                    $tr = 'pas_transit';

                                     $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcourpartoesc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexpartoesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolpartoesc'),
                                        'statuscourrieresc' => $tr,
                                        'partocouresc' => $this->input->post('statenvoipartoesc'),                   
                                        'dateenvoicour_atesc' => now('UTC'), 
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);
                                    $cdcourdp = $this->input->post('heuredpcourpartoesc');
                                if ($derniercr == NULL)
                                {
                                                
                                    $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                }

                                else
                                {
                                    if ($derniercr->verifcouresc == 'A')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'B')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'C')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'D')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }

                                    else
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                }
                                if($imprimepson)
                                {
                                    redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                }
                }

                if($this->input->post('nompartenairepartoesc') != '' AND $this->input->post('persopassdestpartoesc') != '')
                {
                        $argc = array(
                            'clientexpedit' => $this->input->post('clientpasscomppartoesc'),
                            'dateexpedition' => $this->input->post('datedepartpartoesc'),
                        );
                        
                        $exp = $this->m_expediteur->create($argc);

                        $argd = array(
                            'persorecep' => $this->input->post('persopassdestpartoesc'),
                        );
                        
                        $dest = $this->m_recepteur->create($argd);

                        if ($exp != NULL AND $dest != NULL) 

                        //insertion des données dans la table courriers
                        $coarray = array(
                            'expditid' => $exp,
                            'receptid' => $dest, 
                        );
                        $colis = $this->m_expedition_reception->create($coarray);
                        
                        // Get the auto generated CODE
                        $carray = array(
                            'idlignes' => $reg.'-'.$arreg,
                            'exprecepident' => $colis,
                            'valeurscoli'=> $this->input->post('valeur1partoesc'),
                            'naturecoli' => $natid1,
                            'nombrecolis' => $nbrespa,
                        );
                        $codecol = $this->m_code_courrier->create($carray);

                        $today = mdate("%Y-%m-%d", now('UTC'));
                        
                       
                        $passecompter = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today' AND xp.idoperateuresc = '$iduser'")->row();
                        $passecompt = $this->db->query("SELECT COUNT(courrierexpidesc) AS id FROM courriers_expesc xp WHERE xp.dateenvoiesc = '$today'")->row();


                             $derniercr = $this->db->query("SELECT xp.verifcouresc FROM courriers_expesc xp
                                JOIN ligne_heure lh ON xp.departcolisesc = lh.id_ligneheure
                                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                WHERE e.ekey = '$cid'
                                AND dest.id_compaga ='$cd'
                                AND ex.code_gaexp = '$gid'
                                 ORDER BY dateemis_recuesc DESC LIMIT 1")->row();

                            $aregid = $this->db->query("SELECT d.idgaresdest FROM gare_dest d WHERE d.code_gadest = '$arreg'")->row();

                            $quartar = $this->db->query("SELECT g.codegares FROM gares g WHERE g.idengare = '$aregid->idgaresdest'")->row();

                            $sousgar_id = $this->db->query("SELECT sg.idsousgare FROM sousgare sg WHERE sg.gareprinceid = '$aregid->idgaresdest' AND sg.nomsousgare = '$quart3'")->row();

                                $argare_ar = $quartar->codegares;

                            $codcour = $argdp3.$dpgdp1.mdate("%y%m%d", now('UTC')).($passecompter->id + 1).$argare_ar.$quart2.$gid.$usen.$iduser.$nat1;
                            $cdcour = $dpgdp1.mdate("%m%d", now('UTC')).($passecompt->id + 1).$argare_ar.$quart2.$nat1;

                            
                                    $tr = 'pas_transit';

                                     $arraycours = array(
                                        'courrierexpidesc' => $codcour,
                                        'num_couresc' => $cdcour,
                                        'departcolisesc' => $this->input->post('heuredpcourpartoesc'),
                                        'id_codecourrieresc' => $codecol,
                                        'idoperateuresc' => $iduser,
                                        'courrierdepartgareesc' => $reg1,
                                        'quartier_courrieresc' => $quart3,
                                        'garearrivecolisesc'=> $aregid->idgaresdest,
                                        'sousgarearrividesc' => $sousgar_id->idsousgare,
                                        'dateenvoiesc' => mdate("%Y-%m-%d", now('UTC')),
                                        'prixcolisesc' => $this->input->post('fraisexpartoesc'),
                                        'naturecourrieresc' => $this->input->post('naturecolpartoesc'),
                                        'statuscourrieresc' => $tr,
                                        'partocouresc' => $this->input->post('statenvoipartoesc'),                   
                                        'dateenvoicour_atesc' => now('UTC'),
                                    );
                                    
                                    $this->m_courrier_expedieresc->create($arraycours);

                                    $cdcourdp = $this->input->post('heuredpcourpartoesc');
                                if ($derniercr == NULL)
                                {
                                                
                                    $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                }

                                else
                                {
                                    if ($derniercr->verifcouresc == 'A')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'B' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'B')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'C' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'C')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'D' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                    elseif ($derniercr->verifcouresc == 'D')
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'E' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }

                                    else
                                    {
                                                    
                                        $this->db->query("UPDATE courriers_expesc SET verifcouresc = 'A' WHERE courrierexpidesc = '$codcour' AND num_couresc = '$cdcour' AND departcolisesc = '$cdcourdp' AND prixcolisesc != '0.00'");
                                    }
                                }
                                if($imprimepson)
                                {
                                    redirect('Historiquesescal/editpdfesc/'.$this->session->company->ekey.'/'.$codcour.'/'.$exp.'/'.$dest.'/'.$idtypecl.'/'.$typcldest.'/'. $gid.'/'. $iduser.'/'. $sgid); 
                                }
                }

            }
            else
            {
               redirect('gares/'.$this->session->company->ekey.'/gTc/'. $gid.'/compte/'. $iduser.'/'. $sgid.'/'. mdate("%d/%m/%Y", now('UTC')));
            }
            
        }
    }
    
    /** End of file: Reprogrammes.php **/
    /** File location: application/controllers/Reprogrammes.php **/
