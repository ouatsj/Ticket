<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Passager_model extends CI_Model
    {
        protected $table = 'passager';
        
        public function __construct()
        {
            parent::__construct();
        }
        private function normalize_ticket_prix_row($row)
        {
            return ticket_impression_prix_row($row);
        }

        private function normalize_ticket_prix_rows($rows)
        {
            return ticket_impression_prix_rows($rows);
        }

        /** Filtre tampon du jour (évite scan complet de passager ~2,5 M lignes). */
        private function _tampon_passager_jour_sql()
        {
            $today = mdate('%Y-%m-%d', now('UTC'));

            return "AND p.datep_create >= '{$today}' AND ctp.actif_tamp = 0";
        }

        
        /**
         * Colonne passager optionnelle (migration escale / OD final).
         */
        private function passager_column_exists($column)
        {
            static $cache = array();
            $column = (string) $column;
            if (!isset($cache[$column])) {
                $col = $this->db->escape_str($column);
                $q = $this->db->query("SHOW COLUMNS FROM passager LIKE '{$col}'");
                $cache[$column] = ($q && method_exists($q, 'num_rows') && $q->num_rows() > 0);
            }
            return (bool) $cache[$column];
        }

        /**
         * Expression SQL nom_ligne pour rapports caisse (compatible schéma partiel).
         *
         * @return array{select:string,group:string}
         */
        private function rapport_nom_ligne_sql()
        {
            $hasLigne = $this->passager_column_exists('lignetineraire_vendu');
            $hasNomDest = $this->passager_column_exists('nom_dest_vente');

            if ($hasLigne && $hasNomDest) {
                $expr = "COALESCE(NULLIF(TRIM(p.lignetineraire_vendu), ''), " .
                    "CASE WHEN p.nom_dest_vente IS NOT NULL AND TRIM(p.nom_dest_vente) <> '' " .
                    "THEN CONCAT(TRIM(ex.nom_gaep), '-', TRIM(p.nom_dest_vente)) " .
                    "ELSE lg.nom_ligne END)";
            } elseif ($hasLigne) {
                $expr = "COALESCE(NULLIF(TRIM(p.lignetineraire_vendu), ''), lg.nom_ligne)";
            } elseif ($hasNomDest) {
                $expr = "CASE WHEN p.nom_dest_vente IS NOT NULL AND TRIM(p.nom_dest_vente) <> '' " .
                    "THEN CONCAT(TRIM(ex.nom_gaep), '-', TRIM(p.nom_dest_vente)) ELSE lg.nom_ligne END";
            } else {
                $expr = 'lg.nom_ligne';
            }

            return array(
                'select' => "{$expr} AS nom_ligne",
                'group' => $expr,
            );
        }

        public function create(array $data)
        {
            $data = roleattribut_guard_apply_to_data($data, array('idcptuser'));
            $pricing = null;
            $CI =& get_instance();

            // Vente escale : persister destination partielle + prix escale (évite terminus parent).
            if (!isset($CI->sale_svc)) {
                $CI->load->library('sale_passager_service', null, 'sale_svc');
            }
            if (isset($CI->sale_svc) && method_exists($CI->sale_svc, 'enrich_passager_escale')) {
                $data = $CI->sale_svc->enrich_passager_escale($data);
            }
            $isEscaleSale = !empty($data['id_escale_vente']);

            if (!empty($data['code_pro']) && !empty($data['num_siege_categorie'])) {
                $ticketPre = isset($data['code_ticket']) ? (string) $data['code_ticket'] : '';
                if ($ticketPre !== 'R') {
                    if (!isset($CI->m_programme_reconduction)) {
                        $CI->load->model('Programme_reconduction_model', 'm_programme_reconduction');
                    }
                    if (!$CI->m_programme_reconduction->siege_vendable($data['code_pro'], $data['num_siege_categorie'])) {
                        return false;
                    }
                }
            }

            $isOtherSale = sales_price_controls_enabled()
                && isset($CI->router)
                && strtolower((string) $CI->router->fetch_method()) === 'addpassagerfi';

            if ($isOtherSale && isset($data['code_pro']) && array_key_exists('prixvente', $data)) {
                $pricing = sales_price_validate_or_fail(
                    $data['code_pro'],
                    $data['prixvente'],
                    array(
                        'reason' => $this->input->post('prix_libre_motif'),
                        'authorization_type' => $this->input->post('type_autorisation_prix'),
                        'card_number' => $this->input->post('numero_carte_voyage'),
                        'zero_confirmed' => $this->input->post('confirmation_zero') === '1',
                    )
                );
                $data['prixvente'] = $pricing['sold_price'];
            } elseif (
                !$isEscaleSale
                && isset($data['code_pro'])
                && array_key_exists('prixvente', $data)
                && function_exists('ticket_prix_depuis_programme')
            ) {
                // Vente normale : prix catalogue du programme.
                // Vente escale : prix déjà fixé via itineraire_escales.prix_escale.
                $data['prixvente'] = ticket_prix_depuis_programme($data['code_pro'], $data['prixvente']);
            } elseif (
                !sales_price_controls_enabled()
                && isset($CI->router)
                && strtolower((string) $CI->router->fetch_method()) === 'addpassagerfi'
                && array_key_exists('prixvente', $data)
            ) {
                // Prod / hors contrôles : AUTRES VENTE conserve le prix saisi.
                $freePrice = trim((string) $data['prixvente']);
                if ($freePrice === '' || !is_numeric($freePrice) || (float) $freePrice < 0) {
                    show_error('Le prix libre doit être un montant positif ou égal à zéro.', 400);
                    return 0;
                }
                $data['prixvente'] = round((float) $freePrice, 2);
            }

            $ok = $this->db->insert($this->table, $data);
            $insertId = $this->db->insert_id();
            if ($ok && $pricing) {
                sales_price_snapshot_record($data, $pricing);
            }
            if ($ok && !empty($data['code_pro'])) {
                $ticket = isset($data['code_ticket']) ? (string) $data['code_ticket'] : '';
                if ($ticket !== 'R') {
                    if (!isset($CI->m_programme_reconduction)) {
                        $CI->load->model('Programme_reconduction_model', 'm_programme_reconduction');
                    }
                    $CI->m_programme_reconduction->apres_vente($data['code_pro']);
                }
            }
            // Phase 1 : journal confirmation (création) — jamais bloquant.
            if ($ok) {
                $this->_historique_modif_ticket_safe_log('create', '', '', array(), $data);
            }
            return $insertId;
        }
        
        public function update($code_passager, $code_ticket, array $data)
        {
            $multiClause = array('code_passager' => $code_passager, 'code_ticket' => $code_ticket);

            $before = array();
            $type = null;
            if (function_exists('historique_modif_ticket_detect_write_type')) {
                $type = historique_modif_ticket_detect_write_type($data);
            }
            if ($type !== null && function_exists('historique_modif_ticket_row_passager')) {
                try {
                    $before = historique_modif_ticket_row_passager($this->db, $code_passager, $code_ticket);
                } catch (Throwable $e) {
                    $before = array();
                    if (function_exists('log_message')) {
                        log_message('error', 'Passager_model historique before: ' . $e->getMessage());
                    }
                }
            }

            $ok = $this->db->where($multiClause)->update($this->table, $data);

            if ($ok && $type !== null) {
                $this->_historique_modif_ticket_safe_log(
                    'update',
                    (string) $code_passager,
                    (string) $code_ticket,
                    is_array($before) ? $before : array(),
                    $data
                );
            }

            return $ok;
        }

        /**
         * Journal Phase 1 (reprog / confirmation) — isolé du flux métier.
         *
         * @param string $mode
         * @param string $code_passager
         * @param string $code_ticket
         * @param array $before
         * @param array $data
         * @return void
         */
        private function _historique_modif_ticket_safe_log($mode, $code_passager, $code_ticket, array $before, array $data)
        {
            try {
                if (!function_exists('historique_modif_ticket_detect_write_type')
                    || !function_exists('historique_modif_ticket_try_log_passager_write')
                ) {
                    return;
                }
                $type = historique_modif_ticket_detect_write_type($data);
                if ($type === null) {
                    return;
                }
                if ($code_passager === '' && isset($data['code_passager'])) {
                    $code_passager = (string) $data['code_passager'];
                }
                if ($code_ticket === '' && isset($data['code_ticket'])) {
                    $code_ticket = (string) $data['code_ticket'];
                }
                if ($code_passager === '') {
                    return;
                }
                historique_modif_ticket_try_log_passager_write(
                    $this->db,
                    $type,
                    $code_passager,
                    $code_ticket,
                    $before,
                    $data,
                    array(
                        'meta' => array('source' => 'passager_' . $mode),
                    )
                );
            } catch (Throwable $e) {
                if (function_exists('log_message')) {
                    log_message('error', 'Passager_model _historique_modif_ticket_safe_log: ' . $e->getMessage());
                }
            }
        }

        public function del($id, $idtick)
        {
            $multiClause = array('code_passager' => $id, 'code_ticket' => $idtick);
            return $this->db->where($multiClause)->delete($this->table);
        }

        public function get($cid, $p_id, $tf, $t = FALSE)
        {
            if ($t === FALSE) {
                $this->load->helper('app_cache');
                $today = mdate('%Y-%m-%d', now('UTC'));
                $cache_key = 'tampon_passagers_' . $cid . '_' . $today;

                return app_cache_remember($cache_key, 120, function () use ($cid) {
                    $jour = $this->_tampon_passager_jour_sql();
                    $rows = $this->db->query(
                        "SELECT * FROM passager p
                        JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
                        JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                        JOIN client cl ON p.id_client_pass = cl.id_client
                        JOIN type_client tcl ON cl.type_client = tcl.nom_type
                        JOIN programme pr ON p.code_pro = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                        JOIN tarifs t ON pr.typetarif = t.id_tarifs
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '{$cid}'
                        AND p.num_siege_categorie IS NOT NULL
                        AND h.h_active = 1
                        AND lh.actif_lh = 1
                        AND p.actif_pas = 0
                        {$jour}")->result();

                    return $this->normalize_ticket_prix_rows($rows);
                });
            } else
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod 
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND ctp.tamponcod = '$p_id'
                    AND lh.id_ligneheure = '$t'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND p.actif_pas = 0
                    AND t.id_tarifs = '$tf'")->row(); return $this->normalize_ticket_prix_row($row);
        }


        public function gettr($cid, $p_id)
        {
           
            $rows = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid 
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN entreprise e ON u.cle_comp = e.ekey
                WHERE e.ekey = '$cid'
                AND p.num_siege_categorie IS NOT NULL
                AND ctp.tamponcodtr = '$p_id'
                AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function grecu($cid, $cdtp)
        {
            $row = $this->db->query(
            "SELECT * FROM tamponcode ctp
            JOIN passager p ON p.code_passager = ctp.tamponcod 
            JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
            JOIN client cl ON p.id_client_pass = cl.id_client
            JOIN type_client tcl ON cl.type_client = tcl.nom_type
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN heures h ON lh.heure_identif = h.id_heure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
            JOIN tarifs t ON pr.typetarif = t.id_tarifs
            JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cid'
            AND p.num_siege_categorie IS NOT NULL
            AND ctp.tamponcod = '$cdtp'
            AND ctp.recuactif = 1")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function grecus($cid, $gd)
        {
            $rows = $this->db->query(
            "SELECT * FROM tamponcode ctp
            JOIN passager p ON p.code_passager = ctp.tamponcod 
            JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
            JOIN client cl ON p.id_client_pass = cl.id_client
            LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN heures h ON lh.heure_identif = h.id_heure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cid'
            AND ex.code_gaexp = '$gd'
            AND p.num_siege_categorie IS NOT NULL
            AND ctp.recuactif = 1")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        //verification code pour enregistrer bagages

        public function verifcodbag($cid, $cod, $gd, $sg)
        {
            $row = $this->db->query(
            "SELECT * FROM tamponcode ctp
            JOIN passager p ON p.code_passager = ctp.tamponcod 
            JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
            JOIN client cl ON p.id_client_pass = cl.id_client
            LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN heures h ON lh.heure_identif = h.id_heure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$cid'
            AND ex.code_gaexp = '$gd'
            AND BINARY p.code_ticket = '$cod'
            AND p.num_siege_categorie IS NOT NULL
            AND sg.idsousgare = '$sg'
            AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
        
        public function verifcodbagt($cid, $cod)
        {
            $cod = trim((string) $cod);
            if ($cod === '') {
                return array();
            }

            $this->load->helper('app_cache');
            $cache_key = 'verifcodbagt_' . $cid . '_' . md5($cod);

            return app_cache_remember($cache_key, 60, function () use ($cid, $cod) {
                $rows = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod 
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE ctp.tamponcodtr = " . $this->db->escape($cod) . "
                AND e.ekey = " . $this->db->escape($cid) . "
                AND p.num_siege_categorie IS NOT NULL
                AND p.actif_pas = 0
                ORDER BY CAST(
                    LEFT(
                        SUBSTRING(p.code_passager,7),
                    CASE
                        WHEN LOCATE('A', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('A', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('B', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('B', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('C', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('C', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('D', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('D', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('E', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('E', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('F', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('F', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('G', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('G', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('H', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('H', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('I', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('I', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('J', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('J', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('K', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('K', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('L', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('L', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('M', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('M', SUBSTRING(p.code_passager,7)) - 1
                     WHEN LOCATE('N', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('N', SUBSTRING(p.code_passager,7)) - 1
                        WHEN LOCATE('O', SUBSTRING(p.code_passager,7)) > 0 THEN LOCATE('O', SUBSTRING(p.code_passager,7)) - 1
                        ELSE LENGTH(SUBSTRING(p.code_passager,7))
                    END
                    ) AS UNSIGNED
                ) ASC")->result();

                return $this->normalize_ticket_prix_rows($rows);
            });
        }

        public function verifcodbagt2($cid, $cod)
        {
            $cod = trim((string) $cod);
            if ($cod === '') {
                return array();
            }

            $this->load->helper('app_cache');
            $cache_key = 'verifcodbagt2_' . $cid . '_' . md5($cod);

            return app_cache_remember($cache_key, 60, function () use ($cid, $cod) {
                $rows = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod 
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE ctp.tamponcodtr = " . $this->db->escape($cod) . "
                AND e.ekey = " . $this->db->escape($cid) . "
                AND p.prixvente IS NULL
                AND p.num_siege_categorie IS NOT NULL
                AND p.actif_pas = 0
                ORDER BY ctp.tamponcodtr DESC LIMIT 1")->result();

                return $this->normalize_ticket_prix_rows($rows);
            });
        }
        
        public function get1($cid, $p_id, $gid)
        {
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod 
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND ctp.tamponcod = '$p_id'
                    AND h.h_active = 1
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function getad($cid, $p_id, $tf, $t = FALSE)
        {
            if ($t === FALSE) {
                $this->load->helper('app_cache');
                $today = mdate('%Y-%m-%d', now('UTC'));
                $cache_key = 'tampon_passagers_ad_' . $cid . '_' . $today;

                return app_cache_remember($cache_key, 120, function () use ($cid) {
                    $jour = $this->_tampon_passager_jour_sql();
                    $rows = $this->db->query(
                        "SELECT * FROM passager p
                        JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
                        JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                        JOIN client cl ON p.id_client_pass = cl.id_client
                        JOIN type_client tcl ON cl.type_client = tcl.nom_type
                        JOIN programme pr ON p.code_pro = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                        JOIN tarifs t ON pr.typetarif = t.id_tarifs
                        JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = '{$cid}'
                        AND p.num_siege_categorie IS NOT NULL
                        AND h.h_active = 1
                        AND lh.actif_lh = 1
                        AND p.actif_pas = 0
                        {$jour}")->result();

                    return $this->normalize_ticket_prix_rows($rows);
                });
            } else
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod 
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND ctp.tamponcod = '$p_id'
                    AND lh.id_ligneheure = '$t'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND t.id_tarifs = '$tf'")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function get2($cid, $p_id, $gid)
        {
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod 
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ex.code_gaexp = '$gid'
                    AND ctp.tamponcod = '$p_id'
                    AND h.h_active = 1
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function getad2($cid, $p_id, $tf, $t)
        {
           
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod 
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ctp.tamponcod = '$p_id'
                    AND lh.id_ligneheure = '$t'
                    AND t.id_tarifs = '$tf'
                    AND h.h_active = 1
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
        public function get1ad($cid, $p_id)
        {
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod 
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND ctp.tamponcod = '$p_id'
                    AND h.h_active = 1
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
        public function getdayad($cid, $p_id = FALSE, $t = FALSE)
        {
            $today = mdate("%Y-%m-%d", now());
            if ($p_id === FALSE AND $t === FALSE) {
                $this->load->helper('app_cache');
                $cache_key = 'getdayad_' . $cid . '_' . $today;

                return app_cache_remember($cache_key, 120, function () use ($cid, $today) {
                    $rows = $this->db->query(
                        "SELECT * FROM passager p
                        JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
                        JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                        LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                        JOIN client cl ON p.id_client_pass = cl.id_client
                        JOIN type_client tcl ON cl.type_client = tcl.nom_type
                        JOIN programme pr ON p.code_pro = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                        JOIN tarifs t ON pr.typetarif = t.id_tarifs
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = " . $this->db->escape($cid) . "
                        AND p.datep_create = " . $this->db->escape($today) . "
                        AND p.statut_code IS NOT NULL
                        AND p.statut_confirme IS NULL
                        AND p.statut_reprog IS NULL
                        AND h.h_active = 1
                        AND ctp.is_activecode = 0
                        AND p.actif_pas = 0")->result();

                    return $this->normalize_ticket_prix_rows($rows);
                });
            }

            $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = " . $this->db->escape($cid) . "
                    AND p.datep_create = " . $this->db->escape($today) . "
                    AND ctp.tamponcod = " . $this->db->escape($p_id) . "
                    AND p.statut_code IS NOT NULL
                    AND p.statut_confirme IS NULL
                    AND p.statut_reprog IS NULL
                    AND h.h_active = 1
                    AND ctp.is_activecode = 0
                    AND p.actif_pas = 0")->row();

            return $this->normalize_ticket_prix_row($row);
        }
        
		public function getday($cid, $gid, $p_id = FALSE, $t = FALSE)
        {
            $today = mdate("%Y-%m-%d", now());
            if ($p_id === FALSE AND $t === FALSE) {
                $this->load->helper('app_cache');
                $cache_key = 'getday_' . $cid . '_' . $gid . '_' . $today;

                return app_cache_remember($cache_key, 120, function () use ($cid, $gid, $today) {
                    $rows = $this->db->query(
                        "SELECT * FROM passager p
                        JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
                        JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                        LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                        JOIN client cl ON p.id_client_pass = cl.id_client
                        JOIN type_client tcl ON cl.type_client = tcl.nom_type
                        JOIN programme pr ON p.code_pro = pr.code_progr
                        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                        JOIN heures h ON lh.heure_identif = h.id_heure
                        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                        JOIN tarifs t ON pr.typetarif = t.id_tarifs
                        JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                        JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                        JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                        JOIN entreprise e ON c.id_entrep = e.id_entreprise
                        WHERE e.ekey = " . $this->db->escape($cid) . "
                        AND p.datep_create = " . $this->db->escape($today) . "
                        AND ex.code_gaexp = " . $this->db->escape($gid) . "
                        AND p.statut_code IS NOT NULL
                        AND p.statut_confirme IS NULL
                        AND p.statut_reprog IS NULL
                        AND h.h_active = 1
                        AND ctp.is_activecode = 0
                        AND p.actif_pas = 0")->result();

                    return $this->normalize_ticket_prix_rows($rows);
                });
            }

            $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = " . $this->db->escape($cid) . "
                    AND p.datep_create = " . $this->db->escape($today) . "
                    AND ctp.tamponcod = " . $this->db->escape($p_id) . "
                    AND p.statut_code IS NOT NULL
                    AND p.statut_confirme IS NULL
                    AND p.statut_reprog IS NULL
                    AND h.h_active = 1
                    AND ctp.is_activecode = 0
                    AND ex.code_gaexp = " . $this->db->escape($gid) . "
                    AND p.actif_pas = 0")->row();

            return $this->normalize_ticket_prix_row($row);
        }
        //historique passager par heure
        public function reporpass($cid, $cp, $gd, $d1, $d2, $lg = FALSE, $hr = FALSE)
        {
            if($lg === '' AND $hr === ''){
                $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS nbr, pr.date_progr, lg.nom_ligne, h.heure FROM passager p
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pr.date_progr BETWEEN '$d1' AND '$d2'
                AND p.statut_code = 'vendu'
                AND p.statut_confirme IS NULL
                AND p.statut_reprog IS NULL
                AND c.cle_compagnie ='$cp'
                AND ex.code_gaexp = '$gd'
                GROUP BY p.code_pro
                ORDER BY pr.date_progr, lg.nom_ligne, h.heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);
            }

            if($hr === ''){
                $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS nbr, pr.date_progr, lg.nom_ligne, h.heure FROM passager p
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pr.date_progr BETWEEN '$d1' AND '$d2'
                AND p.statut_code = 'vendu'
                AND p.statut_confirme IS NULL
                AND p.statut_reprog IS NULL
                AND c.cle_compagnie ='$cp'
                AND ex.code_gaexp = '$gd'
                AND lg.ident_ligne = '$lg'
                GROUP BY p.code_pro
                ORDER BY pr.date_progr, lg.nom_ligne, h.heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);
            }

            else{
                $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS nbr, pr.date_progr, lg.nom_ligne, h.heure FROM passager p
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pr.date_progr BETWEEN '$d1' AND '$d2'
                AND p.statut_code = 'vendu'
                AND p.statut_confirme IS NULL
                AND p.statut_reprog IS NULL
                AND c.cle_compagnie ='$cp'
                AND ex.code_gaexp = '$gd'
                AND lg.ident_ligne = '$lg'
                AND h.id_heure = '$hr'
                GROUP BY p.code_pro
                ORDER BY pr.date_progr, lg.nom_ligne, h.heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            
        }

        //exo passager

        public function exopass($cid, $cp, $d1, $d2, $gd)
        {
            if($gd === ''){
                $rows = $this->db->query(
                "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND c.cle_compagnie ='$cp'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND p.verifpassager IN('A', 'C', 'D')
                    AND p.statut_code = 'vendu'")->result(); return $this->normalize_ticket_prix_rows($rows);
            }

            else
            {
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND c.cle_compagnie ='$cp'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND ex.code_gaexp = '$gd'
                    AND p.verifpassager IN('A', 'C', 'D')
                    AND p.statut_code = 'vendu'")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            
        }

        public function exopassglob($cid, $cp, $d1, $d2, $gd)
        {
           
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND c.cle_compagnie ='$cp'
                    AND pr.date_progr BETWEEN '$d1' AND '$d2'
                    AND ex.code_gaexp = '$gd'
                    AND p.statut_code = 'vendu'")->result(); return $this->normalize_ticket_prix_rows($rows);            
        }

        public function allday($cid, $datedb, $datef, $gid)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_code = 'vendu'
                    AND p.statut_confirme IS NULL
                    AND p.statut_reprog IS NULL
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND ex.code_gaexp = '$gid'")->result(); return $this->normalize_ticket_prix_rows($rows);        }
		public function alldayad($cid, $datedb, $datef)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_code ='vendu'
                    AND p.statut_confirme IS NULL
                    AND p.statut_reprog IS NULL
                    AND h.h_active = 1
                    AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        public function alldayarch($cid, $datedb, $datef, $gid)
        {
            
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_code ='vendu'
                    AND ex.code_gaexp = '$gid'
                    AND p.statut_confirme IS NULL
                    AND p.statut_reprog IS NULL")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        //passager reprogrammer
        public function trireparch($cid, $datedb, $datef, $gid)
        {
                $rows = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod
                    JOIN attributions_role ar ON rp.idcpuserconect = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare 
                    LEFT JOIN non_passager np ON tp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.date BETWEEN '$datedb' AND '$datef'
                    AND p.statut_reprog ='repor'
                    AND ex.code_gaexp = '$gid'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND ul.guser = '$gid'
                    AND tp.actif_tamp = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        /*public function trirep($cid, $datedb, $datef, $gid)
        {
                $rows = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod
                    JOIN attributions_role ar ON rp.idcpuserconect = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    LEFT JOIN non_passager np ON tp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.date BETWEEN '$datedb' AND '$datef'
                    AND p.statut_reprog ='repor'
                    AND h.h_active = 1
                    AND ex.code_gaexp = '$gid'
                    AND ul.guser = '$gid'
                    AND tp.actif_tamp = 0
                    AND p.actif_pas =0")->result(); return $this->normalize_ticket_prix_rows($rows);        }*/
        

        public function trirep($cid, $datedb, $datef, $gid)
        {
                $rows = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod
                    LEFT JOIN non_passager np ON tp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.date BETWEEN '$datedb' AND '$datef'
                    AND p.statut_reprog ='repor'
                    AND h.h_active = 1
                    AND ex.code_gaexp = '$gid'
                    AND tp.actif_tamp = 0
                    AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }
		
		public function alldayarchad($cid, $datedb, $datef)
        {
            
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    LEFT JOIN non_passager np ON ctp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_code ='vendu'
                    AND p.statut_confirme IS NULL
                    AND p.statut_reprog IS NULL")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        //passager reprogrammer
        public function trireparchad($cid, $datedb, $datef)
        {
                $rows = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod 
                    JOIN attributions_role ar ON rp.idcpuserconect = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    LEFT JOIN non_passager np ON tp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.date BETWEEN '$datedb' AND '$datef'
                    AND p.statut_reprog ='repor'")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function trirepad($cid, $datedb, $datef)
        {
                $rows = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod
                    JOIN attributions_role ar ON rp.idcpuserconect = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare 
                    LEFT JOIN non_passager np ON tp.tamponcod = np.code_non_pass
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.date BETWEEN '$datedb' AND '$datef'
                    AND p.statut_reprog ='repor'
                    AND h.h_active = 1
                    AND p.actif_pas =0")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        public function siegepasse($cid, $dt, $dep_aid = FALSE)
        {
            
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.code_progr = '$dep_aid'
                    AND h.h_active = 1
                    AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }
       

        public function verifiersiege($cid, $cdp, $num_sieg){
            $row = $this->db->query("SELECT * FROM passager ps WHERE ps.code_pro = '$cdp' AND ps.num_siege_categorie = '$num_sieg'")->row(); return $this->normalize_ticket_prix_row($row);
        }

        //report
        public function passereport($cid, $gid, $cdreport, $tf, $cnp, $hr)
        {
                $row = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.code_report = '$cdreport'
                    AND ex.code_gaexp = '$gid'
                    AND p.code_ticket = '$cnp'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND p.actif_pas = 0
					AND tf.ligne_heure_id = '$hr'
                    AND t.id_tarifs = '$tf'")->row(); return $this->normalize_ticket_prix_row($row);
        }
        //report
        public function passereport1ad($cid, $cdreport, $cnp, $hr)
        {
                $row = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.code_report = '$cdreport'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND p.code_ticket = '$cnp'
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
        //passager confirmer
        public function getconfirmead($cid, $gid, $p_id = FALSE)
        {
            
            $det = date('H:i:s', time('H:i:s')-3600);
            $key = mdate("%Y-%m-%d", now());
            $today = $key.' '. $det;
            if ($p_id === FALSE) {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.date_emis >= '$today'
                    AND p.reimprime = 0
                    AND p.statut_confirme = 'confirm'
                    AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);            } else
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.date_emis >= '$today'
                    AND ctp.tamponcod = '$p_id'
                    AND ex.code_gaexp = '$gid'
                    AND p.reimprime = 0
                    AND p.statut_confirme = 'confirm'
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
     
        //
        public function passeconfirme($cid, $cdconf, $tf, $h, $gid)
        {
            $row = $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ctp.tamponcod = '$cdconf'
                    AND p.reimprime = 0
                    AND p.statut_confirme = 'confirm'
					AND tf.ligne_heure_id = '$h'
                    AND t.id_tarifs = '$tf'
                    AND ex.code_gaexp = '$gid'
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function passeconfirmer($cid, $cdconf, $gid)
        {
            $row = $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ctp.tamponcod = '$cdconf'
                    AND p.statut_confirme = 'confirm'
                    AND ex.code_gaexp = '$gid'
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function etats($cid, $d1, $d2, $gid, $us = FALSE, $st = FALSE)
        {
            if( $us === FALSE AND $st === FALSE){
                $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            
            if( $st === FALSE){
                $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'
                    AND ar.roleattribut = '$us'")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            if( $us === FALSE){
                $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'
                    AND p.statut_reprog = '$st'")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'
                    AND p.statut_reprog = '$st'
                    AND ar.roleattribut = '$us'")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        
        public function etatsc($cid, $d1, $d2, $gid, $us = FALSE, $st = FALSE)
        {
            if( $us === FALSE AND $st === FALSE){
                $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            
            if( $st === FALSE){
                $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'
                    AND ar.roleattribut = '$us'")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            if( $us === FALSE){
                $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'
                    AND p.statut_confirme = '$st'")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'
                    AND p.statut_confirme = '$st'
                    AND ar.roleattribut = '$us'")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function etats1($cid, $d1, $d2, $gid, $us = FALSE)
        {
            if( $us === FALSE){
                $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND g.idengare = '$gid'
                    AND p.statut_confirme IS NULL
                    AND p.statut_reprog IS NULL")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            else{
                $rows = $this->db->query("SELECT * FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$d1' AND '$d2'
                    AND p.statut_confirme IS NULL
                    AND p.statut_reprog IS NULL
                    AND g.idengare = '$gid'
                    AND ar.roleattribut = '$us'")->result(); return $this->normalize_ticket_prix_rows($rows);            }
        }
        public function passeconfirmead($cid, $cdconf, $tf, $h)
        {
            $row = $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ctp.tamponcod = '$cdconf'
                    AND p.reimprime = 0
                    AND p.statut_confirme = 'confirm'
                    AND tf.ligne_heure_id = '$h'
                    AND t.id_tarifs = '$tf'
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function passeconfirmerad($cid, $cdconf)
        {
            $row = $this->db->query("SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ctp.tamponcod = '$cdconf'
                    AND p.statut_confirme = 'confirm'
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
        //passager reprogramme
        public function getreprogramme($cid, $gid, $p_id = FALSE)
        {
            if ($p_id === FALSE) {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN siege_categorie sc ON p.num_siege_categorie = sc.siege_num
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.statut_reprog IS NOT NULL
                    AND h.h_active = 1
                    AND ex.code_gaexp = '$gid'
                    AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);            } else
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN siege_categorie sc ON p.num_siege_categorie = sc.siege_num
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ctp.tamponcod = '$p_id'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.statut_reprog IS NOT NULL
                    AND h.h_active = 1
                    AND ex.code_gaexp = '$gid'
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
		
		//
		public function passereportad($cid, $cdreport, $tf, $cnp, $hr)
        {
                $row = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.code_report = '$cdreport'
                    AND h.h_active = 1
                    AND p.code_ticket = '$cnp'
                    AND p.actif_pas = 0
					AND tf.ligne_heure_id = '$hr'
                    AND t.id_tarifs = '$tf'")->row(); return $this->normalize_ticket_prix_row($row);
        }
        //report
        public function passereport1($cid, $cdreport, $tf, $cnp, $hr, $gid)
        {
                $row = $this->db->query(
                    "SELECT * FROM report rp
                    JOIN tamponcode tp ON rp.code_tick_tamp = tp.tamponcod
                    JOIN passager p ON p.code_passager = tp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND rp.code_report = '$cdreport'
                    AND ex.code_gaexp = '$gid'
                    AND tf.ligne_heure_id = '$hr'
                    AND p.code_ticket = '$cnp'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND t.id_tarifs = '$tf'")->row(); return $this->normalize_ticket_prix_row($row);
        }
        //passager confirmer
        public function getconfirme($cid, $gid, $p_id = FALSE)
        {
            $det = date('H:i:s', time('H:i:s')-3600);
            $key = mdate("%Y-%m-%d", now());
            $today = $key.' '. $det;
            if ($p_id === FALSE) {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.date_emis >= '$today'
                    AND ex.code_gaexp = '$gid'
                    AND p.reimprime = 0
                    AND p.statut_confirme = 'confirm'
                    AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);            } else
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.date_emis >= '$today'
                    AND ctp.tamponcod = '$p_id'
                    AND ex.code_gaexp = '$gid'
                    AND p.reimprime = 0
                    AND p.statut_confirme = 'confirm'
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
        
        //passager reprogramme
        public function getreprogrammead($cid, $p_id = FALSE)
        {
            if ($p_id === FALSE) {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN siege_categorie sc ON p.num_siege_categorie = sc.siege_num
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.statut_reprog IS NOT NULL
                    AND h.h_active = 1
                    AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);            } else
                $row = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN siege_categorie sc ON p.num_siege_categorie = sc.siege_num
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND ctp.tamponcod = '$p_id'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.statut_reprog IS NOT NULL
                    AND h.h_active = 1
                    AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
     
        public function getreserve($cid, $gid, $p_id = FALSE)
        {
            if ($p_id === FALSE) {
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.code_ticket = 'R'
                    AND ex.code_gaexp = '$gid'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND p.num_siege_categorie IS NOT NULL")->result(); return $this->normalize_ticket_prix_rows($rows);            } else
                $row = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.code_ticket = 'R'
                    AND ex.code_gaexp = '$gid'
                    AND h.h_active = 1
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    AND p.code_passager = '$p_id'")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function getreservead($cid, $p_id = FALSE)
        {
            if ($p_id === FALSE) {
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.code_ticket = 'R'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND p.num_siege_categorie IS NOT NULL")->result(); return $this->normalize_ticket_prix_rows($rows);            } else
                $row = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.code_ticket = 'R'
                    AND h.h_active = 1
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    AND p.code_passager = '$p_id'")->row(); return $this->normalize_ticket_prix_row($row);
        }
        //voir liste chef guichet
        public function voirliste($cid, $p, $h, $dt, $gid)
        {

                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$p'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND ex.code_gaexp = '$gid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        
        public function liste1($cid, $cddepart, $h, $dt, $gid, $tout = FALSE)
        {
            if ($tout === '') {
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND ex.code_gaexp = '$gid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            else if($tout === 'larle'){
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND ex.code_gaexp = '$gid'
                    AND p.quart = '$tout'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            else{
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND ex.code_gaexp = '$gid'
                    AND p.quart != 'Larle'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            }
        }

        public function liste($cid, $cddepart, $h, $dt, $gid)
        {
            
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND ex.code_gaexp = '$gid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            
        }
        public function prognotin($cid, $cddepart, $h, $dt, $p, $gid)
        {
                $row = $this->db->query(
                    "SELECT * FROM tirage_liste tg
                    JOIN programme pr ON tg.cod_programme = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND ex.code_gaexp = '$gid'
                    AND tg.cod_programme = '$p'")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function listeupdate($cid, $gid)
        {
                $today = mdate("%Y-%m-%d", now());
                $rows = $this->db->query(
                    "SELECT * FROM tirage_liste tg
                    JOIN programme pr ON tg.cod_programme = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr >= '$today'
                    AND tg.datedepart_bus >= '$today'
                    AND ex.code_gaexp = '$gid'
                    ORDER BY h.heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function voirlistead($cid, $p, $h, $dt)
        {
            $rows = $this->db->query(
                "SELECT * FROM passager p
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pr.date_progr = '$dt'
                AND pr.depart_code = '$p'
                AND lh.heure_identif = '$h'
                AND p.code_ticket != 'R'
                AND p.num_siege_categorie IS NOT NULL
                AND p.actif_pas = 0
                ORDER BY p.num_siege_categorie")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        
        public function listead1($cid, $cddepart, $h, $dt, $tout = FALSE)
        {
            if ($tout === '') {
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            else if($tout === 'larle'){
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND p.quart = '$tout'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            }
            else
            {
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND p.quart != 'Larle'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            }
        }

        public function listead($cid, $cddepart, $h, $dt)
        {
            
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND pr.depart_code = '$cddepart'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);            
        }
        public function prognotinad($cid, $cddepart, $h, $dt, $p)
        {
            $row = $this->db->query(
                "SELECT * FROM tirage_liste tg
                JOIN programme pr ON tg.cod_programme = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pr.date_progr = '$dt'
                AND pr.depart_code = '$cddepart'
                AND lh.heure_identif = '$h'
                AND tg.cod_programme = '$p'")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function listeupdatead($cid)
        {
            $today = mdate("%Y-%m-%d", now());
            $rows = $this->db->query(
                "SELECT * FROM tirage_liste tg
                JOIN programme pr ON tg.cod_programme = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pr.date_progr >= '$today'
                AND tg.datedepart_bus >= '$today'
                ORDER BY h.heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function listedate($cid, $d, $f, $gid)
        {
            $today = mdate("%Y-%m-%d", now());
            $rows = $this->db->query(
                "SELECT * FROM tirage_liste tg
                JOIN programme pr ON tg.cod_programme = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND tg.datedepart_bus BETWEEN'$d' AND '$f'
                AND ex.code_gaexp = '$gid'
                ORDER BY h.heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        public function listepasse($cid, $ligne, $h, $dt, $gid)
        {
                $rows = $this->db->query(
                    "SELECT * FROM passager p
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dt'
                    AND lh.ligne_id = '$ligne'
                    AND lh.heure_identif = '$h'
                    AND p.code_ticket != 'R'
                    AND ex.code_gaexp = '$gid'
                    AND p.num_siege_categorie IS NOT NULL
                    AND p.actif_pas = 0
                    ORDER BY p.num_siege_categorie")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function listedatead($cid, $d, $f)
        {
            $today = mdate("%Y-%m-%d", now());
            $rows = $this->db->query(
                "SELECT * FROM tirage_liste tg
                JOIN programme pr ON tg.cod_programme = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND tg.datedepart_bus BETWEEN'$d' AND '$f'
                ORDER BY h.heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        public function listepassead($cid, $ligne, $h, $dt)
        {
            $rows = $this->db->query(
                "SELECT * FROM passager p
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND pr.date_progr = '$dt'
                AND lh.ligne_id = '$ligne'
                AND lh.heure_identif = '$h'
                AND p.code_ticket != 'R'
                AND p.num_siege_categorie IS NOT NULL
                AND p.actif_pas = 0
                ORDER BY p.num_siege_categorie")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        //nb passagers aller et montant
        public function compte($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            $today1 = date("Y-m-d", strtotime("-1 day"));

            $row = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <= '$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND p.statutvente = 0
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND cu.date_conect <= '$today'
                GROUP BY p.idcptuser")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function compteur($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            $row = $this->db->query("SELECT SUM(prixvente) AS total FROM passager p
                WHERE p.idcptuser = '$idcox'
                AND p.statut_code = 'vendu'
                AND p.statutvente = 0
                AND p.datep_create <= '$today'
                AND p.prixvente IS NOT NULL
                AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
        public function comptegroup($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));

            $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, c.nom_compagnie, dest.id_compaga, p.departclient_idgare FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <='$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND p.statutvente = 0
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND cu.date_conect <= '$today'
                GROUP BY p.idcptuser, dest.id_compaga, c.nom_compagnie, p.departclient_idgare")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function compteurcd($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
                        
            $row = $this->db->query("SELECT SUM(prixvente) AS total FROM passager p
                WHERE p.idcptuser = '$idcox'
                AND p.statut_code = 'vendu'
                AND p.statutvente = 0
                AND p.datep_create < '$today'
                AND p.prixvente IS NOT NULL
                AND p.actif_pas = 0")->row(); return $this->normalize_ticket_prix_row($row);
        }
		public function comptes($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $row = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <='$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
				AND p.departclient_idgare= '$sg'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND p.statutvente = 0
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND cu.date_conect <= '$today'
                GROUP BY p.idcptuser")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function comptegroups($cd, $idcox, $g, $sg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, c.nom_compagnie, dest.id_compaga, p.departclient_idgare FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <='$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
				AND p.departclient_idgare= '$sg'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND p.statutvente = 0
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND cu.date_conect <= '$today'
                GROUP BY p.idcptuser, dest.id_compaga, c.nom_compagnie, p.departclient_idgare")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        public function comptegroupsbis($cd, $idcox, $g, $sg, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            
                $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, c.nom_compagnie, dest.id_compaga, p.departclient_idgare FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND p.datep_create <='$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga = '$cpg'
                    AND ul.guser = '$g'
                    AND p.departclient_idgare = '$sg'
                    AND cu.is_conect = 1
                    AND ar.activeattrib = 1
                    AND p.statutvente = 0
                    AND p.prixvente IS NOT NULL
                    AND p.statut_code = 'vendu'
                    AND cu.date_conect <= '$today'
                    GROUP BY p.idcptuser, dest.id_compaga, c.nom_compagnie, p.departclient_idgare")->result(); return $this->normalize_ticket_prix_rows($rows);            
        }
        public function comptebis($cd, $idcox, $g, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));

            $today1 = date("Y-m-d", strtotime("-1 day"));

                $row = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND p.datep_create <= '$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga !='$cpg'
                    AND ul.guser = '$g'
                    AND cu.is_conect = 1
                    AND ar.activeattrib = 1
                    AND p.statutvente = 0
                    AND p.prixvente IS NOT NULL
                    AND p.statut_code = 'vendu'
                    AND cu.date_conect <= '$today'
                    GROUP BY p.idcptuser")->row(); return $this->normalize_ticket_prix_row($row);    
        }
        public function comptegroupbis($cd, $idcox, $g, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            
                $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, c.nom_compagnie, dest.id_compaga, p.departclient_idgare FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND p.datep_create <='$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga !='$cpg'
                    AND ul.guser = '$g'
                    AND cu.is_conect = 1
                    AND ar.activeattrib = 1
                    AND p.statutvente = 0
                    AND p.prixvente IS NOT NULL
                    AND p.statut_code = 'vendu'
                    AND cu.date_conect <= '$today'
                    GROUP BY p.idcptuser, dest.id_compaga, c.nom_compagnie, p.departclient_idgare")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function comptegroupetranstr($cd, $idcox, $g, $sg, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
                $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, SUM(prixretour) AS totalr, c.nom_compagnie, dest.id_compaga, p.departclient_idgare FROM passager p
                    LEFT JOIN non_passager np ON p.code_passager = np.code_non_pass
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND p.datep_create <='$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga = '$cpg'
                    AND ul.guser = '$g'
                    AND cu.is_conect = 1
                    AND ar.activeattrib = 1
                    AND p.statutvente = 0
                    AND p.prixvente IS NOT NULL
                    AND p.statut_code = 'vendu'
                    AND cu.date_conect <= '$today'
                    AND p.departclient_idgare NOT IN (SELECT s.idsousgare FROM sousgare s WHERE s.gareprinceid = '$g')
                    GROUP BY p.idcptuser, dest.id_compaga, c.nom_compagnie, p.departclient_idgare")->result(); return $this->normalize_ticket_prix_rows($rows);
        }
        
        public function comptegroupeptranstr($cd, $idcox, $g, $sg, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
                $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, SUM(prixretour) AS totalr, c.nom_compagnie, dest.id_compaga, p.departclient_idgare FROM passager p
                    LEFT JOIN non_passager np ON p.code_passager = np.code_non_pass
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND p.datep_create <='$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga = '$cpg'
                    AND ul.guser = '$g'
                    AND cu.is_conect = 1
                    AND ar.activeattrib = 1
                    AND p.statutvente = 0
                    AND p.prixvente IS NOT NULL
                    AND p.statut_code = 'vendu'
                    AND cu.date_conect <= '$today'
                    AND p.departclient_idgare = '$sg'
                    GROUP BY p.idcptuser, dest.id_compaga, c.nom_compagnie, p.departclient_idgare")->result(); return $this->normalize_ticket_prix_rows($rows);
        }
        public function comptegroupbisinter($cd, $idcox, $g, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
                $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, SUM(prixretour) AS totalr, c.nom_compagnie, dest.id_compaga, p.departclient_idgare FROM passager p
                    LEFT JOIN non_passager np ON p.code_passager = np.code_non_pass
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND p.datep_create <='$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga !='$cpg'
                    AND ul.guser = '$g'
                    AND cu.is_conect = 1
                    AND ar.activeattrib = 1
                    AND p.statutvente = 0
                    AND p.prixvente IS NOT NULL
                    AND p.statut_code = 'vendu'
                    AND cu.date_conect <= '$today'
                    GROUP BY p.idcptuser, dest.id_compaga, c.nom_compagnie, p.departclient_idgare")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        public function comptegroupb($cd, $idcox, $g, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            
                $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, c.nom_compagnie, dest.id_compaga, p.departclient_idgare FROM passager p
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cd'
                    AND p.datep_create <='$today'
                    AND ar.roleattribut = '$idcox'
                    AND dest.id_compaga = '$cpg'
                    AND ul.guser = '$g'
                    AND cu.is_conect = 1
                    AND ar.activeattrib = 1
                    AND p.statutvente = 0
                    AND p.prixvente IS NOT NULL
                    AND p.statut_code = 'vendu'
                    AND cu.date_conect <= '$today'
                    GROUP BY p.idcptuser, dest.id_compaga, c.nom_compagnie, p.departclient_idgare")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function comptesbis($cd, $idcox, $g, $sg, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            
                $row = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <='$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND dest.id_compaga ='$cpg'
                AND p.departclient_idgare= '$sg'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND p.statutvente = 0
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND cu.date_conect <= '$today'
                GROUP BY p.idcptuser")->row(); return $this->normalize_ticket_prix_row($row);
        }

        public function coptb($cd, $idcox, $g, $cpg)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            
                $row = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total, ex.id_compagd FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <= '$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND dest.id_compaga = '$cpg'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND cu.date_conect <= '$today'
                AND p.actif_pas = 0
                GROUP BY p.idcptuser")->row(); return $this->normalize_ticket_prix_row($row);
        }
        public function totalpassager($cd)
        {
            $this->load->helper('app_cache');
            $cache_key = 'totalpassager_' . $cd;

            return app_cache_remember($cache_key, 120, function () use ($cd) {
                $rows = $this->db->query(
                    "SELECT COUNT(code_passager) AS cod, lg.nom_ligne, c.nom_compagnie, dest.id_compaga
                    FROM passager p
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = " . $this->db->escape($cd) . "
                    AND p.prixvente IS NOT NULL
                    AND p.statut_code = 'vendu'
                    AND p.actif_pas = 0
                    GROUP BY lg.ident_ligne, dest.id_compaga, c.nom_compagnie")->result();

                return $this->normalize_ticket_prix_rows($rows);
            });
        }
        //pass repro
        public function comptrep($cd, $idcox, $g)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $row = $this->db->query("SELECT COUNT(code_passager) AS cd FROM passager p
                JOIN tamponcode tp ON p.code_passager = tp.tamponcod 
                JOIN report rp ON rp.code_tick_tamp = tp.tamponcod
                JOIN attributions_role ar ON rp.idcpuserconect = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND rp.date <= '$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND rp.statutreport = 0
                AND p.statut_reprog ='repor'
                AND cu.date_conect <= '$today'
                AND p.actif_pas = 0
                AND rp.actifrep = 0
                GROUP BY rp.statutreport")->row(); return $this->normalize_ticket_prix_row($row);
        }
        //pass confirm
        public function comptconf($cd, $idcox, $g)
        {
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            $row = $this->db->query("SELECT COUNT(code_passager) AS cd FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <= '$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND p.statutvente = 0
                AND p.statut_confirme = 'confirm'
                AND p.prixvente IS NULL
                AND cu.date_conect <= '$today'
                AND p.actif_pas = 0
                GROUP BY p.idcptuser")->row(); return $this->normalize_ticket_prix_row($row);
        }

        //rapport journalier
        public function rapportaller($cd, $idcox, $comp, $g)
        {
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            $nomLine = $this->rapport_nom_ligne_sql();
            $nomLineSelect = $nomLine['select'];
            $nomLineGroup = $nomLine['group'];

            $rows = $this->db->query("SELECT COUNT(code_passager) AS cd, SUM(prixvente) AS total,
                {$nomLineSelect},
                p.prixvente, dest.id_compaga, ar.roleattribut FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <= '$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND p.statutvente = 1
                AND p.is_valdtick = 0
                AND dest.id_compaga = '$comp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND cu.date_conect <= '$today'
                GROUP BY {$nomLineGroup}, p.prixvente, dest.id_compaga, ar.roleattribut")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function rapportrep($cd, $idcox, $comp, $g)
        {
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $today = mdate("%Y-%m-%d", now('UTC'));

            $nomLine = $this->rapport_nom_ligne_sql();
            $nomLineSelect = $nomLine['select'];
            $nomLineGroup = $nomLine['group'];

            $rows = $this->db->query("SELECT COUNT(code_passager) AS cdrep,
                {$nomLineSelect},
                ar.roleattribut FROM passager p
                JOIN tamponcode tp ON p.code_passager = tp.tamponcod 
                JOIN report rp ON rp.code_tick_tamp = tp.tamponcod
                JOIN attributions_role ar ON rp.idcpuserconect = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND rp.date <= '$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND rp.statutreport = 1
                AND rp.is_statutreport = 0
                AND p.statut_reprog = 'repor'
                AND dest.id_compaga = '$comp'
                GROUP BY ar.roleattribut, {$nomLineGroup}")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        //pass confirm
        public function rapportconf($cd, $idcox, $comp, $g)
        {
            $today1 = date("Y-m-d", strtotime("-1 day"));
            $today = mdate("%Y-%m-%d", now('UTC'));
            
            $nomLine = $this->rapport_nom_ligne_sql();
            $nomLineSelect = $nomLine['select'];
            $nomLineGroup = $nomLine['group'];

            $rows = $this->db->query("SELECT COUNT(code_passager) AS cdconf,
                {$nomLineSelect},
                ar.roleattribut FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create <= '$today'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$g'
                AND cu.is_conect = 1
                AND ar.activeattrib = 1
                AND p.statutvente = 1
                AND p.statut_confirme ='confirm'
                AND p.is_valdtick = 0
                AND p.prixvente IS NULL
                AND dest.id_compaga = '$comp'
                GROUP BY ar.roleattribut, {$nomLineGroup}")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        
        //etat des ventes
        public function vente($cid, $datedb, $datef, $gid)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_code = 'vendu'
                    AND ex.code_gaexp = '$gid'")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        /*public function triconf($cid, $datedb, $datef, $gid)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_confirme = 'confirm'
                    AND ex.code_gaexp = '$gid'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND ul.guser = '$gid'                
                    AND ctp.actif_tamp = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }*/

        //etat general
        public function etatgeneral($cid, $datedb, $datef)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND ex.code_gaexp = '$gid'")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        //etat confirmation
        /*public function triconfarch($cid, $datedb, $datef, $gid, $sg)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_confirme = 'confirm'
                    AND ex.code_gaexp = '$gid'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND p.departclient_idgare = '$sg'
                    AND ctp.actif_tamp = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }*/

        public function triconfarch($cid, $datedb, $datef, $gid, $sg)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_confirme = 'confirm'
                    AND ex.code_gaexp = '$gid'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND p.departclient_idgare = '$sg'
                    AND ctp.actif_tamp = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        public function triconf($cid, $datedb, $datef, $gid, $sg)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_confirme = 'confirm'
                    AND ex.code_gaexp = '$gid'
                    AND h.h_active = 1
                    AND p.actif_pas = 0
                    AND p.departclient_idgare = '$sg'        
                    AND ctp.actif_tamp = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        
        public function ventead($cid, $datedb, $datef)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_code = 'vendu'")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        //etat general
        public function etatgeneralad($cid, $datedb, $datef)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'")->result(); return $this->normalize_ticket_prix_rows($rows);        }

        //etat confirmation
        public function triconfarchad($cid, $datedb, $datef)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_confirme ='confirm'")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        public function triconfad($cid, $datedb, $datef)
        {
                $rows = $this->db->query(
                    "SELECT * FROM tamponcode ctp
                    JOIN passager p ON p.code_passager = ctp.tamponcod
                    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                    JOIN client cl ON p.id_client_pass = cl.id_client
                    JOIN type_client tcl ON cl.type_client = tcl.nom_type
                    JOIN programme pr ON p.code_pro = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND p.datep_create BETWEEN '$datedb' AND '$datef'
                    AND p.statut_confirme ='confirm'
                    AND p.actif_pas = 0")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        
        // tri versement vendeuse
    public function versefiltre($key, $gid, $db, $df, $cp, $idvd = FALSE)
    {
        $ky = mdate("%Y-%m-%d", now('UTC'));
        
        if ($idvd == FALSE) {
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, lg.ident_ligne, dest.id_compaga, lg.nom_ligne, p.prixvente, cu.username FROM passager p
            JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN gares g ON ul.guser = g.idengare
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$key'
            AND p.datep_create BETWEEN '$db' AND '$df'
            AND dest.id_compaga = '$cp'
            AND ul.guser = '$gid'
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            AND p.actif_pas = 0
            GROUP BY lg.ident_ligne, dest.id_compaga, p.prixvente, cu.username")->result(); return $this->normalize_ticket_prix_rows($rows);        } 
        else{
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, lg.ident_ligne, dest.id_compaga, lg.nom_ligne, p.prixvente, cu.username FROM passager p
            JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN gares g ON ul.guser = g.idengare
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$key'
            AND p.datep_create BETWEEN '$db' AND '$df'
            AND dest.id_compaga = '$cp'
            AND ar.roleattribut = '$idvd'
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            AND ul.guser = '$gid'
            AND p.actif_pas = 0
            GROUP BY lg.ident_ligne, dest.id_compaga, p.prixvente, cu.username")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            
    }

    public function versefiltreadmin($key, $gid, $db, $df, $cp, $idvd = FALSE)
    {
        $ky = mdate("%Y-%m-%d", now('UTC'));
        if ($idvd == FALSE) {
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, lg.ident_ligne, dest.id_compaga, lg.nom_ligne, p.prixvente, cu.username FROM passager p
            JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN gares g ON ul.guser = g.idengare
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$key'
            AND p.datep_create BETWEEN '$db' AND '$df'
            AND dest.id_compaga = '$cp'
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            AND ul.guser = '$gid'
            GROUP BY lg.ident_ligne, dest.id_compaga, p.prixvente, cu.username")->result(); return $this->normalize_ticket_prix_rows($rows);        } 
        else{
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, lg.ident_ligne, dest.id_compaga, lg.nom_ligne, p.prixvente, cu.username FROM passager p
            JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN gares g ON ul.guser = g.idengare
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$key'
            AND p.datep_create BETWEEN '$db' AND '$df'
            AND dest.id_compaga = '$cp'
            AND ar.roleattribut = '$idvd'
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            AND ul.guser = '$gid'
            GROUP BY lg.ident_ligne, dest.id_compaga, p.prixvente, cu.username")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            
    }

    public function versefiltreadminsg($key, $gid, $db, $df, $cp, $sg, $idvd = FALSE)
    {
        $ky = mdate("%Y-%m-%d", now('UTC'));
        if ($idvd == FALSE) {
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, lg.ident_ligne, dest.id_compaga, lg.nom_ligne, p.prixvente, cu.username FROM passager p
            JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN gares g ON ul.guser = g.idengare
            JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$key'
            AND p.datep_create BETWEEN '$db' AND '$df'
            AND dest.id_compaga = '$cp'
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            AND ul.guser = '$gid'
            AND p.departclient_idgare = '$sg'
            GROUP BY lg.ident_ligne, dest.id_compaga, p.prixvente, cu.username")->result(); return $this->normalize_ticket_prix_rows($rows);        } 
        else{
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, lg.ident_ligne, dest.id_compaga, lg.nom_ligne, p.prixvente, cu.username FROM passager p
            JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN gares g ON ul.guser = g.idengare
            JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$key'
            AND p.datep_create BETWEEN '$db' AND '$df'
            AND dest.id_compaga = '$cp'
            AND ar.roleattribut = '$idvd'
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            AND ul.guser = '$gid'
            AND p.departclient_idgare = '$sg'
            GROUP BY lg.ident_ligne, dest.id_compaga, p.prixvente, cu.username")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            
    }

    public function versfiltre($key, $gid, $db, $df, $cp, $use)
    {
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, lg.ident_ligne, dest.id_compaga, lg.nom_ligne, p.prixvente, cu.username, p.datep_create FROM passager p
            JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
            JOIN user_login ul ON ar.idgestcompte = ul.uid_login
            JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
            JOIN gares g ON ul.guser = g.idengare
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = '$key'
            AND p.datep_create BETWEEN '$db' AND '$df'
            AND dest.id_compaga = '$cp'
            AND p.prixvente IS NOT NULL
            AND p.statut_code = 'vendu'
            AND ar.roleattribut = '$use'
            AND ul.guser = '$gid'
            GROUP BY lg.ident_ligne, dest.id_compaga, p.prixvente, cu.username, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);        
            
    }
    //report admin
    public function listereport($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
    {        
        if ($acl === '' AND $algn === '') {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        elseif($algn === '')
        {
            $rows = $this->db->query("SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                AND lg.ident_ligne = '$algn'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }
    
    public function listereportcpt($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
    {
        
        if ($acl === '' AND $algn === '') {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        elseif($algn === '')
        {
            $rows = $this->db->query("SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, u.first_name, u.last_name, dest.id_compaga, lg.nom_ligne, p.prixvente, ar.roleattribut FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                AND ar.roleattribut = '$acl'
                GROUP BY ar.roleattribut, u.first_name, dest.id_compaga, u.last_name, lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, u.first_name, u.last_name, dest.id_compaga, lg.nom_ligne, p.prixvente, ar.roleattribut FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                AND lg.ident_ligne = '$algn'
                AND ul.guser = '$gid'
                GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function listereportverscpt($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE)
    {
        
        if ($acl === '') {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND ar.roleattribut = '$acl'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function listereportverscptgl($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
    {
        
        if ($acl === '' AND $algn === '') {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        elseif($algn === '')
        {
            $rows = $this->db->query("SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                AND ar.roleattribut = '$acl'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                AND lg.ident_ligne = '$algn'
                AND ul.guser = '$gid'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function listereportverscptgle($cid, $cp, $dt1, $dt2, $gid = FALSE, $acl = FALSE)
    {
        
        if ($gid === '' AND $acl === '') {
            $rows = $this->db->query(
                "SELECT SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        elseif($acl === '')
        {
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND ar.roleattribut = '$acl'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);    }
    
    public function listereportverscptglexo($cid, $cp, $dt1, $dt2, $gid = FALSE, $acl = FALSE)
    {
        
        if ($gid === '' AND $acl === '') {
            $rows = $this->db->query(
                "SELECT SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        elseif($acl === '')
        {
            $rows = $this->db->query("SELECT SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT SUM(prixvente) AS total, dest.id_compaga, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND ar.roleattribut = '$acl'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY dest.id_compaga, p.datep_create")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function listereportcptadmin($cid, $cp, $gid, $dt1, $dt2, $acl = FALSE, $algn = FALSE)
    {
        
        if ($acl === '' AND $algn === '') {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        elseif($algn === '')
        {
            $rows = $this->db->query("SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                AND ar.roleattribut = '$acl'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                AND lg.ident_ligne = '$algn'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }
    //report ticket admin
    /*public function reporticket($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, dest.id_compaga, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, dest.id_compaga, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, dest.id_compaga, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                GROUP BY lg.nom_ligne, dest.id_compaga, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }*/

    public function reporticket($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE, $sg = FALSE)
    {
        $sgNorm = ($sg === FALSE || $sg === null) ? '' : trim((string) $sg);
        $sgSql = '';
        if ($sgNorm !== '' && $sgNorm !== '0') {
            $sgSql = " AND p.departclient_idgare = '" . $this->db->escape_str($sgNorm) . "'";
        }

        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT 
                    COUNT(p.code_passager) AS codepassager,
                    SUM(p.prixvente) AS total, lg.nom_ligne, p.prixvente
                FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND EXISTS (
                  SELECT 1 FROM user_login ul
                  WHERE ul.uid_login = (
                      SELECT ar.idgestcompte
                      FROM attributions_role ar
                      WHERE ar.roleattribut = p.idcptuser
                      LIMIT 1
                  )
                  AND ul.guser = '$gid'
                 )
                {$sgSql}
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT 
                    COUNT(p.code_passager) AS codepassager,
                    SUM(p.prixvente) AS total, lg.nom_ligne, p.prixvente
                FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND EXISTS (
                  SELECT 1 FROM user_login ul
                  WHERE ul.uid_login = (
                      SELECT ar.idgestcompte
                      FROM attributions_role ar
                      WHERE ar.roleattribut = p.idcptuser
                      LIMIT 1
                  )
                  AND ul.guser = '$gid'
                )
                {$sgSql}
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function reporticketgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT * FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT * FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    //report comptable
    public function reporticketcptd($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.statutvente = 1
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.exop = 1
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.statutvente = 1
                AND p.exop = 1
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }
    
    /*public function reporticketcptadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }*/

    public function reporticketcptadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT 
                    COUNT(p.code_passager) AS codepassager,
                    SUM(p.prixvente) AS total,
                    lg.nom_ligne, p.prixvente
                FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND EXISTS (
                  SELECT 1 FROM user_login ul
                  WHERE ul.uid_login = (
                      SELECT ar.idgestcompte
                      FROM attributions_role ar
                      WHERE ar.roleattribut = p.idcptuser
                      LIMIT 1
                  )
                AND ul.guser = '$gid'
                )
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT 
                    COUNT(p.code_passager) AS codepassager,
                    SUM(p.prixvente) AS total, lg.nom_ligne, p.prixvente
                FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND EXISTS (
                  SELECT 1 FROM user_login ul
                  WHERE ul.uid_login = (
                      SELECT ar.idgestcompte
                      FROM attributions_role ar
                      WHERE ar.roleattribut = p.idcptuser
                      LIMIT 1
                  )
                  AND ul.guser = '$gid'
                 )
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function reporticketcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT 
                    COUNT(p.code_passager) AS codepassager,
                    SUM(p.prixvente) AS total, lg.nom_ligne, p.prixvente
                FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND EXISTS (
                  SELECT 1 FROM user_login ul
                  WHERE ul.uid_login = (
                      SELECT ar.idgestcompte
                      FROM attributions_role ar
                      WHERE ar.roleattribut = p.idcptuser
                      LIMIT 1
                  )
                  AND ul.guser = '$gid'
                )
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT 
                    COUNT(p.code_passager) AS codepassager,
                    SUM(p.prixvente) AS total, lg.nom_ligne, p.prixvente
                FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND EXISTS (
                  SELECT 1 FROM user_login ul
                  WHERE ul.uid_login = (
                      SELECT ar.idgestcompte
                      FROM attributions_role ar
                      WHERE ar.roleattribut = p.idcptuser
                      LIMIT 1
                  )
                  AND ul.guser = '$gid'
                 )
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    /*public function reporticketcpt($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente")->result(); return $this->normalize_ticket_prix_rows($rows);    }*/

    public function reporticketcptgr($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT * FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.statutvente = 1
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND EXISTS (
                  SELECT 1 FROM user_login ul
                  WHERE ul.uid_login = (
                      SELECT ar.idgestcompte
                      FROM attributions_role ar
                      WHERE ar.roleattribut = p.idcptuser
                      LIMIT 1
                  )
                  AND ul.guser = '$gid'
                )")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT * FROM passager p
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.statutvente = 1
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND EXISTS (
                  SELECT 1 FROM user_login ul
                  WHERE ul.uid_login = (
                      SELECT ar.idgestcompte
                      FROM attributions_role ar
                      WHERE ar.roleattribut = p.idcptuser
                      LIMIT 1
                  )
                  AND ul.guser = '$gid'
                )")->result(); return $this->normalize_ticket_prix_rows($rows);    }
    public function nifestad($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, dest.id_compaga, p.prixvente, h.heure FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND ul.guser = '$gid'
                AND p.statut_code = 'vendu'
                GROUP BY lg.nom_ligne, dest.id_compaga, p.prixvente, h.id_heure
                ORDER BY heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, dest.id_compaga, p.prixvente, h.heure FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                AND lg.ident_ligne = '$algn'
                GROUP BY lg.nom_ligne, dest.id_compaga, p.prixvente, h.id_heure
                ORDER BY heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function nifesthebad($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, dest.id_compaga, p.prixvente, h.heure, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                GROUP BY lg.nom_ligne, dest.id_compaga, p.prixvente, h.id_heure, p.datep_create
                ORDER BY p.datep_create, h.id_heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, dest.id_compaga, p.prixvente, h.heure, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                GROUP BY lg.nom_ligne, dest.id_compaga, p.prixvente, h.id_heure, p.datep_create
                ORDER BY p.datep_create, h.id_heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    
    public function nifestcptadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '')
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente, h.heure FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente, h.id_heure
                ORDER BY heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente, h.heure FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente, h.id_heure
                ORDER BY heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function nifesthebcptadmin($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente, h.heure, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente, h.id_heure, p.datep_create
                ORDER BY p.datep_create, h.id_heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente, h.heure, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente, h.id_heure, p.datep_create
                ORDER BY p.datep_create, h.id_heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }
    public function nifest($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente, h.heure FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente, h.id_heure
                ORDER BY heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente, h.heure FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente, h.id_heure
                ORDER BY heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function nifestheb($cid, $gid, $dt1, $dt2, $cp, $algn = FALSE)
    {
        
        if ($algn === '') 
        {
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente, h.heure, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND dest.id_compaga = '$cp'
                AND p.verifpassager IN('A', 'C', 'D')
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente, h.id_heure, p.datep_create
                ORDER BY p.datep_create, h.id_heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT COUNT(code_passager) AS codepassager, SUM(prixvente) AS total, lg.nom_ligne, p.prixvente, h.heure, p.datep_create FROM passager p
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND p.verifpassager IN('A', 'C', 'D')
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND lg.ident_ligne = '$algn'
                AND ul.guser = '$gid'
                GROUP BY lg.nom_ligne, p.prixvente, h.id_heure, p.datep_create
                ORDER BY p.datep_create, h.id_heure ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }
    //vente du jour par vendeur
    public function ventejour($cd, $gid, $idcox, $dd, $fd)
    {
            $today = mdate("%Y-%m-%d", now('UTC'));

            $rows = $this->db->query("SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND p.datep_create BETWEEN '$dd' AND '$fd'
                AND ar.roleattribut = '$idcox'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND p.actif_pas = 0
                GROUP BY p.idcptuser, dest.id_compaga, ctp.tamponcod, c.id_compagnie, p.code_ticket
                ORDER BY p.num_siege_categorie ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    //
    public function dayad($cid, $p_id = FALSE, $t = FALSE)
    {
        $today = mdate("%Y-%m-%d", now());
        if ($p_id === FALSE AND $t === FALSE) {
            $rows = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create ='$today'
                AND p.statut_code IS NOT NULL
                AND p.statut_confirme IS NULL
                AND p.num_siege_categorie IS NOT NULL
                AND p.actif_pas = 0
                AND p.statut_reprog IS NULL")->result(); return $this->normalize_ticket_prix_rows($rows);        } else
            $row = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create ='$today'
                AND p.code_passager = '$p_id'
                AND p.statut_code IS NOT NULL
                AND p.statut_confirme IS NULL
                AND p.num_siege_categorie IS NOT NULL
                AND p.actif_pas = 0
                AND p.statut_reprog IS NULL")->row(); return $this->normalize_ticket_prix_row($row);
    }

    public function day($cid, $gid, $p_id = FALSE, $t = FALSE)
    {
        $today = mdate("%Y-%m-%d", now());
        if ($p_id === FALSE AND $t === FALSE) {
            $rows = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create ='$today'
                AND p.statut_code IS NOT NULL
                AND p.statut_confirme IS NULL
                AND p.num_siege_categorie IS NOT NULL
                AND p.actif_pas = 0
                AND ex.code_gaexp = '$gid'
                AND p.statut_reprog IS NULL")->result(); return $this->normalize_ticket_prix_rows($rows);        } else
            $row = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN type_client tcl ON cl.type_client = tcl.nom_type
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.datep_create ='$today'
                AND p.code_passager = '$p_id'
                AND ex.code_gaexp = '$gid'
                AND p.statut_code IS NOT NULL
                AND p.statut_confirme IS NULL
                AND p.num_siege_categorie IS NOT NULL
                AND p.actif_pas = 0
                AND p.statut_reprog IS NULL")->row(); return $this->normalize_ticket_prix_row($row);
    }
    //ticket fidelite

    public function reduction($cid, $p_id = FALSE, $t = FALSE)
    {
        if ($p_id === FALSE AND $t === FALSE) {
            $rows = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.actif_pas = 0
                AND p.num_siege_categorie IS NOT NULL")->result(); return $this->normalize_ticket_prix_rows($rows);        } else
            $row = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.actif_pas = 0
                AND p.num_siege_categorie IS NOT NULL
                AND ctp.tamponcod = '$p_id'")->row(); return $this->normalize_ticket_prix_row($row);
    }

    public function reductad($cid, $p_id = FALSE, $t = FALSE, $p = FALSE)
    {
        if ($t === FALSE AND $p === FALSE) {
            $rows = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.actif_pas = 0
                AND ctp.tamponcod = '$p_id'
                AND p.num_siege_categorie IS NOT NULL")->result(); return $this->normalize_ticket_prix_rows($rows);        } else
            $row = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.actif_pas = 0
                AND p.num_siege_categorie IS NOT NULL
                AND ctp.tamponcod = '$p_id'
                AND lh.id_ligneheure = '$t'")->row(); return $this->normalize_ticket_prix_row($row);
    }

    public function reduct($cid, $p_id = FALSE, $t = FALSE, $p = FALSE)
    {
        if ($t === FALSE AND $p === FALSE) {
            $rows = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.actif_pas = 0
                AND ctp.tamponcod = '$p_id'
                AND p.num_siege_categorie IS NOT NULL")->result(); return $this->normalize_ticket_prix_rows($rows);        } else
            $row = $this->db->query(
                "SELECT * FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cid'
                AND p.actif_pas = 0
                AND p.num_siege_categorie IS NOT NULL
                AND ctp.tamponcod = '$p_id'
                AND lh.id_ligneheure = '$t'")->row(); return $this->normalize_ticket_prix_row($row);
    }

    public function histovente($cid, $gid, $dt1, $dt2, $acl, $cp = FALSE, $algn = FALSE)
    {
        
        if ($cp === '' AND $algn === '') {
            $rows = $this->db->query(
                "SELECT ctp.tamponcod, p.code_passager, p.code_ticket, cl.nom_client, cl.prenom_client, cl.contact_client, lg.nom_ligne, p.datep_create, h.heure, pr.dateheure_prog FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = $cid
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                AND p.verifpassager IN('A', 'C', 'D')
                ORDER BY datep_create ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        elseif($algn === '')
        {
            $rows = $this->db->query("SELECT ctp.tamponcod, p.code_passager, p.code_ticket, cl.nom_client, cl.prenom_client, cl.contact_client, lg.nom_ligne, p.datep_create, h.heure, pr.dateheure_prog FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = $cid
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND ul.guser = '$gid'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                AND p.verifpassager IN('A', 'C', 'D')
                ORDER BY datep_create ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT ctp.tamponcod, p.code_passager, p.code_ticket, cl.nom_client, cl.prenom_client, cl.contact_client, lg.nom_ligne, p.datep_create, h.heure, pr.dateheure_prog FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = $cid
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                AND dest.id_compaga = '$cp'
                AND lg.ident_ligne = '$algn'
                AND p.verifpassager IN('A', 'C', 'D')
                ORDER BY datep_create ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }

    public function histoventeadmin($cid, $gid, $dt1, $dt2, $acl, $cp = FALSE, $algn = FALSE)
    {
        
        if ($cp === '' AND $algn === '') {
            $rows = $this->db->query(
                "SELECT ctp.tamponcod, p.code_passager, p.code_ticket, cl.nom_client, cl.prenom_client, cl.contact_client, lg.nom_ligne, p.datep_create, h.heure, pr.dateheure_prog FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = $cid
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                ORDER BY datep_create ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
        elseif($algn === '')
        {
            $rows = $this->db->query("SELECT ctp.tamponcod, p.code_passager, p.code_ticket, cl.nom_client, cl.prenom_client, cl.contact_client, lg.nom_ligne, p.datep_create, h.heure, pr.dateheure_prog FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = $cid
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND ul.guser = '$gid'
                AND dest.id_compaga = '$cp'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                ORDER BY datep_create ASC")->result(); return $this->normalize_ticket_prix_rows($rows);        }
            $rows = $this->db->query(
                "SELECT ctp.tamponcod, p.code_passager, p.code_ticket, cl.nom_client, cl.prenom_client, cl.contact_client, lg.nom_ligne, p.datep_create, h.heure, pr.dateheure_prog FROM tamponcode ctp
                JOIN passager p ON p.code_passager = ctp.tamponcod
                JOIN sousgare sg ON p.departclient_idgare = sg.idsousgare 
                LEFT JOIN non_passager np ON np.code_non_pass = ctp.tamponcod 
                JOIN client cl ON p.id_client_pass = cl.id_client
                JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN gares g ON ul.guser = g.idengare
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN programme pr ON p.code_pro = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = $cid
                AND p.datep_create BETWEEN '$dt1' AND '$dt2'
                AND ul.guser = '$gid'
                AND p.prixvente IS NOT NULL
                AND p.statut_code = 'vendu'
                AND ar.roleattribut = '$acl'
                AND dest.id_compaga = '$cp'
                AND lg.ident_ligne = '$algn'
                ORDER BY datep_create ASC")->result(); return $this->normalize_ticket_prix_rows($rows);    }
}
    /** Passager_model.php **/
    /** application/models/Passager_model.php **/
