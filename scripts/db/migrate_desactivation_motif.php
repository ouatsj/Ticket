#!/usr/bin/env php
<?php
/**
 * Ajoute desactivation_motif / desactivation_at sur compte_user.
 * Usage: php scripts/db/migrate_desactivation_motif.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv ?? []);

$cols = [
    'desactivation_motif' => 'VARCHAR(255) NULL',
    'desactivation_at' => 'DATETIME NULL',
];

$existing = [];
$res = $mysqli->query('SHOW COLUMNS FROM compte_user');
while ($row = $res->fetch_assoc()) {
    $existing[$row['Field']] = true;
}

foreach ($cols as $name => $def) {
    if (isset($existing[$name])) {
        echo "OK colonne existante: {$name}\n";
        continue;
    }
    if (!$mysqli->query("ALTER TABLE compte_user ADD COLUMN {$name} {$def}")) {
        fwrite(STDERR, "ERREUR {$name}: {$mysqli->error}\n");
        exit(1);
    }
    echo "Ajouté: {$name}\n";
}

echo "Migration terminée.\n";
