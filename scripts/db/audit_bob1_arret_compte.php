<?php
/**
 * Audit BOB1 : ventes non arrêtées + suffixe code_passager incohérent avec idcptuser.
 *
 * Usage:
 *   php scripts/db/audit_bob1_arret_compte.php [--since=YYYY-MM-DD]
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
$gare = 'BOB1';

/** roleattribut attendu par motif de suffixe en fin de code_passager */
$suffixRules = array(
    array('pattern' => '%a5', 'label' => 'a5 (Abi)', 'expected_ra' => 5),
    array('pattern' => '%M12', 'label' => 'M12 (Mamadousa)', 'expected_ra' => 12),
    array('pattern' => '%BOB1M5', 'label' => 'BOB1M5 (Mamadousa legacy)', 'expected_ra' => 12),
    array('pattern' => '%1M5', 'label' => '1M5 (Mamadousa legacy)', 'expected_ra' => 12),
    array('pattern' => '%k12', 'label' => 'k12 (Konate)', 'expected_ra' => 23),
);

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

echo "=== AUDIT BOB1 — arrêt de compte / roleattribut ===\n";
echo "Gare: {$gare}\n";
echo "Depuis: {$since}\n";
echo 'Généré: ' . date('Y-m-d H:i:s') . " UTC\n\n";

echo "## 1. Vendeurs BOB1 (attributions actives)\n\n";
$vendors = qrows($m, "
    SELECT ar.roleattribut, ar.userole, cu.username, cu.cpuser_id, cu.activer,
        cu.derniere_activite_at
    FROM attributions_role ar
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ul.guser = '{$gare}'
      AND ar.activer_role = 0
      AND ar.activeattrib = 1
      AND ar.userole IN (5, 6, 10, 12, 15, 16, 17, 23)
    ORDER BY ar.roleattribut
");
foreach ($vendors as $v) {
    echo sprintf(
        "  ra=%s | @%-15s | role=%s | activer=%s | dernière activité=%s\n",
        $v['roleattribut'],
        $v['username'],
        $v['userole'],
        $v['activer'],
        $v['derniere_activite_at'] ?: '-'
    );
}

echo "\n## 2. Ventes NON ARRÊTÉES (statutvente=0) par vendeur BOB1\n\n";
$ouvert = qrows($m, "
    SELECT p.idcptuser, cu.username,
        COUNT(*) AS n,
        SUM(IFNULL(p.prixvente, 0)) AS montant,
        MIN(p.date_emis) AS first_dt,
        MAX(p.date_emis) AS last_dt
    FROM passager p
    JOIN attributions_role ar ON ar.roleattribut = p.idcptuser AND ar.activer_role = 0
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ul.guser = '{$gare}'
      AND p.statutvente = 0
      AND p.prixvente IS NOT NULL
      AND p.statut_code = 'vendu'
      AND p.date_emis >= '{$sinceEsc}'
    GROUP BY p.idcptuser, cu.username
    ORDER BY n DESC
");
if (empty($ouvert)) {
    echo "  Aucune vente ticket ouverte.\n";
} else {
    foreach ($ouvert as $r) {
        echo sprintf(
            "  ra=%s @%-15s | %d tickets | %s F | %s → %s\n",
            $r['idcptuser'],
            $r['username'],
            $r['n'],
            number_format((float) $r['montant'], 0, ',', ' '),
            $r['first_dt'],
            $r['last_dt']
        );
    }
}

echo "\n## 3. INCOHÉRENCES suffixe ↔ idcptuser (toutes ventes depuis {$since})\n\n";
$totalCross = 0;
$totalCrossOuvert = 0;
$totalMontant = 0;
$fixGroups = array();

foreach ($suffixRules as $rule) {
    $pat = $m->real_escape_string($rule['pattern']);
    $exp = (int) $rule['expected_ra'];
    $rows = qrows($m, "
        SELECT p.idcptuser AS owner_ra, cu.username AS owner_user,
            COUNT(*) AS n,
            SUM(p.statutvente = 0) AS ouvert,
            SUM(IFNULL(p.prixvente, 0)) AS montant,
            MIN(p.date_emis) AS first_dt,
            MAX(p.date_emis) AS last_dt,
            GROUP_CONCAT(p.code_passager ORDER BY p.date_emis DESC SEPARATOR '|') AS samples
        FROM passager p
        JOIN attributions_role ar ON ar.roleattribut = p.idcptuser AND ar.activer_role = 0
        JOIN user_login ul ON ar.idgestcompte = ul.uid_login
        JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
        WHERE ul.guser = '{$gare}'
          AND p.code_passager LIKE '{$pat}'
          AND p.idcptuser != {$exp}
          AND p.date_emis >= '{$sinceEsc}'
        GROUP BY p.idcptuser, cu.username
        ORDER BY n DESC
    ");
    if (empty($rows)) {
        continue;
    }
    echo "### Suffixe {$rule['label']} → attendu ra={$exp}\n";
    foreach ($rows as $row) {
        $samples = array_slice(explode('|', $row['samples']), 0, 3);
        echo sprintf(
            "  Enregistré sous ra=%s (@%-12s) | %d lignes | %d ouv | %s F | %s — %s\n",
            $row['owner_ra'],
            $row['owner_user'],
            $row['n'],
            $row['ouvert'],
            number_format((float) $row['montant'], 0, ',', ' '),
            $row['first_dt'],
            $row['last_dt']
        );
        echo '    ex: ' . implode(', ', $samples) . "\n";
        echo "    → corriger idcptuser {$row['owner_ra']} → {$exp}\n";

        $totalCross += (int) $row['n'];
        $totalCrossOuvert += (int) $row['ouvert'];
        $totalMontant += (float) $row['montant'];
        $fixGroups[] = array(
            'suffix' => $rule['label'],
            'from_ra' => (int) $row['owner_ra'],
            'to_ra' => $exp,
            'n' => (int) $row['n'],
            'ouvert' => (int) $row['ouvert'],
        );
    }
    echo "\n";
}

if ($totalCross === 0) {
    echo "  Aucune incohérence suffixe connue.\n\n";
} else {
    echo "## TOTAL incohérences suffixe\n";
    echo "Lignes: {$totalCross} | Ouvertes (bloquent arrêt): {$totalCrossOuvert} | Montant: "
        . number_format($totalMontant, 0, ',', ' ') . " F\n\n";
}

echo "## 4. Arrêts compte EN ATTENTE validation (>0 h, BOB1)\n\n";
$pending = qrows($m, "
    SELECT cg.idusercompt AS ra, cu.username, cg.montcomtpte, cg.datearretcompt,
        cg.lastcptg_update, cg.is_validcompte,
        TIMESTAMPDIFF(HOUR, cg.lastcptg_update, NOW()) AS heures_attente
    FROM compte_guichet cg
    JOIN attributions_role ar ON ar.roleattribut = cg.idusercompt AND ar.activer_role = 0
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ul.guser = '{$gare}'
      AND cg.is_validcompte = 0
      AND cg.actifcompt = 0
    ORDER BY cg.lastcptg_update DESC
    LIMIT 20
");
if (empty($pending)) {
    echo "  Aucun arrêt ticket en attente.\n";
} else {
    foreach ($pending as $p) {
        echo sprintf(
            "  ra=%s @%-15s | %s F | arrêt %s | attente %sh | valid=%s\n",
            $p['ra'],
            $p['username'],
            number_format((float) $p['montcomtpte'], 0, ',', ' '),
            $p['datearretcompt'],
            $p['heures_attente'],
            $p['is_validcompte']
        );
    }
}

echo "\n## 5. Détail ventes OUVERTES mal attribuées (impact arrêt immédiat)\n\n";
$detail = qrows($m, "
    SELECT p.code_passager, p.idcptuser, cu.username, p.prixvente, p.date_emis, p.statutvente
    FROM passager p
    JOIN attributions_role ar ON ar.roleattribut = p.idcptuser AND ar.activer_role = 0
    JOIN user_login ul ON ar.idgestcompte = ul.uid_login
    JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
    WHERE ul.guser = '{$gare}'
      AND p.statutvente = 0
      AND p.prixvente IS NOT NULL
      AND p.statut_code = 'vendu'
      AND (
          (p.code_passager LIKE '%M12' AND p.idcptuser != 12)
          OR (p.code_passager LIKE '%a5' AND p.idcptuser != 5)
          OR (p.code_passager LIKE '%k12' AND p.idcptuser != 23)
          OR (p.code_passager LIKE '%BOB1M5' AND p.idcptuser != 12)
          OR (p.code_passager LIKE '%1M5' AND p.idcptuser != 12)
      )
    ORDER BY p.date_emis DESC
    LIMIT 50
");
if (empty($detail)) {
    echo "  Aucune vente ouverte mal attribuée.\n";
} else {
    foreach ($detail as $d) {
        echo sprintf(
            "  %s | ra=%s @%-12s | %s F | %s\n",
            $d['code_passager'],
            $d['idcptuser'],
            $d['username'],
            number_format((float) $d['prixvente'], 0, ',', ' '),
            $d['date_emis']
        );
    }
    if (count($detail) >= 50) {
        echo "  … (limité à 50 lignes)\n";
    }
}

$outFile = '/tmp/audit_bob1_arret_fixable.json';
file_put_contents($outFile, json_encode($fixGroups, JSON_PRETTY_PRINT));
echo "\n## Export\n";
echo "Groupes de correction: " . count($fixGroups) . " → {$outFile}\n";
