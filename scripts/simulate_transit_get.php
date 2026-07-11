<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
require __DIR__ . '/../application/config/database.php';
require __DIR__ . '/../application/helpers/ticket_prix_helper.php';
$c = $db['default'];
$mysqli = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);

function simulate_get($mysqli, $cid, $p_id, $tf, $t)
{
    $sql = "SELECT p.code_passager, p.prixvente, tf.prix AS tarif_prix, lh.id_ligneheure, lg.nom_ligne, pr.typetarif, t.id_tarifs
    FROM tamponcode ctp
    JOIN passager p ON p.code_passager = ctp.tamponcod
    JOIN programme pr ON p.code_pro = pr.code_progr
    JOIN ligne_heure lh ON pr.id_heur = lh.id_ligneheure
    JOIN lignes lg ON lh.ligne_id = lg.ident_ligne
    JOIN tarifs t ON pr.typetarif = t.id_tarifs
    JOIN tarification tf ON tf.typetarif_id = t.id_tarifs AND tf.ligne_heure_id = lh.id_ligneheure
    JOIN gare_exp ex ON lg.gaexp_lg = ex.code_gaexp
    JOIN gare_dest dest ON lg.gadest_lg = dest.code_gadest
    JOIN compagnies c ON dest.id_compaga = c.cle_compagnie
    JOIN entreprise e ON c.id_entrep = e.id_entreprise
    WHERE e.ekey = '" . $mysqli->real_escape_string($cid) . "'
    AND ctp.tamponcod = '" . $mysqli->real_escape_string($p_id) . "'
    AND lh.id_ligneheure = '" . $mysqli->real_escape_string($t) . "'
    AND t.id_tarifs = '" . $mysqli->real_escape_string($tf) . "'
    AND p.actif_pas = 0";
    $res = $mysqli->query($sql);
    $row = $res ? $res->fetch_object() : null;
    if (!$row) {
        return ['found' => false, 'p_id' => $p_id, 'tf' => $tf, 't' => $t];
    }
    $before = $row->tarif_prix;
    ticket_impression_prix_row($row);
    $row->prix = $row->prix ?? $before;
    return [
        'found' => true,
        'p_id' => $p_id,
        'ligne' => $row->nom_ligne,
        'lh' => $row->id_ligneheure,
        'prixvente' => $row->prixvente,
        'tarif_prix' => $before,
        'prix_after' => $row->prix,
    ];
}

$ekeyRes = $mysqli->query("SELECT ekey FROM entreprise LIMIT 1");
$ekey = $ekeyRes->fetch_object()->ekey;

$cases = [
    ['260710127OUA1R146', 1, 806],
    ['260710128OUA1R146', 1, 1091],
    ['260710124OUA1R146', 1, 806],
    ['260710125OUA1R146', 1, 1],
    ['260710130BOB1a5', 1, 16],
    ['260710131BOB1a5', 1, 165],
];

echo "Simulation get() pour segments transit:\n";
foreach ($cases as [$pid, $tf, $lh]) {
    $r = simulate_get($mysqli, $ekey, $pid, $tf, $lh);
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

// Test mauvais lh (erreur URL possible)
echo "\nTest lh incorrect pour segment 2 (806 au lieu de 1091):\n";
echo json_encode(simulate_get($mysqli, $ekey, '260710128OUA1R146', 1, 806), JSON_UNESCAPED_UNICODE) . "\n";
