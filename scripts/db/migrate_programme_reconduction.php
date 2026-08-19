<?php
/**
 * Sortie de départ + reconduction des sièges restants vers une gare aval.
 * Usage: php scripts/db/migrate_programme_reconduction.php
 *
 * Ne touche PAS à programme_correspondance.
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

$sqls = array(
    "CREATE TABLE IF NOT EXISTS programme_sortie (
      id_sortie INT UNSIGNED NOT NULL AUTO_INCREMENT,
      code_progr_source VARCHAR(128) NOT NULL,
      ekey VARCHAR(64) NOT NULL DEFAULT '',
      gareidentif VARCHAR(64) NOT NULL DEFAULT '',
      gadest_lg VARCHAR(64) DEFAULT NULL,
      ligne_id VARCHAR(128) DEFAULT NULL,
      date_progr DATE DEFAULT NULL,
      categori VARCHAR(64) DEFAULT NULL,
      intervalle1 INT NOT NULL DEFAULT 0,
      intervalle2 INT NOT NULL DEFAULT 0,
      declared_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      declared_by VARCHAR(128) DEFAULT NULL,
      source_ferme TINYINT(1) NOT NULL DEFAULT 0,
      ferme_at TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (id_sortie),
      UNIQUE KEY uq_source (code_progr_source),
      KEY idx_gadest (gadest_lg),
      KEY idx_ekey_gare (ekey, gareidentif),
      KEY idx_date (date_progr)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS programme_reconduction (
      id_reconduction INT UNSIGNED NOT NULL AUTO_INCREMENT,
      code_progr_source VARCHAR(128) NOT NULL,
      code_progr_cible VARCHAR(128) NOT NULL,
      gare_cible VARCHAR(64) NOT NULL DEFAULT '',
      ekey VARCHAR(64) NOT NULL DEFAULT '',
      created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      created_by VARCHAR(128) DEFAULT NULL,
      PRIMARY KEY (id_reconduction),
      UNIQUE KEY uq_cible (code_progr_cible),
      KEY idx_source (code_progr_source)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS programme_reconduction_siege (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      code_progr_source VARCHAR(128) NOT NULL,
      code_progr_cible VARCHAR(128) NOT NULL,
      siege_num INT NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY uq_source_siege (code_progr_source, siege_num),
      KEY idx_cible (code_progr_cible)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS programme_sortie_siege (
      id INT UNSIGNED NOT NULL AUTO_INCREMENT,
      code_progr_source VARCHAR(128) NOT NULL,
      siege_num INT NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY uq_sortie_siege (code_progr_source, siege_num),
      KEY idx_sortie_source (code_progr_source)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
);

foreach ($sqls as $sql) {
    if (!$m->query($sql)) {
        fwrite(STDERR, $m->error . "\n");
        exit(1);
    }
}
echo "OK programme_sortie / programme_reconduction / programme_reconduction_siege / programme_sortie_siege\n";
