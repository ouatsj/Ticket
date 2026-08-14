<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Programme_model extends CI_Model
    {
        protected $table = 'programme';
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function create(array $data)
        {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
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
            if ($idsousgare === null || $idsousgare === '' || $idsousgare === FALSE || (int) $idsousgare <= 0) {
                // Sans SG courante: uniquement départs « toutes gares » sans liste multi.
                return " AND pr.idsousgare_prog IS NULL"
                    . " AND NOT EXISTS (SELECT 1 FROM programme_sousgare ps0 WHERE ps0.code_progr = pr.code_progr)";
            }
            $sg = (int) $idsousgare;
            return " AND ("
                . " EXISTS (SELECT 1 FROM programme_sousgare ps WHERE ps.code_progr = pr.code_progr AND ps.idsousgare = {$sg})"
                . " OR ("
                . " NOT EXISTS (SELECT 1 FROM programme_sousgare ps2 WHERE ps2.code_progr = pr.code_progr)"
                . " AND (pr.idsousgare_prog IS NULL OR pr.idsousgare_prog = {$sg})"
                . " )"
                . " )";
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
                    AND lh.ligne_id = '{$lgEsc}'
                    AND pr.date_progr = '{$dtEsc}'
                    AND lh.id_ligneheure = '{$lhEsc}'
                    AND pr.statut_prog = 'actif'
                    AND pr.actif_prog = 0
                    AND h.h_active = 1
                    {$sgFilter}
                    ORDER BY pr.code_progr DESC";

            $rows = $this->db->query($sql)->result();
            return is_array($rows) ? $rows : array();
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

            // Pas de JOIN tarification : plusieurs tarifs (type client / actif) multipliaient
            // la même heure. Prix via sous-requête (1 ligne, typetarif programme, actif).
            return $this->db->query(
                "SELECT pr.code_progr, pr.intervalle1, pr.intervalle2, pr.date_progr,
                        lh.id_ligneheure, h.heure,
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
                WHERE e.ekey = '$cd'
                AND lh.ligne_id = '$id'
                AND pr.date_progr >='$dt'
                AND pr.statut_prog ='actif'
                AND h.h_active = 1
                AND pr.actif_prog = 0
                AND pr.typetarif = '$t'
                AND DATE_FORMAT(pr.dateheure_prog, '%Y-%m-%d-%H:%i:%s') >= '$dtoday'
                GROUP BY pr.code_progr, pr.intervalle1, pr.intervalle2, pr.date_progr,
                         lh.id_ligneheure, h.heure, pr.typetarif
                ORDER BY pr.date_progr ASC, h.heure ASC")->result();
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
            // Voir: départs gare (NULL) + départs de cette sous-gare. Masque les autres SG.
            $sgFilter = $this->sql_filtre_sousgare($idsousgare);
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
                return $this->db->query(
                    "SELECT {$selectArrivee}
                    {$joinsArrivee}
                    WHERE e.id_entreprise = '$cd'
                    AND pr.gareidentif = '$cdg'
                    AND pr.date_progr >= '$today'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
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
         * Heures vente guichet pour un OD + date.
         * - Catalogue ligne_heure de l'OD (si existant)
         * - + départs (programmes) réels de la gare de départ ce jour-là
         * has_programme = true uniquement si un programme existe sur la ligne OD
         * (un départ d'une autre ligne en gare reste sélectionnable → transit / creedepart).
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

            // Hybride: communs gare + propres à la sous-gare courante.
            $sgFilter = $this->sql_filtre_sousgare($sg);

            $gaexp = '';
            if (strpos($axe, '-') !== FALSE) {
                $gaexp = explode('-', $axe, 2)[0];
            }
            $gaexpEsc = $this->db->escape_str($gaexp);
            $mode = 'hybride';

            // Programmes sur la ligne OD (pour flag has_programme / vente directe).
            $progs = $this->db->query(
                "SELECT lh.id_ligneheure, pr.code_progr, pr.idsousgare_prog
                 FROM programme pr
                 JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                 JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
                 JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                 JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                 JOIN entreprise e ON c.id_entrep = e.id_entreprise
                 WHERE e.ekey = '{$cidEsc}'
                 AND lh.ligne_id = '{$axeEsc}'
                 AND pr.date_progr = '{$dateEsc}'
                 AND pr.statut_prog = 'actif'
                 AND pr.actif_prog = 0
                 {$sgFilter}
                 ORDER BY (pr.idsousgare_prog IS NULL) ASC, pr.code_progr DESC"
            )->result();

            $byLh = array();
            $scopeByLh = array();
            foreach ($progs as $p) {
                $idLh = $p->id_ligneheure;
                if (isset($byLh[$idLh])) {
                    continue; // déjà le meilleur (sous-gare d'abord)
                }
                $byLh[$idLh] = $p->code_progr;
                $scopeByLh[$idLh] = ($p->idsousgare_prog === null || $p->idsousgare_prog === '')
                    ? 'gare'
                    : 'sousgare';
            }

            // Filtre heures passées (même logique que heureligne1) pour les départs gare.
            $timeFilter = '';
            $keyToday = mdate("%Y-%m-%d", now());
            if ($date === $keyToday) {
                $tim = date('H', time());
                if ($tim === '00') {
                    $dte = date('H:i', time() - 3600);
                } else {
                    $dte = date('H:i', time() - 3600);
                }
                $dteEsc = $this->db->escape_str($dte);
                $timeFilter = " AND h.heure >= '{$dteEsc}'";
            }

            // Départs réels de la gare de départ (toutes lignes), pour OD sans catalogue / transit.
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
            $seenLh = array();

            foreach ($catalogue as $row) {
                $id = isset($row->id_ligneheure) ? $row->id_ligneheure : '';
                if ($id === '' || isset($seenLh[$id])) {
                    continue;
                }
                $seenLh[$id] = TRUE;
                $heures[] = array(
                    'id_ligneheure' => $id,
                    'heure' => isset($row->heure) ? $row->heure : '',
                    'has_programme' => isset($byLh[$id]),
                    'code_progr' => isset($byLh[$id]) ? $byLh[$id] : null,
                    'scope' => isset($scopeByLh[$id]) ? $scopeByLh[$id] : null,
                    'source' => 'catalogue',
                );
            }

            foreach ($progsGare as $pg) {
                $id = isset($pg->id_ligneheure) ? $pg->id_ligneheure : '';
                if ($id === '' || isset($seenLh[$id])) {
                    continue;
                }
                $seenLh[$id] = TRUE;
                $isOd = (isset($pg->ligne_id) && $pg->ligne_id === $axe);
                if ($isOd && !isset($byLh[$id])) {
                    $byLh[$id] = $pg->code_progr;
                    $scopeByLh[$id] = ($pg->idsousgare_prog === null || $pg->idsousgare_prog === '')
                        ? 'gare'
                        : 'sousgare';
                }
                $heures[] = array(
                    'id_ligneheure' => $id,
                    'heure' => isset($pg->heure) ? $pg->heure : '',
                    'has_programme' => $isOd && isset($byLh[$id]),
                    'code_progr' => ($isOd && isset($byLh[$id])) ? $byLh[$id] : null,
                    'scope' => ($isOd && isset($scopeByLh[$id])) ? $scopeByLh[$id] : null,
                    'source' => 'gare',
                    'ligne_depart' => isset($pg->ligne_id) ? $pg->ligne_id : null,
                );
            }

            usort($heures, function ($a, $b) {
                return strcmp((string) $a['heure'], (string) $b['heure']);
            });

            $CI =& get_instance();
            if (!isset($CI->m_itineraire_etape)) {
                $CI->load->model('Itineraire_etape_model', 'm_itineraire_etape');
            }
            // has_transit = composition déclarative / étapes existantes (indépendant de prefer_direct).
            $etapesTransit = $CI->m_itineraire_etape->get_by_parent($cid, $axe);
            if (empty($etapesTransit) && !isset($CI->m_itineraire)) {
                $CI->load->model('Itineraire_model', 'm_itineraire');
            }
            if (empty($etapesTransit)) {
                // Repli legacy itineraire_lignes via getitine force (sans bloquer prefer_direct).
                if (!isset($CI->m_itineraire)) {
                    $CI->load->model('Itineraire_model', 'm_itineraire');
                }
                $etapesTransit = $CI->m_itineraire->getitine($cid, $axe, $date, $sg, TRUE);
            }
            $has_transit = !empty($etapesTransit);

            return array(
                'ligne' => $axe,
                'mode_depart' => $mode,
                'has_transit' => $has_transit ? TRUE : FALSE,
                'heures' => $heures,
            );
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


        public function heureligne($cid, $it, $keys)
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
            
            if($keys === $key){
                return $this->db->query(
                "SELECT * FROM ligne_heure lh
                    JOIN programme pr ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND h.heure >= '$dte'
                    AND pr.actif_prog = 0
                    AND pr.date_progr = '$keys'
                    AND pr.statut_prog = 'actif'
                    AND lh.actif_lh = 1
                    AND h.h_active = 1
                    ORDER BY h.heure ASC")->result();
            }
            if($keys > $key){
            return $this->db->query(
                "SELECT * FROM ligne_heure lh
                    JOIN programme pr ON pr.id_heur = lh.id_ligneheure
                    JOIN heures h ON lh.heure_identif = h.id_heure
                    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne 
                    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
                    JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                    JOIN entreprise e ON c.id_entrep = e.id_entreprise
                    WHERE e.ekey = '$cid'
                    AND lh.ligne_id = '$it'
                    AND pr.date_progr = '$keys'
                    AND pr.statut_prog = 'actif'
                    AND h.h_active = 1
                    AND pr.actif_prog = 0
                    AND lh.actif_lh = 1
                    ORDER BY h.heure ASC")->result();
            }
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
                    GROUP BY pr.depart_code, pr.date_progr, pr.code_progr")->result();
        }
        /**
         * Codes programmes partageant les sièges (lien correspondance dérivé ↔ suite).
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
            return $this->m_programme_correspondance->codes_sieges_partages($code);
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
            $miroir = $this->m_programme_correspondance->miroir_derive_info($cd);

            // Dérivé Banfora→Bobo : miroir des sièges déjà occupés sur la suite Bobo.
            if ($miroir) {
                $suiteEsc = $this->db->escape_str($miroir['suite']);
                $deriveEsc = $this->db->escape_str($miroir['derive']);
                return $this->db->query(
                    "SELECT * FROM siege_categorie sc
                    JOIN categorie ct ON sc.idcat_bus=ct.categorie
                    JOIN programme pr ON pr.categori=ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                    JOIN lignes l ON lh.ligne_id=l.ident_ligne
                    JOIN heures h ON lh.heure_identif=h.id_heure
                    WHERE sc.siege_num IN (
                        SELECT p.num_siege_categorie FROM passager p
                        WHERE p.code_pro = '{$suiteEsc}'
                          AND p.num_siege_categorie IS NOT NULL
                          AND p.num_siege_categorie BETWEEN {$d} AND {$f}
                    )
                    AND sc.siege_num NOT IN (
                        SELECT p2.num_siege_categorie FROM passager p2
                        WHERE p2.code_pro = '{$deriveEsc}'
                          AND p2.num_siege_categorie IS NOT NULL
                          AND p2.num_siege_categorie BETWEEN {$d} AND {$f}
                    )
                    AND pr.code_progr='{$cdEsc}'
                    AND pr.date_progr='{$datEsc}'
                    AND l.nom_ligne='{$lgEsc}'
                    AND h.heure='{$hrEsc}'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND sc.siege_num BETWEEN {$d} AND {$f}
                    ORDER BY sc.siege_num ASC"
                )->result();
            }

            $occupes = $this->_sql_in_codes($this->codes_sieges_occupes($cd));

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
                                          AND p.num_siege_categorie BETWEEN {$d} AND {$f})
                AND pr.code_progr='{$cdEsc}'
                AND pr.date_progr='{$datEsc}'
                AND l.nom_ligne='{$lgEsc}'
                AND h.heure='{$hrEsc}'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN {$d} AND {$f}
                ORDER BY sc.siege_num ASC"
            )->result();
        }


        ///numero de siege en fonction du bus


        public function cdprogbus($cid, $cd, $dat, $lg, $hr, $d, $f)
        {
            
            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus = ct.categorie
                JOIN programme pr ON pr.categori = ct.categorie
                JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id = l.ident_ligne
                JOIN heures h ON lh.heure_identif = h.id_heure
                WHERE siege_num NOT IN (SELECT p.num_siege_categorie FROM passager p
                                          JOIN programme pr ON p.code_pro = pr.code_progr
                                          JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
                                          JOIN lignes l ON lh.ligne_id = l.ident_ligne
                                          JOIN heures h ON lh.heure_identif = h.id_heure
                                          JOIN gare_exp ex ON l.gaexp_lg = ex.code_gaexp
                                          JOIN compagnies c ON ex.id_compagd = c.cle_compagnie
                                          JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                          WHERE e.ekey = '$cid'
                                          AND pr.depart_code = '$cd'
                                          AND pr.date_progr = '$dat'
                                          AND l.nom_ligne = '$lg'
                                          AND h.heure = '$hr'
                                          AND h.h_active = 1
                                          AND lh.actif_lh = 1
                                          AND pr.actif_prog = 0
                                          AND p.num_siege_categorie IS NOT NULL
                                          AND sc.siege_num BETWEEN $d AND $f)
                    
                AND pr.depart_code = '$cd'
                AND pr.date_progr = '$dat'
                AND l.nom_ligne = '$lg'
                AND h.heure = '$hr'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN $d AND $f
                ORDER BY sc.siege_num ASC")->result();
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
            $miroir = $this->m_programme_correspondance->miroir_derive_info($cd);
            if ($miroir) {
                $suiteEsc = $this->db->escape_str($miroir['suite']);
                $deriveEsc = $this->db->escape_str($miroir['derive']);
                return $this->db->query(
                    "SELECT * FROM siege_categorie sc
                    JOIN categorie ct ON sc.idcat_bus=ct.categorie
                    JOIN programme pr ON pr.categori=ct.categorie
                    JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                    JOIN lignes l ON lh.ligne_id=l.ident_ligne
                    JOIN heures h ON lh.heure_identif=h.id_heure
                    WHERE sc.siege_num IN (
                        SELECT p.num_siege_categorie FROM passager p
                        WHERE p.code_pro = '{$suiteEsc}'
                          AND p.num_siege_categorie IS NOT NULL
                          AND p.num_siege_categorie BETWEEN {$d} AND {$f}
                    )
                    AND sc.siege_num NOT IN (
                        SELECT p2.num_siege_categorie FROM passager p2
                        WHERE p2.code_pro = '{$deriveEsc}'
                          AND p2.num_siege_categorie IS NOT NULL
                          AND p2.num_siege_categorie BETWEEN {$d} AND {$f}
                    )
                    AND pr.code_progr='{$cdEsc}'
                    AND h.h_active = 1
                    AND lh.actif_lh = 1
                    AND pr.actif_prog = 0
                    AND sc.siege_num BETWEEN {$d} AND {$f}
                    ORDER BY sc.siege_num ASC"
                )->result();
            }

            $occupes = $this->_sql_in_codes($this->codes_sieges_occupes($cd));

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
                                          AND p.num_siege_categorie BETWEEN {$d} AND {$f})
                AND pr.code_progr='{$cdEsc}'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN {$d} AND {$f}
                ORDER BY sc.siege_num ASC"
            )->result();
        }

        public function cdprogtransbus($cid, $cd, $d, $f)
        {
            
            return $this->db->query(
                "SELECT * FROM siege_categorie sc
                JOIN categorie ct ON sc.idcat_bus=ct.categorie
                JOIN programme pr ON pr.categori=ct.categorie
                JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                JOIN lignes l ON lh.ligne_id=l.ident_ligne
                JOIN heures h ON lh.heure_identif=h.id_heure
                WHERE siege_num NOT IN (SELECT p.num_siege_categorie FROM passager p
                                          JOIN programme pr ON p.code_pro=pr.code_progr
                                          JOIN ligne_heure lh ON pr.id_heur=lh.id_ligneheure
                                          JOIN lignes l ON lh.ligne_id=l.ident_ligne
                                          JOIN heures h ON lh.heure_identif=h.id_heure
                                          JOIN gare_exp ex ON l.gaexp_lg=ex.code_gaexp
                                          JOIN compagnies c ON ex.id_compagd=c.cle_compagnie
                                          JOIN entreprise e ON c.id_entrep=e.id_entreprise
                                          WHERE e.ekey='$cid'
                                          AND pr.depart_code='$cd'
                                          AND h.h_active = 1
                                          AND lh.actif_lh = 1
                                          AND p.num_siege_categorie IS NOT NULL
                                          AND pr.actif_prog = 0
                                          AND sc.siege_num BETWEEN $d AND $f)
                    
                AND pr.depart_code='$cd'
                AND h.h_active = 1
                AND lh.actif_lh = 1
                AND pr.actif_prog = 0
                AND sc.siege_num BETWEEN $d AND $f
                ORDER BY sc.siege_num ASC")->result();
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
