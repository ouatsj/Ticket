<?php
/**
 * Aperçu du filtre d'octobre : lignes d'escale déjà validées par la caissière,
 * courrier compris, pas encore enfermées dans un arrêt. N'écrit rien.
 *
 * php scripts/db/filtre_sous_caisse_octobre.php --allow-remote
 */
require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv);
$debut = '2026-10-01';

function apercu($mysqli, $titre, $sql)
{
    echo "\n== {$titre} ==\n";
    $res = $mysqli->query($sql);
    if (!$res) {
        echo "ERREUR " . $mysqli->error . "\n";
        return;
    }
    $nb = 0;
    $total = 0;
    while ($row = $res->fetch_assoc()) {
        $nb += (int) $row['nb'];
        $total += (float) $row['total'];
        echo $row['escale'] . " | " . $row['libelle']
            . " | " . $row['nb'] . " lignes | " . $row['total'] . "\n";
    }
    echo "Sous-total : {$nb} lignes, {$total}\n";
    $res->free();
}

$vendeur = "ar.userole = 17";
$periode = "r.date_recet >= '{$debut}' AND r.date_recet <= CURDATE()";

apercu(
    $mysqli,
    'Recettes ticket, bagage et courrier, caissière, arrêt non fermé',
    "SELECT COALESCE(NULLIF(TRIM(ar.vente_escale_value), ''), CONCAT('guser:', ul.guser)) AS escale,
            COALESCE(NULLIF(TRIM(ar.vente_escale_label), ''), ul.guser) AS libelle,
            COUNT(*) AS nb, COALESCE(SUM(r.montant_recet), 0) AS total
     FROM recette r
     JOIN attributions_role ar ON ar.roleattribut = r.idopera
     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
     WHERE {$periode}
       AND r.is_actifrecet = 1
       AND IFNULL(r.ferme_caisrecet, 0) = 0
       AND {$vendeur}
     GROUP BY escale, libelle
     ORDER BY libelle"
);

apercu(
    $mysqli,
    'Dépenses, caissière, arrêt non fermé',
    "SELECT COALESCE(NULLIF(TRIM(ar.vente_escale_value), ''), CONCAT('guser:', ul.guser)) AS escale,
            COALESCE(NULLIF(TRIM(ar.vente_escale_label), ''), ul.guser) AS libelle,
            COUNT(*) AS nb, COALESCE(SUM(d.montant_depens), 0) AS total
     FROM depense d
     JOIN attributions_role ar ON ar.roleattribut = d.idop_dep
     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
     WHERE d.date_depens >= '{$debut}' AND d.date_depens <= CURDATE()
       AND d.is_actifdep = 1
       AND IFNULL(d.ferme_caisdep, 0) = 0
       AND {$vendeur}
     GROUP BY escale, libelle
     ORDER BY libelle"
);

apercu(
    $mysqli,
    'Dépôts, caissière, arrêt non fermé',
    "SELECT COALESCE(NULLIF(TRIM(ar.vente_escale_value), ''), CONCAT('guser:', ul.guser)) AS escale,
            COALESCE(NULLIF(TRIM(ar.vente_escale_label), ''), ul.guser) AS libelle,
            COUNT(*) AS nb, COALESCE(SUM(d.montant_depot), 0) AS total
     FROM depot d
     JOIN attributions_role ar ON ar.roleattribut = d.idop_depot
     JOIN user_login ul ON ar.idgestcompte = ul.uid_login
     WHERE d.datedepot >= '{$debut}' AND d.datedepot <= CURDATE()
       AND d.is_actifdepo = 1
       AND IFNULL(d.ferme_caisdepo, 0) = 0
       AND {$vendeur}
     GROUP BY escale, libelle
     ORDER BY libelle"
);

echo "\nAucune ligne n'a été déplacée.\n";
