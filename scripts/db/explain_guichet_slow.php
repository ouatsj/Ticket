#!/usr/bin/env php
<?php
/**
 * Vérifie que les requêtes lentes guichet utilisent les bons index (EXPLAIN).
 * Usage : php scripts/db/explain_guichet_slow.php [--idcptuser=287]
 */
require __DIR__ . '/_bootstrap.php';

$mysqli = db_script_connect($argv);
$today = date('Y-m-d');

$idcptuser = 287;
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--idcptuser=(\d+)$/', $arg, $m)) {
        $idcptuser = (int) $m[1];
    }
}

$queries = array(
    'compteur_SUM_prixvente' => "
        EXPLAIN SELECT SUM(prixvente) AS total FROM passager p
        WHERE p.idcptuser = '{$idcptuser}'
          AND p.statut_code = 'vendu'
          AND p.statutvente = 0
          AND p.datep_create <= '{$today}'
          AND p.prixvente IS NOT NULL
          AND p.actif_pas = 0",
    'tamponcode_liste_jour' => "
        EXPLAIN SELECT p.code_passager FROM passager p
        JOIN tamponcode ctp ON p.code_passager = ctp.tamponcod
        WHERE p.num_siege_categorie IS NOT NULL
          AND p.actif_pas = 0
          AND p.datep_create >= '{$today}'
          AND ctp.actif_tamp = 0
        LIMIT 10",
    'compteurcd_jours_precedents' => "
        EXPLAIN SELECT SUM(prixvente) AS total FROM passager p
        WHERE p.idcptuser = '{$idcptuser}'
          AND p.statut_code = 'vendu'
          AND p.statutvente = 0
          AND p.datep_create < '{$today}'
          AND p.prixvente IS NOT NULL
          AND p.actif_pas = 0",
);

echo "Date : {$today} | idcptuser : {$idcptuser}\n\n";

foreach ($queries as $label => $sql) {
    echo "=== {$label} ===\n";
    $res = $mysqli->query($sql);
    if (!$res) {
        echo "ERREUR : {$mysqli->error}\n\n";
        continue;
    }
    while ($row = $res->fetch_assoc()) {
        echo sprintf(
            "  table=%s type=%s key=%s rows=%s filtered=%s extra=%s\n",
            $row['table'],
            $row['type'],
            $row['key'] ?: '(none)',
            $row['rows'],
            $row['filtered'],
            $row['Extra']
        );
    }
    echo "\n";
}

echo "Index attendus :\n";
echo "  - compteur* : idx_passager_compte (idcptuser en tête)\n";
echo "  - tamponcode_liste_jour : idx_passager_date ou idx_passager_date_cpt (range datep_create)\n\n";

$res = $mysqli->query("SHOW INDEX FROM passager WHERE Key_name IN ('idx_passager_compte','idx_passager_date_cpt')");
echo "Index présents sur passager :\n";
$seen = array();
while ($row = $res->fetch_assoc()) {
    $seen[$row['Key_name']] = true;
    echo "  OK {$row['Key_name']} ({$row['Column_name']})\n";
}
foreach (array('idx_passager_compte', 'idx_passager_date_cpt') as $name) {
    if (empty($seen[$name])) {
        echo "  MANQUANT {$name} — lancer : php scripts/db/apply_indexes.php\n";
    }
}

echo "\nTerminé.\n";
