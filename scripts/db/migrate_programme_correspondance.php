<?php
/**
 * Lien correspondance programmes (essai).
 * Usage: php scripts/db/migrate_programme_correspondance.php
 *
 * principal = départ direct (ex. BAN→OUA)
 * suite     = départ correspondance déjà créé (ex. BOB→OUA)
 * derive    = départ auto à la gare de départ (ex. BAN→BOB), sièges partagés avec suite
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require __DIR__ . '/../../application/config/database.php';
$d = $db['default'];
$m = new mysqli($d['hostname'], $d['username'], $d['password'], $d['database']);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . "\n");
    exit(1);
}
$m->set_charset('utf8');

$sql = "CREATE TABLE IF NOT EXISTS programme_correspondance (
  id_lien INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code_progr_principal VARCHAR(128) NOT NULL,
  code_progr_suite VARCHAR(128) NOT NULL,
  code_progr_derive VARCHAR(128) DEFAULT NULL,
  ekey VARCHAR(64) NOT NULL DEFAULT '',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_lien),
  UNIQUE KEY uq_principal_suite (code_progr_principal, code_progr_suite),
  UNIQUE KEY uq_principal (code_progr_principal),
  KEY idx_suite (code_progr_suite),
  KEY idx_derive (code_progr_derive)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$m->query($sql)) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}
echo "OK programme_correspondance\n";
