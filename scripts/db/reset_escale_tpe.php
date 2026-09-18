<?php
/**
 * Vide les configs Escale TPE (prix_escale_origine → NULL).
 * N’affecte pas Escales tarifées (prix_escale inchangé).
 * Usage: php scripts/db/reset_escale_tpe.php [--allow-remote] [--entreprise=ID]
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

$cid = null;
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--entreprise=') === 0) {
        $cid = (int) substr($arg, strlen('--entreprise='));
    }
}

$col = $m->query("SHOW COLUMNS FROM itineraire_escales LIKE 'prix_escale_origine'");
if (!$col || $col->num_rows === 0) {
    echo "OK rien à faire : colonne prix_escale_origine absente\n";
    exit(0);
}

if ($cid > 0) {
    $sql = "UPDATE itineraire_escales ie
            JOIN lignes parent ON parent.ident_ligne = ie.id_lignes
            JOIN gare_exp ge ON ge.code_gaexp = parent.gaexp_lg
            JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
            JOIN entreprise e ON c.id_entrep = e.id_entreprise
            SET ie.prix_escale_origine = NULL"
        . ($m->query("SHOW COLUMNS FROM itineraire_escales LIKE 'prix_escale_tpe'") && $m->query("SHOW COLUMNS FROM itineraire_escales LIKE 'prix_escale_tpe'")->num_rows > 0
            ? ', ie.prix_escale_tpe = NULL' : '')
        . " WHERE e.id_entreprise = " . (int) $cid;
} else {
    $hasTpe = $m->query("SHOW COLUMNS FROM itineraire_escales LIKE 'prix_escale_tpe'");
    $sql = "UPDATE itineraire_escales SET prix_escale_origine = NULL"
        . ($hasTpe && $hasTpe->num_rows > 0 ? ', prix_escale_tpe = NULL' : '');
}

if (!$m->query($sql)) {
    fwrite(STDERR, 'UPDATE échoué: ' . $m->error . "\n");
    exit(1);
}
echo 'OK Escale TPE vidé : ' . (int) $m->affected_rows . " ligne(s) réinitialisée(s)\n";
