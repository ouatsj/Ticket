<?php
/**
 * Corrige les passagers dont le suffixe code_passager révèle un autre opérateur
 * (créateur unique identifié sur la même gare).
 *
 * Usage:
 *   php scripts/db/fix_passager_suffix_cross.php [--since=2026-07-01]        # dry-run
 *   php scripts/db/fix_passager_suffix_cross.php --since=2026-07-01 --apply
 */
require __DIR__ . '/_bootstrap.php';

$apply = in_array('--apply', $argv, true);
$since = '2026-07-01';
foreach ($argv as $arg) {
    if (strpos($arg, '--since=') === 0) {
        $since = substr($arg, 8);
    }
}

$m = db_script_connect($argv);
$sinceEsc = $m->real_escape_string($since);

function q($m, $sql)
{
    $r = $m->query($sql);
    if ($r === false) {
        fwrite(STDERR, "SQL error: {$m->error}\n{$sql}\n");
        return array();
    }
    $rows = array();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

$creatorMap = array();
foreach (q($m, "
    SELECT ar.roleattribut, cu.username, ul.guser, LOWER(LEFT(cu.username, 1)) AS initial
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ar.activer_role = 0 AND ar.userole IN (4, 5, 6, 15, 16, 18)
") as $c) {
    $key = $c['guser'] . '|' . $c['initial'];
    if (!isset($creatorMap[$key])) {
        $creatorMap[$key] = array();
    }
    $creatorMap[$key][] = $c;
}

$groups = q($m, "
    SELECT
        p.idcptuser AS owner_ra,
        cu_owner.username AS owner_user,
        ul_owner.guser AS gare,
        LOWER(SUBSTRING(p.code_passager, CHAR_LENGTH(p.code_passager) - CHAR_LENGTH(CAST(p.idcptuser AS CHAR)), 1)) AS creator_initial,
        COUNT(*) AS n,
        SUM(IFNULL(p.prixvente, 0)) AS montant
    FROM passager p
    JOIN attributions_role ar_owner ON ar_owner.roleattribut = p.idcptuser AND ar_owner.activer_role = 0
    JOIN user_login ul_owner ON ar_owner.idgestcompte = ul_owner.uid_login
    JOIN compte_user cu_owner ON ul_owner.uid_usercpte = cu_owner.cpuser_id
    WHERE p.date_emis >= '{$sinceEsc}'
      AND p.code_passager REGEXP '[A-Za-z][0-9]+\$'
      AND LOWER(SUBSTRING(cu_owner.username, 1, 1)) <>
          LOWER(SUBSTRING(p.code_passager, CHAR_LENGTH(p.code_passager) - CHAR_LENGTH(CAST(p.idcptuser AS CHAR)), 1))
    GROUP BY p.idcptuser, cu_owner.username, ul_owner.guser, creator_initial
    ORDER BY n DESC
");

echo "=== fix_passager_suffix_cross ===\n";
echo 'Mode: ' . ($apply ? 'APPLY' : 'DRY-RUN') . "\n";
echo "Depuis: {$since}\n\n";

$totalFixed = 0;
$totalMontant = 0;

foreach ($groups as $g) {
    $key = $g['gare'] . '|' . $g['creator_initial'];
    if (!isset($creatorMap[$key]) || count($creatorMap[$key]) !== 1) {
        continue;
    }
    $targetRa = (int) $creatorMap[$key][0]['roleattribut'];
    $ownerRa = (int) $g['owner_ra'];
    if ($targetRa === $ownerRa) {
        continue;
    }

    $creatorUser = $creatorMap[$key][0]['username'];
    $creatorInitial = $m->real_escape_string($g['creator_initial']);
    $gare = $m->real_escape_string($g['gare']);

    echo sprintf(
        "  %s → ra=%d (était @%s ra=%d) | %d lignes | %s F | gare=%s\n",
        $creatorUser,
        $targetRa,
        $g['owner_user'],
        $ownerRa,
        $g['n'],
        number_format((float) $g['montant'], 0, ',', ' '),
        $g['gare']
    );

    if ($apply) {
        $sql = "
            UPDATE passager p
            JOIN attributions_role ar_owner ON ar_owner.roleattribut = p.idcptuser AND ar_owner.activer_role = 0
            JOIN user_login ul_owner ON ar_owner.idgestcompte = ul_owner.uid_login
            SET p.idcptuser = {$targetRa}
            WHERE p.date_emis >= '{$sinceEsc}'
              AND p.idcptuser = {$ownerRa}
              AND ul_owner.guser = '{$gare}'
              AND LOWER(SUBSTRING(p.code_passager, CHAR_LENGTH(p.code_passager) - CHAR_LENGTH(CAST(p.idcptuser AS CHAR)), 1)) = '{$creatorInitial}'
              AND p.code_passager REGEXP '[A-Za-z][0-9]+\$'
        ";
        if (!$m->query($sql)) {
            fwrite(STDERR, "UPDATE failed: {$m->error}\n");
            exit(1);
        }
        $totalFixed += $m->affected_rows;
    } else {
        $totalFixed += (int) $g['n'];
    }
    $totalMontant += (float) $g['montant'];
}

echo "\nTotal " . ($apply ? 'corrigé' : 'à corriger') . ": {$totalFixed} lignes, " . number_format($totalMontant, 0, ',', ' ') . " F\n";
if (!$apply && $totalFixed > 0) {
    echo "Relancez avec --apply pour appliquer.\n";
}
