<?php
/**
 * Contrainte UNIQUE siège actif : un seul passager actif (actif_pas=0) par (code_pro, num_siege).
 * Colonne générée + index uq_passager_siege_actif (MySQL 5.7.6+).
 *
 * Usage: php scripts/db/migrate_passager_siege_unique.php
 *        php scripts/db/migrate_passager_siege_unique.php --dry-run
 */
require __DIR__ . '/_bootstrap.php';
$m = db_script_connect($argv);
$dryRun = in_array('--dry-run', $argv, true);
$fixDuplicates = in_array('--fix-duplicates', $argv, true);

function column_exists(mysqli $m, $table, $column)
{
    $table = $m->real_escape_string($table);
    $column = $m->real_escape_string($column);
    $r = $m->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    return ($r && $r->num_rows > 0);
}

function index_exists(mysqli $m, $table, $index)
{
    $table = $m->real_escape_string($table);
    $index = $m->real_escape_string($index);
    $r = $m->query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
    return ($r && $r->num_rows > 0);
}

$dupSql = "SELECT code_pro, num_siege_categorie, COUNT(*) AS n
           FROM passager
           WHERE actif_pas = 0
             AND num_siege_categorie IS NOT NULL
             AND num_siege_categorie > 0
           GROUP BY code_pro, num_siege_categorie
           HAVING n > 1
           LIMIT 20";
$dupRes = $m->query($dupSql);
if ($dupRes && $dupRes->num_rows > 0) {
    fwrite(STDERR, "ATTENTION: doublons sièges actifs détectés (max 20 affichés):\n");
    $allDups = $m->query(
        "SELECT code_pro, num_siege_categorie, COUNT(*) AS n
         FROM passager
         WHERE actif_pas = 0
           AND num_siege_categorie IS NOT NULL
           AND num_siege_categorie > 0
         GROUP BY code_pro, num_siege_categorie
         HAVING n > 1"
    );
    while ($row = $dupRes->fetch_assoc()) {
        fwrite(STDERR, sprintf(
            "  code_pro=%s siege=%s count=%s\n",
            $row['code_pro'],
            $row['num_siege_categorie'],
            $row['n']
        ));
    }
    if (!$fixDuplicates) {
        fwrite(STDERR, "Relancez avec --fix-duplicates pour libérer les doublons (garde le passager le plus ancien).\n");
        exit(2);
    }
    echo ($dryRun ? '[dry-run] ' : '') . "Correction des doublons sièges actifs…\n";
    if ($allDups) {
        while ($g = $allDups->fetch_assoc()) {
            $code = $g['code_pro'];
            $siege = (int) $g['num_siege_categorie'];
            $keep = $m->query(
                "SELECT code_passager, code_ticket FROM passager
                 WHERE code_pro = '" . $m->real_escape_string($code) . "'
                   AND num_siege_categorie = {$siege}
                   AND actif_pas = 0
                 ORDER BY date_emis ASC, code_passager ASC
                 LIMIT 1"
            );
            if (!$keep || !($keepRow = $keep->fetch_assoc())) {
                continue;
            }
            $keepPass = $m->real_escape_string($keepRow['code_passager']);
            $keepTick = $m->real_escape_string($keepRow['code_ticket']);
            $sqlFix = "UPDATE passager SET num_siege_categorie = NULL
                       WHERE code_pro = '" . $m->real_escape_string($code) . "'
                         AND num_siege_categorie = {$siege}
                         AND actif_pas = 0
                         AND NOT (code_passager = '{$keepPass}' AND code_ticket = '{$keepTick}')";
            if ($dryRun) {
                echo "  fix {$code}#{$siege} keep {$keepPass}\n";
            } elseif (!$m->query($sqlFix)) {
                fwrite(STDERR, $m->error . "\n");
                exit(1);
            }
        }
    }
}

$col = 'uq_siege_actif_key';
if (!column_exists($m, 'passager', $col)) {
    $sqlCol = "ALTER TABLE passager ADD COLUMN {$col} VARCHAR(220) GENERATED ALWAYS AS (
        IF(actif_pas = 0 AND num_siege_categorie IS NOT NULL AND num_siege_categorie > 0,
           CONCAT(code_pro, '#', num_siege_categorie),
           NULL)
    ) STORED";
    echo ($dryRun ? '[dry-run] ' : '') . "ADD COLUMN {$col}\n";
    if (!$dryRun && !$m->query($sqlCol)) {
        fwrite(STDERR, $m->error . "\n");
        exit(1);
    }
} else {
    echo "OK colonne {$col} déjà présente\n";
}

$idx = 'uq_passager_siege_actif';
if (!index_exists($m, 'passager', $idx)) {
    $sqlIdx = "ALTER TABLE passager ADD UNIQUE KEY {$idx} ({$col})";
    echo ($dryRun ? '[dry-run] ' : '') . "ADD UNIQUE {$idx}\n";
    if (!$dryRun && !$m->query($sqlIdx)) {
        fwrite(STDERR, $m->error . "\n");
        exit(1);
    }
} else {
    echo "OK index {$idx} déjà présent\n";
}

echo "Migration siège UNIQUE terminée.\n";
