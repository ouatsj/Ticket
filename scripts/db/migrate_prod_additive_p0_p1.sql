-- =============================================================================
-- Migrations additives PROD (P0 + P1) — compatible MySQL 8.0
-- =============================================================================
-- Usage :
--   mysql -u <user> -p rakieta < scripts/db/migrate_prod_additive_p0_p1.sql
--   # ou via scripts/deploy_prod.sh
--
-- Règles :
--   - CREATE TABLE IF NOT EXISTS (natif MySQL)
--   - ADD COLUMN / CREATE INDEX via information_schema + PREPARE (idempotent)
--   - Pas de DELETE / DROP / seed
-- =============================================================================

SET @db := DATABASE();

-- -----------------------------------------------------------------------------
-- Helper-style ADD COLUMN (répété par colonne)
-- -----------------------------------------------------------------------------

-- P0.1 programme.idsousgare_prog
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'programme' AND COLUMN_NAME = 'idsousgare_prog'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE programme ADD COLUMN idsousgare_prog INT NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'programme' AND INDEX_NAME = 'idx_programme_idsousgare_prog'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_programme_idsousgare_prog ON programme (idsousgare_prog)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS programme_sousgare (
  code_progr VARCHAR(80) NOT NULL,
  idsousgare INT NOT NULL,
  PRIMARY KEY (code_progr, idsousgare),
  KEY idx_psg_sg (idsousgare)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Sous-gares autorisees sur un depart; aucune ligne = legacy idsousgare_prog';

-- -----------------------------------------------------------------------------
-- P0.2 Correspondances
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS programme_correspondance (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- P0.3 Reconduction / sortie
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS programme_sortie (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS programme_reconduction (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS programme_reconduction_siege (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code_progr_source VARCHAR(128) NOT NULL,
  code_progr_cible VARCHAR(128) NOT NULL,
  siege_num INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_source_siege (code_progr_source, siege_num),
  KEY idx_cible (code_progr_cible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS programme_sortie_siege (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code_progr_source VARCHAR(128) NOT NULL,
  siege_num INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sortie_siege (code_progr_source, siege_num),
  KEY idx_sortie_source (code_progr_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- P1.1 OD final passager
-- -----------------------------------------------------------------------------
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'passager' AND COLUMN_NAME = 'itinecode_vendu'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE passager ADD COLUMN itinecode_vendu VARCHAR(128) NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'passager' AND COLUMN_NAME = 'lignetineraire_vendu'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE passager ADD COLUMN lignetineraire_vendu VARCHAR(255) NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- P1.2 Vente escale
-- -----------------------------------------------------------------------------
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'passager' AND COLUMN_NAME = 'id_escale_vente'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE passager ADD COLUMN id_escale_vente INT UNSIGNED NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS itineraire_escales (
  id_escale INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_lignes VARCHAR(64) NOT NULL COMMENT 'Itineraire / ligne parent',
  code_gadest VARCHAR(32) NOT NULL COMMENT 'Gare destination de l escale',
  nom_escale VARCHAR(128) NOT NULL DEFAULT '',
  prix_escale DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  ordre_escale TINYINT UNSIGNED NOT NULL DEFAULT 1,
  actif_escale TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_escale),
  UNIQUE KEY uq_parent_dest (id_lignes, code_gadest),
  KEY idx_parent (id_lignes, actif_escale, ordre_escale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- P1.3 Étapes déclaratives (coquille vide)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS itineraire_etapes (
  id_etape INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_lignes VARCHAR(64) NOT NULL,
  ident_ligne_etape VARCHAR(64) NOT NULL,
  ordre_etape TINYINT UNSIGNED NOT NULL DEFAULT 1,
  actif_etape TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_etape),
  KEY idx_parent_ordre (id_lignes, ordre_etape)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- P1.4 Lignes : activer / désactiver (masque vente / confirm / réserve / reprog)
-- -----------------------------------------------------------------------------
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'lignes' AND COLUMN_NAME = 'actif_lg'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE lignes ADD COLUMN actif_lg TINYINT(1) NOT NULL DEFAULT 1 COMMENT ''1=visible guichet, 0=masquee''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'lignes' AND INDEX_NAME = 'idx_lignes_actif_lg'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_lignes_actif_lg ON lignes (actif_lg)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- P1.5 Gares d'arrivée : activer / désactiver (masque vente / confirm / réserve)
-- -----------------------------------------------------------------------------
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'gare_dest' AND COLUMN_NAME = 'actif_ga'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE gare_dest ADD COLUMN actif_ga TINYINT(1) NOT NULL DEFAULT 1 COMMENT ''1=visible guichet, 0=masquee''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'gare_dest' AND INDEX_NAME = 'idx_gare_dest_actif_ga'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_gare_dest_actif_ga ON gare_dest (actif_ga)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- P1.6 Sièges bloqués à l'édition programme (décochés = hors vente)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS programme_siege_bloque (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code_progr VARCHAR(128) NOT NULL,
  siege_num INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_prog_siege (code_progr, siege_num),
  KEY idx_code_progr (code_progr)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
