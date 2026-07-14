#!/usr/bin/env php
<?php
/**
 * Cron : désactivation automatique des comptes inactifs > 48 h.
 * Usage: php scripts/cron/compte_arret_inactivite.php
 * Crontab exemple (toutes les heures) :
 *   0 * * * * php /var/www/rakietabus/ticket/scripts/cron/compte_arret_inactivite.php >> /var/log/rakieta-cron.log 2>&1
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

if (!defined('APPPATH')) {
    define('APPPATH', dirname(__DIR__, 2) . '/application/');
}

require dirname(__DIR__, 2) . '/application/helpers/compte_arret_helper.php';

if (!compte_arret_inactivite_cron_enabled()) {
    echo date('Y-m-d H:i:s') . " — cron inactivité désactivé (compte_arret_inactivite_cron = FALSE)\n";
    exit(0);
}

$mysqli = (function () {
    require __DIR__ . '/../db/_bootstrap.php';
    return db_script_connect($argv ?? []);
})();

$hours = (int) compte_arret_hours_limit();
$adminRoles = implode(',', array_map('intval', compte_arret_admin_roles()));

$sql = "UPDATE compte_user cu
    LEFT JOIN (
        SELECT DISTINCT ul.uid_usercpte AS cpuser_id
        FROM attributions_role ar
        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
        WHERE ar.userole IN ({$adminRoles}) AND ar.activer_role = 0
    ) adm ON adm.cpuser_id = cu.cpuser_id
    SET cu.activer = 1
    WHERE cu.activer = 0
    AND cu.exempt_desactivation_auto = 0
    AND adm.cpuser_id IS NULL
    AND cu.derniere_activite_at IS NOT NULL
    AND cu.derniere_activite_at < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)";

if (!$mysqli->query($sql)) {
    fwrite(STDERR, 'Erreur: ' . $mysqli->error . "\n");
    exit(1);
}

$n = $mysqli->affected_rows;
echo date('Y-m-d H:i:s') . " — comptes désactivés (inactivité {$hours}h): {$n}\n";
