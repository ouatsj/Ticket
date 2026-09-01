<?php
/**
 * Vérifie BOB1-NIA4 : pas de faux direct via BOB1-BAN1, jambes transit présentes.
 * Usage : php scripts/tests/check_bob1_nia4_od.php
 */
define('BASEPATH', true);
require dirname(__DIR__, 2) . '/application/config/database.php';
$c = $db['default'];
$mysqli = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database'], (int) $c['port']);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connect: {$mysqli->connect_error}\n");
    exit(1);
}
$date = date('Y-m-d');
$axe = 'BOB1-NIA4';

$r = $mysqli->query(
    "SELECT COUNT(*) AS n FROM programme pr
     JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
     WHERE lh.ligne_id = '{$mysqli->real_escape_string($axe)}'
     AND pr.date_progr = '{$mysqli->real_escape_string($date)}'
     AND pr.statut_prog = 'actif' AND pr.actif_prog = 0"
);
$row = $r->fetch_assoc();
echo "direct_{$axe}_today={$row['n']}\n";

$r2 = $mysqli->query(
    "SELECT lg.ident_ligne, v.nom_ville FROM lignes lg
     JOIN gare_dest gd ON gd.code_gadest = lg.gadest_lg
     JOIN ville v ON v.id_ville = gd.id_villega
     WHERE lg.gaexp_lg = 'BOB1' AND gd.id_compaga = 5000
     AND gd.id_villega = (
       SELECT gd2.id_villega FROM lignes lg2
       JOIN gare_dest gd2 ON gd2.code_gadest = lg2.gadest_lg
       WHERE lg2.ident_ligne = '{$mysqli->real_escape_string($axe)}'
     )"
);
echo "compatible_od:\n";
while ($x = $r2->fetch_assoc()) {
    echo "  {$x['ident_ligne']} ({$x['nom_ville']})\n";
}

$r3 = $mysqli->query(
    "SELECT ordre_etape, ident_ligne_etape FROM itineraire_etapes
     WHERE id_lignes = '{$mysqli->real_escape_string($axe)}' AND actif_etape = 1
     ORDER BY ordre_etape"
);
echo "transit_etapes:\n";
while ($x = $r3->fetch_assoc()) {
    echo "  {$x['ordre_etape']}. {$x['ident_ligne_etape']}\n";
}

$r4 = $mysqli->query("SELECT id_villega FROM gare_dest WHERE code_gadest = 'NIA4'");
$x4 = $r4->fetch_assoc();
echo "NIA4_id_villega={$x4['id_villega']} (attendu 30=NIANGOLOKO)\n";
