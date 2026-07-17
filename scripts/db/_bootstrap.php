<?php
/**
 * Bootstrap partagé pour les scripts CLI scripts/db/*.
 * - Une seule connexion mysqli réutilisée
 * - Bloque les hôtes distants par défaut (quota Hostinger 500 connexions/h)
 * - Passer --allow-remote pour forcer l'accès distant
 */

if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__, 2) . '/system/');
}
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');
}

/** Hôtes distants : chaque connexion compte dans max_connections_per_hour (500/h chez Hostinger). */
function db_script_blocked_hosts()
{
    return array('45.13.253.119', 'rakietabus.com', '89.117.53.33');
}

/**
 * @param array $argv Arguments CLI (ex. $argv)
 * @return mysqli
 */
function db_script_connect(array $argv = array())
{
    static $mysqli = null;
    if ($mysqli instanceof mysqli) {
        return $mysqli;
    }

    $allowRemote = in_array('--allow-remote', $argv, true);
    $root = dirname(__DIR__, 2);
    $db = array();
    require $root . '/application/config/database.php';
    $c = $db['default'];

    $host = (string) $c['hostname'];
    if (!$allowRemote) {
        foreach (db_script_blocked_hosts() as $blocked) {
            if (strcasecmp($host, $blocked) === 0) {
                fwrite(STDERR, "ERREUR: hôte MySQL distant « {$host} » bloqué pour éviter d'épuiser le quota horaire.\n");
                fwrite(STDERR, "        Les scripts doivent utiliser localhost. Ajoutez --allow-remote uniquement si nécessaire.\n");
                exit(2);
            }
        }
    }

    $mysqli = new mysqli(
        $c['hostname'],
        $c['username'],
        $c['password'],
        $c['database'],
        isset($c['port']) ? (int) $c['port'] : 3306
    );

    if ($mysqli->connect_error) {
        fwrite(STDERR, "Connexion DB échouée : {$mysqli->connect_error}\n");
        exit(1);
    }

    $mysqli->set_charset($c['char_set']);

    register_shutdown_function(function () use (&$mysqli) {
        if ($mysqli instanceof mysqli) {
            $mysqli->close();
            $mysqli = null;
        }
    });

    return $mysqli;
}

/**
 * Parse --jours=N depuis $argv.
 */
function db_script_jours_arg(array $argv, $default = 30)
{
    foreach (array_slice($argv, 1) as $arg) {
        if (preg_match('/^--jours=(\d+)$/', $arg, $m)) {
            return (int) $m[1];
        }
    }

    return (int) $default;
}
