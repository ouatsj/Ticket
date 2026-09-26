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
        'ticket_liste' => 'Ticket : exercice liste passagers',
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
            lettres VARCHAR(32) NOT NULL DEFAULT '',
            toutes TINYINT(1) NOT NULL DEFAULT 0,
            declared_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_etat_lettre_declaration (entreprise_ekey, cle_compagnie, famille, mois)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    );
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

function etat_declaration_deja($ekey, $cle, $famille, $dt1, $dt2)
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
    foreach (etat_lettre_mois_entre($debut, $fin) as $mois) {
        $snap = $CI->db->query(
            'SELECT id FROM etat_lettre_declaration
             WHERE entreprise_ekey = ? AND cle_compagnie = ? AND famille = ? AND mois = ? LIMIT 1',
            array((int) $ekey, $cle, $famille, $mois)
        )->row();
        if ($snap) {
            return true;
        }
        if (etat_lettre_tampon_existe($CI, $ekey, $cle, $famille, $mois)) {
            return true;
        }
    }
    return false;
}

function etat_declaration_figer($ekey, $cle, $famille, $dt1, $dt2, $acteur = null)
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
    $now = date('Y-m-d H:i:s');
    foreach (etat_lettre_mois_entre($debut, $fin) as $mois) {
        $regle = etat_lettre_effective($cle, $famille, $mois . '-01', $ekey);
        $CI->db->query(
            'INSERT IGNORE INTO etat_lettre_declaration
             (entreprise_ekey, cle_compagnie, famille, mois, lettres, toutes, declared_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            array(
                (int) $ekey,
                $cle,
                $famille,
                $mois,
                implode(',', $regle['lettres']),
                !empty($regle['toutes']) ? 1 : 0,
                $now,
            )
        );
    }
}

function etat_lettre_tampon_existe($CI, $ekey, $cle, $famille, $mois)
{
    $debut = $mois . '-01';
    $fin = date('Y-m-d', strtotime($debut . ' +1 month'));
    $ekey = (int) $ekey;
    $cleSql = $CI->db->escape($cle);
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
             AND p.datep_create >= " . $CI->db->escape($debut) . "
             AND p.datep_create < " . $CI->db->escape($fin) . "
             AND p.exop = 1 LIMIT 1",
            "SELECT 1 FROM non_passager np
             JOIN lignes lg ON np.id_ligne_pass = lg.ident_ligne
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND np.datevente >= " . $CI->db->escape($debut) . "
             AND np.datevente < " . $CI->db->escape($fin) . "
             AND np.exonp = 1 LIMIT 1",
        ),
        'ticket_escal' => array(
            "SELECT 1 FROM escalclients esp
             JOIN lignes lg ON esp.lignintescal = lg.ident_ligne
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND esp.datedepescal >= " . $CI->db->escape($debut) . "
             AND esp.datedepescal < " . $CI->db->escape($fin) . "
             AND esp.exopes = 1 LIMIT 1",
        ),
        'bagage' => array(
            "SELECT 1 FROM bagages bg
             JOIN programme pr ON bg.progidbagage = pr.code_progr
             JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND bg.date_create >= " . $CI->db->escape($debut) . "
             AND bg.date_create < " . $CI->db->escape($fin) . "
             AND bg.exobg = 1 LIMIT 1",
            "SELECT 1 FROM bagagesesc bg
             JOIN lignes lg ON bg.id_lgeheuresc IS NOT NULL AND lg.ident_ligne = lg.ident_ligne
             WHERE 1 = 0",
        ),
    );
    if ($famille === 'bagage') {
        $map['bagage'][1] = "SELECT 1 FROM bagagesesc bg
             JOIN ligne_heure lh ON bg.id_lgeheuresc = lh.id_ligneheure
             JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
             JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
             JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
             JOIN entreprise e ON c.id_entrep = e.id_entreprise
             WHERE e.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
             AND bg.date_createesc >= " . $CI->db->escape($debut) . "
             AND bg.date_createesc < " . $CI->db->escape($fin) . "
             AND bg.exobagesc = 1 LIMIT 1";
    }
    $map['courrier'] = array(
        "SELECT 1 FROM courriers_exp e
         JOIN code_courriers cd ON e.id_codecourrier = cd.codecolisid
         JOIN lignes lg ON cd.idlignes = lg.ident_ligne
         JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
         JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
         JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
         WHERE ep.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
         AND e.dateenvoi >= " . $CI->db->escape($debut) . "
         AND e.dateenvoi < " . $CI->db->escape($fin) . "
         AND e.exocr = 1 LIMIT 1",
        "SELECT 1 FROM courriers_expesc es
         JOIN code_courriers cd ON es.id_codecourrieresc = cd.codecolisid
         JOIN lignes lg ON cd.idlignes = lg.ident_ligne
         JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
         JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
         JOIN entreprise ep ON c.id_entrep = ep.id_entreprise
         WHERE ep.ekey = {$ekey} AND dest.id_compaga = {$cleSql}
         AND es.dateenvoiesc >= " . $CI->db->escape($debut) . "
         AND es.dateenvoiesc < " . $CI->db->escape($fin) . "
         AND es.exocresc = 1 LIMIT 1",
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
