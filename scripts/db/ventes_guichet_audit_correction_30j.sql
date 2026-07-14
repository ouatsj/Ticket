-- =============================================================================
-- Audit & correction des ventes guichet mal attribuées (30 derniers jours)
-- Projet : ticket.rakietabus.com (CodeIgniter 3)
--
-- Contexte :
--   passager.idcptuser doit contenir attributions_role.roleattribut (guichet),
--   PAS compte_user.cpuser_id (compte login).
--   Bug corrigé dans Programmes::_sale_role_attribut_id() — ce script répare l'historique.
--
-- Exécution recommandée :
--   1) Lancer uniquement la PHASE 1 (audit) en lecture seule
--   2) Vérifier les résultats avec l'équipe métier
--   3) PHASE 2 : sauvegarde
--   4) PHASE 3 : prévisualisation (SELECT)
--   5) PHASE 4 : correction dans une transaction, après validation
--
--   mysql -u USER -p DATABASE < scripts/db/ventes_guichet_audit_correction_30j.sql
--   (ou copier-coller section par section dans phpMyAdmin)
-- =============================================================================

SET @date_debut = DATE_SUB(CURDATE(), INTERVAL 30 DAY);
SET @date_fin   = CURDATE();

-- Rôles guichet susceptibles de vendre des billets (ajuster si besoin)
-- 1 = guichet complet, 2 = guichet, 6 = vente
SET @roles_vente = '1,2,6';

SELECT CONCAT('Période audit : ', @date_debut, ' → ', @date_fin) AS info_periode;


-- =============================================================================
-- PHASE 1 — AUDIT (lecture seule)
-- =============================================================================

-- 1.1 Synthèse globale
SELECT '1.1 Synthèse globale' AS section;

SELECT
    COUNT(*) AS total_ventes_periode
FROM passager p
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin;

-- Ventes dont idcptuser ne correspond à AUCUNE attribution (orphelines)
SELECT
    COUNT(*) AS ventes_orphelines
FROM passager p
LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND ar.roleattribut IS NULL;

-- Ventes où idcptuser = cpuser_id (bug _sale_cpuser_id) — cas le plus fréquent
SELECT
    COUNT(*) AS ventes_idcptuser_est_cpuser_id
FROM passager p
JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND ar.roleattribut IS NULL;


-- 1.2 Détail des ventes orphelines (idcptuser absent de attributions_role)
SELECT '1.2 Ventes orphelines (détail)' AS section;

SELECT
    p.code_passager,
    p.code_ticket,
    p.datep_create,
    p.idcptuser AS id_enregistre,
    cu.username  AS si_cpuser_id,
    sg.nomsousgare,
    g.garenom,
    g.idengare
FROM passager p
LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
LEFT JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
LEFT JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
LEFT JOIN gares g ON g.idengare = sg.gareprinceid
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND ar.roleattribut IS NULL
ORDER BY p.datep_create DESC, p.createpas_at DESC
LIMIT 200;


-- 1.3 Collisions numériques : idcptuser = roleattribut de A ET cpuser_id de B (A ≠ B)
-- ATTENTION : ce comptage est INFORMATIF — beaucoup de collisions sont normales
-- (deux agents différents peuvent avoir des numéros qui se croisent sans erreur).
-- Seule la section 1.3b (suffixe incohérent) indique des ventes probablement mal attribuées.
SELECT '1.3 Collisions numériques (informatif — pas toutes sont des erreurs)' AS section;

SELECT
    p.code_passager,
    p.code_ticket,
    p.datep_create,
    p.idcptuser,
    cu_attr.username   AS guichetier_roleattribut,
    cu_attr.cpuser_id  AS cpuser_du_roleattribut,
    cu_coll.username   AS autre_guichetier_meme_nombre,
    g.garenom,
    sg.nomsousgare,
    CASE
        WHEN p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_attr.username, 1)), p.idcptuser)
            THEN 'suffixe_coherent_roleattribut'
        WHEN p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_coll.username, 1)), p.idcptuser)
            THEN 'suffixe_coherent_cpuser_collision'
        ELSE 'suffixe_ambigu'
    END AS analyse_suffixe_ticket
FROM passager p
JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
JOIN user_login ul_ar ON ul_ar.uid_login = ar.idgestcompte
JOIN compte_user cu_attr ON cu_attr.cpuser_id = ul_ar.uid_usercpte
JOIN compte_user cu_coll ON cu_coll.cpuser_id = p.idcptuser
    AND cu_coll.cpuser_id <> cu_attr.cpuser_id
LEFT JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
LEFT JOIN gares g ON g.idengare = sg.gareprinceid
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
ORDER BY p.datep_create DESC
LIMIT 200;


-- 1.3b Ventes PROBABLEMENT mal attribuées : suffixe ticket ≠ guichetier du roleattribut
-- Le code ticket se termine par {1ère lettre login}{id utilisé à la vente}.
-- Si ce suffixe pointe vers un autre guichetier (même gare), la vente est suspecte.
SELECT '1.3b Suffixe ticket incohérent (candidats correction TYPE C)' AS section;

SELECT
    COUNT(*) AS ventes_suffixe_incoherent
FROM passager p
JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND p.code_ticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), p.idcptuser);

SELECT
    p.code_passager,
    p.code_ticket,
    p.datep_create,
    p.idcptuser AS roleattribut_actuel,
    cu.username AS guichetier_actuel,
    cu_real.username AS guichetier_suffixe,
    ar_fix.roleattribut AS roleattribut_corrige,
    g.garenom
FROM passager p
JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
JOIN compte_user cu_real
    ON p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_real.username, 1)), cu_real.cpuser_id)
JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
JOIN gares g ON g.idengare = sg.gareprinceid
JOIN user_login ul_real
    ON ul_real.uid_usercpte = cu_real.cpuser_id
    AND ul_real.guser = g.idengare
JOIN attributions_role ar_fix
    ON ar_fix.idgestcompte = ul_real.uid_login
    AND ar_fix.activer_role = 0
    AND FIND_IN_SET(ar_fix.userole, @roles_vente)
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND cu_real.cpuser_id <> cu.cpuser_id
  AND p.code_ticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), p.idcptuser)
  AND ar_fix.roleattribut <> p.idcptuser
ORDER BY p.datep_create DESC
LIMIT 200;


-- 1.4 Compteurs guichetiers impactés (écarts avant correction)
SELECT '1.4 Écart compteur par guichetier (attendu vs enregistré)' AS section;

SELECT
    ar.roleattribut,
    cu.username,
    g.garenom,
    COUNT(p_ok.code_passager)  AS ventes_bien_attribuees,
    COUNT(p_bad.code_passager) AS ventes_orphelines_meme_cpuser
FROM attributions_role ar
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
JOIN gares g ON g.idengare = ul.guser
LEFT JOIN passager p_ok ON p_ok.idcptuser = ar.roleattribut
    AND p_ok.statut_code = 'vendu'
    AND p_ok.datep_create BETWEEN @date_debut AND @date_fin
LEFT JOIN passager p_bad ON p_bad.idcptuser = cu.cpuser_id
    AND p_bad.statut_code = 'vendu'
    AND p_bad.datep_create BETWEEN @date_debut AND @date_fin
    AND NOT EXISTS (
        SELECT 1 FROM attributions_role ar2
        WHERE ar2.roleattribut = p_bad.idcptuser
    )
WHERE ar.activer_role = 0
  AND FIND_IN_SET(ar.userole, @roles_vente)
GROUP BY ar.roleattribut, cu.username, g.garenom
HAVING ventes_orphelines_meme_cpuser > 0
ORDER BY ventes_orphelines_meme_cpuser DESC;


-- 1.5 Audit non_passager (retours) — champ cptus = roleattribut
SELECT '1.5 Retours (non_passager.cptus) orphelins' AS section;

SELECT
    COUNT(*) AS retours_orphelins
FROM non_passager np
LEFT JOIN attributions_role ar ON ar.roleattribut = np.cptus
WHERE np.datevente BETWEEN @date_debut AND @date_fin
  AND ar.roleattribut IS NULL;


-- 1.6 Audit bagages — champ idoperabagage = roleattribut
SELECT '1.6 Bagages (idoperabagage) orphelins' AS section;

SELECT
    COUNT(*) AS bagages_orphelins
FROM bagages bg
LEFT JOIN attributions_role ar ON ar.roleattribut = bg.idoperabagage
WHERE bg.date_create BETWEEN @date_debut AND @date_fin
  AND ar.roleattribut IS NULL;


-- =============================================================================
-- PHASE 2 — SAUVEGARDE (exécuter avant toute correction)
-- =============================================================================

-- Décommenter les lignes suivantes après validation de la PHASE 1 :

-- CREATE TABLE IF NOT EXISTS passager_backup_idcptuser_30j AS
-- SELECT p.*
-- FROM passager p
-- WHERE p.statut_code = 'vendu'
--   AND p.datep_create BETWEEN @date_debut AND @date_fin;

-- CREATE TABLE IF NOT EXISTS non_passager_backup_cptus_30j AS
-- SELECT np.*
-- FROM non_passager np
-- WHERE np.datevente BETWEEN @date_debut AND @date_fin;

-- CREATE TABLE IF NOT EXISTS bagages_backup_operateur_30j AS
-- SELECT bg.*
-- FROM bagages bg
-- WHERE bg.date_create BETWEEN @date_debut AND @date_fin;


-- =============================================================================
-- PHASE 3 — PRÉVISUALISATION DES CORRECTIONS (SELECT uniquement)
-- =============================================================================

SELECT '3.1 Prévisualisation corrections passager (TYPE A : idcptuser = cpuser_id)' AS section;

-- Résolution : cpuser_id + gare de vente (sousgare → gareprinceid) → roleattribut
SELECT
    p.code_passager,
    p.code_ticket,
    p.datep_create,
    p.idcptuser                    AS ancien_idcptuser,
    ar_fix.roleattribut            AS nouveau_idcptuser,
    cu.username                    AS guichetier,
    g.garenom,
    sg.nomsousgare,
    ar_fix.userole,
    CASE
        WHEN ar_fix.roleattribut IS NULL THEN 'PAS_DE_CORRECTION_TROUVEE'
        WHEN ar_fix.roleattribut = p.idcptuser THEN 'DEJA_OK'
        ELSE 'A_CORRIGER'
    END AS action
FROM passager p
JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = p.idcptuser
JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
JOIN gares g ON g.idengare = sg.gareprinceid
JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
    AND ul.guser = g.idengare
JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
    AND ar_fix.activer_role = 0
    AND FIND_IN_SET(ar_fix.userole, @roles_vente)
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND ar_bad.roleattribut IS NULL
ORDER BY p.datep_create DESC;


-- Prévisualisation collisions (TYPE B) : réattribuer selon suffixe ticket
SELECT '3.2 Prévisualisation collisions (suffixe ticket)' AS section;

SELECT
    p.code_passager,
    p.code_ticket,
    p.datep_create,
    p.idcptuser AS ancien_idcptuser,
    CASE
        WHEN p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_coll.username, 1)), p.idcptuser)
             AND ar_coll.roleattribut IS NOT NULL
            THEN ar_coll.roleattribut
        ELSE p.idcptuser
    END AS nouveau_idcptuser_propose,
    cu_attr.username AS proprietaire_actuel_roleattribut,
    cu_coll.username AS proprietaire_cpuser_id_collision,
    g.garenom
FROM passager p
JOIN attributions_role ar_attr ON ar_attr.roleattribut = p.idcptuser
JOIN user_login ul_attr ON ul_attr.uid_login = ar_attr.idgestcompte
JOIN compte_user cu_attr ON cu_attr.cpuser_id = ul_attr.uid_usercpte
JOIN compte_user cu_coll ON cu_coll.cpuser_id = p.idcptuser
    AND cu_coll.cpuser_id <> cu_attr.cpuser_id
LEFT JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
LEFT JOIN gares g ON g.idengare = sg.gareprinceid
LEFT JOIN user_login ul_coll ON ul_coll.uid_usercpte = cu_coll.cpuser_id
    AND ul_coll.guser = g.idengare
LEFT JOIN attributions_role ar_coll ON ar_coll.idgestcompte = ul_coll.uid_login
    AND ar_coll.activer_role = 0
    AND FIND_IN_SET(ar_coll.userole, @roles_vente)
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_coll.username, 1)), p.idcptuser)
  AND ar_coll.roleattribut IS NOT NULL
  AND ar_coll.roleattribut <> p.idcptuser
ORDER BY p.datep_create DESC;


-- 3.3 TYPE C : suffixe ticket pointe vers un autre guichetier (cpuser_id + gare)
SELECT '3.3 Prévisualisation corrections TYPE C (suffixe ticket)' AS section;

SELECT
    p.code_passager,
    p.code_ticket,
    p.datep_create,
    p.idcptuser                    AS ancien_roleattribut,
    ar_fix.roleattribut            AS nouveau_roleattribut,
    cu.username                    AS guichetier_actuel,
    cu_real.username               AS guichetier_reel_suffixe,
    g.garenom
FROM passager p
JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
JOIN user_login ul ON ul.uid_login = ar.idgestcompte
JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
JOIN compte_user cu_real
    ON p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_real.username, 1)), cu_real.cpuser_id)
JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
JOIN gares g ON g.idengare = sg.gareprinceid
JOIN user_login ul_real
    ON ul_real.uid_usercpte = cu_real.cpuser_id
    AND ul_real.guser = g.idengare
JOIN attributions_role ar_fix
    ON ar_fix.idgestcompte = ul_real.uid_login
    AND ar_fix.activer_role = 0
    AND FIND_IN_SET(ar_fix.userole, @roles_vente)
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND cu_real.cpuser_id <> cu.cpuser_id
  AND p.code_ticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), p.idcptuser)
  AND ar_fix.roleattribut <> p.idcptuser
ORDER BY p.datep_create DESC;


-- =============================================================================
-- PHASE 4 — CORRECTION (décommenter après validation PHASE 3)
-- =============================================================================

-- START TRANSACTION;

-- 4.1 TYPE A : idcptuser était un cpuser_id → remplacer par roleattribut de la gare
-- UPDATE passager p
-- JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
-- LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = p.idcptuser
-- JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
-- JOIN gares g ON g.idengare = sg.gareprinceid
-- JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
--     AND ul.guser = g.idengare
-- JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
--     AND ar_fix.activer_role = 0
--     AND FIND_IN_SET(ar_fix.userole, @roles_vente)
-- SET p.idcptuser = ar_fix.roleattribut
-- WHERE p.statut_code = 'vendu'
--   AND p.datep_create BETWEEN @date_debut AND @date_fin
--   AND ar_bad.roleattribut IS NULL
--   AND ar_fix.roleattribut IS NOT NULL
--   AND ar_fix.roleattribut <> p.idcptuser;

-- SELECT ROW_COUNT() AS lignes_passager_corrigees_type_a;


-- 4.2 TYPE B : collisions résolues par suffixe ticket (O37 → roleattribut Odette, etc.)
-- UPDATE passager p
-- JOIN attributions_role ar_attr ON ar_attr.roleattribut = p.idcptuser
-- JOIN user_login ul_attr ON ul_attr.uid_login = ar_attr.idgestcompte
-- JOIN compte_user cu_attr ON cu_attr.cpuser_id = ul_attr.uid_usercpte
-- JOIN compte_user cu_coll ON cu_coll.cpuser_id = p.idcptuser
--     AND cu_coll.cpuser_id <> cu_attr.cpuser_id
-- JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
-- JOIN gares g ON g.idengare = sg.gareprinceid
-- JOIN user_login ul_coll ON ul_coll.uid_usercpte = cu_coll.cpuser_id
--     AND ul_coll.guser = g.idengare
-- JOIN attributions_role ar_coll ON ar_coll.idgestcompte = ul_coll.uid_login
--     AND ar_coll.activer_role = 0
--     AND FIND_IN_SET(ar_coll.userole, @roles_vente)
-- SET p.idcptuser = ar_coll.roleattribut
-- WHERE p.statut_code = 'vendu'
--   AND p.datep_create BETWEEN @date_debut AND @date_fin
--   AND p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_coll.username, 1)), p.idcptuser)
--   AND ar_coll.roleattribut IS NOT NULL
--   AND ar_coll.roleattribut <> p.idcptuser;

-- SELECT ROW_COUNT() AS lignes_passager_corrigees_type_b;


-- 4.3 TYPE C : réattribuer selon suffixe ticket + gare de vente
-- UPDATE passager p
-- JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
-- JOIN user_login ul ON ul.uid_login = ar.idgestcompte
-- JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
-- JOIN compte_user cu_real
--     ON p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_real.username, 1)), cu_real.cpuser_id)
-- JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
-- JOIN gares g ON g.idengare = sg.gareprinceid
-- JOIN user_login ul_real
--     ON ul_real.uid_usercpte = cu_real.cpuser_id
--     AND ul_real.guser = g.idengare
-- JOIN attributions_role ar_fix
--     ON ar_fix.idgestcompte = ul_real.uid_login
--     AND ar_fix.activer_role = 0
--     AND FIND_IN_SET(ar_fix.userole, @roles_vente)
-- SET p.idcptuser = ar_fix.roleattribut
-- WHERE p.statut_code = 'vendu'
--   AND p.datep_create BETWEEN @date_debut AND @date_fin
--   AND cu_real.cpuser_id <> cu.cpuser_id
--   AND p.code_ticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), p.idcptuser)
--   AND ar_fix.roleattribut <> p.idcptuser;

-- SELECT ROW_COUNT() AS lignes_passager_corrigees_type_c;


-- 4.4 non_passager (retours) — même logique TYPE A sur cptus
-- UPDATE non_passager np
-- JOIN compte_user cu ON cu.cpuser_id = np.cptus
-- LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = np.cptus
-- JOIN sousgare sg ON sg.idsousgare = np.sousgareidentif
-- JOIN gares g ON g.idengare = sg.gareprinceid
-- JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
--     AND ul.guser = g.idengare
-- JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
--     AND ar_fix.activer_role = 0
--     AND FIND_IN_SET(ar_fix.userole, @roles_vente)
-- SET np.cptus = ar_fix.roleattribut
-- WHERE np.datevente BETWEEN @date_debut AND @date_fin
--   AND ar_bad.roleattribut IS NULL
--   AND ar_fix.roleattribut IS NOT NULL
--   AND ar_fix.roleattribut <> np.cptus;

-- SELECT ROW_COUNT() AS lignes_non_passager_corrigees;


-- 4.5 bagages — même logique sur idoperabagage
-- UPDATE bagages bg
-- JOIN compte_user cu ON cu.cpuser_id = bg.idoperabagage
-- LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = bg.idoperabagage
-- JOIN sousgare sg ON sg.idsousgare = bg.sousgarebag
-- JOIN gares g ON g.idengare = sg.gareprinceid
-- JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id
--     AND ul.guser = g.idengare
-- JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
--     AND ar_fix.activer_role = 0
-- SET bg.idoperabagage = ar_fix.roleattribut
-- WHERE bg.date_create BETWEEN @date_debut AND @date_fin
--   AND ar_bad.roleattribut IS NULL
--   AND ar_fix.roleattribut IS NOT NULL
--   AND ar_fix.roleattribut <> bg.idoperabagage;

-- SELECT ROW_COUNT() AS lignes_bagages_corrigees;


-- COMMIT;
-- /* En cas de doute : ROLLBACK; */


-- =============================================================================
-- PHASE 5 — VÉRIFICATION POST-CORRECTION
-- =============================================================================

SELECT '5.1 Vérification : ventes orphelines restantes' AS section;

SELECT
    COUNT(*) AS ventes_orphelines_restantes
FROM passager p
LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND ar.roleattribut IS NULL;

SELECT '5.2 Cas non résolus automatiquement (revue manuelle)' AS section;

SELECT
    p.code_passager,
    p.code_ticket,
    p.datep_create,
    p.idcptuser,
    cu.username,
    g.garenom,
    sg.nomsousgare
FROM passager p
LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
LEFT JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
LEFT JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
LEFT JOIN gares g ON g.idengare = sg.gareprinceid
WHERE p.statut_code = 'vendu'
  AND p.datep_create BETWEEN @date_debut AND @date_fin
  AND ar.roleattribut IS NULL
ORDER BY p.datep_create DESC;
