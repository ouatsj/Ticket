<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Courriers_exp_model extends CI_Model
    {
        protected $table = 'courriers_exp';
        
        public function __construct()
        {
            parent::__construct();
            $this->load->helper('etat_lettres');
            $this->_ensure_ligne_vendue_columns();
        }

        private function _ensure_ligne_vendue_columns()
        {
            static $done = false;
            if ($done) {
                return;
            }
            $done = true;
            $prev = $this->db->db_debug;
            $this->db->db_debug = false;
            foreach (array('nom_ligne_vendu' => 'VARCHAR(50) NULL', 'code_gaexp_vendu' => 'VARCHAR(20) NULL') as $col => $def) {
                $q = $this->db->query("SHOW COLUMNS FROM courriers_exp LIKE " . $this->db->escape($col));
                if (!$q || $q->num_rows() < 1) {
                    $this->db->query("ALTER TABLE courriers_exp ADD COLUMN {$col} {$def}");
                }
            }
            $this->db->db_debug = $prev;
        }

        protected function _figer_ligne_vendue(array $data)
        {
            if (empty($data['id_codecourrier'])) {
                return $data;
            }
            $row = $this->db->query(
                "SELECT lg.nom_ligne, lg.gaexp_lg
                 FROM code_courriers cd
                 JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                 WHERE cd.codecolisid = ?
                 LIMIT 1",
                array($data['id_codecourrier'])
            )->row();
            if (!$row) {
                return $data;
            }
            if (!isset($data['nom_ligne_vendu']) || trim((string) $data['nom_ligne_vendu']) === '') {
                $data['nom_ligne_vendu'] = $row->nom_ligne;
            }
            if (!isset($data['code_gaexp_vendu']) || trim((string) $data['code_gaexp_vendu']) === '') {
                $data['code_gaexp_vendu'] = $row->gaexp_lg;
            }
            return $data;
        }

        /**
         * Gare d’émission courrier : sous-gare départ (gareprinceid) OU gaexp ligne.
         * Aligné TRI _operateurs_actifs_courrier (old + new).
         */
        public function sql_filtre_gare_emission($gid, $sgAlias = 'sg', $gaexpExpr = 'lg.gaexp_lg')
        {
            $gid = trim((string) $gid);
            if ($gid === '' || $gid === '0') {
                return '';
            }
            if (!isset($this->m_gare_depart)) {
                $this->load->model('Gare_depart_model', 'm_gare_depart');
            }
            $lieu = $this->m_gare_depart->resolve_lieu($gid);
            $codes = is_array($lieu['codes']) ? $lieu['codes'] : array();
            if (!empty($lieu['phys'])) {
                $codes[] = $lieu['phys'];
            }
            $codes[] = $gid;
            $codes = array_values(array_unique(array_filter(array_map('strval', $codes))));
            if (empty($codes)) {
                return '';
            }
            $in = array();
            foreach ($codes as $c) {
                $in[] = $this->db->escape($c);
            }
            $inSql = implode(',', $in);
            $sgAlias = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $sgAlias);
            return " AND (
                {$sgAlias}.gareprinceid IN ({$inSql})
                OR EXISTS (
                    SELECT 1 FROM gare_exp ge
                    WHERE ge.code_gaexp = {$sgAlias}.gareprinceid
                    AND (ge.garesid IN ({$inSql}) OR ge.code_gaexp IN ({$inSql}))
                )
                OR {$gaexpExpr} IN ({$inSql})
            ) ";
        }

        public function sql_filtre_operateur_courrier($us, $arAlias = 'ar')
        {
            $us = trim((string) $us);
            $slashPos = strpos($us, '/');
            if ($slashPos !== false) {
                $us = trim(substr($us, 0, $slashPos));
            }
            if ($us === '' || $us === '0') {
                return '';
            }
            if (!isset($this->m_passager)) {
                $this->load->model('Passager_model', 'm_passager');
            }
            return $this->m_passager->sql_filtre_vendeur($us, $arAlias);
        }


        public function create(array $data)
        {
            $this->db->insert($this->table, $this->_figer_ligne_vendue($data));
            return $this->db->insert_id();
        }
            
        public function update($courrierexpid, $num_cour, $departcolis, array $data)
        {

        $multiClause = array('courrierexpid' => $courrierexpid, 'num_cour' => $num_cour, 'departcolis' => $departcolis);

            return $this->db->where($multiClause)->update($this->table, $data);
        }

        public function del($id, $num_cou, $departcolis)
        {
            $multiClause = array('courrierexpid' => $id, 'num_cour' => $num_cou, 'departcolis' => $departcolis);
            return $this->db->where($multiClause)->delete($this->table);
        }
        
        public function listereportcour($cid, $gid, $dt1, $dt2, $cp, $acl = FALSE, $algn = FALSE)
        {
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $cp = $this->db->escape_str($cp);
            $gareSql = $this->sql_filtre_gare_emission($gid);
            $opSql = $this->sql_filtre_operateur_courrier($acl);
            $algn = ($algn === FALSE || $algn === null) ? '' : trim((string) $algn);
            $ligneSql = ($algn !== '')
                ? " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "' "
                : '';

            return $this->db->query(
                "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, lg.nom_ligne, e.prixcolis
                FROM courriers_exp e
                JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '{$cid}'
                AND dest.id_compaga = '{$cp}'
                AND e.dateenvoi >= '{$dt1}' AND e.dateenvoi < DATE_ADD('{$dt2}', INTERVAL 1 DAY)
                AND e.prixcolis IS NOT NULL
                AND e.partocour IS NULL
                AND e.actif_cour = 0
                {$gareSql}
                {$opSql}
                {$ligneSql}
                GROUP BY COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis"
            )->result();
        }

        public function expetatspli($cid, $dt1, $dt2, $cp = FALSE, $gd = FALSE, $idconx = FALSE, $tycr = FALSE, $al = FALSE)
        {        
            if($cp === '' AND  $gd === '' AND $idconx === '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }        
            elseif($gd === '' AND $idconx=== '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.verifcour IN('A', 'C', 'D')
                    AND dest.id_compaga = '$cp'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif($idconx=== '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.verifcour IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif ($tycr === '' AND $al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND dest.id_compaga = '$cp'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND ar.roleattribut = '$idconx'
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif ($al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND lg.ident_ligne = '$al'
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
        }

        public function expetatsplis($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $idconx = FALSE, $tycr = FALSE, $al = FALSE)
        {
        $filtreLettres = etat_filtre_lettres('e.verifcour', 'e.dateenvoi', $cp, 'courrier', $dt1, $dt2);        
            if($gd === '' AND $cp === '' AND $idconx === '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif($cp === '' AND $idconx === '' AND $tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif ($idconx === '' AND $tycr === '' AND $al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND dest.id_compaga = '$cp'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif($tycr === '' AND $al === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif ($al === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis
                    ORDER BY e.dateenvoi ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    AND lg.ident_ligne = '$al'
                    {$filtreLettres}
                    GROUP BY ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis
                    ORDER BY e.dateenvoi ASC")->result();
        }

        public function expverspli($cid, $dt1, $dt2, $cp = FALSE, $gd = FALSE, $idconx = FALSE, $tycr = FALSE )
        {
        $filtreLettres = etat_filtre_lettres('e.verifcour', 'e.dateenvoi', $cp, 'courrier', $dt1, $dt2);        
            if($cp === '' AND $gd === '' AND $idconx === '' AND $tycr === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif($gd === '' AND $idconx === '' AND $tycr === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND dest.id_compaga = '$cp'
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }
            elseif($idconx === '' AND $tycr === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND dest.id_compaga = '$cp'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }
            
            elseif($tycr === ''){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND ar.roleattribut = '$idconx'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND dest.id_compaga = '$cp'
                    GROUP BY dest.id_compaga, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
            }
            
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, e.dateenvoi FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}
                    GROUP BY dest.id_compaga, e.dateenvoi
                    ORDER BY e.dateenvoi ASC")->result();
        }

        public function expetatspli1($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $idconx = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($tycr === '' AND $idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($idconx === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, ar.roleattribut, dest.id_compaga, u.first_name, u.last_name, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga,, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
        }

        public function expetatspli2($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $idconx = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolis) AS montant, e.dateenvoi, cd.naturecoli FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY e.dateenvoi, cd.naturecoli")->result();
            }
            elseif ($tycr === '' AND $idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolis) AS montant, e.dateenvoi, cd.naturecoli FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    AND e.verifcour IN('A', 'C', 'D')
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY e.dateenvoi, cd.naturecoli")->result();
            }
            elseif ($idconx === '') {
                return $this->db->query(
                    "SELECT SUM(prixcolis) AS montant, e.dateenvoi, cd.naturecoli FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY e.dateenvoi, cd.naturecoli")->result();
            }
            
                return $this->db->query(
                    "SELECT SUM(prixcolis) AS montant, e.dateenvoi, cd.naturecoli FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.actif_cour = 0
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY e.dateenvoi, cd.naturecoli")->result();
        }

        //report global des expeditions
        public function expetatspligl($cid, $dt1, $dt2, $gd = FALSE, $idconx = FALSE, $tycr = FALSE, $cp = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $idconx === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    GROUP BY cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis")->result();
            }
            elseif ($idconx === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis")->result();
            }
            elseif ($tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis")->result();
            }
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND dest.id_compaga = '$cp'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND dest.id_compaga = '$cp'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    GROUP BY cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND dest.id_compaga = '$cp'
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND ar.roleattribut = '$idconx'
                    AND lg.ident_ligne = '$algn'
                    GROUP BY cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis")->result();
        }

        //global plis
        public function expetatspliglob($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'")->result();
            }
            
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND dest.id_compaga = '$cp'")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'")->result();
            }
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'")->result();
        }

        //moitie plis

        public function expetatspliexo($cid, $dt1, $dt2, $cp = FALSE, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {
        $filtreLettres = etat_filtre_lettres('e.verifcour', 'e.dateenvoi', $cp, 'courrier', $dt1, $dt2);        
            if ($cp === '' AND $gd === '' AND $tycr === '' AND $algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}")->result();
            }
            elseif ($gd === '' AND $tycr === '' AND $algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND dest.id_compaga = '$cp'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}")->result();
            }
            elseif ($tycr === '' AND $algn === NULL) {
            
                return $this->db->query("SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND dest.id_compaga = '$cp'
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}")->result();
            }
            elseif($algn === NULL) {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}")->result();

            }
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}")->result();
        }

        //recapt

        public function recaptexopli($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {
        $filtreLettres = etat_filtre_lettres('e.verifcour', 'e.dateenvoi', $cp, 'courrier', $dt1, $dt2);        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
        }

        public function recaptexopligr($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {
        $filtreLettres = etat_filtre_lettres('e.verifcour', 'e.dateenvoi', $cp, 'courrier', $dt1, $dt2);        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    {$filtreLettres}
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}")->result();
            }
                return $this->db->query(
                    "SELECT * FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}")->result();
        }

        public function recaptexoplid($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocr = 1
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocr = 1
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli = '$tycr'
                    AND e.exocr = 1
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND e.exocr = 1
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.exocr = 1
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND e.exocr = 1
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
        }

        public function recaptexoplijr($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, h.heure FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.verifcour IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, h.id_heure
                    ORDER BY heure ASC")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, h.heure FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND e.verifcour IN('A', 'C', 'D')
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, h.id_heure
                    ORDER BY heure ASC")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, h.heure FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, h.id_heure
                    ORDER BY heure ASC")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis, h.heure FROM courriers_exp e
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    AND e.verifcour IN('A', 'C', 'D')
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis, h.id_heure
                    ORDER BY heure ASC")->result();
        }

        
        public function recaptexopliheb($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $tycr = FALSE, $algn = FALSE)
        {
        $filtreLettres = etat_filtre_lettres('e.verifcour', 'e.dateenvoi', $cp, 'courrier', $dt1, $dt2);        
            if ($gd === '' AND $cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis, h.heure, e.dateenvoi FROM courriers_exp e
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis, h.id_heure, e.dateenvoi
                    ORDER BY e.dateenvoi, h.id_heure")->result();
            }
            elseif ($cp === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis, h.heure, e.dateenvoi FROM courriers_exp e
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis, h.id_heure, e.dateenvoi
                    ORDER BY e.dateenvoi, h.id_heure")->result();
            }
            elseif($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis, h.heure, e.dateenvoi FROM courriers_exp e
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    {$filtreLettres}
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis, h.id_heure, e.dateenvoi
                    ORDER BY e.dateenvoi, h.id_heure")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis, h.heure, e.dateenvoi FROM courriers_exp e
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}
                    GROUP BY COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis, h.id_heure, e.dateenvoi
                    ORDER BY e.dateenvoi, h.id_heure")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis, h.heure, e.dateenvoi FROM courriers_exp e
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    {$filtreLettres}
                    GROUP BY COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis, h.id_heure, e.dateenvoi
                    ORDER BY e.dateenvoi, h.id_heure ASC")->result();
        }

        public function recaptpli($cid, $cp, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($tycr === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND cd.naturecoli <> 'Carton'
                    AND cd.naturecoli <> 'Moyen_plis'
                    AND cd.naturecoli <> 'Moyen_colis'
                    AND cd.naturecoli <> 'Argent'
                    AND cd.naturecoli <> 'Divers'
                    AND cd.naturecoli <> 'Sac_partenaire'
                    AND cd.naturecoli <> 'Petit_colis'
                    AND cd.naturecoli <> 'Gros_colis'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
        }
        public function recaptpligl($cid, $dt1, $dt2, $gd = FALSE, $tycr = FALSE, $cp = FALSE, $algn = FALSE)
        {        
            if ($gd === '' AND $tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif($tycr === '' AND $cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif ($cp === '' AND $algn === '') {
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif($tycr === '' AND $algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
            elseif($algn === '')
            {
                return $this->db->query("SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND dest.id_compaga = '$cp'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL
                    AND COALESCE(NULLIF(TRIM(e.code_gaexp_vendu), ''), gex.code_gaexp) = '$gd'
                    AND lg.ident_ligne = '$algn'
                    AND cd.naturecoli = '$tycr'
                    GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), cd.naturecoli, e.prixcolis")->result();
        }

        public function trecaptpligl($cid, $dt1, $dt2, $cp = FALSE, $gd = FALSE, $tycr = FALSE, $algn = FALSE, $sgid = FALSE)
        {
            $filled = function ($v) {
                return $v !== FALSE && $v !== null && $v !== '' && $v !== '0';
            };
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $sql = "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, cd.naturecoli, e.prixcolis FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept 
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1'
                    AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL";
            if ($filled($cp)) {
                $sql .= " AND dest.id_compaga = '" . $this->db->escape_str($cp) . "'";
            }
            if ($filled($gd)) {
                // Gare d’émission (sous-gare / gaexp) — aligné TRI courrier.
                $sql .= $this->sql_filtre_gare_emission($gd, 'sg', 'lg.gaexp_lg');
            }
            if ($filled($tycr)) {
                $sql .= " AND cd.naturecoli = '" . $this->db->escape_str($tycr) . "'";
            }
            if ($filled($algn)) {
                $sql .= " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "'";
            }
            if ($filled($sgid)) {
                $sql .= " AND e.courrierdepartgare = '" . $this->db->escape_str($sgid) . "'";
            }
            $sql .= " GROUP BY dest.id_compaga, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis";
            return $this->db->query($sql)->result();
        }

        public function texpetatspligl($cid, $dt1, $dt2, $gd = FALSE, $cp = FALSE, $idconx = FALSE, $tycr = FALSE, $algn = FALSE)
        {
            $filled = function ($v) {
                return $v !== FALSE && $v !== null && $v !== '' && $v !== '0';
            };
            $cid = $this->db->escape_str($cid);
            $dt1 = $this->db->escape_str($dt1);
            $dt2 = $this->db->escape_str($dt2);
            $sql = "SELECT COUNT(courrierexpid) AS nombres, SUM(prixcolis) AS montant, cd.naturecoli, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne, e.prixcolis
                    FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND e.dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND e.prixcolis IS NOT NULL
                    AND e.partocour IS NULL";
            if ($filled($gd)) {
                $sql .= $this->sql_filtre_gare_emission($gd, 'sg', 'lg.gaexp_lg');
            }
            if ($filled($cp)) {
                $sql .= " AND dest.id_compaga = '" . $this->db->escape_str($cp) . "'";
            }
            if ($filled($idconx)) {
                $sql .= $this->sql_filtre_operateur_courrier($idconx);
            }
            if ($filled($tycr)) {
                $sql .= " AND cd.naturecoli = '" . $this->db->escape_str($tycr) . "'";
            }
            if ($filled($algn)) {
                $sql .= " AND lg.ident_ligne = '" . $this->db->escape_str($algn) . "'";
            }
            $sql .= " GROUP BY COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne), e.prixcolis, cd.naturecoli";
            return $this->db->query($sql)->result();
        }

        public function facts($cid, $dt1, $dt2, $tcl, $gd, $nat = FALSE)
        {   
            if($nat === ''){
                return $this->db->query(
                "SELECT COUNT(courrierexpid) AS nbrs, SUM(nombrecolis) AS nbcol, cd.naturecoli FROM courriers_exp e
                JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND e.courclore = 1
                AND e.partocour IS NOT NULL
                AND e.rexepedier IS NULL
                GROUP BY cd.naturecoli")->result();
            }     
            elseif($nat === 'Petit_plis' OR $nat === 'Gros_plis')
            {
              return $this->db->query(
                "SELECT COUNT(courrierexpid) AS nbrs, SUM(nombrecolis) AS nbcol FROM courriers_exp e
                JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND cd.naturecoli IN ('Petit_plis', 'Moyen_plis', 'Gros_plis')
                AND e.courclore = 1
                AND e.partocour IS NOT NULL
                AND e.rexepedier IS NULL
                GROUP BY cd.naturecoli")->result();
            }
 
            elseif($nat === 'Petit_colis' OR $nat === 'Gros_colis' OR $nat === 'Moyen_colis')
            {
              return $this->db->query(
                "SELECT COUNT(courrierexpid) AS nbrs, SUM(nombrecolis) AS nbcol FROM courriers_exp e
                JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND cd.naturecoli IN ('Petit_colis', 'Moyen_colis', 'Gros_colis')
                AND e.courclore = 1
                AND e.partocour IS NOT NULL
                AND e.rexepedier IS NULL
                GROUP BY cd.naturecoli")->result();
            }
              return $this->db->query(
                "SELECT COUNT(courrierexpid) AS nbrs, SUM(nombrecolis) AS nbcol FROM courriers_exp e
                JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                JOIN utilisateurs u ON cu.userlog_id = u.uid
                JOIN gares g ON ul.guser = g.idengare
                JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'
                AND lg.gaexp_lg = '$gd'
                AND cd.naturecoli = '$nat'
                AND cd.clorecodecour = 1
                AND e.courclore = 1
                AND e.partocour IS NOT NULL
                AND e.rexepedier IS NULL
                GROUP BY cd.naturecoli")->result();
        
        }

        public function factcolis($cid, $dt1, $dt2, $tcl, $gd, $nt)
        {   
            if($nt === 'Petit_colis' OR $nt === 'Moyen_colis' OR $nt === 'Gros_colis'){
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nbrs, SUM(nombrecolis) AS nbcol, e.dateenvoi, e.naturecourrier FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN recepteurs re ON er.receptid = re.idrecepetion
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND ctcl.idtype_client = '$tcl'
                    AND lg.gaexp_lg = '$gd'
                    AND cd.clorecodecour = 1
                    AND cd.naturecoli IN ('Petit_colis', 'Moyen_colis', 'Gros_colis')
                    AND e.courclore = 1
                    AND e.partocour IS NOT NULL
                    AND e.rexepedier IS NULL
                    GROUP BY e.dateenvoi, e.naturecourrier")->result();
            }
                return $this->db->query(
                    "SELECT COUNT(courrierexpid) AS nbrs, SUM(nombrecolis) AS nbcol, e.dateenvoi, e.naturecourrier FROM courriers_exp e
                    JOIN attributions_role ar ON e.idoperateur = ar.roleattribut
                    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
                    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
                    JOIN utilisateurs u ON cu.userlog_id = u.uid
                    JOIN gares g ON ul.guser = g.idengare
                    JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                    JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                    JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                    JOIN expediteurs ex ON er.expditid = ex.id_expedit
                    JOIN recepteurs re ON er.receptid = re.idrecepetion
                    JOIN client cl ON ex.clientexpedit = cl.id_client
                    JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                    JOIN programme pr ON e.departcolis = pr.code_progr
                    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                    JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                    JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                    WHERE ep.ekey = '$cid'
                    AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                    AND ctcl.idtype_client = '$tcl'
                    AND cd.naturecoli = '$nt'
                    AND lg.gaexp_lg = '$gd'
                    AND cd.clorecodecour = 1
                    AND e.courclore = 1
                    AND e.partocour IS NOT NULL
                    AND e.rexepedier IS NULL
                    GROUP BY e.dateenvoi, e.naturecourrier")->result();
        
        }

        public function factvald($cid, $dt1, $dt2, $gd = FALSE, $tcl = FALSE)
        {        
            if ($gd === '' AND $tcl === '') {
                return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND cd.clorecodecour = 1
                AND e.courclore = 1
                AND e.partocour = IS NOT NULL
                AND e.rexepedier IS NULL")->result();
        
            }
            elseif ($tcl === '') {
                return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND e.courclore = 1
                AND e.partocour IS NOT NULL
                AND e.rexepedier IS NULL
                AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)")->result();
            }
                return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN sousgare sg ON e.courrierdepartgare = sg.idsousgare
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN expediteurs ex ON er.expditid = ex.id_expedit
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON ex.clientexpedit = cl.id_client
                JOIN contrat_client ctcl ON ctcl.idtype_client = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND lg.gaexp_lg = '$gd'
                AND cd.clorecodecour = 1
                AND e.courclore = 1
                AND e.partocour IS NOT NULL
                AND e.rexepedier IS NULL
                AND e.dateenvoi >= '$dt1' AND dateenvoi < DATE_ADD('$dt2', INTERVAL 1 DAY)
                AND ctcl.idtype_client = '$tcl'")->result();
        }

        public function lg($cid, $gd, $sg)
        {
                return $this->db->query(
                "SELECT lg.ident_ligne, COALESCE(NULLIF(TRIM(e.nom_ligne_vendu), ''), lg.nom_ligne) AS nom_ligne FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolis = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.is_validcour = 0
                AND e.statuscourrier = 'pas_transit'
                GROUP BY lg.ident_ligne, e.garearrivecolis, sg.idsousgare")->result();
        }

        public function getrecept($cid, $expid, $gd, $sgid)
        {
            
            return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND BINARY e.num_cour = '$expid'
                AND e.garearrivecolis = '$gd'
                AND e.is_validcour = 1
                AND sg.idsousgare = '$sgid'
                AND e.actif_cour = 0
                AND re.datetimerecept IS NULL")->row();
        }

        public function getreceptperso($cid, $expid, $gd, $sgid)
        {
            
            return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND BINARY e.num_cour = '$expid'
                AND e.garearrivecolis = '$gd'
                AND e.is_validcour = 1
                AND sg.idsousgare = '$sgid'
                AND e.actif_cour = 0
                AND re.datetimerecept IS NULL")->row();
        }

        public function vald($cid, $gd, $sg, $sgdep, $dat, $hre, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolis = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgare = '$sgdep'
                AND pr.date_progr = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcour = 1")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolis = '$gd'
                AND sg.idsousgare = '$sg'
                AND pr.date_progr = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcour = 1
                AND e.courrierdepartgare = '$sgdep'
                AND e.courrierexpid = '$exp'")->row();
        }

        public function valdpers($cid, $gd, $sg, $sgdep, $dat, $hre, $exp = FALSE)
        {
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolis = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgare = '$sgdep'
                AND pr.date_progr = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcour = 1")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN personnels pe ON re.persorecep = pe.matricule
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_exp gex ON lg.gaexp_lg = gex.code_gaexp
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolis = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.courrierdepartgare = '$sgdep'
                AND pr.date_progr = '$dat'
                AND h.id_heure = '$hre'
                AND e.is_validcour = 1
                AND e.courrierexpid = '$exp'")->row();
        }

        public function getdest($cid, $gd, $sg, $exp = FALSE)
        {
            $day = mdate("%Y-%m-%d", now('UTC'));
            if ($exp === FALSE) {
                return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolis = '$gd'
                AND sg.idsousgare = '$sg'
                AND e.is_validcour = 0
                AND e.statuscourrier ='pas_transit'
                AND e.actif_cour = 0")->result();
            }
            return $this->db->query(
                "SELECT * FROM courriers_exp e
                JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
                JOIN expeditreception er ON cd.exprecepident = er.idexprecept
                JOIN sousgare sg ON e.sousgarearrivid = sg.idsousgare
                JOIN recepteurs re ON er.receptid = re.idrecepetion
                JOIN client cl ON re.client_recept = cl.id_client 
                JOIN programme pr ON e.departcolis = pr.code_progr
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN heures h ON lh.heure_identif = h.id_heure
                JOIN lignes lg ON cd.idlignes = lg.ident_ligne
                JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
                JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
                JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
                WHERE ep.ekey = '$cid'
                AND e.garearrivecolis = '$gd'
                AND e.courrierexpid = '$exp'
                AND e.is_validcour = 0
                AND e.actif_cour = 0
                AND e.statuscourrier ='pas_transit'
                AND sg.idsousgare = '$sg'")->row();
        }
    }
    /** Courriers_exp_model.php **/
    /** application/models/Courriers_exp_model.php **/
