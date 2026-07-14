<?php
/**
 * Réattribue les passagers saisis par Mamadousa (suffixe BOB1M5 / 1M5)
 * enregistrés à tort sous le roleattribut Hie Abi (idcptuser=5)
 * vers Mamadousa (idcptuser=12).
 *
 * Usage:
 *   php scripts/db/fix_mamadousa_passager_1M5.php              # dry-run
 *   php scripts/db/fix_mamadousa_passager_1M5.php --apply      # applique
 *   php scripts/db/fix_mamadousa_passager_1M5.php --since=2026-07-10
 */
require __DIR__ . '/_bootstrap.php';

$apply = in_array('--apply', $argv, true);
$since = '2026-07-10';
foreach ($argv as $arg) {
    if (strpos($arg, '--since=') === 0) {
        $since = substr($arg, 8);
    }
}

$fromRa = 5;   // Hie Abi BOB1
$toRa = 12;    // Mamadousa BOB1

$m = db_script_connect($argv);

$sinceEsc = $m->real_escape_string($since);
$sqlList = "
    SELECT code_passager, date_emis, code_ticket, prixvente, statutvente, statut_confirme, statut_code
    FROM passager
    WHERE idcptuser = {$fromRa}
      AND code_passager LIKE '%BOB1M5'
      AND date_emis >= '{$sinceEsc}'
    ORDER BY date_emis
";

$res = $m->query($sqlList);
if ($res === false) {
    fwrite(STDERR, "Erreur SELECT : {$m->error}\n");
    exit(1);
}

$rows = array();
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}

echo "=== fix_mamadousa_passager_1M5 ===\n";
echo 'Mode: ' . ($apply ? 'APPLY' : 'DRY-RUN') . "\n";
echo "Période depuis: {$since}\n";
echo "Réattribution: idcptuser {$fromRa} (Hie Abi) → {$toRa} (Mamadousa)\n";
echo 'Lignes trouvées: ' . count($rows) . "\n\n";

if (empty($rows)) {
    echo "Rien à corriger.\n";
    exit(0);
}

$sumPrix = 0;
foreach ($rows as $row) {
    if ($row['prixvente'] !== null) {
        $sumPrix += (float) $row['prixvente'];
    }
    echo sprintf(
        "  %s | %s | ticket=%s | prix=%s | ouvert=%s\n",
        $row['code_passager'],
        $row['date_emis'],
        $row['code_ticket'],
        $row['prixvente'] === null ? 'NULL' : $row['prixvente'],
        $row['statutvente']
    );
}

echo "\nMontant total: " . number_format($sumPrix, 0, ',', ' ') . " F\n";

if (!$apply) {
    echo "\nDry-run terminé. Relancez avec --apply pour appliquer.\n";
    exit(0);
}

$sqlUpdate = "
    UPDATE passager
    SET idcptuser = {$toRa}
    WHERE idcptuser = {$fromRa}
      AND code_passager LIKE '%BOB1M5'
      AND date_emis >= '{$sinceEsc}'
";

if (!$m->query($sqlUpdate)) {
    fwrite(STDERR, "Erreur UPDATE : {$m->error}\n");
    exit(1);
}

echo "\nLignes mises à jour: {$m->affected_rows}\n";

$checkFrom = $m->query("
    SELECT COUNT(*) AS n FROM passager
    WHERE idcptuser = {$fromRa}
      AND code_passager LIKE '%BOB1M5'
      AND date_emis >= '{$sinceEsc}'
")->fetch_assoc();

$checkTo = $m->query("
    SELECT COUNT(*) AS n, SUM(statutvente=0) AS ouv, SUM(prixvente) AS s
    FROM passager
    WHERE idcptuser = {$toRa}
      AND code_passager LIKE '%BOB1M5'
      AND date_emis >= '{$sinceEsc}'
")->fetch_assoc();

echo "Restant sur ra={$fromRa} (BOB1M5): " . (int) $checkFrom['n'] . "\n";
echo "Sur ra={$toRa} (BOB1M5): " . (int) $checkTo['n'] . " lignes, " . (int) $checkTo['ouv'] . " ouvertes, " . number_format((float) $checkTo['s'], 0, ',', ' ') . " F\n";
echo "Terminé.\n";
