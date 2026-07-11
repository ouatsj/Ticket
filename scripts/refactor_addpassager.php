#!/usr/bin/env php
<?php
/**
 * Remplace les blocs dupliqués dans Programmes::addpassager / addpassagerfi.
 * Usage : php scripts/refactor_addpassager.php
 */

ini_set('pcre.backtrack_limit', '5000000');
ini_set('pcre.recursion_limit', '500000');

$path = dirname(__DIR__) . '/application/controllers/Programmes.php';
$content = file_get_contents($path);
$original = $content;

$releaseBlock = '/\s*\$dte = date\(\'H:i\', time\(\'H:i\'\)\+3600\);\s*\$result = \$this->db->query\("SELECT p\.code_passager, p\.code_ticket, p\.code_pro[\s\S]*?foreach \(\$result as \$rew\) \{[\s\S]*?\$this->m_passager->update\(\$rew->code_passager, \$rew->code_ticket, \$plarray\);[\s\S]*?\}\s*/';

$nRelease = 0;
while (preg_match($releaseBlock, $content)) {
    $next = preg_replace($releaseBlock, "\n", $content, 1);
    if ($next === null || preg_last_error() !== PREG_NO_ERROR) {
        fwrite(STDERR, "Erreur release regex: " . preg_last_error() . "\n");
        exit(1);
    }
    $content = $next;
    $nRelease++;
}

$verifBlock = '/if \(\$dernier == NULL\)\s*\{[\s\S]*?verifpassager = \'A\' WHERE code_passager = \'\$tampon\'[\s\S]*?\}\s*else\s*\{[\s\S]*?verifpassager = \'A\' WHERE code_passager = \'\$tampon\'[\s\S]*?\}\s*\}/';
$replacement = '\$this->sale_svc->apply_verif_passager(\$tampon, \$cdtick, \$dernier);';

$nVerif = 0;
while (preg_match($verifBlock, $content)) {
    $next = preg_replace($verifBlock, $replacement, $content, 1);
    if ($next === null || preg_last_error() !== PREG_NO_ERROR) {
        fwrite(STDERR, "Erreur verif regex apres $nVerif remplacements: " . preg_last_error() . "\n");
        exit(1);
    }
    $content = $next;
    $nVerif++;
}

file_put_contents($path, $content);
echo "Blocs reservation R retires: $nRelease\n";
echo "Blocs verifpassager remplaces: $nVerif\n";
echo "Termine.\n";
