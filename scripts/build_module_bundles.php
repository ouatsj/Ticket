#!/usr/bin/env php
<?php
/**
 * Fusionne les JS caisse / confirmation / bagage en un fichier par module.
 * Usage : php scripts/build_module_bundles.php
 */

$root = dirname(__DIR__);
define('BASEPATH', $root . '/');
$sources = require $root . '/application/config/scripts_bundles_module_sources.php';
$jsDir = $root . '/assets/js';
$outDir = $jsDir . '/bundles';

if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$built = 0;

foreach ($sources as $module => $files) {
    if (empty($files)) {
        continue;
    }

    $outFile = $outDir . '/' . $module . '.js';
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

    $content = "/* Bundle $module — genere par scripts/build_module_bundles.php */\n"
        . implode("\n;\n", $parts) . "\n";

    file_put_contents($outFile, $content);
    $built++;
    echo "OK $module.js (" . count($parts) . " fichiers, " . round(strlen($content) / 1024) . " Ko)\n";
}

echo "Termine: $built bundle(s).\n";
