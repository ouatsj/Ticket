#!/usr/bin/env php
<?php
/**
 * Migration colonnes règles arrêt de compte (compte_user).
 * Usage: php scripts/db/migrate_compte_arret_regles.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv ?? []);

$cols = [
    'autorisation_vente_forcee' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'autorisation_vente_jusquau' => 'DATETIME NULL',
    'autorisation_vente_motif' => 'VARCHAR(255) NULL',
    'autorisation_vente_par' => 'INT NULL',
    'exempt_desactivation_auto' => 'TINYINT(1) NOT NULL DEFAULT 0',
    'derniere_activite_at' => 'DATETIME NULL',
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
    $sql = "ALTER TABLE compte_user ADD COLUMN {$name} {$def}";
    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "ERREUR {$name}: {$mysqli->error}\n");
        exit(1);
    }
    echo "Ajouté: {$name}\n";
}

echo "Migration terminée.\n";

$res = $mysqli->query("UPDATE compte_user SET derniere_activite_at = COALESCE(date_conect, NOW()) WHERE derniere_activite_at IS NULL");
if ($res) {
    echo 'Initialisation derniere_activite_at: ' . $mysqli->affected_rows . " lignes\n";
}
