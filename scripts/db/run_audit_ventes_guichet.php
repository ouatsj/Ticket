#!/usr/bin/env php
<?php
/**
 * Lance uniquement la PHASE 1 (audit lecture seule) des ventes guichet mal attribuées.
 *
 * Connexions MySQL : 1 seule (bootstrap partagé). Ne jamais lancer en boucle
 * ni en parallèle contre une base distante (quota 500 connexions/h Hostinger).
 *
 * Usage : php scripts/db/run_audit_ventes_guichet.php
 *         php scripts/db/run_audit_ventes_guichet.php --jours=30
 */

require __DIR__ . '/_bootstrap.php';

$jours = db_script_jours_arg($argv, 30);
$mysqli = db_script_connect($argv);

$dateDebut = date('Y-m-d', strtotime("-{$jours} days"));
$dateFin = date('Y-m-d');
$rolesVente = '1,2,6';

echo "=== Audit ventes guichet (lecture seule) ===\n";
echo "Période : {$dateDebut} → {$dateFin}\n\n";

function qcount(mysqli $db, string $sql): int
{
    $r = $db->query($sql);
    if (!$r) {
        fwrite(STDERR, "Erreur SQL : {$db->error}\n");
        return -1;
    }
    $row = $r->fetch_row();

    return (int) $row[0];
}

function qrows(mysqli $db, string $sql, int $limit = 20): void
{
    $r = $db->query($sql);
    if (!$r) {
        fwrite(STDERR, "Erreur SQL : {$db->error}\n");
        return;
    }
    $n = 0;
    while ($row = $r->fetch_assoc()) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        if (++$n >= $limit) {
            break;
        }
    }
    if ($n === 0) {
        echo "(aucun résultat)\n";
    }
}

$escDebut = $mysqli->real_escape_string($dateDebut);
$escFin = $mysqli->real_escape_string($dateFin);

$total = qcount($mysqli, "
    SELECT COUNT(*) FROM passager p
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
");
$orphelines = qcount($mysqli, "
    SELECT COUNT(*) FROM passager p
    LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND ar.roleattribut IS NULL
");
$cpuserBug = qcount($mysqli, "
    SELECT COUNT(*) FROM passager p
    JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
    LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND ar.roleattribut IS NULL
");

echo "1. Synthèse\n";
echo "   Total ventes        : {$total}\n";
echo "   Orphelines          : {$orphelines}\n";
echo "   idcptuser=cpuser_id : {$cpuserBug}\n\n";

echo "2. Exemples orphelines (max 15)\n";
qrows($mysqli, "
    SELECT p.code_passager, p.code_ticket, p.datep_create, p.idcptuser,
           cu.username, g.garenom, sg.nomsousgare
    FROM passager p
    LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
    LEFT JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
    LEFT JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
    LEFT JOIN gares g ON g.idengare = sg.gareprinceid
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND ar.roleattribut IS NULL
    ORDER BY p.datep_create DESC
    LIMIT 15
", 15);

echo "\n3. Guichetiers avec ventes mal rangées (max 15)\n";
qrows($mysqli, "
    SELECT ar.roleattribut, cu.username, g.garenom,
           COUNT(p_bad.code_passager) AS ventes_mal_attribuees
    FROM attributions_role ar
    JOIN user_login ul ON ul.uid_login = ar.idgestcompte
    JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
    JOIN gares g ON g.idengare = ul.guser
    JOIN passager p_bad ON p_bad.idcptuser = cu.cpuser_id
        AND p_bad.statut_code = 'vendu'
        AND p_bad.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
        AND NOT EXISTS (
            SELECT 1 FROM attributions_role ar2
            WHERE ar2.roleattribut = p_bad.idcptuser
        )
    WHERE ar.activer_role = 0
      AND FIND_IN_SET(ar.userole, '{$rolesVente}')
    GROUP BY ar.roleattribut, cu.username, g.garenom
    ORDER BY ventes_mal_attribuees DESC
    LIMIT 15
", 15);

echo "\n4. Prévisualisation corrections TYPE A (max 15)\n";
qrows($mysqli, "
    SELECT p.code_passager, p.code_ticket, p.datep_create,
           p.idcptuser AS ancien, ar_fix.roleattribut AS nouveau,
           cu.username, g.garenom
    FROM passager p
    JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
    LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = p.idcptuser
    JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
    JOIN gares g ON g.idengare = sg.gareprinceid
    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id AND ul.guser = g.idengare
    JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
        AND ar_fix.activer_role = 0
        AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND ar_bad.roleattribut IS NULL
      AND ar_fix.roleattribut <> p.idcptuser
    ORDER BY p.datep_create DESC
    LIMIT 15
", 15);

$collisions = qcount($mysqli, "
    SELECT COUNT(*) FROM passager p
    JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
    JOIN user_login ul ON ul.uid_login = ar.idgestcompte
    JOIN compte_user cu_attr ON cu_attr.cpuser_id = ul.uid_usercpte
    JOIN compte_user cu_coll ON cu_coll.cpuser_id = p.idcptuser
        AND cu_coll.cpuser_id <> cu_attr.cpuser_id
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
");

echo "\n5. Collisions numériques cpuser_id = roleattribut autre personne : {$collisions}\n";
if ($collisions > 0) {
    echo "   Exemples (max 10) :\n";
    qrows($mysqli, "
        SELECT p.code_ticket, p.datep_create, p.idcptuser,
               cu_attr.username AS roleattribut_de,
               cu_coll.username AS cpuser_id_de,
               g.garenom,
               CASE
                   WHEN p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_coll.username,1)), p.idcptuser)
                       THEN 'suffixe_pointe_vers_cpuser'
                   WHEN p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_attr.username,1)), p.idcptuser)
                       THEN 'suffixe_pointe_vers_roleattribut'
                   ELSE 'suffixe_ambigu'
               END AS analyse
        FROM passager p
        JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
        JOIN user_login ul ON ul.uid_login = ar.idgestcompte
        JOIN compte_user cu_attr ON cu_attr.cpuser_id = ul.uid_usercpte
        JOIN compte_user cu_coll ON cu_coll.cpuser_id = p.idcptuser
            AND cu_coll.cpuser_id <> cu_attr.cpuser_id
        LEFT JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
        LEFT JOIN gares g ON g.idengare = sg.gareprinceid
        WHERE p.statut_code = 'vendu'
          AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
        ORDER BY p.datep_create DESC
        LIMIT 10
    ", 10);
}

echo "\n6. TYPE C — suffixe ticket incohérent (candidats correction réelle)\n";
$typeC = qcount($mysqli, "
    SELECT COUNT(*) FROM passager p
    JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
    JOIN user_login ul ON ul.uid_login = ar.idgestcompte
    JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
    JOIN compte_user cu_real
        ON p.code_ticket LIKE CONCAT('%', UPPER(LEFT(cu_real.username, 1)), cu_real.cpuser_id)
    JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
    JOIN user_login ul_real
        ON ul_real.uid_usercpte = cu_real.cpuser_id
        AND ul_real.guser = sg.gareprinceid
    JOIN attributions_role ar_fix
        ON ar_fix.idgestcompte = ul_real.uid_login
        AND ar_fix.activer_role = 0
        AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND cu_real.cpuser_id <> cu.cpuser_id
      AND p.code_ticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), p.idcptuser)
      AND ar_fix.roleattribut <> p.idcptuser
");
echo "   Candidats TYPE C : {$typeC}\n";
if ($typeC > 0) {
    qrows($mysqli, "
        SELECT p.code_ticket, p.datep_create, p.idcptuser AS ancien,
               ar_fix.roleattribut AS nouveau, cu.username AS actuel,
               cu_real.username AS reel_suffixe, g.garenom
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
            AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
        WHERE p.statut_code = 'vendu'
          AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
          AND cu_real.cpuser_id <> cu.cpuser_id
          AND p.code_ticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), p.idcptuser)
          AND ar_fix.roleattribut <> p.idcptuser
        ORDER BY p.datep_create DESC
        LIMIT 10
    ", 10);
}

echo "\nScript SQL complet : scripts/db/ventes_guichet_audit_correction_30j.sql\n";
echo "Corrections : décommenter PHASE 2-4 du fichier SQL après validation.\n";
