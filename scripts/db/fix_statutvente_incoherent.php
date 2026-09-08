#!/usr/bin/env php
<?php
/**
 * Corrige tickets validés chef mais encore ouverts compteur
 * (is_valdtick=1 ∧ statutvente=0 / is_valedtick=1 ∧ statvente=0).
 *
 * Usage:
 *   php scripts/db/fix_statutvente_incoherent.php --dry-run
 *   php scripts/db/fix_statutvente_incoherent.php --all
 *   php scripts/db/fix_statutvente_incoherent.php --roles=340,338,357
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
require __DIR__ . '/_bootstrap.php';

$m = db_script_connect($argv ?? []);
$dryRun = in_array('--dry-run', $argv, true);
$all = in_array('--all', $argv, true);
$roles = array();

foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--roles=') === 0) {
        $roles = array_filter(array_map('intval', explode(',', substr($arg, 8))));
    }
}

if (!$all && empty($roles)) {
    // Défaut historique (vendeurs déjà audités) — préférer --all en prod.
    $roles = array(340, 338, 357);
}

$scopeSql = '';
$scopeLabel = '';
if ($all) {
    $scopeSql = '1=1';
    $scopeLabel = 'TOUS les vendeurs (--all)';
} else {
    $in = implode(',', array_map('intval', $roles));
    $scopeSqlPass = "idcptuser IN ({$in})";
    $scopeSqlRet = "cptus IN ({$in})";
    $scopeLabel = "Vendeurs (roleattribut) : {$in}";
}

echo '=== Correction statutvente incohérent' . ($dryRun ? ' [dry-run]' : '') . " ===\n";
echo $scopeLabel . "\n\n";

function count_incoherent_pass($m, $where)
{
    return $m->query("
        SELECT COUNT(*) AS n, COALESCE(SUM(prixvente), 0) AS mt
        FROM passager
        WHERE {$where}
          AND is_valdtick = 1 AND statutvente = 0
          AND statut_code = 'vendu' AND prixvente IS NOT NULL
    ")->fetch_assoc();
}

function count_incoherent_ret($m, $where)
{
    return $m->query("
        SELECT COUNT(*) AS n, COALESCE(SUM(prixretour), 0) AS mt
        FROM non_passager
        WHERE {$where}
          AND is_valedtick = 1 AND statvente = 0
    ")->fetch_assoc();
}

if ($all) {
    $wherePass = $scopeSql;
    $whereRet = $scopeSql;
} else {
    $wherePass = $scopeSqlPass;
    $whereRet = $scopeSqlRet;
}

$avantA = count_incoherent_pass($m, $wherePass);
$avantR = count_incoherent_ret($m, $whereRet);
echo "Avant — aller : {$avantA['n']} tickets, " . number_format($avantA['mt'], 0, '', ' ') . " F\n";
echo "Avant — retour : {$avantR['n']} tickets, " . number_format($avantR['mt'], 0, '', ' ') . " F\n\n";

if (!$dryRun) {
    $m->query("
        UPDATE passager
        SET statutvente = 1
        WHERE {$wherePass}
          AND is_valdtick = 1
          AND statutvente = 0
          AND statut_code = 'vendu'
    ");
    $nA = $m->affected_rows;

    $m->query("
        UPDATE non_passager
        SET statvente = 1
        WHERE {$whereRet}
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

$apresA = count_incoherent_pass($m, $wherePass);
$apresR = count_incoherent_ret($m, $whereRet);
echo "Après — aller incohérents : {$apresA['n']}\n";
echo "Après — retour incohérents : {$apresR['n']}\n";
echo "\n=== Terminé ===\n";
