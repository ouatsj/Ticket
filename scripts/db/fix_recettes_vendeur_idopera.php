<?php
/**
 * Corrige recettes d'arrêt vendeur mal routées : idopera = vendeur (role 6…) au lieu du chef guichet.
 * Les recettes corrigées apparaissent alors dans la file caissier (valideget_saisie).
 *
 * Usage:
 *   php scripts/db/fix_recettes_vendeur_idopera.php [--since=2026-07-01] [--gare=BOB1]
 *   php scripts/db/fix_recettes_vendeur_idopera.php --apply [--since=...] [--gare=...]
 */
require __DIR__ . '/_bootstrap.php';

$since = '2026-07-01';
$gareFilter = '';
$apply = in_array('--apply', $argv, true);

foreach ($argv as $arg) {
    if (strpos($arg, '--since=') === 0) {
        $since = substr($arg, 8);
    }
    if (strpos($arg, '--gare=') === 0) {
        $gareFilter = substr($arg, 7);
    }
}

$m = db_script_connect($argv);
$sinceEsc = $m->real_escape_string($since);
$vendeurRoles = implode(',', array(6, 10, 12, 15, 17));
$gareSql = '';
if ($gareFilter !== '') {
    $g = $m->real_escape_string($gareFilter);
    $gareSql = " AND cs.gexp_caiss = '{$g}'";
}

function qrows($m, $sql)
{
    $r = $m->query($sql);
    if ($r === false) {
        fwrite(STDERR, "SQL error: {$m->error}\n");
        return array();
    }
    $rows = array();
    while ($row = $r->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function chef_on_gare($m, $gare_id)
{
    $gare_id = $m->real_escape_string($gare_id);
    $rows = qrows($m, "
        SELECT ar.roleattribut, cu.username, ar.activeattrib
        FROM attributions_role ar
        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
        WHERE ul.guser = '{$gare_id}'
          AND ar.userole IN (5, 16)
          AND ar.activer_role = 0
        ORDER BY ar.activeattrib DESC, ar.roleattribut ASC
        LIMIT 1
    ");
    return !empty($rows) ? $rows[0] : null;
}

echo "=== FIX recettes idopera vendeur → chef guichet ===\n";
echo 'Mode: ' . ($apply ? 'APPLY' : 'DRY-RUN') . "\n";
echo "Depuis: {$since}\n";
if ($gareFilter !== '') {
    echo "Gare: {$gareFilter}\n";
}
echo 'Généré: ' . date('Y-m-d H:i:s') . " UTC\n\n";

$rows = qrows($m, "
    SELECT r.id_recette, r.idopera, r.montant_recet, r.date_recet, r.idcaisse,
        cs.gexp_caiss AS gare,
        cu.username AS wrong_user, ar.userole AS wrong_role,
        cg.idusercompt AS vendor_ra, cu_v.username AS vendor_nom
    FROM recette r
    JOIN attributions_role ar ON r.idopera = ar.roleattribut
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    JOIN caisse cs ON r.idcaisse = cs.id_caiss
    LEFT JOIN compte_guichet cg ON (
        REPLACE(cg.datearretcompt, '/', '-') = r.date_recet
        AND cg.montcomtpte = r.montant_recet
        AND cg.is_validcompte = 1
    )
    LEFT JOIN attributions_role ar_v ON cg.idusercompt = ar_v.roleattribut AND ar_v.activer_role = 0
    LEFT JOIN user_login ul_v ON ar_v.idgestcompte = ul_v.uid_login
    LEFT JOIN compte_user cu_v ON ul_v.uid_usercpte = cu_v.cpuser_id
    WHERE ar.userole IN ({$vendeurRoles})
      AND r.active_recet = 0
      AND r.is_actifrecet = 0
      AND r.is_validerecet = 0
      AND r.actif_rect = 0
      AND r.type_recet <> 'Courrier'
      AND r.date_recet >= '{$sinceEsc}'
      {$gareSql}
    ORDER BY r.date_recet DESC, r.id_recette DESC
");

if (empty($rows)) {
    echo "Aucune recette vendeur mal routée à corriger.\n";
    exit(0);
}

echo count($rows) . " recette(s) à corriger:\n\n";

$updates = array();
$chefCache = array();

foreach ($rows as $row) {
    $gare = $row['gare'];
    if (!isset($chefCache[$gare])) {
        $chefCache[$gare] = chef_on_gare($m, $gare);
    }
    $chef = $chefCache[$gare];
    if (!$chef) {
        echo "  SKIP rec={$row['id_recette']} — aucun chef guichet sur gare {$gare}\n";
        continue;
    }

    $newRa = (int) $chef['roleattribut'];
    $oldRa = (int) $row['idopera'];

    if ($newRa === $oldRa) {
        continue;
    }

    $vendorInfo = $row['vendor_ra']
        ? " | vendeur arrêté ra={$row['vendor_ra']} @{$row['vendor_nom']}"
        : '';

    echo sprintf(
        "  rec=%s | %s F | %s | idopera %s (@%s role=%s) → %s (@%s chef)%s\n",
        $row['id_recette'],
        number_format((float) $row['montant_recet'], 0, ',', ' '),
        $row['date_recet'],
        $oldRa,
        $row['wrong_user'],
        $row['wrong_role'],
        $newRa,
        $chef['username'],
        $vendorInfo
    );

    $updates[] = array(
        'id_recette' => (int) $row['id_recette'],
        'new_idopera' => $newRa,
        'old_idopera' => $oldRa,
    );
}

echo "\nTotal corrections: " . count($updates) . "\n";

if (empty($updates)) {
    exit(0);
}

if (!$apply) {
    echo "\nDry-run — relancer avec --apply pour exécuter.\n";
    file_put_contents('/tmp/fix_recettes_vendeur_idopera.json', json_encode($updates, JSON_PRETTY_PRINT));
    echo "Plan exporté → /tmp/fix_recettes_vendeur_idopera.json\n";
    exit(0);
}

$m->begin_transaction();
$ok = 0;
try {
    $stmt = $m->prepare(
        'UPDATE recette SET idopera = ?, operavalid = NULL WHERE id_recette = ? AND idopera = ?'
    );
    foreach ($updates as $u) {
        $stmt->bind_param('iii', $u['new_idopera'], $u['id_recette'], $u['old_idopera']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $ok++;
        }
    }
    $stmt->close();
    $m->commit();
    echo "\n✓ {$ok} recette(s) corrigée(s).\n";
} catch (Exception $e) {
    $m->rollback();
    fwrite(STDERR, "ROLLBACK: " . $e->getMessage() . "\n");
    exit(1);
}
