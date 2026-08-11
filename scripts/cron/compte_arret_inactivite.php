#!/usr/bin/env php
<?php
/**
 * Cron sessions + désactivation inactivité.
 *
 * 1) Déconnexion : is_conect=0 si aucune activité depuis 30 min
 * 2) Désactivation : activer=1 + motif si aucune activité depuis le délai configuré
 *
 * Usage: php scripts/cron/compte_arret_inactivite.php
 * Crontab (toutes les 5 min) :
 *   (star)/5 * * * * php .../scripts/cron/compte_arret_inactivite.php >> /var/log/rakieta-session-inactivite.log 2>&1
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

if (!defined('APPPATH')) {
    define('APPPATH', dirname(__DIR__, 2) . '/application/');
}

require dirname(__DIR__, 2) . '/application/helpers/compte_arret_helper.php';

$mysqli = (function () {
    require __DIR__ . '/../db/_bootstrap.php';
    return db_script_connect($argv ?? []);
})();

$ts = date('Y-m-d H:i:s');

$n_sess = compte_arret_run_session_deconnexion($mysqli);
$minutes = (int) compte_arret_session_idle_minutes();
echo "{$ts} — sessions déconnectées (inactivité {$minutes} min): {$n_sess}\n";

if (!compte_arret_inactivite_cron_enabled()) {
    echo "{$ts} — désactivation automatique désactivée (compte_arret_inactivite_cron = FALSE)\n";
    exit(0);
}

$n_des = compte_arret_run_inactivite_desactivation($mysqli);
$jours = (int) compte_arret_desactivation_jours();
echo "{$ts} — comptes désactivés (inactivité {$jours} j): {$n_des}\n";
