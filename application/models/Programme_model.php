<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Programme_model extends CI_Model
    {
        protected $table = 'programme';
        protected $table_siege_bloque = 'programme_siege_bloque';
        
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * Sièges décochés à l'édition d'un départ (hors vente).
         */
        public function ensure_siege_bloque_table()
        {
            $t = $this->table_siege_bloque;
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS {$t} (
                  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  code_progr VARCHAR(128) NOT NULL,
                  siege_num INT NOT NULL,
                  PRIMARY KEY (id),
                  UNIQUE KEY uq_prog_siege (code_progr, siege_num),
                  KEY idx_code_progr (code_progr)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        }

        /**
         * Ne conserve que les trous dans [debut, fin] (hors intervalle = hors quota, pas en base).
         *
         * @param int[] $bloques
         * @return int[]
         */
        public function normaliser_trous_intervalle(array $bloques, $debut, $fin)
        {
            $d = (int) $debut;
            $f = (int) $fin;
            $out = array();
            if ($d <= 0 || $f < $d) {
                return $out;
            }
            foreach ($bloques as $n) {
                $n = (int) $n;
                if ($n >= $d && $n <= $f) {
                    $out[$n] = $n;
                }
            }
            ksort($out);
            return array_values($out);
        }

        /**
         * Trous hors vente stockés pour le programme.
         * Si $debut/$fin fournis : uniquement ceux dans l'intervalle.
         *
         * @return int[]
         */
        public function sieges_bloques_programme($code_progr, $debut = null, $fin = null)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return array();
            }
            $this->ensure_siege_bloque_table();
            if (!$this->db->table_exists($this->table_siege_bloque)) {
                return array();
            }
            $rows = $this->db->query(
                "SELECT siege_num FROM {$this->table_siege_bloque}
                 WHERE code_progr = ?
                 ORDER BY siege_num ASC",
                array($code)
            )->result();
            $out = array();
            foreach ($rows as $r) {
                $n = (int) $r->siege_num;
                if ($n > 0) {
                    $out[] = $n;
                }
            }
            if ($debut !== null && $fin !== null) {
                return $this->normaliser_trous_intervalle($out, $debut, $fin);
            }
            return $out;
        }

        /**
         * Siège marqué hors vente (trou dans le quota) à l'édition du programme.
         */
        public function siege_est_bloque_programme($code_progr, $siege_num)
        {
            $n = (int) $siege_num;
            if ($n <= 0) {
                return false;
            }
            $bloques = $this->sieges_bloques_programme($code_progr);
            return in_array($n, $bloques, true);
        }

        /**
         * Remplace la liste des trous hors vente.
         * Ne stocke que les numéros dans [intervalle1, intervalle2] si fournis.
         *
         * @param int[] $bloques
         * @param int|null $intervalle1
         * @param int|null $intervalle2
         */
        public function sync_sieges_bloques_programme($code_progr, array $bloques, $intervalle1 = null, $intervalle2 = null)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return false;
            }
            $this->ensure_siege_bloque_table();
            if ($intervalle1 !== null && $intervalle2 !== null) {
                $norm = $this->normaliser_trous_intervalle($bloques, $intervalle1, $intervalle2);
            } else {
                $norm = array();
                foreach ($bloques as $n) {
                    $n = (int) $n;
                    if ($n > 0) {
                        $norm[$n] = $n;
                    }
                }
                $norm = array_values($norm);
            }
            $this->db->delete($this->table_siege_bloque, array('code_progr' => $code));
            foreach ($norm as $n) {
                $this->db->insert($this->table_siege_bloque, array(
                    'code_progr' => $code,
                    'siege_num' => (int) $n,
                ));
            }
            return true;
        }

        /**
         * @return string SQL AND … ou chaîne vide
         */
        protected function _cdprog_bloque_and($code_progr)
        {
            if (!$this->db->table_exists($this->table_siege_bloque)) {
                return '';
            }
            $code = trim((string) $code_progr);
            if ($code === '') {
                return '';
            }
            $codeEsc = $this->db->escape_str($code);
            $t = $this->table_siege_bloque;
            return " AND NOT EXISTS (
                SELECT 1 FROM {$t} b
                WHERE b.code_progr = '{$codeEsc}' AND b.siege_num = sc.siege_num
            )";
        }

        /**
         * Filtre sièges bloqués via pr.code_progr (requêtes multi-programmes).
         * @return string
         */
        protected function _cdprog_bloque_and_pr()
        {
            if (!$this->db->table_exists($this->table_siege_bloque)) {
                return '';
            }
            $t = $this->table_siege_bloque;
            return " AND NOT EXISTS (
                SELECT 1 FROM {$t} b
                WHERE b.code_progr = pr.code_progr AND b.siege_num = sc.siege_num
            )";
        }

        /**
         * Filtre passager actif uniquement (actif_pas = 0).
         *
         * @param string $alias ex. p, p2
         * @return string
         */
        protected function _cdprog_actif_pas_and($alias = 'p')
        {
            $a = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $alias);
            if ($a === '') {
                $a = 'p';
            }
            return " AND {$a}.actif_pas = 0";
        }

        /**
         * Exclut les sièges réservés en tampon (autre guichet / session).
         *
         * @param string[] $codes
         * @param string $aliasNum ex. sc.siege_num
         * @return string
         */
        protected function _cdprog_tampon_and(array $codes, $aliasNum = 'sc.siege_num')
        {
            if (!$this->db->table_exists('tampon_siege')) {
                return '';
            }
            $in = $this->_sql_in_codes($codes);
            if ($in === "''") {
                return '';
            }
            $aliasNum = preg_replace('/[^a-zA-Z0-9_.]/', '', (string) $aliasNum);
            if ($aliasNum === '') {
                $aliasNum = 'sc.siege_num';
            }
            return " AND NOT EXISTS (
                SELECT 1 FROM tampon_siege t
                WHERE t.codepro IN ({$in}) AND t.numsieg = {$aliasNum}
            )";
        }

        /**
         * Exclut tampon via pr.code_progr (requêtes multi-programmes / bus).
         *
         * @param string $aliasNum
         * @return string
         */
        protected function _cdprog_tampon_and_pr($aliasNum = 'sc.siege_num')
        {
            if (!$this->db->table_exists('tampon_siege')) {
                return '';
            }
            $aliasNum = preg_replace('/[^a-zA-Z0-9_.]/', '', (string) $aliasNum);
            if ($aliasNum === '') {
                $aliasNum = 'sc.siege_num';
            }
            return " AND NOT EXISTS (
                SELECT 1 FROM tampon_siege t
                WHERE t.codepro = pr.code_progr AND t.numsieg = {$aliasNum}
            )";
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }

        /**
         * Nouveau code_progr unique (préfixe date+gare + suffixe).
         * MAX(suffixe) + boucle EXISTS — jamais COUNT+1.
         *
         * @param string $gareidentif
         * @return string
         */
        public function nouveau_code_progr($gareidentif)
        {
            $today = mdate('%Y-%m-%d', now('UTC'));
            $gd = trim((string) $gareidentif);
            $gd4 = ($gd === 'OUA12') ? 'WUA12' : $gd;
            $prefix = mdate('%y%m%d', now('UTC')) . $gd4;
            $prefixLen = strlen($prefix);

            $row = $this->db->query(
                "SELECT MAX(CAST(SUBSTRING(code_progr, ?) AS UNSIGNED)) AS maxn
                 FROM programme
                 WHERE createdatepr = ?
                   AND gareidentif = ?
                   AND code_progr LIKE ?",
                array($prefixLen + 1, $today, $gd, $prefix . '%')
            )->row();
            $n = ($row && $row->maxn !== null && $row->maxn !== '') ? ((int) $row->maxn + 1) : 1;
            if ($n < 1) {
                $n = 1;
            }

            for ($i = 0; $i < 100; $i++) {
                $code = $prefix . (string) ($n + $i);
                $exists = $this->db->query(
                    'SELECT 1 AS ok FROM programme WHERE code_progr = ? LIMIT 1',
                    array($code)
                )->row();
                if (!$exists) {
                    return $code;
                }
            }

            return $prefix . (string) $n . 'T' . mdate('%H%i%s', now('UTC'));
        }

        /**
         * depart_code aligné sur le suffixe numérique de code_progr (jour+gare+n).
         *
         * @param string $gareidentif
         * @param string $code_progr
         * @return string
         */
        public function depart_code_depuis_code_progr($gareidentif, $code_progr)
        {
            $gd = trim((string) $gareidentif);
            $gd4 = ($gd === 'OUA12') ? 'WUA12' : $gd;
            $prefix = mdate('%y%m%d', now('UTC')) . $gd4;
            $code = trim((string) $code_progr);
            $suffix = (strpos($code, $prefix) === 0) ? substr($code, strlen($prefix)) : preg_replace('/\D+/', '', $code);
            if ($suffix === '' || $suffix === null) {
                $suffix = (string) time();
            }
            return mdate('%d', now('UTC')) . $gd4 . $suffix;
        }

        /**
         * INSERT programme avec codes uniques + vérification affected_rows.
         * Réessaie sur collision PK. Ne s'appuie pas sur insert_id() (PK string).
         *
         * @param array $data champs programme (code_progr / depart_code optionnels)
         * @param int   $maxAttempts
         * @return array{ok:bool,code_progr?:string,depart_code?:string,error?:string}
         */
        public function insert_programme(array $data, $maxAttempts = 5)
        {
            $gd = isset($data['gareidentif']) ? trim((string) $data['gareidentif']) : '';
            if ($gd === '') {
                return array('ok' => false, 'error' => 'gare_manquante');
            }

            if (!isset($data['statut_prog'])) {
                $data['statut_prog'] = 'actif';
            }
            if (!isset($data['actif_prog'])) {
                $data['actif_prog'] = 0;
            }
            if (!isset($data['createdatepr'])) {
                $data['createdatepr'] = mdate('%Y-%m-%d', now('UTC'));
            }
            if (!isset($data['createdpg_at'])) {
                $data['createdpg_at'] = now('UTC');
            }

            $maxAttempts = max(1, (int) $maxAttempts);
            for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                $needCode = empty($data['code_progr']);
                if ($needCode || $attempt > 0) {
                    $data['code_progr'] = $this->nouveau_code_progr($gd);
                }
                if (empty($data['depart_code']) || $attempt > 0) {
                    $data['depart_code'] = $this->depart_code_depuis_code_progr($gd, $data['code_progr']);
                }

                $inserted = $this->db->insert($this->table, $data);
                if ($inserted && (int) $this->db->affected_rows() === 1) {
                    $code = (string) $data['code_progr'];
                    $check = $this->db->query(
                        'SELECT code_progr FROM programme WHERE code_progr = ? LIMIT 1',
                        array($code)
                    )->row();
                    if (!$check) {
                        return array('ok' => false, 'error' => 'echec_creation_programme');
                    }
                    return array(
                        'ok' => true,
                        'code_progr' => $code,
                        'depart_code' => (string) $data['depart_code'],
                    );
                }

                $err = $this->db->error();
                $msg = isset($err['message']) ? (string) $err['message'] : '';
                $isDup = (stripos($msg, 'Duplicate') !== false)
                    || ((int) (isset($err['code']) ? $err['code'] : 0) === 1062);
                if (!$isDup && $attempt === 0) {
                    return array('ok' => false, 'error' => 'echec_creation_programme');
                }
                // Collision → régénérer codes au tour suivant.
                unset($data['code_progr'], $data['depart_code']);
            }

            return array('ok' => false, 'error' => 'echec_creation_programme');
        }


        /**
         * Conservé pour compat API UI. Fonctionnement hybride:
         * - départ gare (NULL) visible par toutes les sous-gares
         * - départ sous-gare (id) visible seulement par cette sous-gare
         */
        public function get_mode_depart($code_gaexp)
        {
            return 'hybride';
        }

        public function set_mode_depart($code_gaexp, $mode)
        {
            // Plus de bascule globale: la création choisit le scope (gare|sousgare).
            return TRUE;
        }

        /**
         * Un départ reconduit doit rester visible à la gare aval (actif),
         * sans écraser la portée sous-gares déjà choisie.
         */
        public function assurer_visibilite_reconduits($gareidentif)
        {
            $gare = trim((string) $gareidentif);
            if ($gare === '') {
                return;
            }
            $this->db->query(
                "UPDATE programme pr
                 JOIN programme_reconduction r ON r.code_progr_cible = pr.code_progr
                 SET pr.gareidentif = r.gare_cible,
                     pr.statut_prog = 'actif',
                     pr.actif_prog = 0
                 WHERE r.gare_cible = ?",
                array($gare)
            );
        }

        public function apply_mode_sousgare_toutes_gares()
        {
            return TRUE;
        }

        /**
         * Compat: scope gare|sousgare → idsousgare_prog legacy.
         */
        public function idsousgare_pour_creation($code_gaexp, $sgid, $scope = 'gare')
        {
            $scope = ($scope === 'sousgare') ? 'sousgare' : 'gare';
            if ($scope !== 'sousgare') {
                return null;
            }
            if ($sgid === null || $sgid === '' || $sgid === FALSE) {
                return null;
            }
            $sg = (int) $sgid;
            return ($sg > 0) ? $sg : null;
        }

        /**
         * Normalise la sélection de cases à cocher (ids sous-gares).
         * @return int[]
         */
        public function normaliser_selection_sousgares($selected)
        {
            if (!is_array($selected)) {
                return array();
            }
            $out = array();
            foreach ($selected as $v) {
                $sg = (int) $v;
                if ($sg > 0) {
                    $out[$sg] = $sg;
                }
            }
            return array_values($out);
        }

        /**
         * idsousgare_prog legacy d'après sélection:
         * - 0 ou toutes les SG de la gare => NULL
         * - 1 SG => cet id
         * - plusieurs (pas toutes) => NULL + lignes programme_sousgare
         */
        public function idsousgare_prog_depuis_selection(array $selected, $totalSousgaresGare = null)
        {
            $selected = $this->normaliser_selection_sousgares($selected);
            $n = count($selected);
            $total = ($totalSousgaresGare === null) ? null : (int) $totalSousgaresGare;
            if ($n === 0 || ($total !== null && $total > 0 && $n >= $total)) {
                return null;
            }
            if ($n === 1) {
                return (int) $selected[0];
            }
            return null;
        }

        public function get_portee_sousgares($code_progr)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return array();
            }
            $rows = $this->db->query(
                "SELECT idsousgare FROM programme_sousgare WHERE code_progr = ? ORDER BY idsousgare ASC",
                array($code)
            )->result();
            $out = array();
            foreach ($rows as $row) {
                $out[] = (int) $row->idsousgare;
            }
            return $out;
        }

        /**
         * Enregistre la portée multi. Si sélection vide ou = toutes => aucune ligne (legacy NULL = toutes).
         * Si 1 SG => aucune ligne (legacy idsousgare_prog = id).
         * Si N SG (sous-ensemble) => N lignes.
         */
        public function sync_portee_sousgares($code_progr, array $selected, $totalSousgaresGare = null)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return FALSE;
            }
            $selected = $this->normaliser_selection_sousgares($selected);
            $n = count($selected);
            $total = ($totalSousgaresGare === null) ? null : (int) $totalSousgaresGare;
            $this->db->where('code_progr', $code)->delete('programme_sousgare');
            if ($n === 0 || ($total !== null && $total > 0 && $n >= $total) || $n === 1) {
                return TRUE;
            }
            foreach ($selected as $sg) {
                $this->db->query(
                    "INSERT IGNORE INTO programme_sousgare (code_progr, idsousgare) VALUES (?, ?)",
                    array($code, (int) $sg)
                );
            }
            return TRUE;
        }

        /** Filtre liste/vente pour la sous-gare courante (legacy + multi). */
        public function sql_filtre_sousgare($idsousgare)
        {
            // 0 / '' = même sens que NULL (toute la gare).
            $touteGare = "(pr.idsousgare_prog IS NULL OR pr.idsousgare_prog = 0 OR pr.idsousgare_prog = '')";
            if ($idsousgare === null || $idsousgare === '' || $idsousgare === FALSE || (int) $idsousgare <= 0) {
                // Sans SG courante: uniquement départs « toutes gares » sans liste multi.
                return " AND {$touteGare}"
                    . " AND NOT EXISTS (SELECT 1 FROM programme_sousgare ps0 WHERE ps0.code_progr = pr.code_progr)";
            }
            $sg = (int) $idsousgare;
            return " AND ("
                . " EXISTS (SELECT 1 FROM programme_sousgare ps WHERE ps.code_progr = pr.code_progr AND ps.idsousgare = {$sg})"
                . " OR ("
                . " NOT EXISTS (SELECT 1 FROM programme_sousgare ps2 WHERE ps2.code_progr = pr.code_progr)"
                . " AND ({$touteGare} OR pr.idsousgare_prog = {$sg})"
                . " )"
                . " )";
        }

        /**
         * Lignes du même OD commercial : même gare départ, même ville dest, même compagnie dest.
         * OUA1-BOB32 et un autre code dest Bobo CBT restent ensemble ; CBT ≠ VIP.
         * @return string[]
         */
        public function ident_lignes_od_compatibles($axe)
        {
            $axe = trim((string) $axe);
            $out = array();
            if ($axe !== '') {
                $out[] = $axe;
            }
            if ($axe === '' || strpos($axe, '-') === FALSE) {
                return $out;
            }
            $row = $this->db->query(
                "SELECT lg.ident_ligne, lg.gaexp_lg, gd.id_compaga, gd.id_villega
                 FROM lignes lg
                 JOIN gare_dest gd ON gd.code_gadest = lg.gadest_lg
                 WHERE lg.ident_ligne = ?
                 LIMIT 1",
                array($axe)
            )->row();
            if (!$row || $row->gaexp_lg === '' || $row->id_compaga === '' || $row->id_compaga === null) {
                return $out;
            }
            $sql = "SELECT lg.ident_ligne
                    FROM lignes lg
                    JOIN gare_dest gd ON gd.code_gadest = lg.gadest_lg
                    WHERE lg.gaexp_lg = ?
                      AND gd.id_compaga = ?";
            $params = array($row->gaexp_lg, $row->id_compaga);
            $ville = (int) $row->id_villega;
            if ($ville > 0) {
                $sql .= " AND gd.id_villega = ?";
                $params[] = $ville;
            }
            $rows = $this->db->query($sql, $params)->result();
            foreach ($rows as $r) {
                $id = trim((string) $r->ident_ligne);
                if ($id !== '' && !in_array($id, $out, true)) {
                    $out[] = $id;
                }
            }
            return $out;
        }

        /**
         * Fragment SQL IN (...) pour ident_ligne (déjà échappé).
         */
        public function sql_in_ident_lignes(array $ids)
        {
            $esc = array();
            foreach ($ids as $id) {
                $id = trim((string) $id);
                if ($id !== '') {
                    $esc[] = "'" . $this->db->escape_str($id) . "'";
                }
            }
            return empty($esc) ? "''" : implode(',', $esc);
        }

        /**
         * Précharge compteurs / sous-gares / ventes pour la liste programmes (évite N+1 en vue).
         *
         * @param string[] $code_progrs
         * @return array{passager_nbr:array,sousgares:array,ventes_sg:array}
         */
        public function preload_page_stats(array $code_progrs)
        {
            $codes = array();
            foreach ($code_progrs as $c) {
                $c = trim((string) $c);
                if ($c !== '') {
                    $codes[$c] = $c;
                }
            }

            $empty = array(
                'passager_nbr' => array(),
                'sousgares' => array(),
                'ventes_sg' => array(),
            );
            if (empty($codes)) {
                return $empty;
            }

            $inParts = array();
            foreach ($codes as $c) {
                $inParts[] = "'" . $this->db->escape_str($c) . "'";
            }
            $in = implode(',', $inParts);

            $passager_nbr = array();
            foreach ($this->db->query(
                "SELECT code_pro, COUNT(code_passager) AS nbr FROM passager
                 WHERE code_pro IN ({$in})
                   AND actif_pas = 0
                   AND num_siege_categorie IS NOT NULL
                 GROUP BY code_pro"
            )->result() as $row) {
                $passager_nbr[$row->code_pro] = (int) $row->nbr;
            }

            $sousgares = array();
            foreach ($this->db->query(
                "SELECT ps.code_progr, ps.idsousgare, sg.nomsousgare
                 FROM programme_sousgare ps
                 LEFT JOIN sousgare sg ON sg.idsousgare = ps.idsousgare
                 WHERE ps.code_progr IN ({$in})"
            )->result() as $row) {
                if (!isset($sousgares[$row->code_progr])) {
                    $sousgares[$row->code_progr] = array();
                }
                $sousgares[$row->code_progr][] = $row;
            }

            $ventes_sg = array();
            foreach ($this->db->query(
                "SELECT code_pro, CAST(departclient_idgare AS UNSIGNED) AS sg, COUNT(*) AS nb
                 FROM passager
                 WHERE code_pro IN ({$in})
                   AND departclient_idgare IS NOT NULL
                   AND departclient_idgare != ''
                   AND CAST(departclient_idgare AS UNSIGNED) > 0
                 GROUP BY code_pro, CAST(departclient_idgare AS UNSIGNED)"
            )->result() as $row) {
                $sg = (int) $row->sg;
                $nb = (int) $row->nb;
                if ($sg <= 0 || $nb <= 0) {
                    continue;
                }
                if (!isset($ventes_sg[$row->code_pro])) {
                    $ventes_sg[$row->code_pro] = array();
                }
                $ventes_sg[$row->code_pro][$sg] = $nb;
            }

            return array(
                'passager_nbr' => $passager_nbr,
                'sousgares' => $sousgares,
                'ventes_sg' => $ventes_sg,
            );
        }

        /**
         * Nombre de ventes (passagers) par sous-gare sur ce programme.
         * @return array<int,int> idsousgare => nb
         */
        public function comptes_ventes_par_sousgare($code_progr)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return array();
            }
            $rows = $this->db->query(
                "SELECT CAST(departclient_idgare AS UNSIGNED) AS sg, COUNT(*) AS nb
                 FROM passager
                 WHERE code_pro = ?
                 AND departclient_idgare IS NOT NULL
                 AND departclient_idgare != ''
                 AND CAST(departclient_idgare AS UNSIGNED) > 0
                 GROUP BY CAST(departclient_idgare AS UNSIGNED)",
                array($code)
            )->result();
            $out = array();
            foreach ($rows as $row) {
                $sg = (int) $row->sg;
                $nb = (int) $row->nb;
                if ($sg > 0 && $nb > 0) {
                    $out[$sg] = $nb;
                }
            }
            return $out;
        }

        /**
         * Sous-gares ayant déjà au moins une vente (passager) sur ce programme.
         * @return int[]
         */
        public function sousgares_avec_vente($code_progr)
        {
            return array_map('intval', array_keys($this->comptes_ventes_par_sousgare($code_progr)));
        }

        /**
         * Peut-on appliquer la nouvelle portée ?
         * - NULL (gare) : toujours OK (on n'exclut personne)
         * - SG X : OK seulement si aucune autre sous-gare n'a déjà vendu sur ce départ
         *   (sinon on « retirerait » ces sous-gares du départ)
         */
        public function portee_edit_autorisee($code_progr, $new_idsousgare_prog)
        {
            $vendu = $this->sousgares_avec_vente($code_progr);
            if (empty($vendu)) {
                return TRUE;
            }
            if ($new_idsousgare_prog === null || $new_idsousgare_prog === '' || (int) $new_idsousgare_prog <= 0) {
                return TRUE;
            }
            $newSg = (int) $new_idsousgare_prog;
            foreach ($vendu as $sg) {
                if ((int) $sg !== $newSg) {
                    return FALSE;
                }
            }
            return TRUE;
        }

        /**
         * Codes programmes partageant le stock sièges (correspondance, reconduction, même bus/jour).
         * Correspondance option 3 : suite et dérivé ne partagent pas l'occupation (segments indépendants).
         *
         * @return string[]
         */
        public function codes_siege_stock($code_progr)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return array();
            }

            $codes = array($code => true);

            // Charger sur le super-objet CI (isset($this->…) ne marche pas dans un modèle).
            $this->load->model('Programme_correspondance_model', 'm_programme_correspondance');
            $corr = get_instance()->m_programme_correspondance;

            try {
                foreach ($this->codes_sieges_occupes($code) as $c) {
                    $c = trim((string) $c);
                    if ($c !== '') {
                        $codes[$c] = true;
                    }
                }
            } catch (Exception $e) {
                // ignore
            }

            // Complément reconduit = stock propre : ne pas fusionner avec le principal du créneau.
            $estCibleReco = $this->db->query(
                "SELECT 1 FROM programme_reconduction WHERE code_progr_cible = ? LIMIT 1",
                array($code)
            )->row();

            $pr = $this->db->query(
                "SELECT depart_code, date_progr FROM programme WHERE code_progr = ? LIMIT 1",
                array($code)
            )->row();
            if (!$estCibleReco && $pr && !empty($pr->depart_code) && !empty($pr->date_progr)) {
                $siblings = $this->db->query(
                    "SELECT pr.code_progr
                     FROM programme pr
                     LEFT JOIN programme_reconduction r ON r.code_progr_cible = pr.code_progr
                     WHERE pr.depart_code = ? AND pr.date_progr = ?
                       AND r.code_progr_cible IS NULL",
                    array($pr->depart_code, $pr->date_progr)
                )->result();
                foreach ($siblings as $s) {
                    $c = trim((string) $s->code_progr);
                    if ($c === '') {
                        continue;
                    }
                    // Option 3 : suite ∥ dérivé ne se bloquent pas.
                    if ($corr
                        && method_exists($corr, 'siege_occupation_compatible')
                        && !$corr->siege_occupation_compatible($code, $c)
                    ) {
                        continue;
                    }
                    $codes[$c] = true;
                }
            }

            return array_keys($codes);
        }

        /**
         * Sièges déjà vendus sur un programme (actifs).
         * Inclut codes partagés (correspondance) + même depart_code / date.
         * @return int[]
         */
        public function sieges_occupes_programme($code_progr)
        {
            $codes = $this->codes_siege_stock($code_progr);
            if (empty($codes)) {
                return array();
            }

            $in = $this->_sql_in_codes($codes);
            $rows = $this->db->query(
                "SELECT DISTINCT p.num_siege_categorie AS n
                 FROM passager p
                 WHERE p.code_pro IN ({$in})
                   AND p.num_siege_categorie IS NOT NULL
                   AND p.num_siege_categorie > 0
                   AND p.actif_pas = 0"
            )->result();
            $out = array();
            foreach ($rows as $r) {
                $n = (int) $r->n;
                if ($n > 0) {
                    $out[$n] = $n;
                }
            }
            ksort($out);
            return array_values($out);
        }

        /**
         * Libère des sièges vendus (passager conservé, num_siege_categorie = NULL).
         * Appliqué sur tous les codes sièges partagés du départ.
         * @param int[] $sieges
         * @return array{ok:bool,error?:string,liberes?:int[]}
         */
        public function liberer_sieges_programme($code_progr, array $sieges)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return array('ok' => false, 'error' => 'programme_introuvable');
            }
            $norm = array();
            foreach ($sieges as $n) {
                $n = (int) $n;
                if ($n > 0) {
                    $norm[$n] = $n;
                }
            }
            if (empty($norm)) {
                return array('ok' => true, 'liberes' => array());
            }
            $codes = $this->codes_sieges_occupes($code);
            if (empty($codes)) {
                $codes = array($code);
            }
            $in = $this->_sql_in_codes($codes);
            $liberes = array();
            $this->db->trans_begin();
            foreach ($norm as $n) {
                $this->db->query(
                    "UPDATE passager
                     SET num_siege_categorie = NULL
                     WHERE code_pro IN ({$in})
                       AND num_siege_categorie = ?
                       AND actif_pas = 0",
                    array((int) $n)
                );
                if ($this->db->affected_rows() > 0) {
                    $liberes[] = (int) $n;
                }
            }
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return array('ok' => false, 'error' => 'echec_liberation');
            }
            $this->db->trans_commit();
            // OK même si certains numéros n'étaient pas vendus (trou libre ignoré)
            return array('ok' => true, 'liberes' => $liberes);
        }

        /**
         * Valide intervalle1/2 pour création ou édition d'un départ.
         * @param int[] $sieges_liberer Sièges vendus à libérer (hors quota obligatoire)
         * @param int[] $sieges_bloques Sièges décochés hors vente
         * @return array{ok:bool,error?:string,intervalle1?:int,intervalle2?:int,sieges_bloques?:int[]}
         */
        public function valider_quota_depart($debut, $fin, $categorie, $code_progr = null, array $sieges_liberer = array(), array $sieges_bloques = array())
        {
            $d = (int) $debut;
            $f = (int) $fin;
            $cat = trim((string) $categorie);
            if ($cat === '') {
                return array('ok' => false, 'error' => 'categorie_manquante');
            }
            if ($d <= 0 || $f <= 0 || $f < $d) {
                return array('ok' => false, 'error' => 'quota_invalide');
            }
            $row = $this->db->query(
                "SELECT nbr_place FROM categorie WHERE categorie = ? LIMIT 1",
                array($cat)
            )->row();
            if (!$row) {
                return array('ok' => false, 'error' => 'categorie_introuvable');
            }
            $max = (int) $row->nbr_place;
            if ($max <= 0 || $d < 1 || $f > $max) {
                return array('ok' => false, 'error' => 'quota_hors_bus');
            }
            $liberer = array();
            foreach ($sieges_liberer as $n) {
                $n = (int) $n;
                if ($n > 0) {
                    $liberer[$n] = true;
                }
            }

            $occupes = array();
            $code = trim((string) $code_progr);
            if ($code !== '') {
                foreach ($this->sieges_occupes_programme($code) as $n) {
                    $occupes[$n] = true;
                    if (!empty($liberer[$n])) {
                        continue;
                    }
                    if ($n < $d || $n > $f) {
                        return array('ok' => false, 'error' => 'quota_exclut_vendu');
                    }
                }
            }

            // Trous uniquement dans [d,f] : hors intervalle = hors quota (pas stocké).
            $bloquesNorm = array();
            foreach ($sieges_bloques as $n) {
                $n = (int) $n;
                if ($n < $d || $n > $f || $n > $max) {
                    continue;
                }
                if (!empty($liberer[$n])) {
                    // Libéré puis décoché : trou hors vente OK.
                    $bloquesNorm[$n] = $n;
                    continue;
                }
                if (!empty($occupes[$n])) {
                    return array('ok' => false, 'error' => 'bloque_vendu');
                }
                $bloquesNorm[$n] = $n;
            }
            ksort($bloquesNorm);

            return array(
                'ok' => true,
                'intervalle1' => $d,
                'intervalle2' => $f,
                'sieges_bloques' => array_values($bloquesNorm),
            );
        }

        /**
         * Édition multi: la nouvelle sélection doit inclure chaque SG ayant déjà vendu.
         * Sélection vide / toutes = OK.
         */
        public function portee_selection_autorisee($code_progr, array $selected, $totalSousgaresGare = null)
        {
            $vendu = $this->sousgares_avec_vente($code_progr);
            if (empty($vendu)) {
                return TRUE;
            }
            $selected = $this->normaliser_selection_sousgares($selected);
            $n = count($selected);
            $total = ($totalSousgaresGare === null) ? null : (int) $totalSousgaresGare;
            if ($n === 0 || ($total !== null && $total > 0 && $n >= $total)) {
                return TRUE;
            }
            $set = array();
            foreach ($selected as $sg) {
                $set[(int) $sg] = TRUE;
            }
            foreach ($vendu as $sg) {
                if (empty($set[(int) $sg])) {
                    return FALSE;
                }
            }
            return TRUE;
        }

        /**
         * Résout le programme à vendre pour ligne+date+heure (+ sous-gare optionnelle).
         * Hybride: 1) départ propre sous-gare, 2) sinon départ commun gare (NULL).
         * @return object|null
         */
        public function resoudre_depart($cid, $ligne, $date, $id_ligneheure, $idsousgare = null)
        {
            $cidEsc = $this->db->escape_str($cid);
            $lgEsc = $this->db->escape_str($ligne);
            $dtEsc = $this->db->escape_str($date);
            $lhEsc = $this->db->escape_str($id_ligneheure);
            $sg = ($idsousgare === null || $idsousgare === '' || $idsousgare === FALSE)
                ? null
                : (int) $idsousgare;

            $base = "SELECT pr.*, lh.id_ligneheure, lh.ligne_id AS ident_ligne, h.heure, lg.nom_ligne,
                            t.id_tarifs AS typetarif, t.type_tarifs
                     FROM programme pr
                     JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                     JOIN heures h ON lh.heure_identif = h.id_heure
                     JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                     JOIN tarifs t ON pr.typetarif = t.id_tarifs
                     JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                     JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                     JOIN entreprise e ON c.id_entrep = e.id_entreprise
                     WHERE e.ekey = '{$cidEsc}'
                     AND lh.ligne_id = '{$lgEsc}'
                     AND pr.date_progr = '{$dtEsc}'
                     AND lh.id_ligneheure = '{$lhEsc}'
                     AND pr.statut_prog = 'actif'
                     AND pr.actif_prog = 0
                     AND h.h_active = 1";

            if ($sg !== null && $sg > 0) {
                // 1) Départ multi listant explicitement cette SG
                $row = $this->db->query(
                    $base . " AND EXISTS (SELECT 1 FROM programme_sousgare ps WHERE ps.code_progr = pr.code_progr AND ps.idsousgare = {$sg})"
                    . " ORDER BY pr.code_progr DESC LIMIT 1"
                )->row();
                if ($row) {
                    return $row;
                }
                // 2) Legacy: départ propre à cette SG
                $row = $this->db->query(
                    $base . " AND pr.idsousgare_prog = {$sg}"
                    . " AND NOT EXISTS (SELECT 1 FROM programme_sousgare ps WHERE ps.code_progr = pr.code_progr)"
                    . " ORDER BY pr.code_progr DESC LIMIT 1"
                )->row();
                if ($row) {
                    return $row;
                }
            }

            // 3) Départ commun gare (legacy NULL, sans liste multi)
            return $this->db->query(
                $base . " AND pr.idsousgare_prog IS NULL"
                . " AND NOT EXISTS (SELECT 1 FROM programme_sousgare ps WHERE ps.code_progr = pr.code_progr)"
                . " ORDER BY pr.code_progr DESC LIMIT 1"
            )->row();
        }

        /**
         * Tous les départs actifs visibles pour une heure catalogue (vente multi / « bis »).
         * Même jointures que resoudre_depart + filtre sous-gare hybride, sans LIMIT 1.
         * @return array
         */
        public function lister_departs_actifs($cid, $ligne, $date, $id_ligneheure, $idsousgare = null)
        {
            $cidEsc = $this->db->escape_str($cid);
            $lgEsc = $this->db->escape_str($ligne);
            $dtEsc = $this->db->escape_str($date);
            $lhEsc = $this->db->escape_str($id_ligneheure);
            $sgFilter = $this->sql_filtre_sousgare($idsousgare);
            $inLignes = $this->sql_in_ident_lignes($this->ident_lignes_od_compatibles($ligne));

            $sql = "SELECT pr.*, lh.id_ligneheure, lh.ligne_id AS ident_ligne, h.heure, lg.nom_ligne,
                           t.id_tarifs AS typetarif, t.type_tarifs,
                           (SELECT tf.prix FROM tarification tf
                             WHERE tf.ligne_heure_id = lh.id_ligneheure
                               AND tf.typetarif_id = pr.typetarif
                               AND tf.actif_taf = 1
                             ORDER BY tf.typeclient_id ASC
                             LIMIT 1) AS prix
                    FROM programme pr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '{$cidEsc}'
                    AND lh.ligne_id IN ({$inLignes})
                    AND pr.date_progr = '{$dtEsc}'
                    AND lh.id_ligneheure = '{$lhEsc}'
                    AND pr.statut_prog = 'actif'
                    AND pr.actif_prog = 0
                    AND h.h_active = 1
                    {$sgFilter}
                    ORDER BY (lh.ligne_id = '{$lgEsc}') DESC, pr.code_progr DESC";

            $rows = $this->db->query($sql)->result();
            if (!empty($rows)) {
                return is_array($rows) ? $rows : array();
            }

            // Même ligne + même HH:MM (catalogue VIP vs départ réel sur un autre id_ligneheure).
            $hhRow = $this->db->query(
                "SELECT LEFT(CAST(h.heure AS CHAR), 5) AS hhmm
                 FROM ligne_heure lh
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 WHERE lh.id_ligneheure = ?
                 LIMIT 1",
                array($id_ligneheure)
            )->row();
            $hhmm = ($hhRow && !empty($hhRow->hhmm)) ? $hhRow->hhmm : '';
            if ($hhmm === '' || !preg_match('/^\d{2}:\d{2}$/', $hhmm)) {
                return array();
            }
            $hhEsc = $this->db->escape_str($hhmm);
            $sqlHh = "SELECT pr.*, lh.id_ligneheure, lh.ligne_id AS ident_ligne, h.heure, lg.nom_ligne,
                           t.id_tarifs AS typetarif, t.type_tarifs,
                           (SELECT tf.prix FROM tarification tf
                             WHERE tf.ligne_heure_id = lh.id_ligneheure
                               AND tf.typetarif_id = pr.typetarif
                               AND tf.actif_taf = 1
                             ORDER BY tf.typeclient_id ASC
                             LIMIT 1) AS prix
                    FROM programme pr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '{$cidEsc}'
                    AND lh.ligne_id IN ({$inLignes})
                    AND pr.date_progr = '{$dtEsc}'
                    AND LEFT(CAST(h.heure AS CHAR), 5) = '{$hhEsc}'
                    AND pr.statut_prog = 'actif'
                    AND pr.actif_prog = 0
                    AND h.h_active = 1
                    {$sgFilter}
                    ORDER BY (lh.ligne_id = '{$lgEsc}') DESC, pr.code_progr DESC";
            $rowsHh = $this->db->query($sqlHh)->result();
            return is_array($rowsHh) ? $rowsHh : array();
        }


            
                
        public function update($code_progr, array $data)
        {
            return $this->db->where('code_progr', $code_progr)
            ->update($this->table, $data);
        }

        public function del($id)
        {
            return $this->db->where('code_progr', $id)->delete($this->table);
        }

        /**
         * Vérifie si un programme peut être supprimé (aucun passager / vente).
         *
         * @param string $code_progr
         * @return array{ok:bool, reason?:string}
         */
        public function peut_supprimer($code_progr)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return array('ok' => false, 'reason' => 'code_vide');
            }

            $row = $this->db->query(
                "SELECT COUNT(*) AS nbr FROM passager WHERE code_pro = ?",
                array($code)
            )->row();
            if ($row && (int) $row->nbr > 0) {
                return array('ok' => false, 'reason' => 'passagers');
            }

            if (!empty($this->comptes_ventes_par_sousgare($code))) {
                return array('ok' => false, 'reason' => 'ventes');
            }

            return array('ok' => true);
        }

        /**
         * Supprime un programme sans passager (portée sous-gares incluse).
         *
         * @param string $code_progr
         * @return bool
         */
        public function supprimer_programme($code_progr)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return false;
            }
            $this->db->where('code_progr', $code)->delete('programme_sousgare');
            return (bool) $this->del($code);
        }
        
        public function getpr($cd, $pr_id, $lh)
        {
            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN categorie ct ON pr.categori = ct.categorie
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND pr.code_progr = '$pr_id'
                AND lh.id_ligneheure ='$lh'
                AND h.h_active = 1
                AND pr.actif_prog = 0")->result();
        }

        public function cdpgbus($cd, $g, $h, $dt)
        {
            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND pr.gareidentif = '$g'
                AND h.heure ='$h'
                AND h.h_active = 1
                AND pr.date_progr = '$dt'
                GROUP BY pr.depart_code, pr.code_progr, tf.id_tarification, c.id_compagnie")->result();
        }

        public function getch($cd, $id, $dt)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }

            
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;

            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND lh.ligne_id = '$id'
                AND pr.date_progr >='$dt'
                AND pr.date_progr <= DATE_ADD('$dt', INTERVAL 1 DAY)
                AND pr.statut_prog ='actif'
                AND h.h_active = 1
                AND pr.actif_prog = 0
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                ORDER BY h.heure ASC")->result();
        }

        public function getchtr($cd, $id, $dt, $t)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }

            
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;

            $cd = $this->db->escape_str($cd);
            $id = $this->db->escape_str($id);
            $dt = $this->db->escape_str($dt);
            $dtoday = $this->db->escape_str($dtoday);

            // Tarif optionnel : si vide, ne pas filtrer (dérivé hub peut avoir un typetarif ≠ jambe 1).
            $tarifSql = '';
            if ($t !== null && $t !== '' && $t !== '0') {
                $t = $this->db->escape_str($t);
                $tarifSql = "AND pr.typetarif = '{$t}'";
            }

            // Requête légère (sans JOIN tarification) + compagnie pour le guichet.
            return $this->db->query(
                "SELECT pr.code_progr, pr.intervalle1, pr.intervalle2, pr.date_progr,
                        pr.typetarif, pr.categori,
                        lh.id_ligneheure, h.heure,
                        lg.ident_ligne, lg.nom_ligne,
                        c.cle_compagnie, c.nom_compagnie,
                        ex.id_compagd,
                        (SELECT tf.prix FROM tarification tf
                          WHERE tf.ligne_heure_id = lh.id_ligneheure
                            AND tf.typetarif_id = pr.typetarif
                            AND tf.actif_taf = 1
                          ORDER BY tf.typeclient_id ASC
                          LIMIT 1) AS prix
                FROM programme pr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '{$cd}'
                AND lh.ligne_id = '{$id}'
                AND pr.date_progr >='{$dt}'
                AND pr.date_progr <= DATE_ADD('{$dt}', INTERVAL 1 DAY)
                AND pr.statut_prog ='actif'
                AND h.h_active = 1
                AND pr.actif_prog = 0
                {$tarifSql}
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '{$dtoday}'
                ORDER BY pr.date_progr ASC, h.heure ASC
                LIMIT 200")->result();
        }

        /**
         * Programmes d’un segment reprog : compagnie = gare d’arrivée (id_compaga),
         * comme vente / tickets / « lignes par compagnie d’arrivée ».
         *
         * En reprog : élargit aux lignes jumelles OD (OUAGA-BAMAKO ↔ OUAGA-BAMAKO_VIP)
         * pour lister toutes les compagnies disponibles à la date — pas seulement la
         * ligne CMT de l’étape graphe.
         *
         * @param string|null $cie     Filtre id_compaga (étape) — ignoré si $expand_siblings
         * @param string|null $gadest  Filtre code_gadest (étape)
         * @param bool        $expand_siblings  true = multi-cie (défaut reprog)
         */
        public function getch_seg_reprog($cd, $id, $dt, $t = null, $cie = null, $gadest = null, $expand_siblings = true)
        {
            $ekeyRaw = trim((string) $cd);
            $cd = $this->db->escape_str($ekeyRaw);
            $id = trim((string) $id);
            $idEsc = $this->db->escape_str($id);
            $dt = $this->db->escape_str($dt);

            $tarifSql = '';
            if ($t !== null && $t !== '' && $t !== '0') {
                $t = $this->db->escape_str($t);
                $tarifSql = " AND pr.typetarif = '{$t}' ";
            }

            // Multi-cie : ne pas filtrer dur sur la cie de l’étape (sinon VIP invisible).
            $cieSql = '';
            if (!$expand_siblings && $cie !== null && trim((string) $cie) !== '') {
                $cie = $this->db->escape_str(trim((string) $cie));
                $cieSql = " AND ga.id_compaga = '{$cie}' ";
            }

            $gadestSql = '';
            if ($gadest !== null && trim((string) $gadest) !== '') {
                $gadest = $this->db->escape_str(trim((string) $gadest));
                $gadestSql = " AND lg.gadest_lg = '{$gadest}' ";
            }

            $ligneSql = " AND lh.ligne_id = '{$idEsc}' ";
            if ($expand_siblings && $id !== '') {
                $meta = $this->db->query(
                    "SELECT lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg
                     FROM lignes lg WHERE lg.ident_ligne = ? LIMIT 1",
                    array($id)
                )->row();
                if ($meta && trim((string) $meta->nom_ligne) !== '') {
                    $nom = trim((string) $meta->nom_ligne);
                    $base = preg_replace('/_(VIP|CMT|ORD|EXPRESS|STD|CMTSD)$/i', '', $nom);
                    if ($base === null || $base === '') {
                        $base = $nom;
                    }
                    $gaexp = trim((string) $meta->gaexp_lg);
                    $ids = array($id);
                    foreach (array($nom, $base) as $nTry) {
                        if ($nTry === '') {
                            continue;
                        }
                        $axes = $this->axes_par_nom_ligne($nTry, $ekeyRaw, $gaexp !== '' ? $gaexp : null, null);
                        if (is_array($axes)) {
                            foreach ($axes as $ax) {
                                $ax = trim((string) $ax);
                                if ($ax !== '') {
                                    $ids[] = $ax;
                                }
                            }
                        }
                    }
                    // Variantes inverse : base_VIP si on part de base seule.
                    if ($base !== '' && strcasecmp($base, $nom) === 0) {
                        foreach (array('_VIP', '_CMT', '_ORD') as $suf) {
                            $axes2 = $this->axes_par_nom_ligne($base . $suf, $ekeyRaw, $gaexp !== '' ? $gaexp : null, null);
                            if (is_array($axes2)) {
                                foreach ($axes2 as $ax) {
                                    $ax = trim((string) $ax);
                                    if ($ax !== '') {
                                        $ids[] = $ax;
                                    }
                                }
                            }
                        }
                    }
                    $ids = array_values(array_unique($ids));
                    if (count($ids) > 1) {
                        $in = array();
                        foreach ($ids as $lid) {
                            $in[] = "'" . $this->db->escape_str($lid) . "'";
                        }
                        $ligneSql = ' AND lh.ligne_id IN (' . implode(',', $in) . ') ';
                        // Destination : restreindre à la même ville d’arrivée métier si possible.
                        if ($gadestSql === '' && !empty($meta->gadest_lg)) {
                            $gadestSql = " AND EXISTS (
                                SELECT 1 FROM gare_dest ga0
                                JOIN gare_dest ga1 ON ga1.code_gadest = '"
                                . $this->db->escape_str(trim((string) $meta->gadest_lg)) . "'
                                WHERE ga0.code_gadest = lg.gadest_lg
                                  AND ga0.id_villega = ga1.id_villega
                            ) ";
                        }
                    }
                }
            }

            // Entreprise via cie départ ; libellé / clé commerciale = cie d’arrivée.
            return $this->db->query(
                "SELECT pr.code_progr, pr.intervalle1, pr.intervalle2, pr.date_progr,
                        pr.typetarif, pr.categori, pr.gareidentif,
                        lh.id_ligneheure, h.heure,
                        lg.ident_ligne, lg.nom_ligne, lg.gaexp_lg, lg.gadest_lg,
                        ga.code_gadest, ga.id_compaga,
                        ca.cle_compagnie AS cle_compagnie,
                        ca.nom_compagnie AS nom_compagnie,
                        ca.cle_compagnie AS cle_compagnie_arrivee,
                        ca.nom_compagnie AS nom_compagnie_arrivee,
                        ex.id_compagd,
                        cd.cle_compagnie AS cle_compagnie_depart,
                        cd.nom_compagnie AS nom_compagnie_depart,
                        (SELECT tf.prix FROM tarification tf
                          WHERE tf.ligne_heure_id = lh.id_ligneheure
                            AND tf.typetarif_id = pr.typetarif
                            AND tf.actif_taf = 1
                          ORDER BY tf.typeclient_id ASC
                          LIMIT 1) AS prix
                FROM programme pr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN compagnies cd ON ex.id_compagd = cd.cle_compagnie
                JOIN entreprise e ON cd.id_entrep = e.id_entreprise
                JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                WHERE e.ekey = '{$cd}'
                {$ligneSql}
                AND pr.date_progr >= '{$dt}'
                AND pr.date_progr <= DATE_ADD('{$dt}', INTERVAL 1 DAY)
                AND pr.statut_prog = 'actif'
                AND h.h_active = 1
                AND pr.actif_prog = 0
                {$tarifSql}
                {$cieSql}
                {$gadestSql}
                ORDER BY pr.date_progr ASC, h.heure ASC
                LIMIT 200")->result();
        }
        
        public function get($cd, $pr_id = FALSE)
        {
            if ($pr_id === FALSE) 
            {
                return $this->db->query(
                    "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0")->result();
            } else
                return $this->db->query(
                    "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.id_entreprise = '$cd'
                    AND pr.code_progr = '$pr_id'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0")->row();
        }
        
        //all prgo
        public function getall($cd, $cdg, $pr_id = FALSE, $idsousgare = null)
        {
            $today = mdate("%Y-%m-%d", now('UTC'));
            // Liste admin gare : sans SG explicite, montrer TOUS les départs de la gare
            // (y compris portées multi sous-gares). Le filtre restrictif sql_filtre_sousgare(null)
            // est réservé à la résolution vente, pas à l’écran Programmes.
            $sgFilter = '';
            if ($idsousgare !== null && $idsousgare !== '' && $idsousgare !== FALSE && (int) $idsousgare > 0) {
                $sgFilter = $this->sql_filtre_sousgare($idsousgare);
            }
            $CI =& get_instance();
            if (!isset($CI->m_programme_reconduction)) {
                $CI->load->model('Programme_reconduction_model', 'm_programme_reconduction');
            }
            // Compagnie d'arrivée = compagnie de la gare de destination de la ligne.
            $selectArrivee = "pr.*, lh.*, h.*, lg.*, t.*, ct.*, ex.*, e.*,
                    ca.nom_compagnie AS nom_compagnie_arrivee,
                    ca.cle_compagnie AS cle_compagnie_arrivee,
                    ga.nom_gadest, ga.code_gadest, ga.id_compaga";
            $joinsArrivee = "FROM programme pr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise";
            if ($pr_id === FALSE)
            {
                $cdgEsc = $this->db->escape_str($cdg);
                $CI->m_programme_reconduction->realigner_compagnie_cibles($cdg);
                $this->assurer_visibilite_reconduits($cdg);
                return $this->db->query(
                    "SELECT {$selectArrivee}
                    {$joinsArrivee}
                    WHERE e.id_entreprise = '$cd'
                    AND pr.date_progr >= '$today'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
                    AND (
                        (pr.gareidentif = '$cdg')
                        OR pr.code_progr IN (
                            SELECT r.code_progr_cible FROM programme_reconduction r
                            WHERE r.gare_cible = '{$cdgEsc}'
                        )
                    )
                    {$sgFilter}
                    ORDER BY ca.nom_compagnie ASC, pr.date_progr ASC, h.heure ASC")->result();
            } else
                return $this->db->query(
                    "SELECT {$selectArrivee}
                    {$joinsArrivee}
                    WHERE e.id_entreprise = '$cd'
                    AND pr.code_progr = '$pr_id'
                    AND pr.gareidentif = '$cdg'
                    AND pr.date_progr >= '$today'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
                    {$sgFilter}")->row();
        }

        /**
         * Regroupe des programmes déjà chargés par compagnie d'arrivée.
         *
         * @param array $rows
         * @return array [cle_compagnie => [nom_compagnie, cle_compagnie, programmes[]]]
         */
        public function group_by_compagnie_arrivee($rows)
        {
            $groups = array();
            if (empty($rows)) {
                return $groups;
            }
            foreach ($rows as $row) {
                $key = isset($row->cle_compagnie_arrivee) ? (string) $row->cle_compagnie_arrivee : '';
                if ($key === '' && isset($row->id_compaga)) {
                    $key = (string) $row->id_compaga;
                }
                if ($key === '') {
                    $key = '_sans';
                }
                if (!isset($groups[$key])) {
                    $nom = !empty($row->nom_compagnie_arrivee)
                        ? $row->nom_compagnie_arrivee
                        : 'Sans compagnie';
                    $groups[$key] = array(
                        'cle_compagnie' => $key === '_sans' ? null : $key,
                        'nom_compagnie' => $nom,
                        'programmes' => array(),
                    );
                }
                $groups[$key]['programmes'][] = $row;
            }
            return $groups;
        }


        //lignes
        public function sousligne($cid, $cdar, $h)
        {
            return $this->db->query(
                "SELECT * FROM lignes lg
                    JOIN ligne_heure lh ON lh.ligne_id = lg.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ge ON lg.gaexp_lg	= ge.code_gaexp
                    JOIN ville v ON ga.id_villega = v.id_ville
                    JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.gadest_lg = '$cdar'
                    AND lh.heure_identif = '$h'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0")->result();
        }
        /**
         * HH:MM d'un horaire catalogue (08:00:00 → 08:00).
         */
        protected function _heure_hhmm($heure)
        {
            $hh = substr(trim((string) $heure), 0, 5);
            return preg_match('/^\d{2}:\d{2}$/', $hh) ? $hh : '';
        }

        /**
         * Heures vente guichet pour un OD + date.
         * has_programme = un départ existe sur cet OD commercial (même ville dest + même compagnie),
         * à la même horloge — pas seulement le même id_ligneheure catalogue.
         * Les départs d'une autre compagnie (CBT Ouaga-Bobo) ne sont pas vendables en VIP.
         */
        public function heures_vente_od($cid, $axe, $date, $idsousgare = null)
        {
            $catalogue = $this->heureligne1($cid, $axe, $date);
            if (!is_array($catalogue)) {
                $catalogue = array();
            }

            $axeEsc = $this->db->escape_str($axe);
            $dateEsc = $this->db->escape_str($date);
            $cidEsc = $this->db->escape_str($cid);
            $sg = ($idsousgare === null || $idsousgare === '' || $idsousgare === FALSE)
                ? null
                : (int) $idsousgare;

            $sgFilter = $this->sql_filtre_sousgare($sg);
            $lignesOd = $this->ident_lignes_od_compatibles($axe);
            $inLignes = $this->sql_in_ident_lignes($lignesOd);
            $lignesOdSet = array();
            foreach ($lignesOd as $idLg) {
                $lignesOdSet[(string) $idLg] = TRUE;
            }

            $gaexp = '';
            if (strpos($axe, '-') !== FALSE) {
                $gaexp = explode('-', $axe, 2)[0];
            }
            $gaexpEsc = $this->db->escape_str($gaexp);
            $mode = 'hybride';

            $CI =& get_instance();
            $CI->load->library('graphe_correspondance');
            $evalTransit = $CI->graphe_correspondance->evaluer_transit_od($cid, $axe, $date, $sg);
            $has_transit = !empty($evalTransit['has_transit']);
            $transit_sources = !empty($evalTransit['sources']) ? $evalTransit['sources'] : array();

            // Priorité : portée exacte sous-gare > quota mono-SG > toute gare.
            $porteeOrder = '(pr.idsousgare_prog IS NULL) ASC';
            if ($sg !== null && $sg > 0) {
                $porteeOrder = "(CASE
                    WHEN EXISTS (
                        SELECT 1 FROM programme_sousgare psx
                        WHERE psx.code_progr = pr.code_progr AND psx.idsousgare = {$sg}
                    ) THEN 0
                    WHEN pr.idsousgare_prog = {$sg} THEN 0
                    ELSE 1
                END) ASC, (pr.idsousgare_prog IS NULL) ASC";
            }

            // Programmes sur l'OD (jumeaux même ville/compagnie dest, heure réelle).
            $progs = $this->db->query(
                "SELECT lh.id_ligneheure, lh.ligne_id, pr.code_progr, pr.idsousgare_prog, h.heure
                 FROM programme pr
                 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                 JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = '{$cidEsc}'
                 AND lh.ligne_id IN ({$inLignes})
                 AND pr.date_progr = '{$dateEsc}'
                 AND pr.statut_prog = 'actif'
                 AND pr.actif_prog = 0
                 AND h.h_active = 1
                 {$sgFilter}
                 ORDER BY (lh.ligne_id = '{$axeEsc}') DESC, {$porteeOrder}, pr.code_progr DESC"
            )->result();

            // Index pour savoir s'il existe un départ OD à une HH:MM / id_ligneheure
            // (utilisé pour les créneaux correspondance sans programme).
            $byLh = array();
            $byHhmm = array();
            foreach ($progs as $p) {
                $idLh = (string) $p->id_ligneheure;
                $hh = $this->_heure_hhmm($p->heure);
                if ($idLh !== '' && !isset($byLh[$idLh])) {
                    $byLh[$idLh] = $p;
                }
                if ($hh !== '' && !isset($byHhmm[$hh])) {
                    $byHhmm[$hh] = $p;
                }
            }

            $timeFilter = '';
            $keyToday = mdate("%Y-%m-%d", now());
            if ($date === $keyToday) {
                $dte = date('H:i', time() - 3600);
                $dteEsc = $this->db->escape_str($dte);
                $timeFilter = " AND h.heure >= '{$dteEsc}'";
            }

            $progsGare = array();
            if ($gaexp !== '') {
                $progsGare = $this->db->query(
                    "SELECT lh.id_ligneheure, lh.ligne_id, h.heure, pr.code_progr, pr.idsousgare_prog
                     FROM programme pr
                     JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                     JOIN heures h ON lh.heure_identif = h.id_heure
                     JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                     JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                     JOIN entreprise e ON c.id_entrep = e.id_entreprise
                     WHERE e.ekey = '{$cidEsc}'
                     AND pr.gareidentif = '{$gaexpEsc}'
                     AND pr.date_progr = '{$dateEsc}'
                     AND pr.statut_prog = 'actif'
                     AND pr.actif_prog = 0
                     AND h.h_active = 1
                     AND lh.actif_lh = 1
                     {$sgFilter}
                     {$timeFilter}
                     ORDER BY h.heure ASC, (pr.idsousgare_prog IS NULL) ASC, pr.code_progr DESC"
                )->result();
            }

            $heures = array();
            $seenCodes = array();
            $seenHhmmProg = array();

            // 1) Un option Heure = un programme OD de la date (même HH:MM → plusieurs lignes).
            foreach ($progs as $p) {
                $code = isset($p->code_progr) ? trim((string) $p->code_progr) : '';
                if ($code === '' || isset($seenCodes[$code])) {
                    continue;
                }
                $seenCodes[$code] = TRUE;
                $hh = $this->_heure_hhmm(isset($p->heure) ? $p->heure : '');
                if ($hh !== '') {
                    $seenHhmmProg[$hh] = TRUE;
                }
                $heures[] = array(
                    'id_ligneheure' => (string) $p->id_ligneheure,
                    'heure' => isset($p->heure) ? $p->heure : '',
                    'has_programme' => TRUE,
                    'code_progr' => $code,
                    'scope' => (($p->idsousgare_prog === null || $p->idsousgare_prog === '')
                        ? 'gare' : 'sousgare'),
                    'source' => 'od',
                    'ligne_depart' => isset($p->ligne_id) ? $p->ligne_id : $axe,
                );
            }

            // 2) Créneaux correspondance (sans programme OD à cette HH:MM) si transit dispo.
            if ($has_transit) {
                $seenTransitLh = array();
                $pushTransit = function ($idLh, $heure, $source, $ligneDepart) use (
                    &$heures, &$seenTransitLh, &$seenHhmmProg
                ) {
                    $idLh = (string) $idLh;
                    $hh = $this->_heure_hhmm($heure);
                    if ($idLh === '' || isset($seenTransitLh[$idLh])) {
                        return;
                    }
                    // Déjà couvert par un départ programme à la même horloge.
                    if ($hh !== '' && isset($seenHhmmProg[$hh])) {
                        return;
                    }
                    $seenTransitLh[$idLh] = TRUE;
                    $heures[] = array(
                        'id_ligneheure' => $idLh,
                        'heure' => $heure,
                        'has_programme' => FALSE,
                        'code_progr' => null,
                        'scope' => null,
                        'source' => $source,
                        'ligne_depart' => ($ligneDepart !== null && $ligneDepart !== '')
                            ? $ligneDepart : null,
                    );
                };

                foreach ($catalogue as $row) {
                    $pushTransit(
                        isset($row->id_ligneheure) ? $row->id_ligneheure : '',
                        isset($row->heure) ? $row->heure : '',
                        'catalogue',
                        $axe
                    );
                }

                foreach ($progsGare as $pg) {
                    $isOd = (isset($pg->ligne_id) && isset($lignesOdSet[(string) $pg->ligne_id]));
                    if ($isOd) {
                        continue; // déjà listé via $progs
                    }
                    $pushTransit(
                        isset($pg->id_ligneheure) ? $pg->id_ligneheure : '',
                        isset($pg->heure) ? $pg->heure : '',
                        'gare',
                        isset($pg->ligne_id) ? $pg->ligne_id : null
                    );
                }
            }

            usort($heures, function ($a, $b) {
                $cmp = strcmp((string) $a['heure'], (string) $b['heure']);
                if ($cmp !== 0) {
                    return $cmp;
                }
                $ca = isset($a['code_progr']) ? (string) $a['code_progr'] : '';
                $cb = isset($b['code_progr']) ? (string) $b['code_progr'] : '';
                return strcmp($ca, $cb);
            });

            // Ligne directe : ne pas proposer un créneau catalogue sans départ réel
            // (sinon le 22h CBT s'affiche alors que le départ est VIP, ou l'inverse).
            if (!$has_transit) {
                $kept = array();
                foreach ($heures as $hr) {
                    if (!empty($hr['has_programme'])) {
                        $kept[] = $hr;
                    }
                }
                $heures = $kept;
            }

            return array(
                'ligne' => $axe,
                'mode_depart' => $mode,
                'has_transit' => $has_transit ? TRUE : FALSE,
                'transit_sources' => $transit_sources,
                'heures' => $heures,
            );
        }

        /**
         * Legacy helper (conservé pour compat éventuelle) : une option heure par id_ligneheure.
         * Preferer désormais la liste programmes étendue dans heures_vente_od.
         */
        protected function _push_heure_vente_od(
            array &$heures,
            array &$seenLh,
            array &$seenHhmm,
            array $byLh,
            array $byHhmm,
            $idLh,
            $heure,
            $source,
            $ligneDepart
        ) {
            $idLh = (string) $idLh;
            $hh = $this->_heure_hhmm($heure);
            $p = null;
            if ($idLh !== '' && isset($byLh[$idLh])) {
                $p = $byLh[$idLh];
            } elseif ($hh !== '' && isset($byHhmm[$hh])) {
                $p = $byHhmm[$hh];
            }
            if ($p) {
                $idLh = (string) $p->id_ligneheure;
                $heure = $p->heure;
            }
            if ($idLh === '' || isset($seenLh[$idLh])) {
                return;
            }
            if ($hh !== '' && isset($seenHhmm[$hh])) {
                return;
            }
            $seenLh[$idLh] = TRUE;
            if ($hh !== '') {
                $seenHhmm[$hh] = TRUE;
            }
            $item = array(
                'id_ligneheure' => $idLh,
                'heure' => $heure,
                'has_programme' => $p ? TRUE : FALSE,
                'code_progr' => $p ? $p->code_progr : null,
                'scope' => $p
                    ? (($p->idsousgare_prog === null || $p->idsousgare_prog === '') ? 'gare' : 'sousgare')
                    : null,
                'source' => $source,
            );
            if ($ligneDepart !== null && $ligneDepart !== '') {
                $item['ligne_depart'] = $ligneDepart;
            }
            $heures[] = $item;
        }

        //heure avec date
        public function allprog($cid, $it, $dt, $hp)
        {
			$key = mdate("%Y-%m-%d", now());
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN statutheuregare s ON s.idheure = h.id_heure
                    JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND pr.date_progr = '$dt'
                    AND lh.id_ligneheure = '$hp'
                    AND pr.statut_prog = 'actif'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
					AND t.datefin >= '$dt'
					ORDER BY h.heure ASC")->result();
        }

        public function actifnonactif($cid, $it, $dt, $hp)
        {
        
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN statutheuregare s ON s.idheure = h.id_heure
                    JOIN statutgare sg ON s.idstatgare = sg.idstatutgare
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND pr.date_progr = '$dt'
                    AND lh.id_ligneheure = '$hp'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
                    AND t.datefin >= '$dt'
					ORDER BY h.heure ASC")->result();
        }
        //heure avec date
        public function heureligne1($cid, $it, $keys)
        {   
            $tim = date('H', time());

            if($tim === '00')
            {
                $dte = date('01:00', time() - 3600);
            }
            else
            {
                $dte = date('H:i', time() - 3600);
            }
            $key = mdate("%Y-%m-%d", now());
            
            if($keys === $key){
                return $this->db->query(
                "SELECT * FROM ligne_heure lh
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND h.heure >= '$dte'
                    AND lh.actif_lh = 1
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->result();
            }
            if($keys > $key){
            return $this->db->query(
                "SELECT * FROM ligne_heure lh
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
					ORDER BY h.heure ASC")->result();
            }
        }


        /**
         * Heures de correspondance (1re jambe) : programmes du jour choisi (J) et de J+1.
         * @param string|null $categorie Filtre catégorie bus (tirage liste passagers).
         */
        public function heureligne($cid, $it, $keys, $categorie = null)
        {   
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dte = date('01:00', time('01:00')-3600);
            }
            else
            {
                $dte = date('H:i', time('H:i')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $cidEsc = $this->db->escape_str($cid);
            $itEsc = $this->db->escape_str($it);
            $keysEsc = $this->db->escape_str($keys);
            $dteEsc = $this->db->escape_str($dte);

            $dateFilter = "AND pr.date_progr >= '{$keysEsc}' AND pr.date_progr <= DATE_ADD('{$keysEsc}', INTERVAL 1 DAY)";
            $timeFilter = ($keys === $key) ? "AND NOT (pr.date_progr = '{$keysEsc}' AND h.heure < '{$dteEsc}')" : '';
            $catFilter = '';
            if ($categorie !== null && $categorie !== '') {
                $catEsc = $this->db->escape_str($categorie);
                $catFilter = "AND pr.categori = '{$catEsc}'";
            }

            return $this->db->query(
                "SELECT lh.id_ligneheure, h.id_heure AS heure_identif, h.heure, pr.date_progr, pr.code_progr
                    FROM ligne_heure lh
                    JOIN programme pr ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '{$cidEsc}'
                    AND lh.ligne_id = '{$itEsc}'
                    {$dateFilter}
                    {$timeFilter}
                    {$catFilter}
                    AND pr.actif_prog = 0
                    AND pr.statut_prog = 'actif'
                    AND lh.actif_lh = 1
                    AND h.h_active = 1
                    GROUP BY lh.id_ligneheure, h.id_heure, h.heure, pr.date_progr, pr.code_progr
                    ORDER BY pr.date_progr ASC, h.heure ASC"
            )->result();
        }

        public function alltime($cid, $it, $dt, $hp)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }

            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categorie = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND pr.date_progr = '$dt'
                    AND lh.heure_identif = '$hp'
                    AND pr.statut_prog ='actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND t.datefin >= '$dt'
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
					ORDER BY h.heure ASC")->result();
        }
        

        //heure avec date
        public function timeall($cid, $cdar, $dt)
        {
            
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;

            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.gadest_lg = '$cdar'
                    AND pr.date_progr = '$dt'
                    AND pr.statut_prog ='actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND t.datefin >= '$dt'
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
					ORDER BY h.heure ASC")->result();
        }
        //heure reprogramme
        public function heurereprog($cid, $axedp, $hcl, $lgh)
        {
            
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;

            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$axedp'
                    AND pr.code_progr <> '$hcl'
                    AND pr.date_progr >= '$key'
                    AND pr.statut_prog ='actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                    ORDER BY h.heure ASC")->result();
        }


        public function heurereprogtr($cid, $axedp, $hcl)
        {
            
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            
                return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$axedp'
                    AND pr.code_progr <> '$hcl'
                    AND pr.statut_prog = 'actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                    ORDER BY h.heure ASC")->result();
        }

        public function heurereprogtrt($cid, $axedp, $lgcp, $px)
        {
            
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }

            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            
                return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN tarification tf ON tf.ligne_heure_id = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.statut_prog = 'actif'
                    AND ga.id_compaga IN('5001', '5002')
                    AND h.h_active = 1
                    AND tf.prix = '$px'
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                    ORDER BY pr.date_progr, h.heure ASC")->result();
        }

        /**
         * Heures de reprogrammation unifiées : même OD (gaexp + gadest), toutes compagnies.
         * @param string $cid ekey
         * @param string $gaexp code gare exp
         * @param string $gadest code gare dest
         * @param string $exclude_code programme à exclure
         * @param string|null $prix si non null, filtre même prix tarif
         * @return array
         */
        /**
         * Normalise un identifiant gare vers code_gaexp (pr.gareidentif).
         * Accepte code_gaexp (BOB1) ou idengare numérique (erreur fréquente côté UI).
         *
         * @param string|null $gare
         * @return string
         */
        public function normalize_gareidentif($gare)
        {
            $gare = trim((string) $gare);
            if ($gare === '') {
                return '';
            }
            if (!ctype_digit($gare)) {
                return $gare;
            }
            $row = $this->db->query(
                "SELECT gd.code_gaexp
                 FROM gares g
                 INNER JOIN gare_exp gd ON gd.garesid = g.idengare
                 WHERE g.idengare = ?
                 LIMIT 1",
                array((int) $gare)
            )->row();
            return ($row && !empty($row->code_gaexp)) ? trim((string) $row->code_gaexp) : $gare;
        }

        /**
         * Filtre programmes : même ville de départ que le code gare donné (BOB1 ≡ BOB2…).
         *
         * @param string|null $gare code_gaexp ou idengare
         * @return string fragment SQL (préfixé AND …) ou ''
         */
        public function sql_filtre_gare_depart_ville($gare)
        {
            $gare = $this->normalize_gareidentif($gare);
            if ($gare === '') {
                return '';
            }
            $esc = $this->db->escape($gare);
            return " AND EXISTS (
                SELECT 1 FROM gare_exp ex_pr
                INNER JOIN gare_exp ex_ref ON ex_ref.code_gaexp = {$esc}
                WHERE ex_pr.code_gaexp = pr.gareidentif
                  AND ex_pr.id_villegd = ex_ref.id_villegd
            )";
        }

        /**
         * EXISTS : la ligne lg porte la même escale métier qu’id_escale
         * (id exact, code, nom, ou variante SIKASSO ↔ SIKASSO_VIP).
         *
         * @param int $id_escale
         * @return string fragment SQL sans AND initial (ou vide)
         */
        public function sql_escale_match_ligne($id_escale)
        {
            $id = (int) $id_escale;
            if ($id <= 0) {
                return '1=0';
            }
            // Normalise SIKASSO_VIP / SIKASSO_CMT → SIKASSO pour match multi-cie.
            $baseNom = "REPLACE(REPLACE(REPLACE(UPPER(TRIM(%s)), '_VIP', ''), '_CMT', ''), '_ORD', '')";
            $ieBase = sprintf($baseNom, 'ie.nom_escale');
            $ie0Base = sprintf($baseNom, 'ie0.nom_escale');
            return "EXISTS (
                SELECT 1 FROM itineraire_escales ie
                WHERE ie.id_lignes = lg.ident_ligne
                  AND ie.actif_escale = 1
                  AND (
                    ie.id_escale = {$id}
                    OR ie.code_gadest = (
                        SELECT ie0.code_gadest FROM itineraire_escales ie0
                        WHERE ie0.id_escale = {$id} LIMIT 1
                    )
                    OR UPPER(TRIM(ie.nom_escale)) = (
                        SELECT UPPER(TRIM(ie0.nom_escale)) FROM itineraire_escales ie0
                        WHERE ie0.id_escale = {$id} LIMIT 1
                    )
                    OR {$ieBase} = (
                        SELECT {$ie0Base} FROM itineraire_escales ie0
                        WHERE ie0.id_escale = {$id} LIMIT 1
                    )
                    OR UPPER(TRIM(ie.nom_escale)) LIKE CONCAT((
                        SELECT UPPER(TRIM(ie0.nom_escale)) FROM itineraire_escales ie0
                        WHERE ie0.id_escale = {$id} LIMIT 1
                    ), '_%')
                    OR (
                        SELECT UPPER(TRIM(ie0.nom_escale)) FROM itineraire_escales ie0
                        WHERE ie0.id_escale = {$id} LIMIT 1
                    ) LIKE CONCAT(UPPER(TRIM(ie.nom_escale)), '_%')
                  )
            )";
        }

        public function heurereprog_unifie($cid, $gaexp, $gadest, $exclude_code, $prix = null, $id_escale = null, $gareidentif = null, $idsousgare = null, $nom_ligne = null, $axes = null, $date_filter = null)
        {
            // PHP 8 : time() n'accepte plus d'argument (anciens time('H') / time('H:i:s')).
            $tim = date('H');
            if ($tim === '00') {
                $dat = '00:00:00';
            } else {
                $dat = date('H:i:s', time() - 3600);
            }
            $key = mdate('%Y-%m-%d', now());
            $dtoday = $key . '-' . $dat;

            $cidEsc = $this->db->escape($cid);
            $exEsc = $this->db->escape($exclude_code);

            // Filtre date : programmes à partir d’aujourd’hui (date_progr), pas dateheure_prog
            // (évite d’exclure un départ J+1 si dateheure_prog est mal renseigné).
            $dateSql = " AND pr.date_progr >= " . $this->db->escape($key);
            $df = trim((string) $date_filter);
            if ($df !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $df)) {
                // Date choisie au guichet : J et J+1 (nuit / correspondance).
                $df2 = date('Y-m-d', strtotime($df . ' +1 day'));
                $dateSql = ' AND pr.date_progr IN ('
                    . $this->db->escape($df) . ', '
                    . $this->db->escape($df2) . ')';
                // Heures déjà passées uniquement si la date demandée est aujourd’hui.
                if ($df === $key) {
                    $dateSql .= " AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= "
                        . $this->db->escape($dtoday);
                }
            }

            // Filtre prix : jamais par compagnie. Escale ticket → même nom/code (CMT≠VIP).
            $prixSql = '';
            if ($prix !== null && $prix !== '') {
                $prixEsc = $this->db->escape($prix);
                $idEscPrix = (int) $id_escale;
                if ($idEscPrix > 0) {
                    $prixSql = ' AND ' . $this->sql_escale_match_ligne($idEscPrix)
                        . " AND EXISTS (
                        SELECT 1 FROM itineraire_escales iep
                        WHERE iep.id_lignes = lg.ident_ligne
                          AND iep.actif_escale = 1
                          AND iep.prix_escale = {$prixEsc}
                          AND (
                            iep.id_escale = {$idEscPrix}
                            OR UPPER(REPLACE(REPLACE(TRIM(iep.nom_escale), '_VIP', ''), '_CMT', ''))
                             = (
                                SELECT UPPER(REPLACE(REPLACE(TRIM(ie0.nom_escale), '_VIP', ''), '_CMT', ''))
                                FROM itineraire_escales ie0 WHERE ie0.id_escale = {$idEscPrix} LIMIT 1
                             )
                          )
                    )";
                } else {
                    $prixSql = " AND EXISTS (
                        SELECT 1 FROM tarification tf
                        WHERE tf.ligne_heure_id = lh.id_ligneheure
                          AND tf.typetarif_id = pr.typetarif
                          AND tf.actif_taf = 1
                          AND tf.prix = {$prixEsc}
                    )";
                }
            }

            // Programmes ancrés sur la gare de départ (ville), pas un idengare ni une sous-gare stricte.
            // En reprog unifiée on ignore volontairement le filtre sous-gare (listing gare-ville).
            $gareRef = $this->normalize_gareidentif($gareidentif);
            if ($gareRef === '') {
                $gareRef = $this->normalize_gareidentif($gaexp);
            }
            $gareSql = $this->sql_filtre_gare_depart_ville($gareRef);
            $sgSql = '';

            // OD métier : nom de ligne + départ gare de report (toutes compagnies / codes dest).
            // Ne pas exiger la même ville dest que le code ticket (BAM6 ≠ BAM53 = Bamako / Bamako_VIP).
            $odSql = '';
            $nom = trim((string) $nom_ligne);
            $ga = trim((string) $gaexp);
            $gd = trim((string) $gadest);
            $axeList = array();
            if (is_array($axes)) {
                foreach ($axes as $ax) {
                    $ax = trim((string) $ax);
                    if ($ax !== '') {
                        $axeList[] = $ax;
                    }
                }
            }

            $depCode = $gareRef !== '' ? $gareRef : $ga;
            $depVilleSql = '';
            if ($depCode !== '') {
                $depEsc = $this->db->escape($depCode);
                $depVilleSql = " AND EXISTS (
                    SELECT 1 FROM gare_exp ex_lg
                    INNER JOIN gare_exp ex_dep ON ex_dep.code_gaexp = {$depEsc}
                    WHERE ex_lg.code_gaexp = lg.gaexp_lg
                      AND ex_lg.id_villegd = ex_dep.id_villegd
                )";
            }

            $idEsc = (int) $id_escale;
            // Variantes multi-compagnies : BOBO-BAMAKO ↔ BOBO-BAMAKO_VIP (même OD métier).
            $nomLigneSql = '';
            if ($nom !== '') {
                $nomEsc = $this->db->escape($nom);
                $nomLike = $this->db->escape($nom . '_%');
                $nomLigneSql = " AND (lg.nom_ligne = {$nomEsc} OR lg.nom_ligne LIKE {$nomLike})";
            }

            if ($nom !== '') {
                // Report : même nom (ou variante _VIP/_CMT…) + gare départ ville, toutes cie.
                $odSql = $nomLigneSql . $depVilleSql;
            } elseif ($idEsc > 0) {
                // Sans nom_ligne : lignes du départ report qui portent la même escale (nom/code/variante).
                $odSql = ' AND ' . $this->sql_escale_match_ligne($idEsc) . $depVilleSql;
            } else {
                // Reprog unifiée : pas de recherche par codes seuls (BAM6≠BAM53, etc.).
                $odSql = ' AND 1=0';
            }

            // Prix / escale : si prix fourni, restreindre ; sinon pour une escale ticket
            // matcher la MÊME destination d'escale (nom / variante VIP) sur TOUTES les compagnies
            // (ex. SIKASSO / SIK23 CMT ≠ SIKASSO_VIP / SIK54 VIP).
            if ($prix !== null && $prix !== '') {
                // $prixSql déjà construit plus haut
            } elseif ($idEsc > 0 && ($prix === null || $prix === '')) {
                $prixSql = ' AND ' . $this->sql_escale_match_ligne($idEsc);
            }

            return $this->db->query(
                "SELECT pr.code_progr, pr.date_progr, pr.typetarif, pr.categori, pr.intervalle1, pr.intervalle2,
                        pr.gareidentif, lh.id_ligneheure, h.heure, lg.ident_ligne, lg.nom_ligne,
                        lg.gaexp_lg, lg.gadest_lg,
                        ga.id_compaga, ca.nom_compagnie AS nom_compagnie_arrivee,
                        c.cle_compagnie AS cle_compagnie_depart, c.nom_compagnie AS nom_compagnie_depart
                FROM programme pr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN compagnies ca ON ga.id_compaga = ca.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = {$cidEsc}
                {$odSql}
                AND pr.code_progr <> {$exEsc}
                AND pr.statut_prog = 'actif'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                {$dateSql}
                {$gareSql}
                {$sgSql}
                {$prixSql}
                GROUP BY pr.code_progr, pr.date_progr, pr.typetarif, pr.categori, pr.intervalle1, pr.intervalle2,
                         pr.gareidentif, lh.id_ligneheure, h.heure, lg.ident_ligne, lg.nom_ligne,
                         lg.gaexp_lg, lg.gadest_lg, ga.id_compaga, ca.nom_compagnie,
                         c.cle_compagnie, c.nom_compagnie
                ORDER BY pr.date_progr ASC, h.heure ASC"
            )->result();
        }

        /**
         * Compose un nom de ligne OD global : départ 1ʳᵉ jambe + arrivée dernière.
         * Ex. BOBO-OUAGA + OUAGA-MANGA → BOBO-MANGA (suffixe _VIP conservé si présent à l’arrivée).
         *
         * @param string $nom_first
         * @param string $nom_last
         * @return string
         */
        public function composer_nom_ligne_od($nom_first, $nom_last)
        {
            $a = trim((string) $nom_first);
            $b = trim((string) $nom_last);
            if ($a === '' || $b === '') {
                return '';
            }
            $left = $a;
            $p = strpos($a, '-');
            if ($p !== false) {
                $left = substr($a, 0, $p);
            }
            $right = $b;
            $p2 = strrpos($b, '-');
            if ($p2 !== false) {
                $right = substr($b, $p2 + 1);
            }
            $left = trim($left);
            $right = trim($right);
            if ($left === '' || $right === '') {
                return '';
            }
            return $left . '-' . $right;
        }

        /**
         * Axes (ident_ligne) reliant les mêmes villes que gaexp → gadest (OD métier).
         *
         * @param string      $gaexp
         * @param string      $gadest
         * @param string|null $ekey
         * @return string[]
         */
        public function axes_od_par_villes($gaexp, $gadest, $ekey = null)
        {
            $ga = trim((string) $gaexp);
            $gd = trim((string) $gadest);
            if ($ga === '' || $gd === '') {
                return array();
            }
            $ek = trim((string) $ekey);
            $sql = "SELECT DISTINCT lg.ident_ligne
                    FROM lignes lg
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN gare_exp ex0 ON ex0.code_gaexp = ?
                    JOIN gare_dest ga0 ON ga0.code_gadest = ?
                    WHERE ex.id_villegd = ex0.id_villegd
                      AND ga.id_villega = ga0.id_villega";
            $params = array($ga, $gd);
            if ($ek !== '') {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM compagnies c
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE c.cle_compagnie = ex.id_compagd AND e.ekey = ?
                )";
                $params[] = $ek;
            }
            $sql .= " ORDER BY lg.ident_ligne ASC";
            $rows = $this->db->query($sql, $params)->result();
            $out = array();
            foreach ($rows as $r) {
                $id = trim((string) $r->ident_ligne);
                if ($id !== '') {
                    $out[] = $id;
                }
            }
            return $out;
        }

        /**
         * OD métier globale : gare départ 1ʳᵉ jambe + gare arrivée dernière jambe.
         *
         * @return array{gaexp:string,gadest:string,axe:string,nom_ligne:string,axes:string[]}
         */
        public function od_metier_globale($gaexp, $gadest, $ekey = null, $nom_first = null, $nom_last = null)
        {
            $ga = trim((string) $gaexp);
            $gd = trim((string) $gadest);
            $ek = trim((string) $ekey);
            $out = array(
                'gaexp' => $ga,
                'gadest' => $gd,
                'axe' => ($ga !== '' && $gd !== '') ? ($ga . '-' . $gd) : '',
                'nom_ligne' => '',
                'axes' => array(),
            );
            if ($ga === '' || $gd === '') {
                return $out;
            }

            // 1) Ligne exacte codes.
            $exact = $this->db->query(
                "SELECT lg.nom_ligne, lg.ident_ligne
                 FROM lignes lg
                 WHERE lg.gaexp_lg = ? AND lg.gadest_lg = ?
                 ORDER BY lg.ident_ligne ASC
                 LIMIT 1",
                array($ga, $gd)
            )->row();
            if ($exact && trim((string) $exact->nom_ligne) !== '') {
                $out['nom_ligne'] = trim((string) $exact->nom_ligne);
            }

            // 2) Composition depuis les noms de jambes (BOBO-OUAGA + OUAGA-MANGA → BOBO-MANGA).
            if ($out['nom_ligne'] === '') {
                $composed = $this->composer_nom_ligne_od($nom_first, $nom_last);
                if ($composed !== '') {
                    $alts = $this->axes_par_nom_ligne($composed, $ek !== '' ? $ek : null, $ga, $gd);
                    if (!empty($alts)) {
                        $out['nom_ligne'] = $composed;
                    } elseif ($out['nom_ligne'] === '') {
                        $out['nom_ligne'] = $composed;
                    }
                }
            }

            // 3) Axes / noms par villes OD.
            $axesVille = $this->axes_od_par_villes($ga, $gd, $ek !== '' ? $ek : null);
            $out['axes'] = $axesVille;
            if ($out['nom_ligne'] === '' && !empty($axesVille)) {
                $noms = $this->db->query(
                    "SELECT DISTINCT lg.nom_ligne
                     FROM lignes lg
                     WHERE lg.ident_ligne IN (" . $this->sql_in_ident_lignes($axesVille) . ")
                     ORDER BY (INSTR(lg.nom_ligne, '_') > 0) ASC, CHAR_LENGTH(lg.nom_ligne) ASC, lg.nom_ligne ASC"
                )->result();
                if (!empty($noms) && trim((string) $noms[0]->nom_ligne) !== '') {
                    $out['nom_ligne'] = trim((string) $noms[0]->nom_ligne);
                }
            }

            // Axes de recherche = intersection nom retenu ∩ sens villes (jamais le contre-sens).
            if ($out['nom_ligne'] !== '') {
                $byNom = $this->axes_par_nom_ligne(
                    $out['nom_ligne'],
                    $ek !== '' ? $ek : null,
                    $ga,
                    $gd
                );
                if (!empty($byNom)) {
                    $out['axes'] = $byNom;
                } elseif (!empty($axesVille)) {
                    $out['axes'] = $axesVille;
                }
            }
            if (empty($out['axes']) && $out['axe'] !== '') {
                $out['axes'] = array($out['axe']);
            }

            return $out;
        }

        /**
         * Axes (ident_ligne) partageant le même nom de ligne (OD métier multi-compagnies).
         * Optionnel : restreindre au sens gaexp→gadest (mêmes villes) pour éviter le contre-sens.
         *
         * @param string      $nom_ligne
         * @param string|null $ekey
         * @param string|null $gaexp
         * @param string|null $gadest
         * @return string[]
         */
        public function axes_par_nom_ligne($nom_ligne, $ekey = null, $gaexp = null, $gadest = null)
        {
            $nom = trim((string) $nom_ligne);
            if ($nom === '') {
                return array();
            }
            $ek = trim((string) $ekey);
            $ga = trim((string) $gaexp);
            $gd = trim((string) $gadest);

            $sql = "SELECT DISTINCT lg.ident_ligne
                    FROM lignes lg
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest";
            $params = array();
            // BOBO-BAMAKO et BOBO-BAMAKO_VIP = même OD métier (compagnies différentes).
            $where = array('(lg.nom_ligne = ? OR lg.nom_ligne LIKE ?)');
            $params[] = $nom;
            $params[] = $nom . '_%';

            if ($ek !== '') {
                $sql .= " JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                          JOIN entreprise e ON c.id_entrep = e.id_entreprise";
                $where[] = 'e.ekey = ?';
                $params[] = $ek;
            }
            if ($ga !== '' && $gd !== '') {
                $sql .= " JOIN gare_exp ex0 ON ex0.code_gaexp = ?
                          JOIN gare_dest ga0 ON ga0.code_gadest = ?";
                $params[] = $ga;
                $params[] = $gd;
                $where[] = 'ex.id_villegd = ex0.id_villegd';
                $where[] = 'ga.id_villega = ga0.id_villega';
            } elseif ($ga !== '') {
                // Départ seulement (reprog : toutes dest / compagnies du nom de ligne).
                $sql .= ' JOIN gare_exp ex0 ON ex0.code_gaexp = ?';
                $params[] = $ga;
                $where[] = 'ex.id_villegd = ex0.id_villegd';
            }
            $sql .= ' WHERE ' . implode(' AND ', $where) . ' ORDER BY lg.ident_ligne ASC';
            $rows = $this->db->query($sql, $params)->result();
            $out = array();
            foreach ($rows as $r) {
                $id = trim((string) $r->ident_ligne);
                if ($id !== '') {
                    $out[] = $id;
                }
            }
            return $out;
        }

        /**
         * Codes programmes actifs d'une gare (et sous-gare) pour une date — 1ʳᵉ jambe reprog.
         *
         * @return string[]
         */
        public function codes_progr_gare_date($ekey, $gareidentif, $date, $idsousgare = null)
        {
            $gare = $this->normalize_gareidentif($gareidentif);
            $date = trim((string) $date);
            if ($gare === '' || $date === '') {
                return array();
            }
            $ekeyEsc = $this->db->escape($ekey);
            $dateEsc = $this->db->escape($date);
            // Reprog : tous programmes de la ville de départ (pas de filtre sous-gare).
            $gareSql = $this->sql_filtre_gare_depart_ville($gare);
            $rows = $this->db->query(
                "SELECT pr.code_progr
                 FROM programme pr
                 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                 JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = {$ekeyEsc}
                 {$gareSql}
                 AND pr.date_progr = {$dateEsc}
                 AND pr.statut_prog = 'actif'
                 AND pr.actif_prog = 0
                 AND lh.actif_lh = 1
                 AND h.h_active = 1"
            )->result();
            $out = array();
            foreach ($rows as $r) {
                if (!empty($r->code_progr)) {
                    $out[(string) $r->code_progr] = true;
                }
            }
            return array_keys($out);
        }

        /**
         * Lignes catalogue ayant un départ réel à la gare (date, heure optionnelle).
         * Sert de 1ʳᵉ jambe transit : ex. Banfora–Ouaga 21h pour une vente Banfora–Manga.
         *
         * @param string|null $heure HH:MM ou HH:MM:SS (filtre horloge si fourni)
         * @return string[] ident_ligne
         */
        public function lignes_depart_gare_date($ekey, $gareidentif, $date, $idsousgare = null, $heure = null)
        {
            $gare = $this->normalize_gareidentif($gareidentif);
            $date = trim((string) $date);
            if ($gare === '' || $date === '') {
                return array();
            }
            $ekeyEsc = $this->db->escape($ekey);
            $gareEsc = $this->db->escape($gare);
            $dateEsc = $this->db->escape($date);
            $sgSql = '';
            if ($idsousgare !== null && $idsousgare !== '' && (int) $idsousgare > 0) {
                $sgSql = $this->sql_filtre_sousgare((int) $idsousgare);
            }
            $heureSql = '';
            $hh = $this->_heure_hhmm($heure);
            if ($hh !== '') {
                $hhEsc = $this->db->escape_str($hh);
                $heureSql = " AND LEFT(h.heure, 5) = '{$hhEsc}'";
            }
            $rows = $this->db->query(
                "SELECT DISTINCT lh.ligne_id
                 FROM programme pr
                 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 JOIN heures h ON lh.heure_identif = h.id_heure
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                 JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = {$ekeyEsc}
                 AND pr.gareidentif = {$gareEsc}
                 AND pr.date_progr = {$dateEsc}
                 AND pr.statut_prog = 'actif'
                 AND pr.actif_prog = 0
                 AND lh.actif_lh = 1
                 AND h.h_active = 1
                 {$sgSql}
                 {$heureSql}"
            )->result();
            $out = array();
            foreach ($rows as $r) {
                if (!empty($r->ligne_id)) {
                    $out[(string) $r->ligne_id] = true;
                }
            }
            return array_keys($out);
        }

        //prog
        public function progsiege($cid, $cd, $dat)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.code_progr = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND pr.date_progr='$dat'
                    AND t.datefin >= '$dat'")->result();
        }

        public function progsiegebus($cid, $cd, $dat)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.depart_code = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND pr.date_progr='$dat'
                    AND t.datefin >= '$dat'")->result();
        }

        //prog
        public function prog($cid, $l, $dat)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.ident_ligne = '$l'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND pr.date_progr = '$dat'
                    AND t.datefin >= '$dat'")->result();
        }

        ///
        public function product($cid, $dat, $l, $cdp, $n)
        {
            
            return $this->db->query(
                "SELECT pr.depart_code, pr.date_progr, pr.code_progr FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.date_progr = '$dat'
                    AND h.id_heure = '$l'
                    AND pr.categori ='$cdp'
                    AND lg.ident_ligne = '$n'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND pr.statut_prog = 'actif'
                    GROUP BY pr.depart_code, pr.date_progr, pr.code_progr")->result();
        }
        /**
         * Codes programmes partageant les sièges (lien correspondance — option 3 segments).
         * @return string[]
         */
        public function codes_sieges_occupes($code_progr)
        {
            $code = trim((string) $code_progr);
            if ($code === '') {
                return array();
            }
            if (!isset($this->m_programme_correspondance)) {
                $this->load->model('Programme_correspondance_model', 'm_programme_correspondance');
            }
            $codes = $this->m_programme_correspondance->codes_sieges_partages($code);
            if (!isset($this->m_programme_reconduction)) {
                $this->load->model('Programme_reconduction_model', 'm_programme_reconduction');
            }
            $reco = $this->m_programme_reconduction->get_reco_by_cible($code);
            if ($reco && !empty($reco->code_progr_source)) {
                $codes[] = $reco->code_progr_source;
                $codes[] = $code;
            }
            $clean = array();
            foreach ($codes as $c) {
                $c = trim((string) $c);
                if ($c !== '') {
                    $clean[$c] = true;
                }
            }
            return array_keys($clean);
        }

        protected function _sql_in_codes(array $codes)
        {
            $esc = array();
            foreach ($codes as $c) {
                $c = trim((string) $c);
                if ($c !== '') {
                    $esc[] = "'" . $this->db->escape_str($c) . "'";
                }
            }
            return !empty($esc) ? implode(',', $esc) : "''";
        }

        /**
         * Filtre sièges reconduits / départ source fermé. false = aucun siège.
         * @return string|false
         */
        protected function _reconduction_cdprog_and($code_progr)
        {
            if (!isset($this->m_programme_reconduction)) {
                $this->load->model('Programme_reconduction_model', 'm_programme_reconduction');
            }
            $extra = $this->m_programme_reconduction->cdprog_extra_and($code_progr);
            if ($extra === false) {
                return false;
            }
            return $extra === null ? '' : $extra;
        }

        ///sieges
        public function cdprog($cid, $cd, $dat, $lg, $hr, $d, $f)
        {
            $cidEsc = $this->db->escape_str($cid);
            $cdEsc = $this->db->escape_str($cd);
            $datEsc = $this->db->escape_str($dat);
            $lgEsc = $this->db->escape_str($lg);
            $hrEsc = $this->db->escape_str($hr);
            $d = (int) $d;
            $f = (int) $f;

            if (!isset($this->m_programme_correspondance)) {
                $this->load->model('Programme_correspondance_model', 'm_programme_correspondance');
            }

            $recoAnd = $this->_reconduction_cdprog_and($cd);
            if ($recoAnd === false) {
                return array();
            }
            $bloqueAnd = $this->_cdprog_bloque_and($cd);
            $stockCodes = $this->codes_siege_stock($cd);
            $tamponAnd = $this->_cdprog_tampon_and($stockCodes);
            $actifPas = $this->_cdprog_actif_pas_and('p');

            // Même périmètre que assert_siege_vendable (frères depart_code/date inclus).
            $occupes = $this->_sql_in_codes($stockCodes);

            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus=ct.categorie
                JOIN programme pr ON pr.categori=ct.categorie
                JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id=l.ident_ligne
                JOIN heures h ON lh.heure_identif=h.id_heure
                WHERE siege_num NOT IN (SELECT p.num_siege_categorie FROM passager p
                                          WHERE p.code_pro IN ({$occupes})
                                          AND p.num_siege_categorie IS NOT NULL
                                          AND p.num_siege_categorie BETWEEN {$d} AND {$f}
                                          {$actifPas})
                AND pr.code_progr='{$cdEsc}'
                AND pr.date_progr='{$datEsc}'
                AND l.nom_ligne='{$lgEsc}'
                AND h.heure='{$hrEsc}'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN {$d} AND {$f}
                {$bloqueAnd}
                {$tamponAnd}
                {$recoAnd}
                ORDER BY sc.siege_num ASC"
            )->result();
        }


        ///numero de siege en fonction du bus


        public function cdprogbus($cid, $cd, $dat, $lg, $hr, $d, $f)
        {
            $cidEsc = $this->db->escape_str($cid);
            $cdEsc = $this->db->escape_str($cd);
            $datEsc = $this->db->escape_str($dat);
            $lgEsc = $this->db->escape_str($lg);
            $hrEsc = $this->db->escape_str($hr);
            $d = (int) $d;
            $f = (int) $f;

            $stockCodes = $this->_codes_stock_depart_bus($cid, $cd, $dat, $lg, $hr);
            if (empty($stockCodes)) {
                return array();
            }
            $occupes = $this->_sql_in_codes($stockCodes);
            $bloquePr = $this->_cdprog_bloque_and_pr();
            $tamponAnd = $this->_cdprog_tampon_and($stockCodes);
            $actifPas = $this->_cdprog_actif_pas_and('p');

            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus = ct.categorie
                JOIN programme pr ON pr.categori = ct.categorie
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id = l.ident_ligne
                JOIN heures h ON lh.heure_identif = h.id_heure
                WHERE siege_num NOT IN (
                    SELECT p.num_siege_categorie FROM passager p
                    WHERE p.code_pro IN ({$occupes})
                      AND p.num_siege_categorie IS NOT NULL
                      AND p.num_siege_categorie BETWEEN {$d} AND {$f}
                      {$actifPas}
                )
                AND pr.depart_code = '{$cdEsc}'
                AND pr.date_progr = '{$datEsc}'
                AND l.nom_ligne = '{$lgEsc}'
                AND h.heure = '{$hrEsc}'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN {$d} AND {$f}
                {$bloquePr}
                {$tamponAnd}
                ORDER BY sc.siege_num ASC"
            )->result();
        }

        /**
         * Codes programmes du stock sièges pour un départ bus (depart_code + date + ligne + heure).
         *
         * @return string[]
         */
        protected function _codes_stock_depart_bus($cid, $depart_code, $dat = null, $lg = null, $hr = null)
        {
            $depart_code = trim((string) $depart_code);
            if ($depart_code === '') {
                return array();
            }

            $sql = "SELECT DISTINCT pr.code_progr
                    FROM programme pr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN lignes l ON lh.ligne_id = l.ident_ligne
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN gare_exp ex ON l.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = ?
                      AND pr.depart_code = ?
                      AND h.h_active = 1
                      AND lh.actif_lh = 1
                      AND pr.actif_prog = 0";
            $params = array($cid, $depart_code);
            if ($dat !== null && $dat !== '') {
                $sql .= " AND pr.date_progr = ?";
                $params[] = $dat;
            }
            if ($lg !== null && $lg !== '') {
                $sql .= " AND l.nom_ligne = ?";
                $params[] = $lg;
            }
            if ($hr !== null && $hr !== '') {
                $sql .= " AND h.heure = ?";
                $params[] = $hr;
            }

            $rows = $this->db->query($sql, $params)->result();
            $codes = array();
            foreach ($rows as $r) {
                $c = trim((string) $r->code_progr);
                if ($c === '') {
                    continue;
                }
                foreach ($this->codes_siege_stock($c) as $sc) {
                    $sc = trim((string) $sc);
                    if ($sc !== '') {
                        $codes[$sc] = true;
                    }
                }
            }
            return array_keys($codes);
        }

        
        ///siege pour transite
        public function progsiegetrans($cid, $cd)
        {
                
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.code_progr = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0")->result();
        }

        public function progsiegetransbus($cid, $cd)
        {
                
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.depart_code = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0")->result();
        }
        /// sieges
        public function cdprogtrans($cid, $cd, $d, $f)
        {
            $cdEsc = $this->db->escape_str($cd);
            $d = (int) $d;
            $f = (int) $f;

            if (!isset($this->m_programme_correspondance)) {
                $this->load->model('Programme_correspondance_model', 'm_programme_correspondance');
            }

            $recoAnd = $this->_reconduction_cdprog_and($cd);
            if ($recoAnd === false) {
                return array();
            }
            $bloqueAnd = $this->_cdprog_bloque_and($cd);
            $stockCodes = $this->codes_siege_stock($cd);
            $tamponAnd = $this->_cdprog_tampon_and($stockCodes);
            $actifPas = $this->_cdprog_actif_pas_and('p');

            // Même périmètre que assert_siege_vendable (frères depart_code/date inclus).
            $occupes = $this->_sql_in_codes($stockCodes);

            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus=ct.categorie
                JOIN programme pr ON pr.categori=ct.categorie
                JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id=l.ident_ligne
                JOIN heures h ON lh.heure_identif=h.id_heure
                WHERE siege_num NOT IN (SELECT p.num_siege_categorie FROM passager p
                                          WHERE p.code_pro IN ({$occupes})
                                          AND p.num_siege_categorie IS NOT NULL
                                          AND p.num_siege_categorie BETWEEN {$d} AND {$f}
                                          {$actifPas})
                AND pr.code_progr='{$cdEsc}'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN {$d} AND {$f}
                {$bloqueAnd}
                {$tamponAnd}
                {$recoAnd}
                ORDER BY sc.siege_num ASC"
            )->result();
        }

        public function cdprogtransbus($cid, $cd, $d, $f)
        {
            $cdEsc = $this->db->escape_str($cd);
            $d = (int) $d;
            $f = (int) $f;

            $stockCodes = $this->_codes_stock_depart_bus($cid, $cd);
            if (empty($stockCodes)) {
                return array();
            }
            $occupes = $this->_sql_in_codes($stockCodes);
            $bloquePr = $this->_cdprog_bloque_and_pr();
            $tamponAnd = $this->_cdprog_tampon_and($stockCodes);
            $actifPas = $this->_cdprog_actif_pas_and('p');

            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus=ct.categorie
                JOIN programme pr ON pr.categori=ct.categorie
                JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id=l.ident_ligne
                JOIN heures h ON lh.heure_identif=h.id_heure
                WHERE siege_num NOT IN (
                    SELECT p.num_siege_categorie FROM passager p
                    WHERE p.code_pro IN ({$occupes})
                      AND p.num_siege_categorie IS NOT NULL
                      AND p.num_siege_categorie BETWEEN {$d} AND {$f}
                      {$actifPas}
                )
                AND pr.depart_code='{$cdEsc}'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN {$d} AND {$f}
                {$bloquePr}
                {$tamponAnd}
                ORDER BY sc.siege_num ASC"
            )->result();
        }

        /*public function indexprog($cid, $cd)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.code_progr = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0")->result();
        }*/

        public function indexprog($cid, $cd)
        {
            
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN gare_dest ga ON lg.gadest_lg = ga.code_gadest
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND pr.code_progr = '$cd'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0")->result();
        }
        
        //programme confirme
        public function timeconf($cid, $it, $dt)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
                        
            return $this->db->query(
                "SELECT * FROM programme pr 
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN tarifs t ON pr.typetarif = t.id_tarifs
                    JOIN categorie ct ON pr.categori = ct.categorie
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lg.ident_ligne = '$it'
                    AND pr.date_progr >= '$dt'
                    AND pr.statut_prog ='actif'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND t.datefin >= '$dt'
                    AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
					ORDER BY h.heure ASC")->result();
        }

        public function progdepart($cd, $cat, $h, $dt)
        {
            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN categorie ct ON pr.categori = ct.categorie
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND pr.categori = '$cat'
                AND pr.id_heur = '$h'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND pr.date_progr = '$dt'
                AND t.datefin >= '$dt'")->result();
        }

        //progrogramme pour faire un update sur le depart d'un client
        public function updepart($cd, $idlg)
        {
            $tim = date('H', time('H'));

            if($tim === '00')
            {
                $dat = date('01:00:00', time('01:00:00')-3600);
            }
            else
            {
                $dat = date('H:i:s', time('H:i:s')-3600);
            }         

            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            $dt = mdate("%Y-%m-%d", now());

            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN categorie ct ON pr.categori = ct.categorie
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND lh.ligne_id ='$idlg'
                AND pr.date_progr >= '$dt'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.statut_prog ='actif'
                AND pr.actif_prog = 0
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                ORDER BY h.heure ASC")->result();
        

        }
        public function getchcour($cd, $id, $dt)
        {
            $dat = date('H:i:s', time('H:i:s'));
            $key = mdate("%Y-%m-%d", now());
            $dtoday = $key.'-'.$dat;
            return $this->db->query(
                "SELECT * FROM programme pr 
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                JOIN tarifs t ON pr.typetarif = t.id_tarifs
                JOIN gare_exp ex ON pr.gareidentif = ex.code_gaexp
                JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                WHERE e.ekey = '$cd'
                AND lh.ligne_id = '$id'
                AND pr.date_progr = '$dt'
                AND pr.statut_prog = 'actif'
                AND h.h_active = 1
                AND pr.actif_prog = 0
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                ORDER BY h.heure ASC")->result();
        }
    }
    /** Programme_model.php **/
    /** application/models/Programme_model.php **/
    /** application/models/Programme_model.php **/
