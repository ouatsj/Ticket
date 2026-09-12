<?php
/**
 * Crée la table arret_compte_audit (trace des arrêts sans écart).
 * Usage: php scripts/db/migrate_arret_compte_complet.php
 */
require __DIR__ . '/_bootstrap.php';

$m = db_script_connect($argv);

$sql = "CREATE TABLE IF NOT EXISTS arret_compte_audit (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_ekey INT NOT NULL,
    roleattribut INT NOT NULL,
    gare_code VARCHAR(32) NOT NULL,
    idsousgare INT NULL,
    source VARCHAR(32) NOT NULL DEFAULT 'valide',
    totals_json TEXT NOT NULL,
    pass_count INT NOT NULL DEFAULT 0,
    np_count INT NOT NULL DEFAULT 0,
    merged_extra INT NOT NULL DEFAULT 0,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    ok TINYINT(1) NOT NULL DEFAULT 1,
    error_msg VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_arret_audit_ra (roleattribut, created_at),
    KEY idx_arret_audit_gare (gare_code, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if (!$m->query($sql)) {
    fwrite(STDERR, "ERREUR: {$m->error}\n");
    exit(1);
}

$r = $m->query("SHOW TABLES LIKE 'arret_compte_audit'");
echo $r && $r->num_rows ? "OK arret_compte_audit prête\n" : "ECHEC création table\n";
