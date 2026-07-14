#!/usr/bin/env php
<?php
/**
 * Réactive les comptes désactivés par erreur (cron avant init derniere_activite_at).
 * Usage: php scripts/db/recover_compte_arret_inactivite.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv ?? []);

require dirname(__DIR__, 2) . '/application/helpers/compte_arret_helper.php';
$hours = (int) compte_arret_hours_limit();

$sql = "UPDATE compte_user
    SET activer = 0
    WHERE activer = 1
    AND derniere_activite_at IS NOT NULL
    AND derniere_activite_at >= DATE_SUB(NOW(), INTERVAL {$hours} HOUR)";

if (!$mysqli->query($sql)) {
    fwrite(STDERR, 'Erreur: ' . $mysqli->error . "\n");
    exit(1);
}

echo date('Y-m-d H:i:s') . ' — comptes réactivés: ' . $mysqli->affected_rows . "\n";
