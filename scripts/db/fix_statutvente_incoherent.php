#!/usr/bin/env php
<?php
/**
 * Corrige tickets validés chef (is_valdtick=1) mais encore ouverts compteur (statutvente=0).
 *
 * Usage:
 *   php scripts/db/fix_statutvente_incoherent.php
 *   php scripts/db/fix_statutvente_incoherent.php --dry-run
 *   php scripts/db/fix_statutvente_incoherent.php --roles=340,338,357
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
require __DIR__ . '/_bootstrap.php';

$m = db_script_connect($argv ?? []);
$dryRun = in_array('--dry-run', $argv, true);
$roles = [340, 338, 357];

foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--roles=') === 0) {
        $roles = array_filter(array_map('intval', explode(',', substr($arg, 8))));
    }
}

if (empty($roles)) {
    fwrite(STDERR, "Aucun roleattribut.\n");
    exit(1);
}

$in = implode(',', array_map('intval', $roles));
echo '=== Correction statutvente incohérent' . ($dryRun ? ' [dry-run]' : '') . " ===\n";
echo "Vendeurs (roleattribut) : {$in}\n\n";

function count_incoherent($m, $in)
{
    $aller = $m->query("
        SELECT COUNT(*) AS n, COALESCE(SUM(prixvente), 0) AS mt
        FROM passager
        WHERE idcptuser IN ({$in})
          AND is_valdtick = 1 AND statutvente = 0
          AND statut_code = 'vendu' AND prixvente IS NOT NULL
    ")->fetch_assoc();

    $retour = $m->query("
        SELECT COUNT(*) AS n, COALESCE(SUM(prixretour), 0) AS mt
        FROM non_passager
        WHERE cptus IN ({$in})
          AND is_valedtick = 1 AND statvente = 0
    ")->fetch_assoc();

    return [$aller, $retour];
}

list($avantA, $avantR) = count_incoherent($m, $in);
echo "Avant — aller : {$avantA['n']} tickets, " . number_format($avantA['mt'], 0, '', ' ') . " F\n";
echo "Avant — retour : {$avantR['n']} tickets, " . number_format($avantR['mt'], 0, '', ' ') . " F\n\n";

if (!$dryRun) {
    $m->query("
        UPDATE passager
        SET statutvente = 1
        WHERE idcptuser IN ({$in})
          AND is_valdtick = 1
          AND statutvente = 0
          AND statut_code = 'vendu'
    ");
    $nA = $m->affected_rows;

    $m->query("
        UPDATE non_passager
        SET statvente = 1
        WHERE cptus IN ({$in})
          AND is_valedtick = 1
          AND statvente = 0
    ");
    $nR = $m->affected_rows;

    echo "Corrigé — passager : {$nA} lignes\n";
    echo "Corrigé — non_passager : {$nR} lignes\n\n";
} else {
    echo "[dry-run] UPDATE passager SET statutvente=1 WHERE is_valdtick=1 AND statutvente=0\n";
    echo "[dry-run] UPDATE non_passager SET statvente=1 WHERE is_valedtick=1 AND statvente=0\n\n";
}

list($apresA, $apresR) = count_incoherent($m, $in);
echo "Après — aller incohérents : {$apresA['n']}\n";
echo "Après — retour incohérents : {$apresR['n']}\n";

// Compteur guichet (SOLDE) pour les 3 vendeurs
echo "\n--- Compteur guichet (statutvente=0) ---\n";
$r = $m->query("
    SELECT ar.roleattribut, cu.username, g.garenom,
        COALESCE((
            SELECT SUM(p.prixvente) FROM passager p
            WHERE p.idcptuser = ar.roleattribut AND p.statutvente = 0
              AND p.statut_code = 'vendu' AND p.prixvente IS NOT NULL
              AND p.datep_create <= CURDATE()
        ), 0) AS aller,
        COALESCE((
            SELECT SUM(np.prixretour) FROM non_passager np
            WHERE np.cptus = ar.roleattribut AND np.statvente = 0
              AND np.datevente <= CURDATE()
        ), 0) AS retour
    FROM attributions_role ar
    JOIN user_login ul ON ul.uid_login = ar.idgestcompte
    JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
    LEFT JOIN gares g ON g.idengare = ul.guser
    WHERE ar.roleattribut IN ({$in})
    GROUP BY ar.roleattribut, cu.username, g.garenom
");
while ($row = $r->fetch_assoc()) {
    $solde = (float) $row['aller'] + (float) $row['retour'];
    echo sprintf(
        "  %s (%s) role %d — SOLDE : %s FCFA\n",
        $row['username'],
        $row['garenom'] ?? '-',
        $row['roleattribut'],
        number_format($solde, 0, '', ' ')
    );
}

echo "\n=== Terminé ===\n";
