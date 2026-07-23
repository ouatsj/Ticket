#!/usr/bin/env php
<?php
/**
 * Ajoute l'horodatage précis de création des dépôts pour le délai d'arrêt de 36 h.
 * Usage : php scripts/db/migrate_depot_created_at.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv ?? []);
$column = $mysqli->query("SHOW COLUMNS FROM depot LIKE 'createddepot_at'");

if (!$column || $column->num_rows === 0) {
    if (!$mysqli->query('ALTER TABLE depot ADD COLUMN createddepot_at DATETIME NULL AFTER datedepot')) {
        fwrite(STDERR, "ERREUR createddepot_at: {$mysqli->error}\n");
        exit(1);
    }
    echo "Ajouté: createddepot_at\n";
} else {
    echo "OK colonne existante: createddepot_at\n";
}

// L'heure historique est inconnue : retenir la fin de journée évite un blocage avant 36 h réelles.
if (!$mysqli->query(
    "UPDATE depot
     SET createddepot_at = TIMESTAMP(datedepot) + INTERVAL 1 DAY - INTERVAL 1 SECOND
     WHERE createddepot_at IS NULL AND datedepot IS NOT NULL"
)) {
    fwrite(STDERR, "ERREUR reprise historique: {$mysqli->error}\n");
    exit(1);
}

echo 'Dépôts historiques horodatés: ' . (int) $mysqli->affected_rows . "\n";
echo "Migration terminée.\n";
