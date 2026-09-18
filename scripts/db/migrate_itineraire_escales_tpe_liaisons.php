<?php
/**
 * Table liaisons escale→escale (Escale TPE, profil venteescale uniquement).
 * Usage: php scripts/db/migrate_itineraire_escales_tpe_liaisons.php [--allow-remote]
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

$r = $m->query("SHOW TABLES LIKE 'itineraire_escales_tpe_liaisons'");
if ($r && $r->num_rows > 0) {
    echo "OK déjà présent : itineraire_escales_tpe_liaisons\n";
    exit(0);
}

$sql = "CREATE TABLE itineraire_escales_tpe_liaisons (
    id_liaison INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_lignes VARCHAR(64) NOT NULL,
    id_escale_depart INT UNSIGNED NOT NULL,
    id_escale_arrivee INT UNSIGNED NOT NULL,
    prix_liaison DECIMAL(12,2) NOT NULL DEFAULT 0,
    actif_liaison TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_liaison),
    UNIQUE KEY uq_tpe_liaison (id_lignes, id_escale_depart, id_escale_arrivee),
    KEY idx_tpe_liaison_depart (id_escale_depart),
    KEY idx_tpe_liaison_arrivee (id_escale_arrivee)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Prix escale→escale exclusifs profil vente escale (TPE)'";

if (!$m->query($sql)) {
    fwrite(STDERR, 'CREATE échoué: ' . $m->error . "\n");
    exit(1);
}
echo "OK table créée : itineraire_escales_tpe_liaisons\n";
