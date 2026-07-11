<?php
/**
 * Réattribue les passagers saisis par Konate (suffixe k12) enregistrés à tort
 * sous le roleattribut Mamadousa (idcptuser=12) vers Konate (idcptuser=23).
 *
 * Usage:
 *   php scripts/db/fix_konate_passager_k12.php              # dry-run
 *   php scripts/db/fix_konate_passager_k12.php --apply      # applique
 *   php scripts/db/fix_konate_passager_k12.php --since=2026-07-08
 */
require __DIR__ . '/_bootstrap.php';

$apply = in_array('--apply', $argv, true);
$since = '2026-07-08';
foreach ($argv as $arg) {
    if (strpos($arg, '--since=') === 0) {
        $since = substr($arg, 8);
    }
}

$fromRa = 12;  // Mamadousa BOB1
$toRa = 23;    // Konate BOB1 (chef guichet)

$m = db_script_connect($argv);

$sinceEsc = $m->real_escape_string($since);
$sqlList = "
    SELECT code_passager, date_emis, code_ticket, prixvente, statutvente, statut_confirme, statut_code
    FROM passager
    WHERE idcptuser = {$fromRa}
      AND code_passager LIKE '%k12'
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

echo "=== fix_konate_passager_k12 ===\n";
echo 'Mode: ' . ($apply ? 'APPLY' : 'DRY-RUN') . "\n";
echo "Période depuis: {$since}\n";
echo "Réattribution: idcptuser {$fromRa} (Mamadousa) → {$toRa} (Konate)\n";
echo 'Lignes trouvées: ' . count($rows) . "\n\n";

if (empty($rows)) {
    echo "Rien à corriger.\n";
    exit(0);
}

foreach ($rows as $row) {
    echo sprintf(
        "  %s | %s | ticket=%s | prix=%s | ouvert=%s | confirm=%s\n",
        $row['code_passager'],
        $row['date_emis'],
        $row['code_ticket'],
        $row['prixvente'] === null ? 'NULL' : $row['prixvente'],
        $row['statutvente'],
        $row['statut_confirme'] === null ? '-' : $row['statut_confirme']
    );
}

if (!$apply) {
    echo "\nDry-run terminé. Relancez avec --apply pour appliquer.\n";
    exit(0);
}

$sqlUpdate = "
    UPDATE passager
    SET idcptuser = {$toRa}
    WHERE idcptuser = {$fromRa}
      AND code_passager LIKE '%k12'
      AND date_emis >= '{$sinceEsc}'
";

if (!$m->query($sqlUpdate)) {
    fwrite(STDERR, "Erreur UPDATE : {$m->error}\n");
    exit(1);
}

echo "\nLignes mises à jour: {$m->affected_rows}\n";

$check = $m->query("
    SELECT COUNT(*) AS n FROM passager
    WHERE idcptuser = {$fromRa}
      AND code_passager LIKE '%k12'
      AND date_emis >= '{$sinceEsc}'
")->fetch_assoc();

echo "Restant sur ra={$fromRa} (k12): " . (int) $check['n'] . "\n";
echo "Terminé.\n";
