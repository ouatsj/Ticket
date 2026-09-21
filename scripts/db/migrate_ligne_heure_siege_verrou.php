<?php
/**
 * Verrous de sièges admin par ligne_heure (Paramètres).
 * Usage: php scripts/db/migrate_ligne_heure_siege_verrou.php
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

$sql = "CREATE TABLE IF NOT EXISTS ligne_heure_siege_verrou (
  id_verrou INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_entreprise INT UNSIGNED NOT NULL,
  id_ligneheure INT UNSIGNED NOT NULL,
  siege_num INT UNSIGNED NOT NULL,
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_verrou),
  UNIQUE KEY uq_lh_siege (id_ligneheure, siege_num),
  KEY idx_entreprise (id_entreprise),
  KEY idx_ligneheure (id_ligneheure)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$m->query($sql)) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}
echo "OK ligne_heure_siege_verrou\n";
