#!/usr/bin/env php
<?php
/**
 * Remplace les liens RETOUR GARE dupliqués par le partial _partials/btn_retour_gare.
 * Usage : php scripts/apply_btn_retour_gare_partial.php
 */
$root = dirname(__DIR__);
$dir = $root . '/application/views/beagle/pages/guichet';
$pattern = '#<a href="\<\?= site_url\(\'gares/\'\. \$this->session->company->ekey \. \'/gTs/\'\s*\.\s*\$bus_stop->idengare\.\'/sousgare/\'\.\$conex->roleattribut\.\'/\' \. mdate\("%d/%m/%Y", now\(\'UTC\'\)\)\); \?>"\s*class="btn btn-secondary btn-space md-trigger" data-modal="">\s*<i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;\s*</a>#s';
$replacement = '<?php $this->load->view(\'_partials/btn_retour_gare\'); ?>';
$total = 0;

foreach (glob($dir . '/role_*.php') as $file) {
    $content = file_get_contents($file);
    $new = preg_replace($pattern, $replacement, $content, -1, $count);
    if ($count > 0 && $new !== null) {
        file_put_contents($file, $new);
        echo basename($file) . ": {$count}\n";
        $total += $count;
    }
}

echo $total > 0 ? "OK — {$total} remplacement(s)\n" : "Rien à remplacer.\n";
exit($total > 0 ? 0 : 0);
