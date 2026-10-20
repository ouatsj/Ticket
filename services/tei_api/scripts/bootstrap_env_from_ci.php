<?php
/**
 * Génère services/tei_api/.env à partir de application/config/database.php
 * sans afficher le mot de passe. Usage :
 *   php7.2 scripts/bootstrap_env_from_ci.php
 */
define('BASEPATH', 1);
define('ENVIRONMENT', 'development');

$root = dirname(__DIR__, 3);
$dbFile = $root . '/application/config/database.php';
$out = dirname(__DIR__) . '/.env';

if (!is_readable($dbFile)) {
    fwrite(STDERR, "database.php introuvable\n");
    exit(1);
}
if (is_file($out)) {
    fwrite(STDERR, ".env existe déjà — non écrasé\n");
    exit(0);
}

$db = array();
require $dbFile;
$c = $db['default'];

$access = bin2hex(random_bytes(32));
$refresh = bin2hex(random_bytes(32));

// Node résout souvent « localhost » en ::1 ; MySQL n’écoute que 127.0.0.1 en prod.
$dbHost = $c['hostname'];
if ($dbHost === 'localhost' || $dbHost === '::1') {
    $dbHost = '127.0.0.1';
}

$lines = array(
    'PORT=8091',
    'TZ=Europe/Berlin',
    'DB_HOST=' . $dbHost,
    'DB_PORT=' . (isset($c['port']) && $c['port'] !== '' ? $c['port'] : '3306'),
    'DB_USER=' . $c['username'],
    'DB_PASSWORD=' . $c['password'],
    'DB_NAME=' . $c['database'],
    'JWT_ACCESS_SECRET=' . $access,
    'JWT_REFRESH_SECRET=' . $refresh,
    'ACCESS_TTL_SEC=900',
    'REFRESH_TTL_SEC=2592000',
    'CORS_ORIGINS=*',
);

file_put_contents($out, implode("\n", $lines) . "\n");
chmod($out, 0600);
echo "OK .env écrit (secrets non affichés)\n";
