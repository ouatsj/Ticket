-- Règles arrêt de compte vendeur : dérogations admin + suivi activité
-- Exécuter une fois : mysql ... rakieta < scripts/db/migrate_compte_arret_regles.sql

ALTER TABLE compte_user
    ADD COLUMN IF NOT EXISTS autorisation_vente_forcee TINYINT(1) NOT NULL DEFAULT 0 AFTER activer,
    ADD COLUMN IF NOT EXISTS autorisation_vente_jusquau DATETIME NULL AFTER autorisation_vente_forcee,
    ADD COLUMN IF NOT EXISTS autorisation_vente_motif VARCHAR(255) NULL AFTER autorisation_vente_jusquau,
    ADD COLUMN IF NOT EXISTS autorisation_vente_par INT NULL AFTER autorisation_vente_motif,
    ADD COLUMN IF NOT EXISTS exempt_desactivation_auto TINYINT(1) NOT NULL DEFAULT 0 AFTER autorisation_vente_par,
    ADD COLUMN IF NOT EXISTS derniere_activite_at DATETIME NULL AFTER exempt_desactivation_auto;
