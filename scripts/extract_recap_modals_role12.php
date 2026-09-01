#!/usr/bin/env php
<?php
/**
 * Extrait les modales recap identiques role_1 ↔ role_2 en partials partagés.
 * Usage : php scripts/extract_recap_modals_role12.php [--dry-run]
 */
$root = dirname(__DIR__);
$dryRun = in_array('--dry-run', $argv, true);

// Plages ligne role_1.php (1-indexed, inclusives) — modales byte-identiques role_1/role_2
$modals = array(
    'form-declarrecaptbgesc-0' => array(369, 435),
    'form-clarrecaptbgesc-0' => array(437, 500),
    'form-declarrecaptcresc-0' => array(680, 768),
    'form-clarrecaptbg-0' => array(770, 834),
    'form-declarrecaptbg-0' => array(836, 902),
    'form-declarrecapt-0' => array(904, 972),
    'form-clarrecapt-0' => array(974, 1040),
    'form-recaptes-0' => array(1042, 1108),
    'form-clarrecapes-0' => array(1180, 1246),
    'form-recapesc-0' => array(1323, 1389),
    'form-recaptbgop-0' => array(1467, 1540),
    'form-recaptbgopesc-0' => array(1542, 1614),
    'form-recaptbgopgl-0' => array(1616, 1689),
    'form-recapbg-0' => array(4697, 4763),
    'form-recaptbg-0' => array(5222, 5288),
    'form-recaptheb-0' => array(5618, 5705),
    'form-recapthebesc-0' => array(5707, 5794),
    'form-recaptbgheb-0' => array(5796, 5867),
    'form-recaptbgescheb-0' => array(5869, 5939),
);

$partialDir = $root . '/application/views/beagle/pages/guichet/_modales_recap';
$role1 = $root . '/application/views/beagle/pages/guichet/role_1.php';
$role2 = $root . '/application/views/beagle/pages/guichet/role_2.php';

if (!$dryRun && !is_dir($partialDir)) {
    mkdir($partialDir, 0755, true);
}

$role1Lines = file($role1, FILE_IGNORE_NEW_LINES);
if ($role1Lines === false) {
    fwrite(STDERR, "Impossible de lire role_1.php\n");
    exit(1);
}

$role1Content = file_get_contents($role1);
$role2Content = file_get_contents($role2);
$extracted = 0;

foreach ($modals as $id => $range) {
    list($start, $end) = $range;
    $slice = array_slice($role1Lines, $start - 1, $end - $start + 1);
    $block = implode("\n", $slice) . "\n";
    $slug = preg_replace('/-0$/', '', $id);
    $partialFile = $partialDir . '/' . $slug . '.php';
    $include = '            <?php $this->load->view(\'beagle/pages/guichet/_modales_recap/' . $slug . '\'); ?>';

    if (!$dryRun) {
        $header = "<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>\n";
        file_put_contents($partialFile, $header . $block);
    }

    $role1Content = str_replace($block, $include . "\n", $role1Content);

    if (strpos($role2Content, $block) !== false) {
        $role2Content = str_replace($block, $include . "\n", $role2Content);
    } else {
        fwrite(STDERR, "AVERTISSEMENT: bloc $id absent ou différent dans role_2.php\n");
    }

    echo ($dryRun ? '[dry-run] ' : '') . "OK $id → _modales_recap/$slug.php\n";
    $extracted++;
}

if (!$dryRun) {
    file_put_contents($role1, $role1Content);
    file_put_contents($role2, $role2Content);
}

echo "Terminé: $extracted modales extraites.\n";
exit(0);
