-- prix_escale_origine : segment escale → origine du parent (ex. Boromo–Ouaga)
-- prix_escale : segment escale → destination/terminus (ex. Boromo–Bobo)
SET @db := DATABASE();
SET @exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'itineraire_escales'
    AND COLUMN_NAME = 'prix_escale_origine'
);
SET @sql := IF(@exists = 0,
  'ALTER TABLE itineraire_escales ADD COLUMN prix_escale_origine DECIMAL(12,2) NULL DEFAULT NULL COMMENT ''Segment escale vers origine du parent'' AFTER prix_escale',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
