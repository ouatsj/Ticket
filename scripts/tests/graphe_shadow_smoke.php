#!/usr/bin/env php
<?php
/**
 * Smoke-test Phase 1 graphe (shadow) — CLI.
 *
 * Usage:
 *   php scripts/tests/graphe_shadow_smoke.php --allow-remote
 *   php scripts/tests/graphe_shadow_smoke.php --allow-remote --axe=BAN3-BOU20 --date=2026-07-29
 *
 * Compare compositions déclaratives vs chemins graphe (départs du jour).
 * N'écrit pas en base ; peut écrire dans application/logs/graphe_shadow-*.log via la lib
 * si appelé via HTTP. Ici on affiche un résumé stdout.
 */

$root = dirname(__DIR__, 2);
require $root . '/scripts/db/_bootstrap.php';

$mysqli = db_script_connect($argv);
$mysqli->set_charset('utf8');

$axe = null;
$date = date('Y-m-d');
$limit = 15;
foreach ($argv as $arg) {
    if (strpos($arg, '--axe=') === 0) {
        $axe = substr($arg, 6);
    }
    if (strpos($arg, '--date=') === 0) {
        $date = substr($arg, 7);
    }
    if (strpos($arg, '--limit=') === 0) {
        $limit = (int) substr($arg, 8);
    }
}

$ekeyRes = $mysqli->query('SELECT ekey FROM entreprise LIMIT 1');
if (!$ekeyRes || !($er = $ekeyRes->fetch_assoc())) {
    fwrite(STDERR, "Pas d'entreprise\n");
    exit(1);
}
$ekey = $er['ekey'];

function code_prefix($code)
{
    $code = strtoupper(trim((string) $code));
    $p = preg_replace('/[0-9].*$/', '', $code);
    return ($p !== null && $p !== '') ? $p : $code;
}

function heure_to_minutes($h)
{
    $parts = preg_split('/[:hH]/', trim((string) $h));
    if (!$parts) {
        return null;
    }
    return ((int) $parts[0]) * 60 + (isset($parts[1]) ? (int) $parts[1] : 0);
}

function make_node($villeId, $code)
{
    if ((int) $villeId > 0) {
        return 'v:' . (int) $villeId;
    }
    return 'p:' . code_prefix($code);
}

// --- Charger nœuds ---
$exp_nodes = array();
$r = $mysqli->query(
    "SELECT ge.code_gaexp, ge.id_villegd
     FROM gare_exp ge
     JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
     JOIN entreprise e ON c.id_entrep = e.id_entreprise
     WHERE e.ekey = '" . $mysqli->real_escape_string($ekey) . "'"
);
while ($row = $r->fetch_assoc()) {
    $exp_nodes[$row['code_gaexp']] = make_node($row['id_villegd'], $row['code_gaexp']);
}
$dest_nodes = array();
$r = $mysqli->query(
    "SELECT ga.code_gadest, ga.id_villega
     FROM gare_dest ga
     JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
     JOIN entreprise e ON c.id_entrep = e.id_entreprise
     WHERE e.ekey = '" . $mysqli->real_escape_string($ekey) . "'"
);
while ($row = $r->fetch_assoc()) {
    $dest_nodes[$row['code_gadest']] = make_node($row['id_villega'], $row['code_gadest']);
}

// --- Arêtes avec départs ---
$dateEsc = $mysqli->real_escape_string($date);
$deps = $mysqli->query(
    "SELECT lh.ligne_id, h.heure, lg.gaexp_lg, lg.gadest_lg, lg.nom_ligne
     FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
     JOIN heures h ON lh.heure_identif = h.id_heure
     JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
     JOIN gare_exp ge ON lg.gaexp_lg = ge.code_gaexp
     JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
     JOIN entreprise e ON c.id_entrep = e.id_entreprise
     WHERE e.ekey = '" . $mysqli->real_escape_string($ekey) . "'
     AND pr.date_progr = '{$dateEsc}'
     AND pr.statut_prog = 'actif'
     AND pr.actif_prog = 0
     AND lh.actif_lh = 1
     AND h.h_active = 1
     ORDER BY h.heure ASC"
);
$edges = array();
while ($d = $deps->fetch_assoc()) {
    $lid = $d['ligne_id'];
    if (!isset($edges[$lid])) {
        $edges[$lid] = array(
            'gaexp' => $d['gaexp_lg'],
            'gadest' => $d['gadest_lg'],
            'from' => isset($exp_nodes[$d['gaexp_lg']]) ? $exp_nodes[$d['gaexp_lg']] : make_node(0, $d['gaexp_lg']),
            'to' => isset($dest_nodes[$d['gadest_lg']]) ? $dest_nodes[$d['gadest_lg']] : make_node(0, $d['gadest_lg']),
            'departs' => array(),
        );
    }
    $edges[$lid]['departs'][] = heure_to_minutes($d['heure']);
}
$adj = array();
foreach ($edges as $lid => $e) {
    if ($e['from'] === $e['to']) {
        continue;
    }
    if (!isset($adj[$e['from']])) {
        $adj[$e['from']] = array();
    }
    $adj[$e['from']][] = $lid;
}

function pick_dep($mins, $last, $marge)
{
    foreach ($mins as $m) {
        if ($m === null) {
            continue;
        }
        if ($last === null || $m >= $last + $marge) {
            return $m;
        }
    }
    return null;
}

function bfs_best($edges, $adj, $start, $goalDestCode, $gaexpOd, $gadestOd, $marge = 30, $maxJ = 4)
{
    $found = array();
    $q = array(array('node' => $start, 'path' => array(), 'last' => null, 'used' => array()));
    while ($q) {
        $st = array_shift($q);
        if (count($st['path']) >= $maxJ) {
            continue;
        }
        if (empty($adj[$st['node']])) {
            continue;
        }
        $n = 0;
        foreach ($adj[$st['node']] as $lid) {
            if ($n++ > 40) {
                break;
            }
            if (!empty($st['used'][$lid])) {
                continue;
            }
            $e = $edges[$lid];
            if (count($st['path']) === 0 && $e['gaexp'] !== $gaexpOd) {
                continue;
            }
            $dep = pick_dep($e['departs'], $st['last'], $marge);
            if ($dep === null) {
                continue;
            }
            $path = $st['path'];
            $path[] = $lid;
            $used = $st['used'];
            $used[$lid] = true;
            if ($e['gadest'] === $gadestOd) {
                if (count($path) >= 2) {
                    $found[] = $path;
                }
                continue;
            }
            $q[] = array('node' => $e['to'], 'path' => $path, 'last' => $dep, 'used' => $used);
        }
    }
    usort($found, function ($a, $b) {
        return count($a) - count($b);
    });
    return $found;
}

// --- OD à tester ---
$axes = array();
if ($axe) {
    $axes[] = $axe;
} else {
    $r = $mysqli->query(
        "SELECT et.id_lignes AS axe, GROUP_CONCAT(et.ident_ligne_etape ORDER BY et.ordre_etape SEPARATOR '>') AS decl
         FROM itineraire_etapes et
         WHERE et.actif_etape = 1
         GROUP BY et.id_lignes
         HAVING COUNT(*) BETWEEN 2 AND 4
         ORDER BY et.id_lignes
         LIMIT " . (int) $limit
    );
    while ($row = $r->fetch_assoc()) {
        $axes[] = $row['axe'];
    }
}

echo "ekey={$ekey} date={$date} aretes=" . count($edges) . " axes=" . count($axes) . "\n";
echo str_pad('axe', 18) . " | " . str_pad('declaratif', 40) . " | graphe_best | match\n";
echo str_repeat('-', 100) . "\n";

$match = 0;
$grapheOk = 0;
$declOnly = 0;
$bothEmpty = 0;

foreach ($axes as $ax) {
    $parts = explode('-', $ax, 2);
    if (count($parts) < 2) {
        continue;
    }
    list($gaexpOd, $gadestOd) = $parts;
    $start = isset($exp_nodes[$gaexpOd]) ? $exp_nodes[$gaexpOd] : make_node(0, $gaexpOd);

    $r = $mysqli->query(
        "SELECT GROUP_CONCAT(ident_ligne_etape ORDER BY ordre_etape SEPARATOR '>') AS decl
         FROM itineraire_etapes WHERE id_lignes='" . $mysqli->real_escape_string($ax) . "' AND actif_etape=1"
    );
    $decl = ($row = $r->fetch_assoc()) ? (string) $row['decl'] : '';

    $paths = bfs_best($edges, $adj, $start, $gadestOd, $gaexpOd, $gadestOd);
    $best = !empty($paths) ? implode('>', $paths[0]) : '';
    if ($best !== '') {
        $grapheOk++;
    }
    if ($decl !== '' && $best === '') {
        $declOnly++;
    }
    if ($decl === '' && $best === '') {
        $bothEmpty++;
    }
    $isMatch = ($decl !== '' && $decl === $best);
    if ($isMatch) {
        $match++;
    }
    $flag = $isMatch ? 'YES' : (($best !== '' && $decl !== '') ? 'DIFF' : (($best !== '') ? 'GRAPH' : 'NONE'));
    echo str_pad($ax, 18) . ' | ' . str_pad($decl, 40) . ' | ' . str_pad($best, 40) . " | {$flag}\n";
}

echo str_repeat('-', 100) . "\n";
echo "resume: match_exact={$match} graphe_ok={$grapheOk} decl_sans_graphe={$declOnly} vides={$bothEmpty}\n";
echo "Note: DIFF/NONE sur un jour sans départs sur les jambes est attendu (graphe adaptatif).\n";
