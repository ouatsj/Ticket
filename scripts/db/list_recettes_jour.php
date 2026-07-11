<?php
/**
 * Liste les recettes du jour.
 * Usage:
 *   php scripts/db/list_recettes_jour.php [YYYY-MM-DD]
 *   php scripts/db/list_recettes_jour.php [YYYY-MM-DD] saisi [roleattribut[,roleattribut...]]
 *   php scripts/db/list_recettes_jour.php [YYYY-MM-DD] valid [roleattribut]
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require __DIR__ . '/../../application/config/database.php';
$dbCfg = $db['default'];
$db = new mysqli($dbCfg['hostname'], $dbCfg['username'], $dbCfg['password'], $dbCfg['database']);
if ($db->connect_error) {
    fwrite(STDERR, 'Connexion DB: ' . $db->connect_error . PHP_EOL);
    exit(1);
}

$day = isset($argv[1]) ? $argv[1] : date('Y-m-d');
$mode = isset($argv[2]) ? $argv[2] : 'all';
$rolesArg = isset($argv[3]) ? $argv[3] : '';

$whereExtra = '';
$label = '';
if ($mode === 'saisi' && $rolesArg !== '') {
    $roles = array_map('intval', explode(',', $rolesArg));
    $rolesList = implode(',', $roles);
    $whereExtra = " AND r.idopera IN ({$rolesList})";
    $label = " (saisies idopera IN {$rolesList})";
} elseif ($mode === 'valid' && $rolesArg !== '') {
    $validator = (int) $rolesArg;
    $whereExtra = " AND r.operavalid = {$validator}";
    $label = " (validées operavalid={$validator})";
}

$sql = "
SELECT
    r.id_recette, r.date_recet, r.type_recet, r.nom, r.montant_recet,
    r.active_recet, r.is_validerecet, r.is_actifrecet, r.operavalid, r.idopera,
    cu.username AS saisisseur, val.username AS validateur, ex.nom_gaep AS gare
FROM recette r
LEFT JOIN attributions_role ar ON r.idopera = ar.roleattribut
LEFT JOIN user_login ul ON ar.idgestcompte = ul.uid_login
LEFT JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
LEFT JOIN attributions_role arv ON r.operavalid = arv.roleattribut
LEFT JOIN user_login ulv ON arv.idgestcompte = ulv.uid_login
LEFT JOIN compte_user val ON ulv.uid_usercpte = val.cpuser_id
LEFT JOIN caisse cs ON r.idcaisse = cs.id_caiss
LEFT JOIN gare_exp ex ON cs.gexp_caiss = ex.code_gaexp
WHERE r.date_recet = ? {$whereExtra}
ORDER BY r.type_recet, r.id_recette ASC
";

$stmt = $db->prepare($sql);
$stmt->bind_param('s', $day);
$stmt->execute();
$res = $stmt->get_result();

$byType = [];
$total = 0;
$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
    $type = $row['type_recet'] ?: '(vide)';
    if (!isset($byType[$type])) {
        $byType[$type] = ['count' => 0, 'montant' => 0];
    }
    $byType[$type]['count']++;
    $byType[$type]['montant'] += (float) $row['montant_recet'];
    $total += (float) $row['montant_recet'];
}
$stmt->close();

echo "=== RECETTES DU {$day}{$label} ===\n\n";

echo "--- Synthèse par type ---\n";
printf("%-15s %6s %15s\n", 'TYPE', 'NB', 'MONTANT (F)');
foreach ($byType as $type => $s) {
    printf("%-15s %6d %15s\n", $type, $s['count'], number_format($s['montant'], 0, '', ' '));
}
printf("%-15s %6d %15s\n", 'TOTAL', count($rows), number_format($total, 0, '', ' '));

echo "\n--- Détail ---\n";
printf(
    "%-8s %-10s %-12s %-20s %12s %-10s %-8s %-8s %-8s %-10s %-12s\n",
    'ID', 'TYPE', 'GARE', 'VENDEUR', 'MONTANT', 'VALIDÉE', 'ACTIF', 'OPERA', 'FERMÉ', 'VALIDATEUR', 'NOM'
);

foreach ($rows as $r) {
    $validee = ($r['is_validerecet'] == 1 || $r['is_actifrecet'] == 1 || $r['active_recet'] == 1) ? 'oui' : 'non';
    printf(
        "%-8s %-10s %-12s %-20s %12s %-10s %-8s %-8s %-8s %-10s %-12s\n",
        $r['id_recette'],
        $r['type_recet'],
        mb_substr($r['gare'] ?: '-', 0, 12),
        mb_substr($r['saisisseur'] ?: '-', 0, 20),
        number_format((float) $r['montant_recet'], 0, '', ' '),
        $validee,
        $r['is_actifrecet'] ? '1' : '0',
        $r['operavalid'] ?: '-',
        $r['ferme_caisrecet'] ? '1' : '0',
        mb_substr($r['validateur'] ?: '-', 0, 10),
        mb_substr($r['nom'] ?: '-', 0, 12)
    );
}

echo "\nLégende: VALIDÉE=oui si active_recet, is_validerecet ou is_actifrecet = 1\n";
