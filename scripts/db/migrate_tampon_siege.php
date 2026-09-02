<?php
/**
 * Tampon siège : created_at + UNIQUE (codepro, numsieg).
 * Usage: php scripts/db/migrate_tampon_siege.php
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

if (!$m->query("SHOW TABLES LIKE 'tampon_siege'")->num_rows) {
    fwrite(STDERR, "Table tampon_siege absente.\n");
    exit(1);
}

$col = $m->query("SHOW COLUMNS FROM tampon_siege LIKE 'created_at'");
if (!$col || $col->num_rows === 0) {
    $sql = "ALTER TABLE tampon_siege
            ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP";
    if (!$m->query($sql)) {
        fwrite(STDERR, $m->error . "\n");
        exit(1);
    }
    echo "OK colonne created_at\n";
} else {
    echo "OK colonne created_at déjà présente\n";
}

$idx = $m->query("SHOW INDEX FROM tampon_siege WHERE Key_name = 'uq_tampon_code_siege'");
if (!$idx || $idx->num_rows === 0) {
    if (!$m->query(
        "DELETE t1 FROM tampon_siege t1
         INNER JOIN tampon_siege t2
           ON t1.codepro = t2.codepro AND t1.numsieg = t2.numsieg AND t1.idtamp > t2.idtamp
         WHERE t1.codepro IS NOT NULL AND t1.numsieg IS NOT NULL"
    )) {
        fwrite(STDERR, $m->error . "\n");
        exit(1);
    }
    echo "OK doublons tampon supprimés\n";

    if (!$m->query(
        "ALTER TABLE tampon_siege ADD UNIQUE KEY uq_tampon_code_siege (codepro, numsieg)"
    )) {
        fwrite(STDERR, $m->error . "\n");
        exit(1);
    }
    echo "OK index UNIQUE uq_tampon_code_siege\n";
} else {
    echo "OK index uq_tampon_code_siege déjà présent\n";
}

echo "Migration tampon_siege terminée.\n";
