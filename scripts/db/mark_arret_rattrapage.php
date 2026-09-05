<?php
/**
 * Marque les tickets orphelins d'arrêt multi-SG pour rattrapage au prochain arrêt.
 *
 * Critère : statutvente=0, vendu, date < aujourd'hui, gare multi-SG,
 * départ dans une SG de la gare vendeur, et un compte_guichet du vendeur
 * daté >= date de vente avec idsousga <> départ (trou historique).
 *
 * Usage :
 *   php scripts/db/mark_arret_rattrapage.php
 *   php scripts/db/mark_arret_rattrapage.php --apply
 */
require __DIR__ . '/_bootstrap.php';

$m = db_script_connect($argv);
$apply = in_array('--apply', $argv, true);
$today = gmdate('Y-m-d');

$col = $m->query("SHOW COLUMNS FROM passager LIKE 'flag_rattrapage_arret'");
if (!$col || $col->num_rows === 0) {
    fwrite(STDERR, "Colonne flag_rattrapage_arret absente — lancer d'abord migrate_prod_additive_p0_p1.sql\n");
    exit(1);
}

$sqlSelect = "
SELECT p.code_passager, p.code_ticket, p.idcptuser, p.prixvente, p.datep_create,
       p.departclient_idgare, ul.guser AS gare, cu.username
FROM passager p
JOIN attributions_role ar ON p.idcptuser = ar.roleattribut
JOIN user_login ul ON ar.idgestcompte = ul.uid_login
JOIN compte_user cu ON ul.uid_usercpte = cu.cpuser_id
JOIN sousgare sgd ON sgd.idsousgare = p.departclient_idgare
WHERE p.statutvente = 0
  AND p.statut_code = 'vendu'
  AND p.prixvente IS NOT NULL
  AND p.datep_create < ?
  AND IFNULL(p.flag_rattrapage_arret, 0) = 0
  AND sgd.gareprinceid = ul.guser
  AND (SELECT COUNT(*) FROM sousgare s WHERE s.gareprinceid = ul.guser) > 1
  AND EXISTS (
    SELECT 1 FROM compte_guichet cg
    JOIN sousgare sga ON sga.idsousgare = cg.idsousga
    WHERE cg.idusercompt = p.idcptuser
      AND cg.datearretcompt >= p.datep_create
      AND cg.idsousga <> p.departclient_idgare
      AND sga.gareprinceid = ul.guser
  )
ORDER BY ul.guser, cu.username, p.datep_create
";

$stmt = $m->prepare($sqlSelect);
$stmt->bind_param('s', $today);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}
$stmt->close();

$byUser = [];
$mt = 0.0;
foreach ($rows as $r) {
    $k = $r['gare'] . '|' . $r['idcptuser'] . '|' . $r['username'];
    if (!isset($byUser[$k])) {
        $byUser[$k] = [
            'gare' => $r['gare'],
            'vendeur' => $r['idcptuser'],
            'username' => $r['username'],
            'n' => 0,
            'mt' => 0.0,
        ];
    }
    $byUser[$k]['n']++;
    $byUser[$k]['mt'] += (float) $r['prixvente'];
    $mt += (float) $r['prixvente'];
}

echo 'mode=' . ($apply ? 'APPLY' : 'DRY-RUN') . " today=$today\n";
echo 'tickets_a_marquer=' . count($rows) . " montant_total=$mt\n";
echo 'guichetiers_concernes=' . count($byUser) . "\n";
foreach ($byUser as $u) {
    echo json_encode($u, JSON_UNESCAPED_UNICODE) . "\n";
}

if (!$apply) {
    echo "Dry-run OK. Relancer avec --apply pour marquer flag_rattrapage_arret=1.\n";
    exit(0);
}

$upd = $m->prepare(
    'UPDATE passager SET flag_rattrapage_arret = 1 WHERE code_passager = ? AND code_ticket = ? AND statutvente = 0'
);
$ok = 0;
foreach ($rows as $r) {
    $upd->bind_param('ss', $r['code_passager'], $r['code_ticket']);
    if ($upd->execute()) {
        $ok += $upd->affected_rows;
    }
}
$upd->close();
echo "marques_ok=$ok\n";
