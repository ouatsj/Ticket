#!/usr/bin/env php
<?php
/**
 * Vérifie et corrige l'attribution des ventes aux guichetiers (idcptuser = roleattribut).
 *
 * Usage :
 *   php scripts/db/run_correction_comptes_guichet.php --jours=30              # audit + prévisualisation
 *   php scripts/db/run_correction_comptes_guichet.php --jours=30 --apply      # sauvegarde + correction
 */

$root = dirname(__DIR__, 2);
define('BASEPATH', $root . '/system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'production');

require $root . '/application/config/database.php';
require __DIR__ . '/_bootstrap.php';

$jours = db_script_jours_arg($argv, 30);
$apply = in_array('--apply', $argv, true);
$mysqli = db_script_connect($argv);

$dateDebut = date('Y-m-d', strtotime("-{$jours} days"));
$dateFin = date('Y-m-d');
$today = date('Y-m-d');
$rolesVente = '1,2,6';
$escDebut = $mysqli->real_escape_string($dateDebut);
$escFin = $mysqli->real_escape_string($dateFin);
$escToday = $mysqli->real_escape_string($today);

echo "=== Vérification comptes guichetiers (idcptuser = roleattribut) ===\n";
echo "Période : {$dateDebut} → {$dateFin}\n";
echo "Mode : " . ($apply ? "CORRECTION" : "AUDIT (ajouter --apply pour corriger)") . "\n\n";

function qcount(mysqli $db, string $sql): int
{
    $r = $db->query($sql);
    if (!$r) {
        throw new RuntimeException("SQL : {$db->error}");
    }
    $row = $r->fetch_row();

    return (int) $row[0];
}

function printComptes(mysqli $db, string $escDebut, string $escFin, string $escToday, string $label): void
{
    echo "{$label}\n";
    $sql = "
        SELECT ar.roleattribut, cu.username, ul.guser AS gare_id,
               SUM(CASE WHEN p.datep_create = '{$escToday}' THEN 1 ELSE 0 END) AS ventes_aujourdhui,
               COUNT(p.code_passager) AS ventes_periode,
               SUM(p.prixvente) AS montant_periode
        FROM passager p
        JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
        JOIN user_login ul ON ul.uid_login = ar.idgestcompte
        JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
        WHERE p.statut_code = 'vendu'
          AND p.code_ticket != 'R'
          AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
        GROUP BY ar.roleattribut, cu.username, ul.guser
        HAVING ventes_periode > 0
        ORDER BY ventes_periode DESC
        LIMIT 25
    ";
    $r = $db->query($sql);
    if (!$r) {
        throw new RuntimeException("SQL comptes : {$db->error}");
    }
    printf("%-8s %-18s %-10s %6s %8s %12s\n", 'roleattr', 'guichetier', 'gare', 'jour', '30j', 'montant');
    while ($row = $r->fetch_assoc()) {
        printf(
            "%-8s %-18s %-10s %6d %8d %12.0f\n",
            $row['roleattribut'],
            substr($row['username'], 0, 18),
            substr($row['gare_id'], 0, 10),
            (int) $row['ventes_aujourdhui'],
            (int) $row['ventes_periode'],
            (float) $row['montant_periode']
        );
    }
    echo "\n";
}

$sqlTypeA = "
    SELECT p.code_passager, p.idcptuser AS ancien, ar_fix.roleattribut AS nouveau,
           cu.username, ul.guser AS gare_id
    FROM passager p
    JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
    LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = p.idcptuser
    JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id AND ul.guser = sg.gareprinceid
    JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
        AND ar_fix.activer_role = 0
        AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND ar_bad.roleattribut IS NULL
      AND ar_fix.roleattribut <> p.idcptuser
";

$sqlTypeC = "
    SELECT p.code_passager, p.code_ticket, p.idcptuser AS ancien, ar_fix.roleattribut AS nouveau,
           cu.username AS actuel, cu_real.username AS reel, ul.guser AS gare_id
    FROM passager p
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
";

$sqlCountTypeA = "
    SELECT COUNT(DISTINCT p.code_passager)
    FROM passager p
    JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
    LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = p.idcptuser
    JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id AND ul.guser = sg.gareprinceid
    JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
        AND ar_fix.activer_role = 0
        AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
    WHERE p.statut_code = 'vendu'
      AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND ar_bad.roleattribut IS NULL
      AND ar_fix.roleattribut <> p.idcptuser
";

$sqlCountTypeC = "
    SELECT COUNT(DISTINCT p.code_passager)
    FROM passager p
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
";

try {
    $nbTypeA = qcount($mysqli, $sqlCountTypeA);
    $nbTypeC = qcount($mysqli, $sqlCountTypeC);
    $orphelines = qcount($mysqli, "
        SELECT COUNT(*) FROM passager p
        LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
        WHERE p.statut_code = 'vendu'
          AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
          AND ar.roleattribut IS NULL
    ");

    echo "1. Anomalies détectées\n";
    echo "   Orphelines (idcptuser ∉ attributions_role) : {$orphelines}\n";
    echo "   TYPE A (idcptuser = cpuser_id)             : {$nbTypeA}\n";
    echo "   TYPE C (suffixe ticket → autre guichetier) : {$nbTypeC}\n\n";

    if ($nbTypeA > 0) {
        echo "   Détail TYPE A :\n";
        $r = $mysqli->query($sqlTypeA . ' ORDER BY p.datep_create DESC LIMIT 20');
        while ($row = $r->fetch_assoc()) {
            echo "   - {$row['code_passager']} : {$row['ancien']} → {$row['nouveau']} ({$row['username']}, {$row['gare_id']})\n";
        }
        echo "\n";
    }

    if ($nbTypeC > 0) {
        echo "   Détail TYPE C :\n";
        $r = $mysqli->query($sqlTypeC . ' ORDER BY p.datep_create DESC LIMIT 30');
        while ($row = $r->fetch_assoc()) {
            echo "   - {$row['code_ticket']} : roleattr {$row['ancien']} → {$row['nouveau']} ({$row['actuel']} → {$row['reel']}, {$row['gare_id']})\n";
        }
        echo "\n";
    }

    printComptes($mysqli, $escDebut, $escFin, $escToday, '2. Top comptes guichetiers (état actuel)');

    if (!$apply) {
        echo "Aucune modification. Relancer avec --apply pour corriger TYPE A + TYPE C.\n";
        $mysqli->close();
        exit(0);
    }

    if ($nbTypeA === 0 && $nbTypeC === 0) {
        echo "Rien à corriger.\n";
        $mysqli->close();
        exit(0);
    }

    $backup = 'passager_backup_idcptuser_' . date('Ymd_His');
    if (!$mysqli->query("
        CREATE TABLE `{$backup}` AS
        SELECT p.* FROM passager p
        WHERE p.statut_code = 'vendu'
          AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
    ")) {
        throw new RuntimeException("Backup échoué : {$mysqli->error}");
    }
    echo "3. Sauvegarde créée : {$backup}\n";

    $mysqli->begin_transaction();

    $updA = 0;
    if ($nbTypeA > 0) {
        $mysqli->query("
            UPDATE passager p
            JOIN compte_user cu ON cu.cpuser_id = p.idcptuser
            LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = p.idcptuser
            JOIN sousgare sg ON sg.idsousgare = p.departclient_idgare
            JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id AND ul.guser = sg.gareprinceid
            JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
                AND ar_fix.activer_role = 0
                AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
            SET p.idcptuser = ar_fix.roleattribut
            WHERE p.statut_code = 'vendu'
              AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
              AND ar_bad.roleattribut IS NULL
              AND ar_fix.roleattribut IS NOT NULL
              AND ar_fix.roleattribut <> p.idcptuser
        ");
        $updA = $mysqli->affected_rows;
    }

    $updC = 0;
    if ($nbTypeC > 0) {
        $mysqli->query("
            UPDATE passager p
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
            SET p.idcptuser = ar_fix.roleattribut
            WHERE p.statut_code = 'vendu'
              AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
              AND cu_real.cpuser_id <> cu.cpuser_id
              AND p.code_ticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), p.idcptuser)
              AND ar_fix.roleattribut <> p.idcptuser
        ");
        $updC = $mysqli->affected_rows;
    }

    $reste = qcount($mysqli, "
        SELECT COUNT(*) FROM passager p
        LEFT JOIN attributions_role ar ON ar.roleattribut = p.idcptuser
        WHERE p.statut_code = 'vendu'
          AND p.datep_create BETWEEN '{$escDebut}' AND '{$escFin}'
          AND ar.roleattribut IS NULL
    ");

    if ($reste > 0) {
        $mysqli->rollback();
        throw new RuntimeException("Rollback : {$reste} ventes orphelines restantes après correction.");
    }

    $mysqli->commit();

    echo "4. Corrections appliquées\n";
    echo "   TYPE A : {$updA} ligne(s)\n";
    echo "   TYPE C : {$updC} ligne(s)\n";
    echo "   Orphelines restantes : {$reste}\n\n";

    printComptes($mysqli, $escDebut, $escFin, $escToday, '5. Top comptes guichetiers (après correction)');

    echo "Terminé. Backup : {$backup}\n";
} catch (Throwable $e) {
    if ($mysqli->errno === 0) {
        @$mysqli->rollback();
    }
    fwrite(STDERR, "ERREUR : {$e->getMessage()}\n");
    exit(1);
}

$mysqli->close();
