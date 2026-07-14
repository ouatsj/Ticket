<?php
/**
 * @deprecated Utiliser prepare_arret_ventes_importees.php
 * Wrapper de compatibilité.
 */
$args = array_slice($argv, 1);
$cmd = 'php ' . escapeshellarg(__DIR__ . '/prepare_arret_ventes_importees.php');
foreach ($args as $arg) {
    $cmd .= ' ' . escapeshellarg($arg);
}
passthru($cmd, $exitCode);
exit($exitCode);
