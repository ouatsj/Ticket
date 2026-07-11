<?php
/**
 * Audit : activités enregistrées sous un roleattribut alors que le propriétaire
 * du compte semble inactif (indicateur possible d'utilisation du compte d'autrui via URL).
 *
 * Usage: php scripts/db/audit_roleattribut_cross_usage.php [--since=2026-07-01]
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
require dirname(__DIR__) . '/db/_bootstrap.php';

$since = '2026-07-01';
foreach ($argv as $arg) {
    if (strpos($arg, '--since=') === 0) {
        $since = substr($arg, 8);
    }
}

$m = db_script_connect([]);

function q($m, $sql)
{
    $r = $m->query($sql);
    if ($r === false) {
        fwrite(STDERR, "SQL error: {$m->error}\n{$sql}\n");
        return array();
    }
    $rows = array();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

echo "=== AUDIT ROLEATTRIBUT — période depuis {$since} ===\n";
echo "Généré le " . date('Y-m-d H:i:s') . " UTC\n\n";

// 1. Cartographie
echo "## 1. CARTOGRAPHIE DES ATTRIBUTIONS (rôles terrain 4,5,6,15,16,18)\n\n";
$map = q($m, "
    SELECT ar.roleattribut, cu.username, cu.cpuser_id, ar.userole, ul.guser AS gare,
        cu.is_conect, ar.activeattrib, cu.derniere_activite_at, cu.date_conect
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ar.activer_role = 0
    AND ar.userole IN (4, 5, 6, 15, 16, 18)
    ORDER BY ul.guser, ar.userole, cu.username
");
foreach ($map as $row) {
    echo sprintf(
        "  ra=%s | @%-20s | cpuser=%s | role=%s | gare=%-6s | connecté=%s | actif_attrib=%s | dernière_act=%s\n",
        $row['roleattribut'],
        $row['username'],
        $row['cpuser_id'],
        $row['userole'],
        $row['gare'],
        $row['is_conect'],
        $row['activeattrib'],
        $row['derniere_activite_at'] ?: 'NULL'
    );
}
echo "\nTotal attributions: " . count($map) . "\n\n";

// Index cpuser -> roleattributs
$by_cpuser = array();
foreach ($map as $row) {
    $by_cpuser[$row['cpuser_id']][] = $row;
}

echo "## 2. COMPTES MULTI-GARES (risque confusion roleattribut)\n\n";
$multi = q($m, "
    SELECT cu.cpuser_id, cu.username, ar.userole, COUNT(DISTINCT ul.guser) AS nb_gares,
        GROUP_CONCAT(DISTINCT CONCAT(ar.roleattribut, ':', ul.guser) ORDER BY ul.guser SEPARATOR ' | ') AS attributions
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ar.activer_role = 0
    AND ar.userole IN (4, 5, 6, 15, 16, 18)
    GROUP BY cu.cpuser_id, cu.username, ar.userole
    HAVING nb_gares > 1
    ORDER BY nb_gares DESC
");
foreach ($multi as $row) {
    echo sprintf(
        "  @%-18s cpuser=%-4s role=%s | %d gares → %s\n",
        $row['username'],
        $row['cpuser_id'],
        $row['userole'],
        $row['nb_gares'],
        $row['attributions']
    );
}
echo "\nComptes multi-gares: " . count($multi) . "\n\n";

// 3. Activité par roleattribut + suspicion inactivité propriétaire
echo "## 3. ACTIVITÉ SOUS UN ROLEATTRIBUT — propriétaire possiblement inactif\n";
echo "(Indicateur : opérations récentes mais derniere_activite_at antérieure ou compte déconnecté)\n\n";

$activity = q($m, "
    SELECT ar.roleattribut, cu.username, cu.cpuser_id, ar.userole, ul.guser AS gare,
        cu.is_conect, cu.derniere_activite_at,
        COALESCE(p.nb, 0) AS tickets,
        COALESCE(p.mt, 0) AS tickets_montant,
        COALESCE(p.last_dt, NULL) AS last_ticket,
        COALESCE(r.nb, 0) AS recettes,
        COALESCE(r.last_dt, NULL) AS last_recette,
        COALESCE(d.nb, 0) AS depenses,
        COALESCE(d.last_dt, NULL) AS last_depense,
        COALESCE(cg.nb, 0) AS arrets_compte
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    LEFT JOIN (
        SELECT idcptuser AS ra, COUNT(*) AS nb, SUM(prixvente) AS mt, MAX(date_emis) AS last_dt
        FROM passager WHERE date_emis >= '{$since}' GROUP BY idcptuser
    ) p ON p.ra = ar.roleattribut
    LEFT JOIN (
        SELECT idopera AS ra, COUNT(*) AS nb, MAX(date_recet) AS last_dt
        FROM recette WHERE date_recet >= '{$since}' GROUP BY idopera
    ) r ON r.ra = ar.roleattribut
    LEFT JOIN (
        SELECT idop_dep AS ra, COUNT(*) AS nb, MAX(date_depens) AS last_dt
        FROM depense WHERE date_depens >= '{$since}' GROUP BY idop_dep
    ) d ON d.ra = ar.roleattribut
    LEFT JOIN (
        SELECT idusercompt AS ra, COUNT(*) AS nb
        FROM compte_guichet WHERE datearretcompt >= '{$since}' GROUP BY idusercompt
    ) cg ON cg.ra = ar.roleattribut
    WHERE ar.activer_role = 0
    AND ar.userole IN (4, 5, 6, 15, 16, 18)
    HAVING tickets > 0 OR recettes > 0 OR depenses > 0 OR arrets_compte > 0
    ORDER BY GREATEST(COALESCE(p.last_dt,'0000-00-00'), COALESCE(r.last_dt,'0000-00-00'), COALESCE(d.last_dt,'0000-00-00')) DESC
");

$suspects = array();
foreach ($activity as $row) {
    $last_op = max(
        $row['last_ticket'] ?: '0000-00-00',
        $row['last_recette'] ?: '0000-00-00',
        $row['last_depense'] ?: '0000-00-00'
    );
    $last_act = $row['derniere_activite_at'] ?: '0000-00-00';
    $gap_days = $last_op !== '0000-00-00' && $last_act !== '0000-00-00'
        ? (strtotime($last_op) - strtotime($last_act)) / 86400
        : null;

    $flag = ($row['is_conect'] === '0' && $last_op >= $since)
        || ($gap_days !== null && $gap_days > 1);

    if ($flag) {
        $suspects[] = $row;
        echo sprintf(
            "  *** SUSPECT ra=%s @%-16s gare=%-6s | tickets=%s rec=%s dep=%s | last_op=%s | dernière_act=%s | écart=%s j | connecté=%s\n",
            $row['roleattribut'],
            $row['username'],
            $row['gare'],
            $row['tickets'],
            $row['recettes'],
            $row['depenses'],
            $last_op,
            $row['derniere_activite_at'] ?: 'NULL',
            $gap_days !== null ? round($gap_days, 1) : 'n/a',
            $row['is_conect']
        );
    }
}
echo "\nAttributions suspectes (inactivité propriétaire): " . count($suspects) . "\n\n";

// 4. Lignes RD fantômes (arrêt chef sans validation caissier)
echo "## 4. LIGNES RD « FANTÔMES » (arrêt chef, pas validé caissier) — par chef\n\n";
$ghosts_r = q($m, "
    SELECT r.idopera AS roleattribut, cu.username, ul.guser AS gare,
        COUNT(*) AS nb, SUM(r.montant_recet) AS montant, MIN(r.date_recet) AS depuis, MAX(r.date_recet) AS jusqu
    FROM recette r
    JOIN attributions_role ar ON r.idopera = ar.roleattribut
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE r.active_recet = 1 AND r.is_actifrecet = 0 AND (r.is_validerecet = 0 OR r.is_validerecet IS NULL)
    AND r.date_recet >= '{$since}'
    GROUP BY r.idopera, cu.username, ul.guser
    ORDER BY montant DESC
");
foreach ($ghosts_r as $row) {
    echo sprintf(
        "  REC ra=%s @%-16s gare=%-6s | %d lignes | %s F | %s → %s\n",
        $row['roleattribut'],
        $row['username'],
        $row['gare'],
        $row['nb'],
        number_format($row['montant'], 0, '', ' '),
        $row['depuis'],
        $row['jusqu']
    );
}

$ghosts_d = q($m, "
    SELECT d.idop_dep AS roleattribut, cu.username, ul.guser AS gare,
        COUNT(*) AS nb, SUM(d.montant_depens) AS montant, MIN(d.date_depens) AS depuis, MAX(d.date_depens) AS jusqu
    FROM depense d
    JOIN attributions_role ar ON d.idop_dep = ar.roleattribut
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE d.active_dep = 1 AND d.is_actifdep = 0 AND (d.is_validedep = 0 OR d.is_validedep IS NULL)
    AND d.date_depens >= '{$since}'
    GROUP BY d.idop_dep, cu.username, ul.guser
    ORDER BY montant DESC
");
echo "\n";
foreach ($ghosts_d as $row) {
    echo sprintf(
        "  DEP ra=%s @%-16s gare=%-6s | %d lignes | %s F | %s → %s\n",
        $row['roleattribut'],
        $row['username'],
        $row['gare'],
        $row['nb'],
        number_format($row['montant'], 0, '', ' '),
        $row['depuis'],
        $row['jusqu']
    );
}
echo "\n";

// 5. Tickets incohérents (validés chef mais compteur ouvert — contamination)
echo "## 5. TICKETS INCOHÉRENTS (is_valdtick=1, statutvente=0) — ventes au nom du vendeur\n\n";
$ticket_ghost = q($m, "
    SELECT p.idcptuser AS roleattribut, cu.username, ul.guser AS gare,
        COUNT(*) AS nb, SUM(p.prixvente) AS montant,
        MIN(p.date_emis) AS depuis, MAX(p.date_emis) AS jusqu
    FROM passager p
    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE p.is_valdtick = 1 AND p.statutvente = 0
    AND p.date_emis >= '{$since}'
    GROUP BY p.idcptuser, cu.username, ul.guser
    ORDER BY montant DESC
");
foreach ($ticket_ghost as $row) {
    echo sprintf(
        "  TICKET ra=%s @%-16s gare=%-6s | %d tickets | %s F | %s → %s\n",
        $row['roleattribut'],
        $row['username'],
        $row['gare'],
        $row['nb'],
        number_format($row['montant'], 0, '', ' '),
        $row['depuis'],
        $row['jusqu']
    );
}
echo "\nTotal vendeurs avec tickets fantômes: " . count($ticket_ghost) . "\n\n";

// 6. Même gare, même jour : activité sur 2+ roleattributs même userole — qui était connecté ?
echo "## 6. MÊME GARE / MÊME JOUR : plusieurs opérateurs même rôle actifs\n";
echo "(Si un seul était connecté, l'autre a peut-être eu de l'activité via URL)\n\n";

$same_day = q($m, "
    SELECT ul.guser AS gare, ar.userole, p.date_emis AS jour,
        GROUP_CONCAT(DISTINCT CONCAT(cu.username, '(ra', ar.roleattribut, '):', cnt) ORDER BY cnt DESC SEPARATOR ' | ') AS operateurs
    FROM (
        SELECT idcptuser AS ra, DATE(date_emis) AS date_emis, COUNT(*) AS cnt
        FROM passager WHERE date_emis >= '{$since}'
        GROUP BY idcptuser, DATE(date_emis)
        HAVING cnt >= 5
    ) p
    JOIN attributions_role ar ON p.ra = ar.roleattribut
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ar.userole IN (5, 6)
    GROUP BY ul.guser, ar.userole, p.date_emis
    HAVING COUNT(DISTINCT ar.roleattribut) > 1
    ORDER BY p.date_emis DESC, ul.guser
    LIMIT 40
");
foreach ($same_day as $row) {
    echo sprintf(
        "  %s | gare=%-6s role=%s | %s\n",
        $row['jour'],
        $row['gare'],
        $row['userole'],
        $row['operateurs']
    );
}
echo "\n";

// 7. Résumé retombées financières
echo "## 7. RETOMBÉES FINANCIÈRES GLOBALES (depuis {$since})\n\n";

$totals = q($m, "
    SELECT
        (SELECT COUNT(*) FROM recette WHERE active_recet=1 AND is_actifrecet=0 AND date_recet>='{$since}') AS ghost_rec_nb,
        (SELECT COALESCE(SUM(montant_recet),0) FROM recette WHERE active_recet=1 AND is_actifrecet=0 AND date_recet>='{$since}') AS ghost_rec_mt,
        (SELECT COUNT(*) FROM depense WHERE active_dep=1 AND is_actifdep=0 AND date_depens>='{$since}') AS ghost_dep_nb,
        (SELECT COALESCE(SUM(montant_depens),0) FROM depense WHERE active_dep=1 AND is_actifdep=0 AND date_depens>='{$since}') AS ghost_dep_mt,
        (SELECT COUNT(*) FROM passager WHERE is_valdtick=1 AND statutvente=0 AND date_emis>='{$since}') AS ghost_tick_nb,
        (SELECT COALESCE(SUM(prixvente),0) FROM passager WHERE is_valdtick=1 AND statutvente=0 AND date_emis>='{$since}') AS ghost_tick_mt
");
$t = $totals[0];
echo "  Recettes fantômes (arrêt chef, pas validé caissier): {$t['ghost_rec_nb']} lignes | " . number_format($t['ghost_rec_mt'], 0, '', ' ') . " F\n";
echo "  Dépenses fantômes (arrêt chef, pas validé caissier): {$t['ghost_dep_nb']} lignes | " . number_format($t['ghost_dep_mt'], 0, '', ' ') . " F\n";
echo "  Tickets fantômes (validés chef, compteur ouvert):      {$t['ghost_tick_nb']} tickets | " . number_format($t['ghost_tick_mt'], 0, '', ' ') . " F\n\n";

echo "## 8. LIMITES DE L'AUDIT\n\n";
echo "  - Pas de journal de session/IP : impossible de prouver à 100%% qui était devant l'écran.\n";
echo "  - Les indicateurs « SUSPECT » = activité sous roleattribut X alors que le propriétaire @X semble inactif.\n";
echo "  - Les lignes fantômes = données enregistrées au nom de X, bloquées dans un cycle incomplet.\n";
echo "  - Le garde roleattribut_guard (déployé) empêche désormais les nouvelles occurrences via URL.\n\n";
echo "=== FIN AUDIT ===\n";
