<?php
/**
 * Ajoute prix_escale_tpe (prix escale→destination exclusif profil vente escale).
 * Usage: php scripts/db/migrate_itineraire_escales_prix_tpe.php [--allow-remote]
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

$r = $m->query("SHOW COLUMNS FROM itineraire_escales LIKE 'prix_escale_tpe'");
if ($r && $r->num_rows > 0) {
    echo "OK déjà présent : itineraire_escales.prix_escale_tpe\n";
    exit(0);
}

if (!$m->query(
    "ALTER TABLE itineraire_escales
     ADD COLUMN prix_escale_tpe DECIMAL(12,2) NULL DEFAULT NULL
     COMMENT 'TPE exclusif: escale vers destination parent (venteescale)'
     AFTER prix_escale_origine"
)) {
    fwrite(STDERR, 'ALTER échoué: ' . $m->error . "\n");
    exit(1);
}
echo "OK colonne ajoutée : itineraire_escales.prix_escale_tpe\n";
