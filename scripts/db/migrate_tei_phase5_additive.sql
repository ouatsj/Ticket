-- TEI Phase 5 — préférences notifications + anti-doublon alertes
-- Idempotent.

CREATE TABLE IF NOT EXISTS tei_notification_prefs (
  cpuser_id INT NOT NULL,
  cat_depart TINYINT(1) NOT NULL DEFAULT 1,
  cat_finance TINYINT(1) NOT NULL DEFAULT 1,
  cat_courrier TINYINT(1) NOT NULL DEFAULT 1,
  cat_bagages TINYINT(1) NOT NULL DEFAULT 1,
  cat_systeme TINYINT(1) NOT NULL DEFAULT 1,
  urgentes_seulement TINYINT(1) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (cpuser_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Index utile pour lister les alertes récentes (la contrainte unique historique
-- (alert_key, emitted_at) reste ; on dédoublonne aussi en application).
CREATE TABLE IF NOT EXISTS tei_alert_dedupe (
  alert_key VARCHAR(191) NOT NULL,
  ekey INT NOT NULL,
  first_emitted_at DATETIME NOT NULL,
  last_emitted_at DATETIME NOT NULL,
  PRIMARY KEY (alert_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
