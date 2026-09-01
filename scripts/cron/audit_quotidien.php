#!/usr/bin/env php
<?php
/**
 * Cron audit quotidien Ticket — 01h00.
 * Usage: php scripts/cron/audit_quotidien.php
 *        php scripts/cron/audit_quotidien.php --date=2026-07-15
 *
 * Crontab :
 *   0 1 * * * php /var/www/rakietabus/ticket/scripts/cron/audit_quotidien.php >> /var/log/rakieta-audit-quotidien.log 2>&1
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');
if (!defined('APPPATH')) {
    define('APPPATH', dirname(__DIR__, 2) . '/application/');
}

require dirname(__DIR__, 2) . '/application/helpers/audit_quotidien_helper.php';

$mysqli = (function () use ($argv) {
    require __DIR__ . '/../db/_bootstrap.php';
    return db_script_connect($argv ?? []);
})();

$date_ref = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--date=') === 0) {
        $date_ref = substr($arg, 7);
    }
}

try {
    $report = audit_quotidien_run($mysqli, $date_ref);
    echo date('Y-m-d H:i:s')
        . ' — audit OK date=' . $report['date_rapport']
        . ' alertes=' . $report['nb_alertes']
        . ' avertissements=' . $report['nb_avertissements']
        . "\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, date('Y-m-d H:i:s') . ' — ERREUR: ' . $e->getMessage() . "\n");
    exit(1);
}
