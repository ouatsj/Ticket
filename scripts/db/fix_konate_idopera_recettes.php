<?php
/**
 * Corrige idopera : recettes saisies par KONATE mais enregistrées à tort sous Mamadousa.
 * Usage: php scripts/db/fix_konate_idopera_recettes.php [YYYY-MM-DD] [idopera_cible]
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require __DIR__ . '/../../application/config/database.php';
$dbCfg = $db['default'];
$db = new mysqli($dbCfg['hostname'], $dbCfg['username'], $dbCfg['password'], $dbCfg['database']);
if ($db->connect_error) {
    fwrite(STDERR, 'Connexion DB: ' . $db->connect_error . PHP_EOL);
    exit(1);
}

$day = isset($argv[1]) ? $argv[1] : date('Y-m-d');
$targetOp = isset($argv[2]) ? (int) $argv[2] : 23;
$wrongOp = 12; // Mamadousa BOB1

$nomPatterns = [
    'DAHANI Raissa',
    'SOMBIE Ami',
    'SOMBIE Oumar',
    'GUIRA Lassina',
];

$ids = [];
foreach ($nomPatterns as $nom) {
    $stmt = $db->prepare("
        SELECT id_recette FROM recette
        WHERE date_recet = ? AND idopera = ? AND nom = ?
    ");
    $stmt->bind_param('sis', $day, $wrongOp, $nom);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $ids[] = (int) $row['id_recette'];
    }
    $stmt->close();
}

if (empty($ids)) {
    echo "Aucune recette à corriger pour {$day}.\n";
    exit(0);
}

$idList = implode(',', $ids);
$sql = "
UPDATE recette
SET idopera = ?,
    operavalid = NULL,
    active_recet = 0,
    is_validerecet = 0,
    is_actifrecet = 0
WHERE id_recette IN ({$idList})
  AND date_recet = ?
  AND idopera = ?
";
$stmt = $db->prepare($sql);
$stmt->bind_param('isi', $targetOp, $day, $wrongOp);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

echo "Date: {$day}\n";
echo "idopera corrigé: {$wrongOp} (Mamadousa) → {$targetOp} (KONATE BOB1)\n";
echo "IDs: {$idList}\n";
echo "Lignes mises à jour: {$affected}\n";
