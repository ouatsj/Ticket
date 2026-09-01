<?php
/**
 * Détecte fichiers PHP tronqués ou en erreur de syntaxe vs git HEAD.
 * Usage: php scripts/audit_truncated_files.php [--fix-php]
 *
 * --fix-php : restaure depuis git les contrôleurs/modèles/helpers tronqués (>10% manquant).
 */
$root = realpath(dirname(__DIR__));
if ($root === false) {
    fwrite(STDERR, "Racine projet introuvable.\n");
    exit(2);
}

$fix = in_array('--fix-php', $argv, true);
$dirs = array(
    'application/controllers',
    'application/models',
    'application/helpers',
    'application/views',
);
$minDelta = 30;
$minRatio = 0.90;

function git_line_count($root, $rel)
{
    $cmd = 'git -C ' . escapeshellarg($root) . ' show ' . escapeshellarg('HEAD:' . $rel) . ' 2>/dev/null | wc -l';
    $out = trim((string) shell_exec($cmd));
    return ($out !== '' && ctype_digit($out)) ? (int) $out : null;
}

function local_line_count($abs)
{
    if (!is_readable($abs)) {
        return null;
    }
    $n = 0;
    $fh = fopen($abs, 'r');
    if (!$fh) {
        return null;
    }
    while (fgets($fh) !== false) {
        $n++;
    }
    fclose($fh);
    return $n;
}

function php_syntax_ok($abs)
{
    $out = array();
    $code = 0;
    exec('php -l ' . escapeshellarg($abs) . ' 2>&1', $out, $code);
    return $code === 0 ? null : implode("\n", $out);
}

$truncated = array();
$syntax = array();
$deleted = array();
$outDeleted = trim((string) shell_exec('git -C ' . escapeshellarg($root) . ' ls-files --deleted 2>/dev/null'));
if ($outDeleted !== '') {
    $deleted = array_filter(explode("\n", $outDeleted));
}

foreach ($dirs as $dir) {
    $base = $root . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($base)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($it as $file) {
        if (!$file->isFile() || substr($file->getFilename(), -4) !== '.php') {
            continue;
        }
        $abs = $file->getPathname();
        $rel = substr($abs, strlen($root) + 1);
        $rel = str_replace('\\', '/', $rel);
        $loc = local_line_count($abs);
        $git = git_line_count($root, $rel);
        $err = php_syntax_ok($abs);
        if ($err) {
            $syntax[] = array('path' => $rel, 'local' => $loc, 'git' => $git, 'err' => $err);
            continue;
        }
        if ($git !== null && $loc !== null && $loc < $git * $minRatio && ($git - $loc) >= $minDelta) {
            $truncated[] = array('path' => $rel, 'local' => $loc, 'git' => $git, 'missing' => $git - $loc);
        }
    }
}

echo "=== Audit troncature (" . date('Y-m-d H:i:s') . ") ===\n\n";

if ($syntax) {
    echo "Erreurs syntaxe PHP (" . count($syntax) . "):\n";
    foreach ($syntax as $row) {
        echo "  - {$row['path']} (local={$row['local']}, git={$row['git']})\n";
        echo "    " . trim(str_replace("\n", ' ', $row['err'])) . "\n";
    }
    echo "\n";
} else {
    echo "Aucune erreur syntaxe PHP.\n\n";
}

if ($truncated) {
    echo "Fichiers tronqués vs git HEAD (" . count($truncated) . "):\n";
    foreach ($truncated as $row) {
        echo "  - {$row['path']}: {$row['local']} lignes (git {$row['git']}, -{$row['missing']})\n";
        if ($fix && (
            strpos($row['path'], 'application/controllers/') === 0
            || strpos($row['path'], 'application/models/') === 0
            || strpos($row['path'], 'application/helpers/') === 0
        )) {
            passthru('git -C ' . escapeshellarg($root) . ' checkout HEAD -- ' . escapeshellarg($row['path']));
            echo "    -> restauré depuis git\n";
        }
    }
    echo "\n";
} else {
    echo "Aucun fichier PHP tronqué détecté.\n\n";
}

if ($deleted) {
    echo "Fichiers suivis supprimés (" . count($deleted) . ") — vérifier si suppression volontaire:\n";
    foreach ($deleted as $d) {
        echo "  - $d\n";
    }
    echo "\n";
} else {
    echo "Aucun fichier suivi supprimé.\n\n";
}

echo "Bundles JS: php scripts/build_guichet_bundles.php && php scripts/build_module_bundles.php\n";

exit(($syntax || $truncated) ? 1 : 0);
