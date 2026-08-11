#!/usr/bin/env php
<?php
/**
 * Réparation idopera / idop_dep écrasés par validation caissier.
 *
 * Règle : lignes où saisisseur = caissier (4/18) ET saisisseur = validateur
 *          → restaurer idopera/idop_dep = chef dominant (volume) même gare + même jour.
 *          operavalid / opevalid inchangés (restent le caissier).
 *
 * Périmètre recettes : tous types (surtout Courrier / Ticket).
 * Périmètre dépenses : type Courrier uniquement (Reçu/Facture/Bon = revue manuelle).
 *
 * Usage:
 *   php scripts/db/repair_idopera_caissier_overwrite.php              # dry-run
 *   php scripts/db/repair_idopera_caissier_overwrite.php --apply       # écrit
 *   php scripts/db/repair_idopera_caissier_overwrite.php --from=2026-07-04 --to=2026-07-16
 *   php scripts/db/repair_idopera_caissier_overwrite.php --min-confidence=70
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';
$mysqli = db_script_connect($argv ?? []);

$apply = in_array('--apply', $argv, true);
$from = '2026-07-04';
$to = '2026-07-16';
$minConf = 50;
foreach ($argv as $arg) {
    if (strpos($arg, '--from=') === 0) {
        $from = substr($arg, 7);
    }
    if (strpos($arg, '--to=') === 0) {
        $to = substr($arg, 5);
    }
    if (strpos($arg, '--min-confidence=') === 0) {
        $minConf = (int) substr($arg, 17);
    }
}

echo ($apply ? "MODE APPLY\n" : "MODE DRY-RUN (aucune écriture)\n");
echo "Période: {$from} → {$to} | confiance min: {$minConf}%\n\n";

function chef_map(mysqli $db, $from, $to, $table)
{
    $dateCol = $table === 'recette' ? 'date_recet' : 'date_depens';
    $idCol = $table === 'recette' ? 'idopera' : 'idop_dep';
    $mtCol = $table === 'recette' ? 'montant_recet' : 'montant_depens';
    $active = $table === 'recette' ? 'active_recet=1' : 'active_dep=1';

    $sql = "
    SELECT ul.guser, t.{$dateCol} AS jour, t.{$idCol} AS ra_chef, cu.username,
           SUM(t.{$mtCol}) AS mt,
           SUM(SUM(t.{$mtCol})) OVER (PARTITION BY ul.guser, t.{$dateCol}) AS mt_jour,
           ROW_NUMBER() OVER (
             PARTITION BY ul.guser, t.{$dateCol}
             ORDER BY SUM(t.{$mtCol}) DESC, COUNT(*) DESC
           ) AS rk
    FROM {$table} t
    JOIN attributions_role ar ON ar.roleattribut = t.{$idCol} AND ar.userole IN (5,16)
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE t.{$active}
      AND t.{$dateCol} BETWEEN ? AND ?
    GROUP BY ul.guser, t.{$dateCol}, t.{$idCol}, cu.username
    ";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ss', $from, $to);
    $stmt->execute();
    $res = $stmt->get_result();
    $map = [];
    while ($row = $res->fetch_assoc()) {
        if ((int) $row['rk'] !== 1) {
            continue;
        }
        $pct = $row['mt_jour'] > 0 ? round(100 * $row['mt'] / $row['mt_jour'], 1) : 0;
        $map[$row['guser'] . '|' . $row['jour']] = [
            'ra' => (int) $row['ra_chef'],
            'username' => $row['username'],
            'pct' => $pct,
        ];
    }
    $stmt->close();
    return $map;
}

$recMap = chef_map($mysqli, $from, $to, 'recette');
$depMap = chef_map($mysqli, $from, $to, 'depense');

// --- RECETTES ---
$sqlBadRec = "
SELECT re.id_recette, re.date_recet, re.idopera AS ra_actuel, re.operavalid,
       re.montant_recet, re.type_recet, ul.guser, g.garenom, cu.username AS caissier
FROM recette re
JOIN attributions_role ar ON ar.roleattribut = re.idopera AND ar.userole IN (4,18)
JOIN user_login ul ON ar.idgestcompte = ul.uid_login
JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE re.active_recet = 1
  AND re.date_recet BETWEEN ? AND ?
  AND re.idopera <=> re.operavalid
ORDER BY re.date_recet, re.id_recette
";
$stmt = $mysqli->prepare($sqlBadRec);
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$badRec = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$updRec = 0;
$skipRec = 0;
$mtRec = 0;
$byChef = [];

echo "=== RECETTES à réparer ===\n";
foreach ($badRec as $row) {
    $key = $row['guser'] . '|' . $row['date_recet'];
    $chef = $recMap[$key] ?? null;
    if (!$chef || $chef['pct'] < $minConf) {
        $skipRec++;
        echo sprintf(
            "SKIP id=%d %s %s mt=%d caissier=%s (confiance=%s)\n",
            $row['id_recette'],
            $row['date_recet'],
            $row['garenom'],
            $row['montant_recet'],
            $row['caissier'],
            $chef ? $chef['pct'] . '%' : 'aucun'
        );
        continue;
    }
    $ra = $chef['ra'];
    echo sprintf(
        "FIX  id=%d %s %s %s %dF : idopera %d → %d (%s %.1f%%)\n",
        $row['id_recette'],
        $row['date_recet'],
        $row['garenom'],
        $row['type_recet'],
        $row['montant_recet'],
        $row['ra_actuel'],
        $ra,
        $chef['username'],
        $chef['pct']
    );
    if ($apply) {
        $u = $mysqli->prepare('UPDATE recette SET idopera = ? WHERE id_recette = ? AND idopera = ?');
        $id = (int) $row['id_recette'];
        $old = (int) $row['ra_actuel'];
        $u->bind_param('iii', $ra, $id, $old);
        $u->execute();
        $u->close();
    }
    $updRec++;
    $mtRec += (int) $row['montant_recet'];
    $ck = $chef['username'] . '|' . $ra . '|' . $row['garenom'];
    if (!isset($byChef[$ck])) {
        $byChef[$ck] = ['n' => 0, 'mt' => 0, 'user' => $chef['username'], 'ra' => $ra, 'gare' => $row['garenom']];
    }
    $byChef[$ck]['n']++;
    $byChef[$ck]['mt'] += (int) $row['montant_recet'];
}

// --- DEPENSES Courrier ---
$sqlBadDep = "
SELECT d.id_depense, d.date_depens, d.idop_dep AS ra_actuel, d.opevalid,
       d.montant_depens, d.type_depense, ul.guser, g.garenom, cu.username AS caissier
FROM depense d
JOIN attributions_role ar ON ar.roleattribut = d.idop_dep AND ar.userole IN (4,18)
JOIN user_login ul ON ar.idgestcompte = ul.uid_login
JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
LEFT JOIN gares g ON g.idengare = ul.guser
WHERE d.active_dep = 1
  AND d.date_depens BETWEEN ? AND ?
  AND d.idop_dep <=> d.opevalid
  AND d.type_depense = 'Courrier'
ORDER BY d.date_depens, d.id_depense
";
$stmt = $mysqli->prepare($sqlBadDep);
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$badDep = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$updDep = 0;
$skipDep = 0;
$mtDep = 0;

echo "\n=== DEPENSES Courrier à réparer ===\n";
foreach ($badDep as $row) {
    $key = $row['guser'] . '|' . $row['date_depens'];
    $chef = $depMap[$key] ?? ($recMap[$key] ?? null); // fallback recettes du jour
    if (!$chef || $chef['pct'] < $minConf) {
        $skipDep++;
        echo sprintf(
            "SKIP id=%d %s %s mt=%d (confiance=%s)\n",
            $row['id_depense'],
            $row['date_depens'],
            $row['garenom'],
            $row['montant_depens'],
            $chef ? $chef['pct'] . '%' : 'aucun'
        );
        continue;
    }
    $ra = $chef['ra'];
    echo sprintf(
        "FIX  id=%d %s %s %dF : idop_dep %d → %d (%s)\n",
        $row['id_depense'],
        $row['date_depens'],
        $row['garenom'],
        $row['montant_depens'],
        $row['ra_actuel'],
        $ra,
        $chef['username']
    );
    if ($apply) {
        $u = $mysqli->prepare('UPDATE depense SET idop_dep = ? WHERE id_depense = ? AND idop_dep = ?');
        $id = (int) $row['id_depense'];
        $old = (int) $row['ra_actuel'];
        $u->bind_param('iii', $ra, $id, $old);
        $u->execute();
        $u->close();
    }
    $updDep++;
    $mtDep += (int) $row['montant_depens'];
}

echo "\n=== SYNTHÈSE ===\n";
echo "Recettes FIX: {$updRec} lignes / {$mtRec} F | SKIP: {$skipRec}\n";
echo "Dépenses Courrier FIX: {$updDep} lignes / {$mtDep} F | SKIP: {$skipDep}\n";
echo "\nPar chef (recettes):\n";
usort($byChef, function ($a, $b) {
    return $b['mt'] <=> $a['mt'];
});
foreach ($byChef as $c) {
    echo sprintf("  %s RA=%d %s : %d lignes / %d F\n", $c['user'], $c['ra'], $c['gare'], $c['n'], $c['mt']);
}

echo "\nNOTE: dépenses Recu/Facture/Bon avec idop=caissier restent en revue manuelle (souvent saisie caisse légitime).\n";
if (!$apply) {
    echo "Relancer avec --apply pour écrire.\n";
}
