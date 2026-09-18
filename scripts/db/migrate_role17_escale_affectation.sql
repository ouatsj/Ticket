-- =============================================================================
-- Rôle 17 (Venteescal) : affectation ligne + escale de vente (additif)
-- =============================================================================
-- Usage :
--   mysql -u <user> -p rakieta < scripts/db/migrate_role17_escale_affectation.sql
--   # ou : php scripts/db/migrate_role17_escale_affectation.php
--
-- Règles :
--   - ADD COLUMN via information_schema + PREPARE (idempotent)
--   - Pas de DELETE / DROP / seed
--   - user_login.guser (gare d’affiliation) inchangé
--   - Colonnes NULL = comptes 17 non migrés → parcours manuel actuel
-- =============================================================================

SET @db := DATABASE();

-- Ligne affectée (ident_ligne / id_lignes)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'attributions_role'
    AND COLUMN_NAME = 'vente_escale_id_lignes'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE attributions_role ADD COLUMN vente_escale_id_lignes VARCHAR(64) NULL DEFAULT NULL COMMENT ''Rôle 17: ligne affectée pour vente escale''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Clé départ (ex. escale~123, origin~LIGNE, terminus~LIGNE)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'attributions_role'
    AND COLUMN_NAME = 'vente_escale_value'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE attributions_role ADD COLUMN vente_escale_value VARCHAR(160) NULL DEFAULT NULL COMMENT ''Rôle 17: valeur départ (escale~id / origin~…)''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Libellé affiché (header guichet / admin)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'attributions_role'
    AND COLUMN_NAME = 'vente_escale_label'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE attributions_role ADD COLUMN vente_escale_label VARCHAR(255) NULL DEFAULT NULL COMMENT ''Rôle 17: libellé escale de départ''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index utile pour retrouver les attributions 17 avec escale renseignée
SET @exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'attributions_role'
    AND INDEX_NAME = 'idx_ar_vente_escale_value'
);
SET @sql := IF(@exists = 0,
  'CREATE INDEX idx_ar_vente_escale_value ON attributions_role (vente_escale_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
