-- Champs "OD final" pour le rapport journalier des ventes transit
-- Usage (exemple) : mysql -u <user> -p <database> < scripts/db/migrate_passager_od_final.sql

ALTER TABLE passager
    ADD COLUMN IF NOT EXISTS itinecode_vendu VARCHAR(128) NULL,
    ADD COLUMN IF NOT EXISTS lignetineraire_vendu VARCHAR(255) NULL;

