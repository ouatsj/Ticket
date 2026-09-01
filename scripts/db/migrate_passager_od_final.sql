-- Champs "OD final" pour le rapport journalier des ventes transit / vente escale
-- Usage : mysql -u <user> -p <database> < scripts/db/migrate_passager_od_final.sql

SET @db := DATABASE();

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

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'passager' AND COLUMN_NAME = 'nom_dest_vente'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE passager ADD COLUMN nom_dest_vente VARCHAR(255) NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'passager' AND COLUMN_NAME = 'code_gadest_vente'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE passager ADD COLUMN code_gadest_vente VARCHAR(64) NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
