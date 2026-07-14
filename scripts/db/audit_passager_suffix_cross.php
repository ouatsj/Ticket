<?php
/**
 * Audit SQL agrégé : suffixe code_passager ≠ propriétaire du roleattribut.
 *
 * Usage:
 *   php scripts/db/audit_passager_suffix_cross.php [--since=YYYY-MM-DD]
 */
require __DIR__ . '/_bootstrap.php';

$since = '2026-01-01';
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

echo "=== AUDIT GLOBAL suffixe code_passager ===\n";
echo "Depuis: {$since}\n";
echo 'Généré: ' . date('Y-m-d H:i:s') . " UTC\n\n";

$rows = q($m, "
    SELECT
        p.idcptuser AS owner_ra,
        cu_owner.username AS owner_user,
        ul_owner.guser AS gare,
        LOWER(SUBSTRING(cu_owner.username, 1, 1)) AS owner_initial,
        LOWER(SUBSTRING(p.code_passager, CHAR_LENGTH(p.code_passager) - CHAR_LENGTH(CAST(p.idcptuser AS CHAR)), 1)) AS creator_initial,
        COUNT(*) AS n,
        SUM(p.statutvente = 0) AS ouvert,
        SUM(IFNULL(p.prixvente, 0)) AS montant,
        MIN(p.date_emis) AS first_dt,
        MAX(p.date_emis) AS last_dt,
        GROUP_CONCAT(p.code_passager ORDER BY p.date_emis DESC SEPARATOR '|') AS samples
    FROM passager p
    JOIN attributions_role ar_owner ON ar_owner.roleattribut = p.idcptuser AND ar_owner.activer_role = 0
    JOIN user_login ul_owner ON ar_owner.idgestcompte = ul_owner.uid_login
    JOIN compte_user cu_owner ON ul_owner.uid_usercpte = cu_owner.cpuser_id
    WHERE p.date_emis >= '{$sinceEsc}'
      AND p.code_passager REGEXP '[A-Za-z][0-9]+\$'
      AND LOWER(SUBSTRING(cu_owner.username, 1, 1)) <>
          LOWER(SUBSTRING(p.code_passager, CHAR_LENGTH(p.code_passager) - CHAR_LENGTH(CAST(p.idcptuser AS CHAR)), 1))
    GROUP BY p.idcptuser, cu_owner.username, ul_owner.guser, owner_initial, creator_initial
    ORDER BY n DESC
");

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

$totalN = 0;
$totalMontant = 0;
$fixable = array();

echo "## SUSPICIONS PAR PAIRE\n\n";
foreach ($rows as $row) {
    $key = $row['gare'] . '|' . $row['creator_initial'];
    $creators = isset($creatorMap[$key]) ? $creatorMap[$key] : array();
    $creatorLabel = '?';
    $targetRa = null;
    if (count($creators) === 1) {
        $creatorLabel = $creators[0]['username'];
        $targetRa = (int) $creators[0]['roleattribut'];
    } elseif (count($creators) > 1) {
        $parts = array();
        foreach ($creators as $c) {
            $parts[] = $c['username'] . '(ra=' . $c['roleattribut'] . ')';
        }
        $creatorLabel = implode('/', $parts);
    }

    $samples = array_slice(explode('|', $row['samples']), 0, 2);
    $fixHint = $targetRa ? " → corriger vers ra={$targetRa}" : '';

    echo sprintf(
        "  %s(%s) → @%s(ra=%s) | %d lignes | %d ouv | %s F | %s — %s%s\n",
        $creatorLabel,
        $row['creator_initial'],
        $row['owner_user'],
        $row['owner_ra'],
        $row['n'],
        $row['ouvert'],
        number_format((float) $row['montant'], 0, ',', ' '),
        $row['first_dt'],
        $row['last_dt'],
        $fixHint
    );
    echo '    ex: ' . implode(', ', $samples) . "\n";

    $totalN += (int) $row['n'];
    $totalMontant += (float) $row['montant'];

    if ($targetRa && $targetRa !== (int) $row['owner_ra']) {
        $fixable[] = array(
            'owner_ra' => (int) $row['owner_ra'],
            'target_ra' => $targetRa,
            'creator_initial' => $row['creator_initial'],
            'gare' => $row['gare'],
            'n' => (int) $row['n'],
        );
    }
}

echo "\n## TOTAL\n";
echo "Lignes suspectes: {$totalN}\n";
echo 'Montant: ' . number_format($totalMontant, 0, ',', ' ') . " F\n";
echo 'Groupes corrigeables (créateur unique): ' . count($fixable) . "\n";

file_put_contents('/tmp/audit_passager_suffix_fixable.json', json_encode($fixable, JSON_PRETTY_PRINT));

// Recettes idopera — même logique si champ nom existe
echo "\n## RECETTES idopera (depuis {$since}) — activité sous autre compte\n";
$rec = q($m, "
    SELECT r.idopera AS owner_ra, cu.username AS owner_user,
        COUNT(*) AS n, SUM(IFNULL(r.montrec, 0)) AS montant
    FROM recette r
    JOIN attributions_role ar ON ar.roleattribut = r.idopera AND ar.activer_role = 0
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE r.date_recet >= '{$sinceEsc}'
    GROUP BY r.idopera, cu.username
    HAVING n > 0
    ORDER BY n DESC
    LIMIT 5
");
echo "(Top 5 volume — vérification manuelle opérateur vs idopera recommandée)\n";
