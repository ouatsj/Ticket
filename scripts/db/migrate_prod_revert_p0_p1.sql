-- =============================================================================
-- Revert schéma additif PROD (inverse de migrate_prod_additive_p0_p1.sql)
-- =============================================================================
-- Usage :
--   mysql … rakieta < scripts/db/migrate_prod_revert_p0_p1.sql
--   # ou via : bash scripts/rollback_prod.sh --apply --with-schema
--
-- Règles :
--   - Uniquement DROP TABLE IF EXISTS / DROP COLUMN IF EXISTS / DROP INDEX IF EXISTS
--   - Idempotent
--   - Ne touche PAS aux tables métier historiques (passager hors nouvelles colonnes,
--     programme hors idsousgare_prog, etc.)
--
-- Attention :
--   - Perte des données éventuelles dans les objets P0/P1 (liens correspondance,
--     reconductions, escales, portées sous-gare, OD final, id_escale_vente)
--   - Pour un retour DB *exact* pré-deploy (toutes tables), préférer le dump
--     via rollback_prod.sh --with-db --i-understand-data-loss
-- =============================================================================

-- P1.3 / P1.2 tables
DROP TABLE IF EXISTS itineraire_etapes;
DROP TABLE IF EXISTS itineraire_escales;

-- P1 colonnes passager
ALTER TABLE passager
  DROP COLUMN IF EXISTS id_escale_vente,
  DROP COLUMN IF EXISTS lignetineraire_vendu,
  DROP COLUMN IF EXISTS itinecode_vendu;

-- P0.3 reconduction / sortie
DROP TABLE IF EXISTS programme_sortie_siege;
DROP TABLE IF EXISTS programme_reconduction_siege;
DROP TABLE IF EXISTS programme_reconduction;
DROP TABLE IF EXISTS programme_sortie;

-- P0.2 correspondance
DROP TABLE IF EXISTS programme_correspondance;

-- P0.1 sous-gare
DROP TABLE IF EXISTS programme_sousgare;
DROP INDEX IF EXISTS idx_programme_idsousgare_prog ON programme;
ALTER TABLE programme
  DROP COLUMN IF EXISTS idsousgare_prog;
