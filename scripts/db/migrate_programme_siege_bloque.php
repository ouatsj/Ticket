<?php
/**
 * Sièges bloqués à l'édition d'un programme (décochés = hors vente).
 * Usage: php scripts/db/migrate_programme_siege_bloque.php
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

$sql = "CREATE TABLE IF NOT EXISTS programme_siege_bloque (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code_progr VARCHAR(128) NOT NULL,
  siege_num INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_prog_siege (code_progr, siege_num),
  KEY idx_code_progr (code_progr)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$m->query($sql)) {
    fwrite(STDERR, $m->error . "\n");
    exit(1);
}
echo "OK programme_siege_bloque\n";
