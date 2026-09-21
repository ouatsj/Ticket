<?php
/**
 * Multi-correspondances : 1 principal → N suites.
 * Retire UNIQUE uq_principal ; garde uq_principal_suite.
 * Usage: php scripts/db/migrate_programme_correspondance_multi.php [--allow-remote]
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);

$t = 'programme_correspondance';
$r = $m->query("SHOW TABLES LIKE '{$t}'");
if (!$r || $r->num_rows === 0) {
    fwrite(STDERR, "Table {$t} absente — créez-la d'abord (migrate_programme_correspondance.php).\n");
    exit(1);
}

function index_exists(mysqli $m, $table, $name)
{
    $esc = $m->real_escape_string($name);
    $res = $m->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$esc}'");
    return $res && $res->num_rows > 0;
}

// 1) Drop uq_principal (bloque le multi-lien)
if (index_exists($m, $t, 'uq_principal')) {
    if (!$m->query("ALTER TABLE `{$t}` DROP INDEX `uq_principal`")) {
        fwrite(STDERR, "DROP uq_principal: " . $m->error . "\n");
        exit(1);
    }
    echo "OK DROP INDEX uq_principal\n";
} else {
    echo "SKIP uq_principal (absent)\n";
}

// 2) Index non-unique pour lister les liens d'un principal
if (!index_exists($m, $t, 'idx_principal')) {
    if (!$m->query("ALTER TABLE `{$t}` ADD INDEX `idx_principal` (`code_progr_principal`)")) {
        fwrite(STDERR, "ADD idx_principal: " . $m->error . "\n");
        exit(1);
    }
    echo "OK ADD INDEX idx_principal\n";
} else {
    echo "SKIP idx_principal (déjà présent)\n";
}

// 3) Une suite ne peut appartenir qu'à un seul lien
if (!index_exists($m, $t, 'uq_suite')) {
    // Nettoyer doublons suite avant UNIQUE (garder id_lien min)
    $dup = $m->query(
        "SELECT code_progr_suite, COUNT(*) c FROM `{$t}`
         WHERE code_progr_suite IS NOT NULL AND code_progr_suite <> ''
         GROUP BY code_progr_suite HAVING c > 1"
    );
    if ($dup && $dup->num_rows > 0) {
        fwrite(STDERR, "Doublons code_progr_suite détectés — nettoyez avant uq_suite.\n");
        while ($row = $dup->fetch_assoc()) {
            fwrite(STDERR, "  suite={$row['code_progr_suite']} x{$row['c']}\n");
        }
        exit(1);
    }
    if (!$m->query("ALTER TABLE `{$t}` ADD UNIQUE KEY `uq_suite` (`code_progr_suite`)")) {
        fwrite(STDERR, "ADD uq_suite: " . $m->error . "\n");
        exit(1);
    }
    echo "OK ADD UNIQUE uq_suite\n";
} else {
    echo "SKIP uq_suite (déjà présent)\n";
}

// 4) Un dérivé ne peut appartenir qu'à un seul lien (NULL autorisés en multiple)
if (!index_exists($m, $t, 'uq_derive')) {
    $dup = $m->query(
        "SELECT code_progr_derive, COUNT(*) c FROM `{$t}`
         WHERE code_progr_derive IS NOT NULL AND code_progr_derive <> ''
         GROUP BY code_progr_derive HAVING c > 1"
    );
    if ($dup && $dup->num_rows > 0) {
        fwrite(STDERR, "Doublons code_progr_derive détectés — nettoyez avant uq_derive.\n");
        while ($row = $dup->fetch_assoc()) {
            fwrite(STDERR, "  derive={$row['code_progr_derive']} x{$row['c']}\n");
        }
        exit(1);
    }
    if (!$m->query("ALTER TABLE `{$t}` ADD UNIQUE KEY `uq_derive` (`code_progr_derive`)")) {
        fwrite(STDERR, "ADD uq_derive: " . $m->error . "\n");
        exit(1);
    }
    echo "OK ADD UNIQUE uq_derive\n";
} else {
    echo "SKIP uq_derive (déjà présent)\n";
}

// 5) Garde uq_principal_suite
if (!index_exists($m, $t, 'uq_principal_suite')) {
    if (!$m->query(
        "ALTER TABLE `{$t}` ADD UNIQUE KEY `uq_principal_suite` (`code_progr_principal`, `code_progr_suite`)"
    )) {
        fwrite(STDERR, "ADD uq_principal_suite: " . $m->error . "\n");
        exit(1);
    }
    echo "OK ADD UNIQUE uq_principal_suite\n";
} else {
    echo "SKIP uq_principal_suite (déjà présent)\n";
}

echo "OK migrate_programme_correspondance_multi\n";
