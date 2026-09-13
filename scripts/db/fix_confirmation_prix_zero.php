<?php
/**
 * Remet prixvente=0 sur toutes les confirmations (règle : toujours gratuit / non facturable).
 *
 * Aperçu : php scripts/db/fix_confirmation_prix_zero.php --allow-remote
 * Appliquer : php scripts/db/fix_confirmation_prix_zero.php --allow-remote --apply
 */

require __DIR__ . '/_bootstrap.php';

$apply = in_array('--apply', $argv, true);
/** @var mysqli $db */
$db = db_script_connect($argv);

$statuts = "'confirm','catconfirm','confirmcarte'";
$where = "statut_confirme IN ({$statuts}) AND prixvente IS NOT NULL AND prixvente > 0";

$aggRes = $db->query("SELECT COUNT(*) AS n, COALESCE(SUM(prixvente),0) AS total FROM passager WHERE {$where}");
$agg = $aggRes ? $aggRes->fetch_assoc() : array('n' => 0, 'total' => 0);
$n = isset($agg['n']) ? (int) $agg['n'] : 0;
$total = isset($agg['total']) ? $agg['total'] : 0;
echo "Confirmations avec prix > 0 : {$n} (somme {$total} F)\n";

$listRes = $db->query(
    "SELECT code_passager, code_ticket, prixvente, statut_confirme, datep_create
     FROM passager WHERE {$where}
     ORDER BY datep_create DESC, code_passager DESC
     LIMIT 50"
);
while ($listRes && ($r = $listRes->fetch_assoc())) {
    echo sprintf(
        "  %s | %s | %s F | %s | %s\n",
        $r['code_passager'],
        $r['code_ticket'],
        $r['prixvente'],
        $r['statut_confirme'],
        $r['datep_create']
    );
}

if (!$apply) {
    echo "Dry-run. Relancer avec --apply pour remettre à 0 F.\n";
    exit(0);
}

if (!$db->query("UPDATE passager SET prixvente = 0 WHERE {$where}")) {
    fwrite(STDERR, 'UPDATE échoué: ' . $db->error . "\n");
    exit(1);
}
echo 'Lignes mises à jour : ' . $db->affected_rows . "\n";
exit(0);
