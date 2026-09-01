#!/usr/bin/env php
<?php
/**
 * Purge le cache fichier application/cache/data (entrées expirées + orphelins).
 *
 * Usage:
 *   php scripts/cron/purge_app_cache.php
 *   php scripts/cron/purge_app_cache.php --dry-run
 *   php scripts/cron/purge_app_cache.php --orphan-days=7
 *
 * Crontab recommandé (quotidien 02h30) :
 *   30 2 * * * php /var/www/rakietabus/ticket/scripts/cron/purge_app_cache.php >> /var/log/rakieta-cache-purge.log 2>&1
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');
define('APPPATH', dirname(__DIR__, 2) . '/application/');

$dryRun = in_array('--dry-run', $argv, true);
$orphanDays = 7;
foreach ($argv as $arg) {
    if (preg_match('/^--orphan-days=(\d+)$/', $arg, $m)) {
        $orphanDays = max(1, (int) $m[1]);
    }
}

$dir = APPPATH . 'cache/data';
if (!is_dir($dir)) {
    echo date('Y-m-d H:i:s') . " — rien à purger (répertoire absent)\n";
    exit(0);
}

$now = time();
$orphanCutoff = $now - ($orphanDays * 86400);
$stats = array(
    'scanned' => 0,
    'expired' => 0,
    'orphan' => 0,
    'invalid' => 0,
    'kept' => 0,
    'bytes_freed' => 0,
);

$files = glob($dir . '/*.cache');
if ($files === false) {
    fwrite(STDERR, date('Y-m-d H:i:s') . " — ERREUR: lecture du répertoire cache\n");
    exit(1);
}

foreach ($files as $path) {
    $stats['scanned']++;
    $size = @filesize($path);
    if ($size === false) {
        continue;
    }

    $mtime = @filemtime($path);
    $raw = @file_get_contents($path);
    $delete = false;
    $reason = '';

    if ($raw === false) {
        $delete = true;
        $reason = 'invalid';
        $stats['invalid']++;
    } else {
        $data = @unserialize($raw);
        if (!is_array($data) || !isset($data['expires']) || !array_key_exists('value', $data)) {
            $delete = true;
            $reason = 'invalid';
            $stats['invalid']++;
        } elseif ((int) $data['expires'] < $now) {
            $delete = true;
            $reason = 'expired';
            $stats['expired']++;
        } elseif ($mtime !== false && $mtime < $orphanCutoff) {
            $delete = true;
            $reason = 'orphan';
            $stats['orphan']++;
        } else {
            $stats['kept']++;
        }
    }

    if ($delete) {
        $stats['bytes_freed'] += $size;
        if (!$dryRun) {
            @unlink($path);
        }
    }
}

$mb = round($stats['bytes_freed'] / 1048576, 2);
echo date('Y-m-d H:i:s')
    . ($dryRun ? ' — DRY-RUN' : ' — purge OK')
    . " scanned={$stats['scanned']}"
    . " expired={$stats['expired']}"
    . " orphan={$stats['orphan']}"
    . " invalid={$stats['invalid']}"
    . " kept={$stats['kept']}"
    . " freed={$mb}Mo"
    . "\n";

exit(0);
