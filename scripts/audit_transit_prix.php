<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
require __DIR__ . '/../application/config/database.php';
$c = $db['default'];
$mysqli = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($mysqli->connect_error) {
    fwrite(STDERR, $mysqli->connect_error . "\n");
    exit(1);
}

$sql = "SELECT p.code_passager, p.prixvente, tf.prix AS tarif_prix, lh.id_ligneheure, lg.nom_ligne, p.datep_create
FROM passager p
JOIN tamponcode tc ON tc.tamponcod = p.code_passager
JOIN tamponcodetr tct ON tct.codtampon = tc.tamponcodtr
JOIN programme pr ON p.code_pro = pr.code_progr
JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
JOIN tarifs t ON pr.typetarif = t.id_tarifs
LEFT JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
WHERE tc.tamponcodtr IS NOT NULL
AND p.prixvente IS NOT NULL
ORDER BY p.createpas_at DESC
LIMIT 15";

$res = $mysqli->query($sql);
echo "Transit tickets (linked via tamponcodetr):\n";
while ($row = $res->fetch_assoc()) {
    $match = ((float)$row['prixvente'] === (float)$row['tarif_prix']) ? 'OK' : 'MISMATCH';
    echo sprintf("%s | pv=%s tarif=%s | %s | %s\n", $match, $row['prixvente'], $row['tarif_prix'] ?? 'NULL', $row['nom_ligne'], $row['datep_create']);
}

$sql2 = "SELECT tc.tamponcodtr, COUNT(*) AS n, GROUP_CONCAT(DISTINCT p.prixvente) AS prixventes
FROM passager p
JOIN tamponcode tc ON tc.tamponcod = p.code_passager
WHERE tc.tamponcodtr IS NOT NULL AND tc.tamponcodtr != ''
GROUP BY tc.tamponcodtr
HAVING n >= 2
ORDER BY MAX(p.createpas_at) DESC
LIMIT 5";
$res2 = $mysqli->query($sql2);
echo "\nRecent multi-segment transit sales:\n";
while ($row = $res2->fetch_assoc()) {
    echo "group {$row['tamponcodtr']}: {$row['n']} segments, prixventes=[{$row['prixventes']}]\n";
    $g = $mysqli->real_escape_string($row['tamponcodtr']);
    $r3 = $mysqli->query("SELECT p.code_passager, p.prixvente, lg.nom_ligne, lh.id_ligneheure, pr.typetarif
        FROM passager p
        JOIN tamponcode tc ON tc.tamponcod = p.code_passager
        JOIN programme pr ON p.code_pro = pr.code_progr
        JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
        JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
        WHERE tc.tamponcodtr = '$g' ORDER BY p.createpas_at");
    while ($seg = $r3->fetch_assoc()) {
        echo "  - {$seg['code_passager']} | pv={$seg['prixvente']} | lh={$seg['id_ligneheure']} | tf={$seg['typetarif']} | {$seg['nom_ligne']}\n";
    }
}
