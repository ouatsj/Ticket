#!/usr/bin/env php
<?php
/**
 * Jeton de session unique par compte (invalidation à la déconnexion / désactivation).
 * Usage: php scripts/db/migrate_session_token.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv ?? []);

$res = $mysqli->query("SHOW COLUMNS FROM compte_user LIKE 'session_token'");
if ($res && $res->num_rows > 0) {
    echo "OK colonne existante: session_token\n";
    echo "Migration terminée.\n";
    exit(0);
}

$sql = "ALTER TABLE compte_user ADD COLUMN session_token VARCHAR(64) NULL DEFAULT NULL AFTER is_conect";
if (!$mysqli->query($sql)) {
    fwrite(STDERR, "ERREUR session_token: {$mysqli->error}\n");
    exit(1);
}

echo "Ajouté: session_token\n";
echo "Migration terminée.\n";
