-- Verrou déclaration par gare (compagnie + famille + mois + code_gare).
-- Aussi appliqué au runtime par etat_lettre_ensure_declaration_gare().

ALTER TABLE etat_lettre_declaration
    ADD COLUMN IF NOT EXISTS code_gare VARCHAR(32) NOT NULL DEFAULT '' AFTER mois;

-- MySQL < 8 : si ADD COLUMN IF NOT EXISTS échoue, utiliser le helper PHP.

ALTER TABLE etat_lettre_declaration DROP INDEX uq_etat_lettre_declaration;
ALTER TABLE etat_lettre_declaration
    ADD UNIQUE KEY uq_etat_lettre_declaration
    (entreprise_ekey, cle_compagnie, famille, mois, code_gare);
