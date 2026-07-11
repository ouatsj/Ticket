#!/usr/bin/env php
<?php
/**
 * Réactive tous les comptes guichet et rafraîchit l'activité.
 * À utiliser pendant la refonte des règles arrêt / blocage par rôle.
 *
 * Usage: php scripts/db/liberer_comptes_temporaire.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv ?? []);

echo "=== Rétablissement usage comptes (avant règles arrêt) ===\n\n";

$before = $mysqli->query(
    'SELECT SUM(activer = 1) AS desactives, SUM(activer = 0) AS actifs, COUNT(*) AS total FROM compte_user'
)->fetch_assoc();
echo "Avant — actifs: {$before['actifs']}, désactivés: {$before['desactives']}, total: {$before['total']}\n";

$mysqli->query('UPDATE compte_user SET activer = 0 WHERE activer = 1');
$reactives = $mysqli->affected_rows;
echo "Comptes réactivés (activer 1 → 0): {$reactives}\n";

$mysqli->query('UPDATE compte_user SET derniere_activite_at = NOW()');
$refresh = $mysqli->affected_rows;
echo "Dernière activité rafraîchie pour tous: {$refresh}\n";

$after = $mysqli->query(
    'SELECT SUM(activer = 1) AS desactives, SUM(activer = 0) AS actifs, COUNT(*) AS total FROM compte_user'
)->fetch_assoc();
echo "\nAprès — actifs: {$after['actifs']}, désactivés: {$after['desactives']}, total: {$after['total']}\n";

echo "\nRappel: règles arrêt désactivées — application/config/compte_arret.php (FALSE)\n";
echo "Cron inactivité: retirer de crontab si encore présent.\n";
echo "Terminé.\n";
