-- =============================================================================
-- Revert schéma additif PROD — compatible MySQL 8.0
-- Inverse de migrate_prod_additive_p0_p1.sql
-- =============================================================================
-- Usage :
--   mysql … rakieta < scripts/db/migrate_prod_revert_p0_p1.sql
--   # ou : bash scripts/rollback_prod.sh --apply --with-schema
-- =============================================================================

SET @db := DATABASE();

-- P1 tables
DROP TABLE IF EXISTS itineraire_etapes;
DROP TABLE IF EXISTS itineraire_escales;

-- P1 colonnes passager
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'passager' AND COLUMN_NAME = 'id_escale_vente'
);
SET @sql := IF(@exists = 1, 'ALTER TABLE passager DROP COLUMN id_escale_vente', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'passager' AND COLUMN_NAME = 'lignetineraire_vendu'
);
SET @sql := IF(@exists = 1, 'ALTER TABLE passager DROP COLUMN lignetineraire_vendu', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'passager' AND COLUMN_NAME = 'itinecode_vendu'
);
SET @sql := IF(@exists = 1, 'ALTER TABLE passager DROP COLUMN itinecode_vendu', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- P0.3
DROP TABLE IF EXISTS programme_sortie_siege;
DROP TABLE IF EXISTS programme_reconduction_siege;
DROP TABLE IF EXISTS programme_reconduction;
DROP TABLE IF EXISTS programme_sortie;

-- P0.2
DROP TABLE IF EXISTS programme_correspondance;

-- P0.1
DROP TABLE IF EXISTS programme_sousgare;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'programme' AND INDEX_NAME = 'idx_programme_idsousgare_prog'
);
SET @sql := IF(@exists = 1, 'DROP INDEX idx_programme_idsousgare_prog ON programme', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'programme' AND COLUMN_NAME = 'idsousgare_prog'
);
SET @sql := IF(@exists = 1, 'ALTER TABLE programme DROP COLUMN idsousgare_prog', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
