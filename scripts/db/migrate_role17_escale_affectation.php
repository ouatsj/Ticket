#!/usr/bin/env php
<?php
/**
 * Migration additive : colonnes vente escale sur attributions_role (rôle 17).
 * Usage: php scripts/db/migrate_role17_escale_affectation.php
 */
define('BASEPATH', dirname(__DIR__, 2) . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv ?? []);

$cols = array(
    'vente_escale_id_lignes' => "VARCHAR(64) NULL DEFAULT NULL COMMENT 'Rôle 17: ligne affectée pour vente escale'",
    'vente_escale_value' => "VARCHAR(160) NULL DEFAULT NULL COMMENT 'Rôle 17: valeur départ (escale~id / origin~…)'",
    'vente_escale_label' => "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Rôle 17: libellé escale de départ'",
);

$existing = array();
$res = $mysqli->query('SHOW COLUMNS FROM attributions_role');
if (!$res) {
    fwrite(STDERR, 'ERREUR SHOW COLUMNS: ' . $mysqli->error . "\n");
    exit(1);
}
while ($row = $res->fetch_assoc()) {
    $existing[$row['Field']] = true;
}

foreach ($cols as $name => $def) {
    if (isset($existing[$name])) {
        echo "OK colonne existante: {$name}\n";
        continue;
    }
    $sql = "ALTER TABLE attributions_role ADD COLUMN {$name} {$def}";
    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "ERREUR {$name}: {$mysqli->error}\n");
        exit(1);
    }
    echo "Ajouté: {$name}\n";
}

$idx = 'idx_ar_vente_escale_value';
$idxRes = $mysqli->query(
    "SHOW INDEX FROM attributions_role WHERE Key_name = '" . $mysqli->real_escape_string($idx) . "'"
);
if ($idxRes && $idxRes->num_rows > 0) {
    echo "OK index existant: {$idx}\n";
} else {
    if (!$mysqli->query("CREATE INDEX {$idx} ON attributions_role (vente_escale_value)")) {
        fwrite(STDERR, "ERREUR index {$idx}: {$mysqli->error}\n");
        exit(1);
    }
    echo "Ajouté index: {$idx}\n";
}

echo "Migration terminée.\n";
