<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lettres des états (ticket, bagage, courrier), par compagnie et par mois.
 * La règle d'origine reproduit le comportement déjà en place.
 * Une période qui traverse deux règles applique chaque mois puis se combine.
 */

function etat_lettre_familles()
{
    return array(
        'ticket_etats' => 'Ticket : exercice, récap EX, brouillard, déclaration, manifest',
        'ticket_liste' => 'Ticket : liste passagers (legacy ; hub utilise ticket_etats)',
        'ticket_escal' => 'Ticket escale',
        'bagage' => 'Bagage et bagage escale',
        'courrier' => 'Courrier et courrier escale',
    );
}

function etat_lettre_regles_origine()
{
    return array(
        array('cle' => '0', 'famille' => 'ticket_etats', 'lettres' => array('A', 'C', 'D'), 'toutes' => false),
        array('cle' => '0', 'famille' => 'ticket_liste', 'lettres' => array('A', 'C', 'D'), 'toutes' => false),
        array('cle' => '0', 'famille' => 'ticket_escal', 'lettres' => array('A', 'C', 'D'), 'toutes' => false),
        array('cle' => '0', 'famille' => 'bagage', 'lettres' => array('A', 'C'), 'toutes' => false),
        array('cle' => '0', 'famille' => 'courrier', 'lettres' => array('A', 'C', 'D'), 'toutes' => false),
        array('cle' => '5002', 'famille' => 'ticket_etats', 'lettres' => array(), 'toutes' => true),
    );
}

function etat_lettre_ensure($db = null)
{
    static $done = false;
    if ($done) {
        return true;
    }
    $CI = function_exists('get_instance') ? get_instance() : null;
    if ($db === null) {
        if (!$CI || !isset($CI->db)) {
            return false;
        }
        $db = $CI->db;
    }
    $prev = $db->db_debug;
    $db->db_debug = false;
    $db->query(
        "CREATE TABLE IF NOT EXISTS etat_lettre_regle (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            entreprise_ekey INT NOT NULL DEFAULT 0,
            cle_compagnie VARCHAR(32) NOT NULL DEFAULT '0',
            famille VARCHAR(32) NOT NULL,
            lettres VARCHAR(32) NOT NULL DEFAULT '',
            toutes TINYINT(1) NOT NULL DEFAULT 0,
            applicable_depuis DATE NOT NULL,
            created_at DATETIME NOT NULL,
            created_by INT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_etat_lettre_regle (entreprise_ekey, cle_compagnie, famille, applicable_depuis)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    );
    $db->query(
        "CREATE TABLE IF NOT EXISTS etat_lettre_declaration (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            entreprise_ekey INT NOT NULL,
            cle_compagnie VARCHAR(32) NOT NULL,
            famille VARCHAR(32) NOT NULL,
            mois CHAR(7) NOT NULL,
            code_gare VARCHAR(32) NOT NULL DEFAULT '',
            lettres VARCHAR(32) NOT NULL DEFAULT '',
            toutes TINYINT(1) NOT NULL DEFAULT 0,
            declared_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_etat_lettre_declaration (entreprise_ekey, cle_compagnie, famille, mois, code_gare)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    );
    etat_lettre_ensure_declaration_gare($db);
    $ok = $db->table_exists('etat_lettre_regle');
    if ($ok) {
        $n = $db->query('SELECT COUNT(*) AS n FROM etat_lettre_regle')->row();
        if (!$n || (int) $n->n === 0) {
            $now = date('Y-m-d H:i:s');
            foreach (etat_lettre_regles_origine() as $regle) {
                $db->query(
                    'INSERT IGNORE INTO etat_lettre_regle
                    (entreprise_ekey, cle_compagnie, famille, lettres, toutes, applicable_depuis, created_at, created_by)
                    VALUES (0, ?, ?, ?, ?, ?, ?, NULL)',
                    array(
                        $regle['cle'],
                        $regle['famille'],
                        implode(',', $regle['lettres']),
                        $regle['toutes'] ? 1 : 0,
                        '1970-01-01',
                        $now,
                    )
                );
            }
        }
        etat_lettre_ensure_index($db, 'bagages', 'idx_bagage_couleurcarnet', 'couleurcarnet');
        etat_lettre_ensure_index($db, 'bagagesesc', 'idx_bagageesc_couleurcarnet', 'couleurcarnetesc');
        etat_lettre_ensure_index($db, 'courriers_exp', 'idx_courrier_verifcour', 'verifcour');
        etat_lettre_ensure_index($db, 'courriers_expesc', 'idx_courrieresc_verifcouresc', 'verifcouresc');
        etat_lettre_ensure_index($db, 'non_passager', 'idx_nonpassager_verif', 'verifnonpassager');
        etat_lettre_ensure_index($db, 'escalclients', 'idx_escal_panier', 'escalpanier');
    }
    $db->db_debug = $prev;
    $done = $ok;
    return $ok;
}

function etat_lettre_ensure_index($db, $table, $index, $column)
{
    if (!$db->table_exists($table) || !$db->field_exists($column, $table)) {
        return;
    }
    $q = $db->query('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ' . $db->escape($index));
    if ($q && $q->num_rows() > 0) {
        return;
    }
    $db->query('ALTER TABLE `' . $table . '` ADD INDEX `' . $index . '` (`' . $column . '`)');
}

/**
 * Verrou déclaration : compagnie + famille + mois + gare (plus compagnie seule).
 */
function etat_lettre_ensure_declaration_gare($db)
{
    if (!$db->table_exists('etat_lettre_declaration')) {
        return;
    }
    $prev = $db->db_debug;
    $db->db_debug = false;
    if (!$db->field_exists('code_gare', 'etat_lettre_declaration')) {
        $db->query(
            "ALTER TABLE etat_lettre_declaration
             ADD COLUMN code_gare VARCHAR(32) NOT NULL DEFAULT '' AFTER mois"
        );
        $idx = $db->query(
            "SHOW INDEX FROM etat_lettre_declaration WHERE Key_name = 'uq_etat_lettre_declaration'"
        );
        if ($idx && $idx->num_rows() > 0) {
            $db->query('ALTER TABLE etat_lettre_declaration DROP INDEX uq_etat_lettre_declaration');
        }
        $db->query(
            "ALTER TABLE etat_lettre_declaration
             ADD UNIQUE KEY uq_etat_lettre_declaration
             (entreprise_ekey, cle_compagnie, famille, mois, code_gare)"
        );
    }
    // Anciens verrous sans gare : rattacher à la gare qui a réellement déclaré.
    $orphelins = $db->query(
        "SELECT id, entreprise_ekey, cle_compagnie, famille, mois
         FROM etat_lettre_declaration
         WHERE code_gare = '' OR code_gare IS NULL"
    );
    if ($orphelins && $orphelins->num_rows() > 0) {
        $CI = function_exists('get_instance') ? get_instance() : null;
        foreach ($orphelins->result() as $row) {
            $gare = ($CI && isset($CI->db))
                ? etat_declaration_inferer_gare($CI, $row)
                : '';
            if ($gare !== '') {
                $db->query(
                    'UPDATE etat_lettre_declaration SET code_gare = ? WHERE id = ? AND (code_gare = \'\' OR code_gare IS NULL)',
                    array($gare, (int) $row->id)
                );
            }
        }
    }
    $db->db_debug = $prev;
}

function etat_declaration_normaliser_gare($CI, $codeGare)
{
    $raw = trim((string) $codeGare);
    if ($raw === '' || $raw === '0') {
        return '';
    }
    $row = $CI->db->query(
        'SELECT code_gaexp, garesid FROM gare_exp
         WHERE code_gaexp = ? OR garesid = ?
         LIMIT 1',
        array($raw, $raw)
    )->row();
    if ($row) {
        $code = trim((string) $row->code_gaexp);
        if ($code !== '') {
            return $code;
        }
        $sid = trim((string) $row->garesid);
        if ($sid !== '') {
            return $sid;
        }
    }
    return $raw;
}

function etat_declaration_sql_match_gare($CI, $codeGare)
{
    $code = etat_declaration_normaliser_gare($CI, $codeGare);
    if ($code === '') {
        return '';
    }
    $esc = $CI->db->escape($code);
    return "(SELECT gx_lv.garesid FROM gare_exp gx_lv
             WHERE gx_lv.code_gaexp = {$esc} OR gx_lv.garesid = {$esc}
             LIMIT 1)";
}

function etat_lettre_expr_gare_passager($alias = 'p')
{
    return "COALESCE(
 (SELECT ge_v.garesid FROM sousgare sg_lv JOIN gare_exp ge_v ON ge_v.code_gaexp = sg_lv.gareprinceid WHERE sg_lv.idsousgare = {$alias}.idsousgare_vente LIMIT 1),
 (SELECT ulx.guser FROM attributions_role arx JOIN user_login ulx ON arx.idgestcompte = ulx.uid_login WHERE arx.roleattribut = {$alias}.idcptuser LIMIT 1),
 (SELECT ge_d.garesid FROM sousgare sg_ld JOIN gare_exp ge_d ON ge_d.code_gaexp = sg_ld.gareprinceid WHERE sg_ld.idsousgare = {$alias}.departclient_idgare LIMIT 1)
)";
}

function etat_lettre_expr_gare_non_passager($alias = 'np')
{
    return "COALESCE(
 (SELECT ulx.guser FROM attributions_role arx JOIN user_login ulx ON arx.idgestcompte = ulx.uid_login WHERE arx.roleattribut = {$alias}.cptus LIMIT 1),
 (SELECT ge_v.garesid FROM sousgare sg_v JOIN gare_exp ge_v ON ge_v.code_gaexp = sg_v.gareprinceid WHERE sg_v.idsousgare = {$alias}.sousgareidentif LIMIT 1)
)";
}

function etat_lettre_expr_gare_escal($alias = 'esp')
{
    return "COALESCE(
 (SELECT ge_v.garesid FROM sousgare sg_lv JOIN gare_exp ge_v ON ge_v.code_gaexp = sg_lv.gareprinceid WHERE sg_lv.idsousgare = {$alias}.departsgescal LIMIT 1),
 (SELECT ulx.guser FROM attributions_role arx JOIN user_login ulx ON arx.idgestcompte = ulx.uid_login WHERE arx.roleattribut = {$alias}.iduseescal LIMIT 1)
)";
}

/**
 * Devine la gare d'un ancien verrou (sans code_gare) via les tickets déjà flaggés.
 */
function etat_declaration_inferer_gare($CI, $row)
{
    $ekey = (int) $row->entreprise_ekey;
    $cle = trim((string) $row->cle_compagnie);
    $famille = trim((string) $row->famille);
    $mois = trim((string) $row->mois);
    if ($mois === '' || !preg_match('/^\d{4}-\d{2}$/', $mois)) {
        return '';
    }
    $debut = $mois . '-01';
    $fin = date('Y-m-d', strtotime($debut . ' +1 month'));
    $cleSql = $CI->db->escape($cle);
    $debutSql = $CI->db->escape($debut);
    $finSql = $CI->db->escape($fin);
    $sql = '';
    if ($famille === 'ticket_etats') {
        $expr = etat_lettre_expr_gare_passager('p');
        $sql = "SELECT {$expr} AS g, COUNT(*) AS n
            FROM passager p
            JOIN programme pr ON p.code_pro = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
            AND p.datep_create >= {$debutSql} AND p.datep_create < {$finSql}
            AND p.exop = 1
            GROUP BY g HAVING g IS NOT NULL AND g <> ''
            ORDER BY n DESC LIMIT 1";
    } elseif ($famille === 'ticket_escal') {
        $expr = etat_lettre_expr_gare_escal('esp');
        $sql = "SELECT {$expr} AS g, COUNT(*) AS n
            FROM escalclients esp
            JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
            AND esp.datedepescal >= {$debutSql} AND esp.datedepescal < {$finSql}
            AND esp.exopes = 1
            GROUP BY g HAVING g IS NOT NULL AND g <> ''
            ORDER BY n DESC LIMIT 1";
    } elseif ($famille === 'bagage') {
        $sql = "SELECT ex.code_gaexp AS g, COUNT(*) AS n
            FROM bagages bg
            JOIN programme pr ON bg.progidbagage = pr.code_progr
            JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
            JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
            AND bg.date_create >= {$debutSql} AND bg.date_create < {$finSql}
            AND bg.exobg = 1
            GROUP BY ex.code_gaexp HAVING g IS NOT NULL AND g <> ''
            ORDER BY n DESC LIMIT 1";
    } elseif ($famille === 'courrier') {
        $sql = "SELECT ex.code_gaexp AS g, COUNT(*) AS n
            FROM courriers_exp ce
            JOIN code_courriers cd ON ce.id_codecourrier = cd.codecolisid
            JOIN lignes lg ON cd.idlignes = lg.ident_ligne
            JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
            JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
            JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
            JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
            WHERE ep.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
            AND ce.dateenvoi >= {$debutSql} AND ce.dateenvoi < {$finSql}
            AND ce.exocr = 1
            GROUP BY ex.code_gaexp HAVING g IS NOT NULL AND g <> ''
            ORDER BY n DESC LIMIT 1";
    }
    if ($sql === '') {
        return '';
    }
    $prev = $CI->db->db_debug;
    $CI->db->db_debug = false;
    $q = $CI->db->query($sql);
    $CI->db->db_debug = $prev;
    if (!$q || !$q->row() || trim((string) $q->row()->g) === '') {
        return '';
    }
    return etat_declaration_normaliser_gare($CI, $q->row()->g);
}

function etat_lettre_colonnes()
{
    return array(
        'p.verifpassager' => 'p.datep_create',
        'np.verifnonpassager' => 'np.datevente',
        'esp.escalpanier' => 'esp.datedepescal',
        'es.escalpanier' => 'es.datedepescal',
        'bg.couleurcarnet' => 'bg.date_create',
        'bg.couleurcarnetesc' => 'bg.date_createesc',
        'e.verifcour' => 'e.dateenvoi',
        'e.verifcouresc' => 'e.dateenvoiesc',
        'es.verifcouresc' => 'es.dateenvoiesc',
    );
}

/**
 * Fragment SQL. Vide = toutes les lettres (aucun filtre), comme aujourd'hui pour la compagnie 5002.
 */
function etat_filtre_lettres($colonne, $colonneDate, $cleCompagnie, $famille, $dt1, $dt2)
{
    $cols = etat_lettre_colonnes();
    if (!isset($cols[$colonne]) || $cols[$colonne] !== $colonneDate) {
        return '';
    }
    if (!isset(etat_lettre_familles()[$famille])) {
        return '';
    }
    $ekey = 0;
    $CI = function_exists('get_instance') ? get_instance() : null;
    if ($CI && isset($CI->entreprise->ekey)) {
        $ekey = (int) $CI->entreprise->ekey;
    }
    $regles = etat_lettre_charger_regles();
    return etat_lettre_sql($colonne, $colonneDate, $cleCompagnie, $famille, $dt1, $dt2, $regles, $ekey);
}

function etat_lettre_charger_regles()
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = array();
    foreach (etat_lettre_regles_origine() as $regle) {
        $cache[] = array(
            'ekey' => 0,
            'cle' => (string) $regle['cle'],
            'famille' => $regle['famille'],
            'lettres' => $regle['lettres'],
            'toutes' => $regle['toutes'],
            'depuis' => '1970-01-01',
        );
    }
    if (!etat_lettre_ensure()) {
        return $cache;
    }
    $CI = get_instance();
    $q = $CI->db->query(
        'SELECT entreprise_ekey, cle_compagnie, famille, lettres, toutes, applicable_depuis
         FROM etat_lettre_regle'
    );
    if (!$q) {
        return $cache;
    }
    $rows = array();
    foreach ($q->result() as $row) {
        $lettres = etat_lettre_normaliser($row->lettres);
        $rows[] = array(
            'ekey' => (int) $row->entreprise_ekey,
            'cle' => (string) $row->cle_compagnie,
            'famille' => (string) $row->famille,
            'lettres' => $lettres,
            'toutes' => ((int) $row->toutes === 1),
            'depuis' => substr((string) $row->applicable_depuis, 0, 10),
        );
    }
    if ($rows) {
        $cache = $rows;
    }
    return $cache;
}

function etat_lettre_normaliser($brut)
{
    $out = array();
    foreach (preg_split('/[^A-E]+/i', (string) $brut) as $lettre) {
        $lettre = strtoupper(trim($lettre));
        if (in_array($lettre, array('A', 'B', 'C', 'D', 'E'), true) && !in_array($lettre, $out, true)) {
            $out[] = $lettre;
        }
    }
    return $out;
}

function etat_lettre_sql($colonne, $colonneDate, $cleCompagnie, $famille, $dt1, $dt2, array $regles, $ekey)
{
    $segments = etat_lettre_segments($cleCompagnie, $famille, $dt1, $dt2, $regles, $ekey);
    if (!$segments) {
        return '';
    }
    if (count($segments) === 1) {
        return etat_lettre_sql_simple($colonne, $segments[0]['lettres'], $segments[0]['toutes']);
    }
    $parts = array();
    foreach ($segments as $segment) {
        $date = $colonneDate . " >= '" . $segment['debut'] . "' AND " . $colonneDate . " < '" . $segment['fin'] . "'";
        if ($segment['toutes']) {
            $parts[] = '(' . $date . ')';
        } else {
            $parts[] = '(' . $date . ' AND ' . $colonne . ' IN (' . etat_lettre_in($segment['lettres']) . '))';
        }
    }
    return ' AND (' . implode(' OR ', $parts) . ')';
}

function etat_lettre_sql_simple($colonne, array $lettres, $toutes)
{
    if ($toutes) {
        return '';
    }
    if (!$lettres) {
        return '';
    }
    return ' AND ' . $colonne . ' IN (' . etat_lettre_in($lettres) . ')';
}

function etat_lettre_in(array $lettres)
{
    $bits = array();
    foreach ($lettres as $lettre) {
        $bits[] = "'" . $lettre . "'";
    }
    return implode(',', $bits);
}

function etat_lettre_segments($cleCompagnie, $famille, $dt1, $dt2, array $regles, $ekey)
{
    $cle = trim((string) $cleCompagnie);
    if ($cle === '' || $cle === 'FALSE') {
        $cle = '0';
    }
    $debut = etat_lettre_date($dt1);
    $finIncluse = etat_lettre_date($dt2);
    if ($debut === null || $finIncluse === null || $finIncluse < $debut) {
        $regle = etat_lettre_regle_au($cle, $famille, $debut ? $debut : '1970-01-01', $regles, $ekey);
        return array(array(
            'debut' => '1970-01-01',
            'fin' => '2999-01-01',
            'lettres' => $regle['lettres'],
            'toutes' => $regle['toutes'],
        ));
    }
    $finExclusive = date('Y-m-d', strtotime($finIncluse . ' +1 day'));
    $mois = etat_lettre_mois_entre($debut, $finIncluse);
    $segments = array();
    foreach ($mois as $m) {
        $mDebut = $m . '-01';
        $mFin = date('Y-m-d', strtotime($mDebut . ' +1 month'));
        $segDebut = ($mDebut < $debut) ? $debut : $mDebut;
        $segFin = ($mFin > $finExclusive) ? $finExclusive : $mFin;
        if ($segDebut >= $segFin) {
            continue;
        }
        $regle = etat_lettre_regle_au($cle, $famille, $mDebut, $regles, $ekey);
        $dernier = $segments ? $segments[count($segments) - 1] : null;
        $meme = $dernier
            && $dernier['toutes'] === $regle['toutes']
            && $dernier['lettres'] === $regle['lettres']
            && $dernier['fin'] === $segDebut;
        if ($meme) {
            $segments[count($segments) - 1]['fin'] = $segFin;
        } else {
            $segments[] = array(
                'debut' => $segDebut,
                'fin' => $segFin,
                'lettres' => $regle['lettres'],
                'toutes' => $regle['toutes'],
            );
        }
    }
    return $segments;
}

function etat_lettre_date($valeur)
{
    $valeur = substr(trim((string) $valeur), 0, 10);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valeur)) {
        return null;
    }
    return $valeur;
}

function etat_lettre_mois_entre($debut, $fin)
{
    $cur = substr($debut, 0, 7);
    $stop = substr($fin, 0, 7);
    $out = array();
    $garde = 0;
    while ($cur <= $stop && $garde < 240) {
        $out[] = $cur;
        $cur = date('Y-m', strtotime($cur . '-01 +1 month'));
        $garde++;
    }
    return $out;
}

function etat_lettre_regle_au($cle, $famille, $jour, array $regles, $ekey)
{
    $meilleure = null;
    $scoreMax = -1;
    $depuisMax = '';
    foreach ($regles as $regle) {
        if ($regle['famille'] !== $famille || $regle['depuis'] > $jour) {
            continue;
        }
        $score = 0;
        if ((int) $regle['ekey'] === (int) $ekey && (int) $ekey !== 0) {
            $score += 2;
        } elseif ((int) $regle['ekey'] === 0) {
            $score += 1;
        } else {
            continue;
        }
        if ((string) $regle['cle'] === (string) $cle && (string) $cle !== '0') {
            $score += 2;
        } elseif ((string) $regle['cle'] === '0') {
            $score += 1;
        } else {
            continue;
        }
        if ($score > $scoreMax || ($score === $scoreMax && $regle['depuis'] >= $depuisMax)) {
            $scoreMax = $score;
            $depuisMax = $regle['depuis'];
            $meilleure = $regle;
        }
    }
    if ($meilleure) {
        return $meilleure;
    }
    return array('lettres' => array('A', 'C', 'D'), 'toutes' => false, 'depuis' => '1970-01-01');
}

function etat_lettre_effective($cle, $famille, $jour, $ekey)
{
    $regles = etat_lettre_charger_regles();
    return etat_lettre_regle_au($cle, $famille, $jour, $regles, $ekey);
}

/**
 * @param string $codeGare code_gaexp / garesid ; vide = n'importe quelle gare (param lettres)
 */
function etat_declaration_deja($ekey, $cle, $famille, $dt1, $dt2, $codeGare = '')
{
    $debut = etat_lettre_date($dt1);
    $fin = etat_lettre_date($dt2);
    if ($debut === null || $fin === null) {
        return false;
    }
    if (!etat_lettre_ensure()) {
        return false;
    }
    $CI = get_instance();
    $cle = trim((string) $cle);
    $gare = etat_declaration_normaliser_gare($CI, $codeGare);
    foreach (etat_lettre_mois_entre($debut, $fin) as $mois) {
        if ($gare !== '') {
            $snap = $CI->db->query(
                'SELECT id FROM etat_lettre_declaration
                 WHERE entreprise_ekey = ? AND cle_compagnie = ? AND famille = ? AND mois = ? AND code_gare = ?
                 LIMIT 1',
                array((int) $ekey, $cle, $famille, $mois, $gare)
            )->row();
        } else {
            $snap = $CI->db->query(
                'SELECT id FROM etat_lettre_declaration
                 WHERE entreprise_ekey = ? AND cle_compagnie = ? AND famille = ? AND mois = ?
                 LIMIT 1',
                array((int) $ekey, $cle, $famille, $mois)
            )->row();
        }
        if ($snap) {
            return true;
        }
        if (etat_lettre_tampon_existe($CI, $ekey, $cle, $famille, $mois, $gare)) {
            return true;
        }
    }
    return false;
}

function etat_declaration_figer($ekey, $cle, $famille, $dt1, $dt2, $acteur = null, $codeGare = '')
{
    if (!etat_lettre_ensure()) {
        return;
    }
    $debut = etat_lettre_date($dt1);
    $fin = etat_lettre_date($dt2);
    if ($debut === null || $fin === null) {
        return;
    }
    $CI = get_instance();
    $cle = trim((string) $cle);
    $gare = etat_declaration_normaliser_gare($CI, $codeGare);
    if ($gare === '') {
        // Sans gare le verrou redeviendrait global : on refuse de figer.
        return;
    }
    $now = date('Y-m-d H:i:s');
    foreach (etat_lettre_mois_entre($debut, $fin) as $mois) {
        $regle = etat_lettre_effective($cle, $famille, $mois . '-01', $ekey);
        $CI->db->query(
            'INSERT IGNORE INTO etat_lettre_declaration
             (entreprise_ekey, cle_compagnie, famille, mois, code_gare, lettres, toutes, declared_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            array(
                (int) $ekey,
                $cle,
                $famille,
                $mois,
                $gare,
                implode(',', $regle['lettres']),
                !empty($regle['toutes']) ? 1 : 0,
                $now,
            )
        );
    }
}

/**
 * Liste les mois déclarés pour un hub Déclarer (ticket / bagage / courrier).
 *
 * @param int          $ekey
 * @param string|array $familles  famille(s) etat_lettre_declaration
 * @param string       $codeGare  filtre gare optionnel
 * @param string       $cleCompagnie filtre compagnie optionnel
 * @return array{annees: string[], mois: array<string, array>} mois indexés YYYY-MM
 */
function etat_declaration_lister_mois($ekey, $familles, $codeGare = '', $cleCompagnie = '')
{
    $out = array('annees' => array(), 'mois' => array());
    if (!etat_lettre_ensure()) {
        return $out;
    }
    $CI = get_instance();
    $familles = is_array($familles) ? $familles : array($familles);
    $familles = array_values(array_filter(array_map('strval', $familles)));
    if (!$familles) {
        return $out;
    }
    $params = array((int) $ekey);
    $in = array();
    foreach ($familles as $f) {
        $in[] = '?';
        $params[] = $f;
    }
    $sql = 'SELECT cle_compagnie, famille, mois, code_gare, lettres, toutes, declared_at
            FROM etat_lettre_declaration
            WHERE entreprise_ekey = ? AND famille IN (' . implode(',', $in) . ')';
    $gare = etat_declaration_normaliser_gare($CI, $codeGare);
    if ($gare !== '') {
        $sql .= ' AND code_gare = ?';
        $params[] = $gare;
    }
    $cle = trim((string) $cleCompagnie);
    if ($cle !== '') {
        $sql .= ' AND cle_compagnie = ?';
        $params[] = $cle;
    }
    $sql .= ' ORDER BY mois DESC, declared_at DESC';
    $rows = $CI->db->query($sql, $params)->result();
    $annees = array();
    foreach ($rows as $row) {
        $m = trim((string) $row->mois);
        if (!preg_match('/^\d{4}-\d{2}$/', $m)) {
            continue;
        }
        if (!isset($out['mois'][$m])) {
            $out['mois'][$m] = array(
                'mois' => $m,
                'annee' => substr($m, 0, 4),
                'debut' => $m . '-01',
                'fin' => date('Y-m-d', strtotime($m . '-01 +1 month -1 day')),
                'familles' => array(),
                'compagnies' => array(),
                'gares' => array(),
                'lignes' => array(),
            );
            $annees[substr($m, 0, 4)] = true;
        }
        $fam = trim((string) $row->famille);
        $out['mois'][$m]['familles'][$fam] = true;
        $cie = trim((string) $row->cle_compagnie);
        if ($cie !== '') {
            $out['mois'][$m]['compagnies'][$cie] = true;
        }
        $cg = trim((string) $row->code_gare);
        if ($cg !== '') {
            $out['mois'][$m]['gares'][$cg] = true;
        }
        $lettresRow = trim((string) (isset($row->lettres) ? $row->lettres : ''));
        if (!empty($row->toutes)) {
            $lettresRow = 'TOUTES';
        }
        $out['mois'][$m]['lignes'][] = array(
            'famille' => $fam,
            'cle_compagnie' => $cie,
            'code_gare' => $cg,
            'lettres' => $lettresRow,
            'declared_at' => isset($row->declared_at) ? (string) $row->declared_at : '',
        );
    }
    foreach ($out['mois'] as &$bloc) {
        $bloc['familles'] = array_keys($bloc['familles']);
        $bloc['compagnies'] = array_keys($bloc['compagnies']);
        $bloc['gares'] = array_keys($bloc['gares']);
    }
    unset($bloc);
    $out['annees'] = array_keys($annees);
    rsort($out['annees']);
    return $out;
}

function etat_lettre_tampon_existe($CI, $ekey, $cle, $famille, $mois, $codeGare = '')
{
    $debut = $mois . '-01';
    $fin = date('Y-m-d', strtotime($debut . ' +1 month'));
    $ekey = (int) $ekey;
    $cleSql = $CI->db->escape($cle);
    $debutSql = $CI->db->escape($debut);
    $finSql = $CI->db->escape($fin);
    $gareMatch = etat_declaration_sql_match_gare($CI, $codeGare);
    $filtreGarePassager = ($gareMatch !== '')
        ? ' AND ' . etat_lettre_expr_gare_passager('p') . ' = ' . $gareMatch
        : '';
    $filtreGareNp = ($gareMatch !== '')
        ? ' AND ' . etat_lettre_expr_gare_non_passager('np') . ' = ' . $gareMatch
        : '';
    $filtreGareEscal = ($gareMatch !== '')
        ? ' AND ' . etat_lettre_expr_gare_escal('esp') . ' = ' . $gareMatch
        : '';
    $filtreGareExp = '';
    if ($gareMatch !== '') {
        $code = etat_declaration_normaliser_gare($CI, $codeGare);
        $codeEsc = $CI->db->escape($code);
        $filtreGareExp = " AND (ex.code_gaexp = {$codeEsc} OR ex.garesid = {$codeEsc}
             OR ex.garesid = {$gareMatch})";
    }
    $map = array(
        'ticket_etats' => array(
            "SELECT 1 FROM passager p
             JOIN programme pr ON p.code_pro = pr.code_progr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND p.datep_create >= {$debutSql}
             AND p.datep_create < {$finSql}
             AND p.exop = 1
             {$filtreGarePassager}
             LIMIT 1",
            "SELECT 1 FROM non_passager np
             JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND np.datevente >= {$debutSql}
             AND np.datevente < {$finSql}
             AND np.exonp = 1
             {$filtreGareNp}
             LIMIT 1",
        ),
        'ticket_escal' => array(
            "SELECT 1 FROM escalclients esp
             JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND esp.datedepescal >= {$debutSql}
             AND esp.datedepescal < {$finSql}
             AND esp.exopes = 1
             {$filtreGareEscal}
             LIMIT 1",
        ),
        'bagage' => array(
            "SELECT 1 FROM bagages bg
             JOIN programme pr ON bg.progidbagage = pr.code_progr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND bg.date_create >= {$debutSql}
             AND bg.date_create < {$finSql}
             AND bg.exobg = 1
             {$filtreGareExp}
             LIMIT 1",
            "SELECT 1 FROM bagagesesc bg
             JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND bg.date_createesc >= {$debutSql}
             AND bg.date_createesc < {$finSql}
             AND bg.exobagesc = 1
             {$filtreGareExp}
             LIMIT 1",
        ),
        'courrier' => array(
            "SELECT 1 FROM courriers_exp e
             JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
             JOIN lignes lg ON cd.idlignes = lg.ident_ligne
             JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
             WHERE ep.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND e.dateenvoi >= {$debutSql}
             AND e.dateenvoi < {$finSql}
             AND e.exocr = 1
             {$filtreGareExp}
             LIMIT 1",
            "SELECT 1 FROM courriers_expesc es
             JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
             JOIN lignes lg ON cd.idlignes = lg.ident_ligne
             JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
             WHERE ep.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND es.dateenvoiesc >= {$debutSql}
             AND es.dateenvoiesc < {$finSql}
             AND es.exocresc = 1
             {$filtreGareExp}
             LIMIT 1",
        ),
    );
    if (!isset($map[$famille])) {
        return false;
    }
    $prev = $CI->db->db_debug;
    $CI->db->db_debug = false;
    foreach ($map[$famille] as $sql) {
        $q = $CI->db->query($sql);
        if ($q && $q->num_rows() > 0) {
            $CI->db->db_debug = $prev;
            return true;
        }
    }
    $CI->db->db_debug = $prev;
    return false;
}

function etat_lettre_enregistrer($ekey, $cle, $famille, $mois, array $lettres, $toutes, $acteur = null)
{
    if (!etat_lettre_ensure()) {
        return false;
    }
    if (!isset(etat_lettre_familles()[$famille]) || !preg_match('/^\d{4}-\d{2}$/', $mois)) {
        return false;
    }
    $cle = trim((string) $cle);
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $cle)) {
        return false;
    }
    $depuis = $mois . '-01';
    if ($depuis < date('Y-m-01')) {
        return false;
    }
    $CI = get_instance();
    $CI->db->query(
        'INSERT INTO etat_lettre_regle
         (entreprise_ekey, cle_compagnie, famille, lettres, toutes, applicable_depuis, created_at, created_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE lettres = VALUES(lettres), toutes = VALUES(toutes), created_at = VALUES(created_at), created_by = VALUES(created_by)',
        array(
            (int) $ekey,
            $cle,
            $famille,
            implode(',', etat_lettre_normaliser(implode(',', $lettres))),
            $toutes ? 1 : 0,
            $depuis,
            date('Y-m-d H:i:s'),
            $acteur ? (int) $acteur : null,
        )
    );
    etat_lettre_vider_cache();
    return true;
}

function etat_lettre_vider_cache()
{
    // Le cache statique de etat_lettre_charger_regles() vit le temps de la requête.
}
