#!/usr/bin/env php
<?php
/**
 * Fusionne les JS guichet par rôle en un fichier (réduit les 403 CDN).
 * Usage : php scripts/build_guichet_bundles.php
 * À relancer après modification de scripts_bundles_guichet_sources.php ou assets/js/*.js
 */

$root = dirname(__DIR__);
define('BASEPATH', $root . '/');
require $root . '/scripts/build_js_minify.php';
$sources = require $root . '/application/config/scripts_bundles_guichet_sources.php';
$jsDir = $root . '/assets/js';
$outDir = $jsDir . '/bundles';

if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$built = 0;

foreach ($sources as $role => $files) {
    if (empty($files)) {
        continue;
    }

    $slug = $role === 'default' ? 'default' : (string) $role;
    $outFile = $outDir . '/guichet-' . $slug . '.js';
    $parts = array();

    foreach ($files as $file) {
        $path = $jsDir . '/' . $file;

        if (!is_readable($path)) {
            fwrite(STDERR, "AVERTISSEMENT: fichier manquant: $file\n");
            continue;
        }

        $parts[] = "/* --- $file --- */\n" . file_get_contents($path);
    }

    if (empty($parts)) {
        continue;
    }

    $content = "/* Bundle guichet role=$slug — genere par scripts/build_guichet_bundles.php */\n"
        . implode("\n;\n", $parts) . "\n";

    $rawSize = strlen($content);
    $content = build_js_minify($content, $root);

    file_put_contents($outFile, $content);
    $built++;
    echo "OK guichet-$slug.js (" . count($parts) . " fichiers, "
        . round($rawSize / 1024) . " Ko → " . round(strlen($content) / 1024) . " Ko min)\n";
}

echo "Termine: $built bundle(s).\n";
