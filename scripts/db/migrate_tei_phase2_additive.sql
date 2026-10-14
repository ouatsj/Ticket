-- TEI Phase 2 — schéma additif (idempotent)
-- Rôle décideur mobile + tables service API séparée.
-- Ne contient aucun DML métier Ticket (ventes/caisse).
-- PHP/MySQL 5.7+ / MariaDB compatibles.

-- ---------------------------------------------------------------------------
-- 1) Rôle dédié « Decideur » (id 21) — pas de vue guichet
-- ---------------------------------------------------------------------------
INSERT INTO user_roles (id_rols, type_rols, created_at)
SELECT 21, 'Decideur', UNIX_TIMESTAMP()
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM user_roles WHERE id_rols = 21);

-- ---------------------------------------------------------------------------
-- 2) Tables TEI (préfixe tei_) — utilisées uniquement par services/tei_api
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tei_decideur_scope (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cpuser_id INT NOT NULL COMMENT 'compte_user.cpuser_id',
  ekey INT NOT NULL COMMENT 'entreprise.ekey',
  cle_compagnie INT NULL COMMENT 'NULL = toutes les compagnies de ekey',
  actif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tei_scope_user (cpuser_id),
  KEY idx_tei_scope_ekey (ekey),
  KEY idx_tei_scope_cie (cle_compagnie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tei_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cpuser_id INT NOT NULL,
  token_hash CHAR(64) NOT NULL COMMENT 'SHA-256 du refresh token',
  access_jti CHAR(36) NOT NULL,
  expires_at DATETIME NOT NULL,
  revoked_at DATETIME NULL,
  user_agent VARCHAR(255) NULL,
  ip_addr VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tei_token_hash (token_hash),
  KEY idx_tei_tokens_user (cpuser_id),
  KEY idx_tei_tokens_exp (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tei_devices (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  cpuser_id INT NOT NULL,
  platform ENUM('android','ios','other') NOT NULL DEFAULT 'other',
  fcm_token VARCHAR(512) NOT NULL,
  app_version VARCHAR(32) NULL,
  last_seen_at DATETIME NULL,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tei_fcm (fcm_token(191)),
  KEY idx_tei_devices_user (cpuser_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tei_alerts_emises (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  alert_key VARCHAR(191) NOT NULL COMMENT 'ex: occupancy:CODE_PROGR:90',
  ekey INT NOT NULL,
  cle_compagnie INT NULL,
  code_progr VARCHAR(128) NULL,
  niveau ENUM('info','vigilance','critique') NOT NULL DEFAULT 'info',
  categorie ENUM('depart','finance','courrier','bagages','systeme') NOT NULL,
  titre VARCHAR(160) NOT NULL,
  corps TEXT NOT NULL,
  payload_json TEXT NULL,
  emitted_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tei_alert_key_day (alert_key, emitted_at),
  KEY idx_tei_alerts_ekey (ekey),
  KEY idx_tei_alerts_emitted (emitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tei_alert_thresholds (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ekey INT NULL COMMENT 'NULL = défaut global',
  cle_compagnie INT NULL,
  ident_ligne VARCHAR(30) NULL,
  fill_orange_pct TINYINT UNSIGNED NOT NULL DEFAULT 80,
  fill_priority_pct TINYINT UNSIGNED NOT NULL DEFAULT 90,
  low_fill_pct TINYINT UNSIGNED NOT NULL DEFAULT 40,
  minutes_before_depart INT NOT NULL DEFAULT 60,
  actif TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_tei_thresh_ekey (ekey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO tei_alert_thresholds (ekey, fill_orange_pct, fill_priority_pct, low_fill_pct, minutes_before_depart)
SELECT NULL, 80, 90, 40, 60
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tei_alert_thresholds WHERE ekey IS NULL AND cle_compagnie IS NULL AND ident_ligne IS NULL);
