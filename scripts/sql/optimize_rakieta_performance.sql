-- Optimisation performances MySQL — rakieta
-- À exécuter en heure creuse (ALTER TABLE passager ≈ 2,2 M lignes, peut prendre plusieurs minutes).
-- Toujours faire une sauvegarde avant : mysqldump rakieta > backup_avant_index.sql

USE rakieta;

-- =============================================================================
-- 1. DIAGNOSTIC (lecture seule)
-- =============================================================================

-- Requêtes lentes en cours
-- SHOW FULL PROCESSLIST;

-- Taille des tables
-- SELECT table_name, table_rows,
--        ROUND(data_length/1024/1024,1) AS data_mb,
--        ROUND(index_length/1024/1024,1) AS idx_mb
-- FROM information_schema.tables
-- WHERE table_schema = 'rakieta'
-- ORDER BY data_length DESC LIMIT 10;

-- Test EXPLAIN (doit utiliser idx_passager_compte après création)
-- EXPLAIN SELECT SUM(prixvente) FROM passager p
-- WHERE p.idcptuser = 287
--   AND p.statut_code = 'vendu'
--   AND p.statutvente = 0
--   AND p.datep_create <= CURDATE();


-- =============================================================================
-- 2. INDEX CRITIQUES — passager (compte / arrêt guichet)
-- =============================================================================
-- Problème : MySQL scanne ~1,1 M lignes via idx_passager_statut ('vendu')
-- car idcptuser n'est pas indexé. Une requête compteur prend ~10 s.

ALTER TABLE passager
    ADD INDEX idx_passager_compte (idcptuser, statut_code, statutvente, datep_create);

-- Variante si les pages filtrent aussi par sous-gare :
-- ALTER TABLE passager
--     ADD INDEX idx_passager_compte_sg (idcptuser, statut_code, statutvente, datep_create, departclient_idgare);


-- =============================================================================
-- 3. INDEX — non_passager (retours)
-- =============================================================================

ALTER TABLE non_passager
    ADD INDEX idx_non_passager_compte (cptus, statvente, datevente);


-- =============================================================================
-- 4. INDEX — recette / dépense (caisse chef guichet, arrêt RD)
-- =============================================================================

ALTER TABLE recette
    ADD INDEX idx_recette_arret_cg (is_actifrecet, date_recet, idopera),
    ADD INDEX idx_recette_arret_valid (is_actifrecet, date_recet, operavalid),
    ADD INDEX idx_recette_caisse (idcaisse, recetsgid, date_recet);

ALTER TABLE depense
    ADD INDEX idx_depense_arret_cg (is_actifdep, date_depens, idop_dep),
    ADD INDEX idx_depense_arret_valid (is_actifdep, date_depens, opevalid),
    ADD INDEX idx_depense_caisse (idcaisse_depens, sousgidepens, date_depens);


-- =============================================================================
-- 5. INDEX — attributions_role (connexion gare)
-- =============================================================================

ALTER TABLE attributions_role
    ADD INDEX idx_ar_user_gare_active (userole, activer_role, activeattrib);


-- =============================================================================
-- 6. MAINTENANCE après création des index
-- =============================================================================

ANALYZE TABLE passager, non_passager, recette, depense, attributions_role;


-- =============================================================================
-- 7. INDEX REDONDANTS (optionnel — à valider avant suppression)
-- =============================================================================
-- passager : ~1,8 Go d'index pour ~280 Mo de données. Doublons possibles :
--
-- ALTER TABLE passager DROP INDEX idx_passager_date;          -- doublon de idx_passager_datep_create
-- ALTER TABLE passager DROP INDEX idx_passager_codepro;       -- doublon de code_pro
-- ALTER TABLE passager DROP INDEX idx_passager_prix;          -- faible sélectivité
-- ALTER TABLE passager DROP INDEX idx_passager_statut;        -- cardinality = 1, inutile


-- =============================================================================
-- 8. JOURNAL DES REQUÊTES LENTES (my.cnf ou fichier dédié)
-- =============================================================================
-- slow_query_log = 1
-- slow_query_log_file = /var/log/mysql/slow.log
-- long_query_time = 2
-- log_queries_not_using_indexes = 1
