#!/usr/bin/env php
<?php
/**
 * Audit préventif anti-403 hCDN — à lancer en cron (ex. toutes les heures).
 * Usage : php scripts/monitor_cdn_health.php [--json]
 */

$root = dirname(__DIR__);
define('BASEPATH', $root . '/');
$jsonOut = in_array('--json', $argv, true);
$config = require $root . '/application/config/cdn_watch.php';
$bundles = require $root . '/application/config/scripts_bundles.php';
$sources = require $root . '/application/config/scripts_bundles_guichet_sources.php';

$report = array(
    'at' => gmdate('c'),
    'ok' => true,
    'warnings' => array(),
    'critical' => array(),
);

function add_issue(array &$report, $level, $message)
{
    $report[$level][] = $message;
    if ($level === 'critical') {
        $report['ok'] = false;
    }
}

// .htaccess racine : règles anti-403
$htaccess = $root . '/.htaccess';
if (!is_readable($htaccess)) {
    add_issue($report, 'critical', 'Fichier .htaccess racine illisible');
} else {
    $ht = file_get_contents($htaccess);
    foreach (array(
        'RewriteRule ^assets/ - [L]' => 'assets non court-circuités avant index.php',
        'RewriteRule ^ping\.txt$ - [L]' => 'ping.txt non exclu du routeur PHP',
        'dynamic_request' => 'en-têtes no-cache pages dynamiques absents',
    ) as $needle => $msg) {
        if (strpos($ht, $needle) === false) {
            add_issue($report, 'warnings', $msg);
        }
    }
}

// Bundles guichet présents et non vides
$bundleDir = $root . '/assets/js/bundles';
foreach ($bundles['guichet'] as $role => $files) {
    if ($role === 'default' || empty($files)) {
        continue;
    }
    foreach ($files as $rel) {
        $path = $root . '/assets/js/' . $rel;
        if (!is_readable($path)) {
            add_issue($report, 'critical', "Bundle manquant: assets/js/$rel (rôle $role)");
            continue;
        }
        if (filesize($path) < 64) {
            add_issue($report, 'warnings', "Bundle suspect (trop petit): $rel");
        }
    }
}

// Rôles guichet sans bundle mais avec sources non vides → risque multi-requêtes
$noBundleRoles = isset($config['guichet_roles_without_bundle'])
    ? $config['guichet_roles_without_bundle']
    : array();

foreach ($noBundleRoles as $role) {
    $srcCount = isset($sources[$role]) ? count($sources[$role]) : 0;
    $bundleCount = isset($bundles['guichet'][$role]) ? count($bundles['guichet'][$role]) : 0;
    if ($srcCount > 0 && $bundleCount === 0) {
        add_issue($report, 'warnings', "Rôle guichet $role : $srcCount JS sources mais aucun bundle actif");
    }
}

// Estimation scripts par profil (layout scripts_bundle.php)
$baseScripts = 12; // jquery…mprogress + ligne_option + retour + request-guard
$datatablesScripts = 5;
foreach (array('accueil', 'historique', 'admin', 'confirmation', 'bagage', 'program', 'caisse') as $profile) {
    $extra = isset($bundles[$profile]) ? count($bundles[$profile]) : 0;
    $total = $baseScripts + $extra;
    $label = $profile;
    if ($profile === 'caisse' || $profile === 'historique') {
        $total += $datatablesScripts;
    }
    $warn = isset($config['script_warn']) ? (int) $config['script_warn'] : 20;
    $crit = isset($config['script_critical']) ? (int) $config['script_critical'] : 28;
    if ($total >= $crit) {
        add_issue($report, 'critical', "Profil $label : ~$total scripts/page (seuil critique $crit)");
    } elseif ($total >= $warn) {
        add_issue($report, 'warnings', "Profil $label : ~$total scripts/page (seuil alerte $warn)");
    }
}

// Vue dépréciée scripts_guichet (15+ fichiers individuels)
$deprecated = $root . '/application/views/_layouts/scripts_guichet.php';
if (is_readable($deprecated)) {
    $viewsDir = $root . '/application/views';
    $refs = array();
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
    foreach ($it as $file) {
        if (!$file->isFile() || substr($file->getFilename(), -4) !== '.php') {
            continue;
        }
        $path = $file->getPathname();
        if ($path === $deprecated) {
            continue;
        }
        $content = @file_get_contents($path);
        if ($content !== false && strpos($content, 'scripts_guichet') !== false) {
            $refs[] = str_replace($root . '/', '', $path);
        }
    }
    if (!empty($refs)) {
        add_issue($report, 'warnings', 'Vue scripts_guichet.php encore référencée: ' . implode(', ', $refs));
    }
}

// Signalements navigateur du jour
$watchLog = $root . '/application/logs/cdn_watch-' . gmdate('Y-m-d') . '.log';
if (is_readable($watchLog)) {
    $lines = file($watchLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $counts = array();
    foreach ($lines as $line) {
        $parts = explode("\t", $line, 3);
        if (count($parts) < 3) {
            continue;
        }
        $payload = json_decode($parts[2], true);
        $ev = is_array($payload) && isset($payload['event']) ? $payload['event'] : 'unknown';
        $counts[$ev] = isset($counts[$ev]) ? $counts[$ev] + 1 : 1;
    }
    $report['client_events_today'] = $counts;
    foreach (array('asset_load_fail', 'http_403', 'http_408', 'script_burst') as $ev) {
        if (!empty($counts[$ev])) {
            add_issue($report, 'warnings', "Événements client $ev aujourd'hui : " . $counts[$ev]);
        }
    }
} else {
    $report['client_events_today'] = array();
}

// Journal audit
$auditLog = $root . '/application/logs/cdn_audit-' . gmdate('Y-m-d') . '.log';
$auditLine = gmdate('Y-m-d H:i:s') . ' UTC'
    . "\t" . ($report['ok'] ? 'OK' : 'FAIL')
    . "\t" . json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    . "\n";
file_put_contents($auditLog, $auditLine, FILE_APPEND | LOCK_EX);

if ($jsonOut) {
    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo ($report['ok'] ? 'OK' : 'ATTENTION') . ' — audit CDN ' . $report['at'] . "\n";
    foreach ($report['warnings'] as $w) {
        echo "  WARN: $w\n";
    }
    foreach ($report['critical'] as $c) {
        echo "  CRIT: $c\n";
    }
    if (!empty($report['client_events_today'])) {
        echo "  Événements client: " . json_encode($report['client_events_today']) . "\n";
    }
}

exit($report['ok'] ? 0 : 1);
