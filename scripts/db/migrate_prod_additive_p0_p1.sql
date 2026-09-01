-- =============================================================================
-- Migrations additives PROD (P0 + P1) — billetterie Rakieta / ticket
-- =============================================================================
-- Usage (exemple, depuis le worktree prod) :
--   mysql -u <user> -p rakieta < scripts/db/migrate_prod_additive_p0_p1.sql
--   # ou via scripts/deploy_prod.sh
--
-- Règles :
--   - Uniquement CREATE TABLE IF NOT EXISTS / ADD COLUMN IF NOT EXISTS
--   - CREATE INDEX IF NOT EXISTS (MariaDB) pour idsousgare_prog
--   - Idempotent : relançable sans effet de bord
--   - NE PAS y ajouter de DELETE / DROP / seed / UPDATE métier
--
-- Hors scope (ne pas mélanger ici) :
--   - migrate_itineraire_etapes.php (DELETE + repopulate)
--   - tables antifraude / sales_price / super_admin (flags host off en prod)
-- =============================================================================

-- -----------------------------------------------------------------------------
-- P0.1 — Sous-gare programmes (bloquant listes getall / filtres)
-- -----------------------------------------------------------------------------
ALTER TABLE programme
  ADD COLUMN IF NOT EXISTS idsousgare_prog INT NULL DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_programme_idsousgare_prog
  ON programme (idsousgare_prog);

CREATE TABLE IF NOT EXISTS programme_sousgare (
  code_progr VARCHAR(80) NOT NULL,
  idsousgare INT NOT NULL,
  PRIMARY KEY (code_progr, idsousgare),
  KEY idx_psg_sg (idsousgare)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  COMMENT='Sous-gares autorisees sur un depart; aucune ligne = legacy idsousgare_prog';

-- -----------------------------------------------------------------------------
-- P0.2 — Correspondances (pas d’auto-CREATE au runtime)
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
-- P0.3 — Reconduction / sortie
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
-- P1.1 — OD final passager (rapports transit)
-- -----------------------------------------------------------------------------
ALTER TABLE passager
  ADD COLUMN IF NOT EXISTS itinecode_vendu VARCHAR(128) NULL,
  ADD COLUMN IF NOT EXISTS lignetineraire_vendu VARCHAR(255) NULL;

-- -----------------------------------------------------------------------------
-- P1.2 — Vente escale
-- -----------------------------------------------------------------------------
ALTER TABLE passager
  ADD COLUMN IF NOT EXISTS id_escale_vente INT UNSIGNED NULL;

CREATE TABLE IF NOT EXISTS itineraire_escales (
  id_escale INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_lignes VARCHAR(64) NOT NULL COMMENT 'Itinéraire / ligne parent',
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
-- P1.3 — Étapes déclaratives (coquille vide pour verifchemins)
--     Ne pas exécuter migrate_itineraire_etapes.php (DELETE + seed).
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
