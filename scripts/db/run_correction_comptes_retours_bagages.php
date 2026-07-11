#!/usr/bin/env php
<?php
/**
 * Vérifie et corrige l'attribution guichet :
 *   - non_passager.cptus = roleattribut
 *   - bagages.idoperabagage = roleattribut
 *
 * Usage :
 *   php scripts/db/run_correction_comptes_retours_bagages.php --jours=30
 *   php scripts/db/run_correction_comptes_retours_bagages.php --jours=30 --apply
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

echo "=== Vérification retours + bagages (cptus / idoperabagage = roleattribut) ===\n";
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

function printRetours(mysqli $db, string $escDebut, string $escFin, string $escToday, string $label): void
{
    echo "{$label}\n";
    $sql = "
        SELECT ar.roleattribut, cu.username, ul.guser AS gare_id,
               SUM(CASE WHEN np.datevente = '{$escToday}' THEN 1 ELSE 0 END) AS jour,
               COUNT(np.code_non_pass) AS periode,
               SUM(np.prixretour) AS montant
        FROM non_passager np
        JOIN attributions_role ar ON ar.roleattribut = np.cptus
        JOIN user_login ul ON ul.uid_login = ar.idgestcompte
        JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
        WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
        GROUP BY ar.roleattribut, cu.username, ul.guser
        HAVING periode > 0
        ORDER BY periode DESC
        LIMIT 15
    ";
    $r = $db->query($sql);
    if (!$r) {
        throw new RuntimeException("SQL retours : {$db->error}");
    }
    printf("%-8s %-18s %-10s %6s %8s %12s\n", 'roleattr', 'guichetier', 'gare', 'jour', '30j', 'montant');
    while ($row = $r->fetch_assoc()) {
        printf(
            "%-8s %-18s %-10s %6d %8d %12.0f\n",
            $row['roleattribut'],
            substr($row['username'], 0, 18),
            substr($row['gare_id'], 0, 10),
            (int) $row['jour'],
            (int) $row['periode'],
            (float) $row['montant']
        );
    }
    echo "\n";
}

function printBagages(mysqli $db, string $escDebut, string $escFin, string $escToday, string $label): void
{
    echo "{$label}\n";
    $sql = "
        SELECT ar.roleattribut, cu.username, ul.guser AS gare_id,
               SUM(CASE WHEN bg.date_create = '{$escToday}' THEN 1 ELSE 0 END) AS jour,
               COUNT(bg.id_bagage) AS periode,
               SUM(bg.prix_bagage) AS montant
        FROM bagages bg
        JOIN attributions_role ar ON ar.roleattribut = bg.idoperabagage
        JOIN user_login ul ON ul.uid_login = ar.idgestcompte
        JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
        WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
          AND bg.annulebag = 0
        GROUP BY ar.roleattribut, cu.username, ul.guser
        HAVING periode > 0
        ORDER BY periode DESC
        LIMIT 15
    ";
    $r = $db->query($sql);
    if (!$r) {
        throw new RuntimeException("SQL bagages : {$db->error}");
    }
    printf("%-8s %-18s %-10s %6s %8s %12s\n", 'roleattr', 'guichetier', 'gare', 'jour', '30j', 'montant');
    while ($row = $r->fetch_assoc()) {
        printf(
            "%-8s %-18s %-10s %6d %8d %12.0f\n",
            $row['roleattribut'],
            substr($row['username'], 0, 18),
            substr($row['gare_id'], 0, 10),
            (int) $row['jour'],
            (int) $row['periode'],
            (float) $row['montant']
        );
    }
    echo "\n";
}

// --- Retours (non_passager) : suffixe codeticket = {lettre}{id} ---
$sqlRetoursCountA = "
    SELECT COUNT(DISTINCT np.code_non_pass)
    FROM non_passager np
    JOIN compte_user cu ON cu.cpuser_id = np.cptus
    LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = np.cptus
    JOIN sousgare sg ON sg.idsousgare = np.sousgareidentif
    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id AND ul.guser = sg.gareprinceid
    JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
        AND ar_fix.activer_role = 0
        AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
    WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
      AND ar_bad.roleattribut IS NULL
      AND ar_fix.roleattribut <> np.cptus
";

$sqlRetoursCountC = "
    SELECT COUNT(DISTINCT np.code_non_pass)
    FROM non_passager np
    JOIN attributions_role ar ON ar.roleattribut = np.cptus
    JOIN user_login ul ON ul.uid_login = ar.idgestcompte
    JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
    JOIN compte_user cu_real
        ON np.codeticket LIKE CONCAT('%', UPPER(LEFT(cu_real.username, 1)), cu_real.cpuser_id)
    JOIN sousgare sg ON sg.idsousgare = np.sousgareidentif
    JOIN user_login ul_real
        ON ul_real.uid_usercpte = cu_real.cpuser_id
        AND ul_real.guser = sg.gareprinceid
    JOIN attributions_role ar_fix
        ON ar_fix.idgestcompte = ul_real.uid_login
        AND ar_fix.activer_role = 0
        AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
    WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
      AND cu_real.cpuser_id <> cu.cpuser_id
      AND np.codeticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), np.cptus)
      AND ar_fix.roleattribut <> np.cptus
";

$sqlRetoursDetailC = "
    SELECT np.code_non_pass, np.codeticket, np.datevente, np.cptus AS ancien,
           ar_fix.roleattribut AS nouveau, cu.username AS actuel,
           cu_real.username AS reel, ul.guser AS gare_id
    FROM non_passager np
    JOIN attributions_role ar ON ar.roleattribut = np.cptus
    JOIN user_login ul ON ul.uid_login = ar.idgestcompte
    JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
    JOIN compte_user cu_real
        ON np.codeticket LIKE CONCAT('%', UPPER(LEFT(cu_real.username, 1)), cu_real.cpuser_id)
    JOIN sousgare sg ON sg.idsousgare = np.sousgareidentif
    JOIN user_login ul_real
        ON ul_real.uid_usercpte = cu_real.cpuser_id
        AND ul_real.guser = sg.gareprinceid
    JOIN attributions_role ar_fix
        ON ar_fix.idgestcompte = ul_real.uid_login
        AND ar_fix.activer_role = 0
        AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
    WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
      AND cu_real.cpuser_id <> cu.cpuser_id
      AND np.codeticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), np.cptus)
      AND ar_fix.roleattribut <> np.cptus
    ORDER BY np.datevente DESC
";

// --- Bagages : suffixe codebag = {id}{lettre} ---
$sqlBagagesCountA = "
    SELECT COUNT(DISTINCT bg.id_bagage)
    FROM bagages bg
    JOIN compte_user cu ON cu.cpuser_id = bg.idoperabagage
    LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = bg.idoperabagage
    JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id AND ul.guser = bg.idgarebag
    JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
        AND ar_fix.activer_role = 0
    WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND ar_bad.roleattribut IS NULL
      AND ar_fix.roleattribut <> bg.idoperabagage
";

$sqlBagagesCountC = "
    SELECT COUNT(DISTINCT bg.id_bagage)
    FROM bagages bg
    JOIN attributions_role ar ON ar.roleattribut = bg.idoperabagage
    JOIN user_login ul ON ul.uid_login = ar.idgestcompte
    JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
    JOIN compte_user cu_real
        ON bg.codebag LIKE CONCAT('%', cu_real.cpuser_id, UPPER(LEFT(cu_real.username, 1)))
    JOIN user_login ul_real
        ON ul_real.uid_usercpte = cu_real.cpuser_id
        AND ul_real.guser = bg.idgarebag
    JOIN attributions_role ar_fix
        ON ar_fix.idgestcompte = ul_real.uid_login
        AND ar_fix.activer_role = 0
    WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND cu_real.cpuser_id <> cu.cpuser_id
      AND bg.codebag NOT LIKE CONCAT('%', bg.idoperabagage, UPPER(LEFT(cu.username, 1)))
      AND ar_fix.roleattribut <> bg.idoperabagage
";

$sqlBagagesDetailC = "
    SELECT bg.id_bagage, bg.codebag, bg.date_create, bg.idoperabagage AS ancien,
           ar_fix.roleattribut AS nouveau, cu.username AS actuel,
           cu_real.username AS reel, bg.idgarebag AS gare_id
    FROM bagages bg
    JOIN attributions_role ar ON ar.roleattribut = bg.idoperabagage
    JOIN user_login ul ON ul.uid_login = ar.idgestcompte
    JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
    JOIN compte_user cu_real
        ON bg.codebag LIKE CONCAT('%', cu_real.cpuser_id, UPPER(LEFT(cu_real.username, 1)))
    JOIN user_login ul_real
        ON ul_real.uid_usercpte = cu_real.cpuser_id
        AND ul_real.guser = bg.idgarebag
    JOIN attributions_role ar_fix
        ON ar_fix.idgestcompte = ul_real.uid_login
        AND ar_fix.activer_role = 0
    WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
      AND cu_real.cpuser_id <> cu.cpuser_id
      AND bg.codebag NOT LIKE CONCAT('%', bg.idoperabagage, UPPER(LEFT(cu.username, 1)))
      AND ar_fix.roleattribut <> bg.idoperabagage
    ORDER BY bg.date_create DESC
";

try {
    $retOrphelines = qcount($mysqli, "
        SELECT COUNT(*) FROM non_passager np
        LEFT JOIN attributions_role ar ON ar.roleattribut = np.cptus
        WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
          AND ar.roleattribut IS NULL
    ");
    $retA = qcount($mysqli, $sqlRetoursCountA);
    $retC = qcount($mysqli, $sqlRetoursCountC);

    $bagOrphelines = qcount($mysqli, "
        SELECT COUNT(*) FROM bagages bg
        LEFT JOIN attributions_role ar ON ar.roleattribut = bg.idoperabagage
        WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
          AND ar.roleattribut IS NULL
    ");
    $bagA = qcount($mysqli, $sqlBagagesCountA);
    $bagC = qcount($mysqli, $sqlBagagesCountC);

    echo "1. RETOURS (non_passager.cptus)\n";
    echo "   Orphelines                          : {$retOrphelines}\n";
    echo "   TYPE A (cptus = cpuser_id)          : {$retA}\n";
    echo "   TYPE C (suffixe codeticket)         : {$retC}\n";
    if ($retC > 0) {
        echo "   Détail TYPE C :\n";
        $r = $mysqli->query($sqlRetoursDetailC . ' LIMIT 20');
        while ($row = $r->fetch_assoc()) {
            echo "   - {$row['codeticket']} : {$row['ancien']} → {$row['nouveau']} ({$row['actuel']} → {$row['reel']}, {$row['gare_id']})\n";
        }
    }
    echo "\n";

    echo "2. BAGAGES (idoperabagage)\n";
    echo "   Orphelines                          : {$bagOrphelines}\n";
    echo "   TYPE A (idoperabagage = cpuser_id)  : {$bagA}\n";
    echo "   TYPE C (suffixe codebag)            : {$bagC}\n";
    if ($bagC > 0) {
        echo "   Détail TYPE C :\n";
        $r = $mysqli->query($sqlBagagesDetailC . ' LIMIT 20');
        while ($row = $r->fetch_assoc()) {
            echo "   - {$row['codebag']} : {$row['ancien']} → {$row['nouveau']} ({$row['actuel']} → {$row['reel']}, {$row['gare_id']})\n";
        }
    }
    echo "\n";

    printRetours($mysqli, $escDebut, $escFin, $escToday, '3. Top comptes retours (état actuel)');
    printBagages($mysqli, $escDebut, $escFin, $escToday, '4. Top comptes bagages (état actuel)');

    if (!$apply) {
        echo "Aucune modification. Relancer avec --apply pour corriger.\n";
        $mysqli->close();
        exit(0);
    }

    if ($retA === 0 && $retC === 0 && $bagA === 0 && $bagC === 0) {
        echo "Rien à corriger.\n";
        $mysqli->close();
        exit(0);
    }

    $ts = date('Ymd_His');
    $backupRet = 'non_passager_backup_cptus_' . $ts;
    $backupBag = 'bagages_backup_operateur_' . $ts;

    if (!$mysqli->query("
        CREATE TABLE `{$backupRet}` AS
        SELECT np.* FROM non_passager np
        WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
    ")) {
        throw new RuntimeException("Backup retours échoué : {$mysqli->error}");
    }
    if (!$mysqli->query("
        CREATE TABLE `{$backupBag}` AS
        SELECT bg.* FROM bagages bg
        WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
    ")) {
        throw new RuntimeException("Backup bagages échoué : {$mysqli->error}");
    }
    echo "5. Sauvegardes : {$backupRet}, {$backupBag}\n";

    $mysqli->begin_transaction();

    $updRetA = 0;
    $updRetC = 0;
    $updBagA = 0;
    $updBagC = 0;

    if ($retA > 0) {
        $mysqli->query("
            UPDATE non_passager np
            JOIN compte_user cu ON cu.cpuser_id = np.cptus
            LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = np.cptus
            JOIN sousgare sg ON sg.idsousgare = np.sousgareidentif
            JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id AND ul.guser = sg.gareprinceid
            JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
                AND ar_fix.activer_role = 0
                AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
            SET np.cptus = ar_fix.roleattribut
            WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
              AND ar_bad.roleattribut IS NULL
              AND ar_fix.roleattribut IS NOT NULL
              AND ar_fix.roleattribut <> np.cptus
        ");
        $updRetA = $mysqli->affected_rows;
    }

    if ($retC > 0) {
        $mysqli->query("
            UPDATE non_passager np
            JOIN attributions_role ar ON ar.roleattribut = np.cptus
            JOIN user_login ul ON ul.uid_login = ar.idgestcompte
            JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
            JOIN compte_user cu_real
                ON np.codeticket LIKE CONCAT('%', UPPER(LEFT(cu_real.username, 1)), cu_real.cpuser_id)
            JOIN sousgare sg ON sg.idsousgare = np.sousgareidentif
            JOIN user_login ul_real
                ON ul_real.uid_usercpte = cu_real.cpuser_id
                AND ul_real.guser = sg.gareprinceid
            JOIN attributions_role ar_fix
                ON ar_fix.idgestcompte = ul_real.uid_login
                AND ar_fix.activer_role = 0
                AND FIND_IN_SET(ar_fix.userole, '{$rolesVente}')
            SET np.cptus = ar_fix.roleattribut
            WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
              AND cu_real.cpuser_id <> cu.cpuser_id
              AND np.codeticket NOT LIKE CONCAT('%', UPPER(LEFT(cu.username, 1)), np.cptus)
              AND ar_fix.roleattribut <> np.cptus
        ");
        $updRetC = $mysqli->affected_rows;
    }

    if ($bagA > 0) {
        $mysqli->query("
            UPDATE bagages bg
            JOIN compte_user cu ON cu.cpuser_id = bg.idoperabagage
            LEFT JOIN attributions_role ar_bad ON ar_bad.roleattribut = bg.idoperabagage
            JOIN user_login ul ON ul.uid_usercpte = cu.cpuser_id AND ul.guser = bg.idgarebag
            JOIN attributions_role ar_fix ON ar_fix.idgestcompte = ul.uid_login
                AND ar_fix.activer_role = 0
            SET bg.idoperabagage = ar_fix.roleattribut
            WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
              AND ar_bad.roleattribut IS NULL
              AND ar_fix.roleattribut IS NOT NULL
              AND ar_fix.roleattribut <> bg.idoperabagage
        ");
        $updBagA = $mysqli->affected_rows;
    }

    if ($bagC > 0) {
        $mysqli->query("
            UPDATE bagages bg
            JOIN attributions_role ar ON ar.roleattribut = bg.idoperabagage
            JOIN user_login ul ON ul.uid_login = ar.idgestcompte
            JOIN compte_user cu ON cu.cpuser_id = ul.uid_usercpte
            JOIN compte_user cu_real
                ON bg.codebag LIKE CONCAT('%', cu_real.cpuser_id, UPPER(LEFT(cu_real.username, 1)))
            JOIN user_login ul_real
                ON ul_real.uid_usercpte = cu_real.cpuser_id
                AND ul_real.guser = bg.idgarebag
            JOIN attributions_role ar_fix
                ON ar_fix.idgestcompte = ul_real.uid_login
                AND ar_fix.activer_role = 0
            SET bg.idoperabagage = ar_fix.roleattribut
            WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
              AND cu_real.cpuser_id <> cu.cpuser_id
              AND bg.codebag NOT LIKE CONCAT('%', bg.idoperabagage, UPPER(LEFT(cu.username, 1)))
              AND ar_fix.roleattribut <> bg.idoperabagage
        ");
        $updBagC = $mysqli->affected_rows;
    }

    $retReste = qcount($mysqli, "
        SELECT COUNT(*) FROM non_passager np
        LEFT JOIN attributions_role ar ON ar.roleattribut = np.cptus
        WHERE np.datevente BETWEEN '{$escDebut}' AND '{$escFin}'
          AND ar.roleattribut IS NULL
    ");
    $bagReste = qcount($mysqli, "
        SELECT COUNT(*) FROM bagages bg
        LEFT JOIN attributions_role ar ON ar.roleattribut = bg.idoperabagage
        WHERE bg.date_create BETWEEN '{$escDebut}' AND '{$escFin}'
          AND ar.roleattribut IS NULL
    ");

    if ($retReste > 0 || $bagReste > 0) {
        $mysqli->rollback();
        throw new RuntimeException("Rollback : orphelines restantes retours={$retReste} bagages={$bagReste}");
    }

    $mysqli->commit();

    echo "6. Corrections appliquées\n";
    echo "   Retours TYPE A : {$updRetA}\n";
    echo "   Retours TYPE C : {$updRetC}\n";
    echo "   Bagages TYPE A : {$updBagA}\n";
    echo "   Bagages TYPE C : {$updBagC}\n\n";

    printRetours($mysqli, $escDebut, $escFin, $escToday, '7. Top comptes retours (après correction)');
    printBagages($mysqli, $escDebut, $escFin, $escToday, '8. Top comptes bagages (après correction)');

    echo "Terminé.\n";
} catch (Throwable $e) {
    @$mysqli->rollback();
    fwrite(STDERR, "ERREUR : {$e->getMessage()}\n");
    exit(1);
}

$mysqli->close();
