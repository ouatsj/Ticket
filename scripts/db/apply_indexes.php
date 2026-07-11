#!/usr/bin/env php
<?php
/**
 * Applique les index de performance (ignore si déjà présents).
 * Usage : php scripts/db/apply_indexes.php
 */
require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv);

$indexes = array(
    array('client', 'idx_client_contact', 'contact_client'),
    array('passager', 'idx_passager_date_cpt', 'datep_create, idcptuser'),
    array('passager', 'idx_passager_pro_siege', 'code_pro, num_siege_categorie'),
    array('compte_user', 'idx_compte_username', 'username'),
);

foreach ($indexes as $spec) {
    list($table, $name, $cols) = $spec;
    $r = $mysqli->query("SHOW INDEX FROM `$table` WHERE Key_name = '$name'");
    if ($r && $r->num_rows > 0) {
        echo "SKIP $table.$name (existe)\n";
        continue;
    }
    $colsSql = implode(', ', array_map(function ($c) {
        return '`' . trim($c) . '`';
    }, explode(',', $cols)));
    $sql = "ALTER TABLE `$table` ADD INDEX `$name` ($colsSql)";
    if ($mysqli->query($sql)) {
        echo "OK $table.$name\n";
    } else {
        echo "FAIL $table.$name: {$mysqli->error}\n";
    }
}

echo "Termine.\n";
