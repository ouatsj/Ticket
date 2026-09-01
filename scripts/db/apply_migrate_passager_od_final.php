<?php
define('BASEPATH', 'system/');
require dirname(__DIR__, 2) . '/application/config/database.php';

$db = $db['default'];
$mysqli = new mysqli($db['hostname'], $db['username'], $db['password'], $db['database']);
if ($mysqli->connect_error) {
    fwrite(STDERR, "Connexion DB échouée: {$mysqli->connect_error}\n");
    exit(1);
}

$cols = array('itinecode_vendu', 'lignetineraire_vendu', 'nom_dest_vente', 'code_gadest_vente');
echo "Avant migration:\n";
foreach ($cols as $col) {
    $r = $mysqli->query("SHOW COLUMNS FROM passager LIKE '" . $mysqli->real_escape_string($col) . "'");
    echo "  {$col}: " . ($r && $r->num_rows ? 'OUI' : 'NON') . "\n";
}

$sqlFile = __DIR__ . '/migrate_passager_od_final.sql';
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Fichier migration introuvable\n");
    exit(1);
}

foreach (array_filter(array_map('trim', explode(';', $sql))) as $q) {
    if ($q === '' || strpos($q, '--') === 0) {
        continue;
    }
    if (!$mysqli->query($q)) {
        fwrite(STDERR, "Erreur SQL: {$mysqli->error}\n");
        exit(1);
    }
}

echo "Migration appliquée.\n\nAprès migration:\n";
foreach ($cols as $col) {
    $r = $mysqli->query("SHOW COLUMNS FROM passager LIKE '" . $mysqli->real_escape_string($col) . "'");
    echo "  {$col}: " . ($r && $r->num_rows ? 'OUI' : 'NON') . "\n";
}
