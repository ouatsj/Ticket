#!/usr/bin/env php
<?php
/**
 * Détecte les fichiers PHP critiques tronqués (incidents Programmes.php / role_*.php).
 *
 * Usage:
 *   php scripts/check_truncated_php.php
 *   php scripts/check_truncated_php.php --strict
 *
 * Exit 0 = OK, 1 = fichier suspect.
 */
$root = dirname(__DIR__);
$strict = in_array('--strict', $argv, true);

$checks = array(
    array(
        'path' => 'application/controllers/Programmes.php',
        'min_lines' => 21800,
        'must_contain' => array('function verifheuresvente', 'function addpassager'),
    ),
    array(
        'path' => 'application/controllers/Caisses.php',
        'min_lines' => 11600,
        'must_contain' => array('function arcompte', 'function opts'),
    ),
    array(
        'path' => 'application/controllers/Gares.php',
        'min_lines' => 500,
        'must_contain' => array('function options', 'function ajax_passagers'),
    ),
    array(
        'path' => 'application/views/beagle/pages/guichet/role_1.php',
        'min_lines' => 7000,
        'must_contain' => array('id="date_depheure"', 'id="ticketaller-0"', '_modales_recap/'),
    ),
    array(
        'path' => 'application/views/beagle/pages/guichet/role_2.php',
        'min_lines' => 5900,
        'must_contain' => array('id="date_depheure"', 'ticketaller-0', '_modales_recap/'),
    ),
);

$errors = array();

foreach ($checks as $spec) {
    $path = $root . '/' . $spec['path'];
    $label = $spec['path'];

    if (!is_readable($path)) {
        $errors[] = "$label — fichier illisible ou absent";
        continue;
    }

    $lines = 0;
    $content = file_get_contents($path);
    if ($content === false) {
        $errors[] = "$label — lecture impossible";
        continue;
    }

    $lines = substr_count($content, "\n") + 1;

    if ($lines < $spec['min_lines']) {
        $errors[] = "$label — tronqué ($lines lignes, minimum {$spec['min_lines']})";
    }

    foreach ($spec['must_contain'] as $needle) {
        if (strpos($content, $needle) === false) {
            $errors[] = "$label — contenu manquant: $needle";
        }
    }

    if ($strict && !preg_match('/\}\s*(\/\*[^*]*\*\/\s*)?$/s', trim($content))) {
        $errors[] = "$label — fin de fichier suspecte (pas de fermeture propre)";
    }

    $syntax = shell_exec('php -l ' . escapeshellarg($path) . ' 2>&1');
    if ($syntax !== null && strpos($syntax, 'No syntax errors') === false) {
        $errors[] = "$label — erreur syntaxe PHP";
    }
}

echo date('Y-m-d H:i:s') . ' — vérification fichiers critiques' . PHP_EOL;

if (empty($errors)) {
    echo "OK — " . count($checks) . " fichiers valides\n";
    exit(0);
}

foreach ($errors as $err) {
    fwrite(STDERR, "ERREUR: $err\n");
}
exit(1);
