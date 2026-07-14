<?php
/**
 * Annule la régularisation incorrecte operavalid=23 sur les tickets saisis par KONATE.
 * Usage: php scripts/db/undo_repair_chefguichet_recettes.php [roleattribut] [YYYY-MM-DD]
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

$validator = isset($argv[1]) ? (int) $argv[1] : 23;
$day = isset($argv[2]) ? $argv[2] : date('Y-m-d');

$sql = "
UPDATE recette
SET operavalid = NULL,
    active_recet = 0,
    is_validerecet = 0,
    is_actifrecet = 0
WHERE date_recet = ?
  AND idopera = ?
  AND operavalid = ?
  AND type_recet = 'Ticket'
";

$stmt = $db->prepare($sql);
$stmt->bind_param('sii', $day, $validator, $validator);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

echo "Annulation régularisation — date: {$day}, idopera/operavalid: {$validator}\n";
echo "Tickets remis en saisie (active_recet=0, operavalid=NULL): {$affected}\n";
