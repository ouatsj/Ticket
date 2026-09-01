<?php
define('BASEPATH', true);
require dirname(__DIR__, 2) . '/application/config/database.php';
$c = $db['default'];
$mysqli = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database'], (int) $c['port']);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB: {$mysqli->connect_error}\n");
    exit(1);
}

echo "=== gare_dest NIA4 / BAN1 ===\n";
$r = $mysqli->query("SELECT code_gadest, id_villega, nom_gadest FROM gare_dest WHERE code_gadest IN ('NIA4','BAN1')");
while ($x = $r->fetch_assoc()) {
    echo "{$x['code_gadest']} ville={$x['id_villega']} ({$x['nom_gadest']})\n";
}

echo "\n=== quartiers via getqart1 logic (ga.code_gadest) ===\n";
foreach (array('NIA4', 'BAN1') as $g) {
    $gEsc = $mysqli->real_escape_string($g);
    $r2 = $mysqli->query(
        "SELECT q.nom_quartier, v.nom_ville, v.id_ville
         FROM quartier q
         JOIN ville v ON q.id_ville_qua = v.id_ville
         JOIN gare_dest ga ON ga.id_villega = v.id_ville
         WHERE ga.code_gadest = '{$gEsc}'
         ORDER BY q.nom_quartier"
    );
    echo "-- {$g} --\n";
    while ($x = $r2->fetch_assoc()) {
        echo "  {$x['nom_quartier']} ({$x['nom_ville']})\n";
    }
}

echo "\n=== getqartr1 Marche filter (BOB32/BAN1/NIA4 branch) ===\n";
foreach (array('BAN1', 'NIA4') as $g) {
    $gEsc = $mysqli->real_escape_string($g);
    $r3 = $mysqli->query(
        "SELECT q.nom_quartier FROM quartier q
         JOIN ville v ON q.id_ville_qua = v.id_ville
         JOIN gare_dest ga ON ga.id_villega = v.id_ville
         WHERE ga.code_gadest = '{$gEsc}' AND q.nom_quartier = 'Marche'"
    );
    echo "-- verifquartr/{$g} filter Marche: ";
    $names = array();
    while ($x = $r3->fetch_assoc()) {
        $names[] = $x['nom_quartier'];
    }
    echo (empty($names) ? '(aucun)' : implode(', ', $names)) . "\n";
}
