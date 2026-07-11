<?php
/**
 * Après import distant → local : positionne les montants pour l'arrêt guichet.
 *
 * - Réouvre passager / retours importés (suffixe D) — statutvente ET is_valdtick remis à 0
 * - Réouvre les ventes du jour non encore validées chef (is_valdtick=0) des vendeurs avec import D
 * - Ne touche jamais aux tickets déjà validés chef (is_valdtick=1 / is_valedtick=1)
 * - Garantit activeattrib=1 (une gare) pour ces vendeurs
 * - Affiche le récapitulatif par utilisateur
 *
 * Usage:
 *   php scripts/db/prepare_arret_ventes_importees.php
 *   php scripts/db/prepare_arret_ventes_importees.php --date=2026-07-08
 *   php scripts/db/prepare_arret_ventes_importees.php --dry-run
 */
require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv);

$date = date('Y-m-d');
$dryRun = in_array('--dry-run', $argv, true);
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--date=') === 0) {
        $date = substr($arg, 7);
    }
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    fwrite(STDERR, "Date invalide: {$date}\n");
    exit(1);
}

echo "=== Préparation arrêt — ventes importées du {$date}" . ($dryRun ? ' [dry-run]' : '') . " ===\n\n";

function run_update(mysqli $db, $sql, $types, array $params, $dryRun)
{
    if ($dryRun) {
        return 0;
    }
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException($db->error);
    }
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $n = $stmt->affected_rows;
    $stmt->close();

    return $n;
}

// 1) Passager suffixe D (réouverture cohérente compteur + validation chef)
$n1 = run_update($mysqli, "
    UPDATE passager
    SET statutvente = 0, is_valdtick = 0
    WHERE code_passager LIKE '%D'
      AND datep_create = ?
      AND statutvente = 1
      AND (statut_code = 'vendu' OR statut_code IS NULL)
", 's', array($date), $dryRun);
echo "1. Passager importés (suffixe D) réouverts: {$n1}\n";

// 2) Passager du jour non validés chef, vendeurs avec import D ce jour-là
$n2 = run_update($mysqli, "
    UPDATE passager p
    INNER JOIN (
        SELECT DISTINCT p2.idcptuser
        FROM passager p2
        WHERE p2.datep_create = ?
          AND p2.code_passager LIKE '%D'
    ) imp ON imp.idcptuser = p.idcptuser
    SET p.statutvente = 0, p.is_valdtick = 0
    WHERE p.datep_create = ?
      AND p.statutvente = 1
      AND p.is_valdtick = 0
      AND p.statut_code = 'vendu'
", 'ss', array($date, $date), $dryRun);
echo "2. Passager du jour (non validés chef, vendeurs avec import D) réouverts: {$n2}\n";

// 3) Retours suffixe D
$n3 = run_update($mysqli, "
    UPDATE non_passager
    SET statvente = 0, is_valedtick = 0
    WHERE codeticket LIKE '%D'
      AND datevente = ?
      AND statvente = 1
", 's', array($date), $dryRun);
echo "3. Retours importés (suffixe D) réouverts: {$n3}\n";

// 4) Retours du jour non validés chef, vendeurs avec import D
$n4 = run_update($mysqli, "
    UPDATE non_passager np
    INNER JOIN (
        SELECT DISTINCT p.idcptuser
        FROM passager p
        WHERE p.datep_create = ?
          AND p.code_passager LIKE '%D'
    ) imp ON imp.idcptuser = np.cptus
    SET np.statvente = 0, np.is_valedtick = 0
    WHERE np.datevente = ?
      AND np.statvente = 1
      AND np.is_valedtick = 0
", 'ss', array($date, $date), $dryRun);
echo "4. Retours du jour (non validés chef, vendeurs avec import D) réouverts: {$n4}\n";

// 5) Bagages importés : état « ouvert » pour compteur arrêt
$n5 = run_update($mysqli, "
    UPDATE bagages
    SET isvalidbag = 0, actifbag = 0, annulebag = 0
    WHERE codebag LIKE '%D'
      AND date_create = ?
      AND (isvalidbag = 1 OR actifbag = 1 OR annulebag = 1)
", 's', array($date), $dryRun);
echo "5. Bagages importés (suffixe D) réouverts: {$n5}\n";

// 6) activeattrib : une gare active par vendeur concerné
$n6 = run_update($mysqli, "
    UPDATE attributions_role ar
    INNER JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    INNER JOIN (
        SELECT DISTINCT p.idcptuser AS roleattribut
        FROM passager p
        WHERE p.datep_create = ?
          AND (p.code_passager LIKE '%D' OR p.statutvente = 0)
    ) vend ON vend.roleattribut = ar.roleattribut
    INNER JOIN (
        SELECT ul2.uid_usercpte AS cpuser_id, ar2.userole,
               MIN(ar2.roleattribut) AS keeper
        FROM attributions_role ar2
        JOIN user_login ul2 ON ar2.idgestcompte = ul2.uid_login
        WHERE ar2.userole = 6 AND ar2.activer_role = 0 AND ul2.comptactif = 0
        GROUP BY ul2.uid_usercpte, ar2.userole
    ) pick ON pick.cpuser_id = ul.uid_usercpte AND pick.userole = ar.userole
    SET ar.activeattrib = IF(ar.roleattribut = pick.keeper, 1, 0)
    WHERE ar.userole = 6 AND ar.activer_role = 0 AND ul.comptactif = 0
", 's', array($date), $dryRun);
echo "6. Attributions vendeurs régularisées (activeattrib): {$n6}\n";

// 7) Corriger le passager D encore fermé s'il reste
$n7 = run_update($mysqli, "
    UPDATE passager SET statutvente = 0, is_valdtick = 0
    WHERE code_passager LIKE '%D' AND datep_create = ? AND statutvente = 1
", 's', array($date), $dryRun);
if ($n7 > 0) {
    echo "7. Passager D résiduels réouverts: {$n7}\n";
}

echo "\n--- Récapitulatif arrêt disponible ({$date}) ---\n";
echo str_pad('Vendeur', 16) . str_pad('Gare', 8) . str_pad('Tickets', 10)
    . str_pad('Montant', 12) . str_pad('Retours', 10) . "Bagages\n";
echo str_repeat('-', 70) . "\n";

$res = $mysqli->prepare("
    SELECT cu.username, g.codegares,
           COUNT(DISTINCT p.code_passager) AS nb_pass,
           COALESCE(SUM(p.prixvente), 0) AS mt_pass,
           (SELECT COUNT(*) FROM non_passager np
            WHERE np.cptus = ar.roleattribut AND np.datevente = ?
              AND np.statvente = 0) AS nb_ret,
           (SELECT COALESCE(SUM(bg.prix_bagage), 0) FROM bagages bg
            WHERE bg.idoperabagage = ar.roleattribut AND bg.date_create = ?
              AND bg.isvalidbag = 0 AND bg.actifbag = 0 AND bg.annulebag = 0) AS mt_bag
    FROM passager p
    JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    JOIN gares g ON ul.guser = g.idengare
    WHERE p.datep_create = ?
      AND p.statutvente = 0
      AND p.statut_code = 'vendu'
      AND ar.userole = 6
      AND ar.activer_role = 0
      AND ul.comptactif = 0
    GROUP BY cu.username, g.codegares, ar.roleattribut
    HAVING nb_pass > 0 OR nb_ret > 0 OR mt_bag > 0
    ORDER BY cu.username, g.codegares
");
$res->bind_param('sss', $date, $date, $date);
$res->execute();
$result = $res->get_result();
$total = 0;
while ($row = $result->fetch_assoc()) {
    echo str_pad($row['username'], 16)
        . str_pad($row['codegares'], 8)
        . str_pad((string) $row['nb_pass'], 10)
        . str_pad(number_format($row['mt_pass'], 0, '', ' '), 12)
        . str_pad((string) $row['nb_ret'], 10)
        . number_format($row['mt_bag'], 0, '', ' ') . "\n";
    $total += (float) $row['mt_pass'];
}
$res->close();

echo str_repeat('-', 70) . "\n";
echo "Total passagers ouverts: " . number_format($total, 0, '', ' ') . " F\n";
echo "\nLes vendeurs peuvent se connecter et faire l'arrêt avant de reprendre les ventes.\n";
