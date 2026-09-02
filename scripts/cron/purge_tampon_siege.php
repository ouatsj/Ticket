#!/usr/bin/env php
<?php
/**
 * Purge les tampons siège expirés (réservations UI abandonnées).
 *
 * Usage:
 *   php scripts/cron/purge_tampon_siege.php
 *   php scripts/cron/purge_tampon_siege.php --minutes=45
 *   php scripts/cron/purge_tampon_siege.php --dry-run
 *
 * Crontab recommandé (toutes les 15 min) :
 *   */15 * * * * php /var/www/rakietabus/ticket/scripts/cron/purge_tampon_siege.php >> /var/log/rakieta-tampon-purge.log 2>&1
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');
define('APPPATH', dirname(__DIR__, 2) . '/application/');

require APPPATH . 'config/database.php';
$c = $db['default'];
$m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset(isset($c['char_set']) ? $c['char_set'] : 'utf8mb4');

$dryRun = in_array('--dry-run', $argv, true);
$minutes = 45;
foreach ($argv as $arg) {
    if (preg_match('/^--minutes=(\d+)$/', $arg, $match)) {
        $minutes = max(1, (int) $match[1]);
    }
}

$col = $m->query("SHOW COLUMNS FROM tampon_siege LIKE 'created_at'");
if (!$col || $col->num_rows === 0) {
    echo date('Y-m-d H:i:s') . " — colonne created_at absente, rien à purger\n";
    exit(0);
}

$cutoff = date('Y-m-d H:i:s', time() - ($minutes * 60));
$countRes = $m->query(
    "SELECT COUNT(*) AS n FROM tampon_siege WHERE created_at < '" . $m->real_escape_string($cutoff) . "'"
);
$n = ($countRes && ($row = $countRes->fetch_assoc())) ? (int) $row['n'] : 0;

if ($dryRun) {
    echo date('Y-m-d H:i:s') . " — [dry-run] {$n} tampon(s) > {$minutes} min\n";
    exit(0);
}

if ($n === 0) {
    echo date('Y-m-d H:i:s') . " — aucun tampon expiré\n";
    exit(0);
}

if (!$m->query(
    "DELETE FROM tampon_siege WHERE created_at < '" . $m->real_escape_string($cutoff) . "'"
)) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}

echo date('Y-m-d H:i:s') . " — {$n} tampon(s) supprimé(s) (TTL {$minutes} min)\n";
