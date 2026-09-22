<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Affichage générique d’un état (tableau) + exports + retour.
 *
 * Attendu :
 * - $titre, $columns[{key,label,align?,money?}], $lignes[], $total
 * - $export_base (URL sans ?format=), $filters_qs, $retour_url
 * - $page_label (optionnel)
 */
$titre = isset($titre) ? $titre : 'État';
$page_label = isset($page_label) ? $page_label : $titre;
$columns = isset($columns) && is_array($columns) ? $columns : array();
$lignes = isset($lignes) && is_array($lignes) ? $lignes : array();
$total = isset($total) ? (float) $total : 0;
$retour = isset($retour_url) ? $retour_url : '#';
$qs = isset($filters_qs) ? $filters_qs : '';
$exportBase = isset($export_base) ? $export_base : '#';
$sep = (strpos($exportBase, '?') === false) ? '?' : '&';
$urlPdf = $exportBase . $sep . 'format=pdf' . ($qs !== '' ? '&' . $qs : '');
$urlCsv = $exportBase . $sep . 'format=csv' . ($qs !== '' ? '&' . $qs : '');
$urlXls = $exportBase . $sep . 'format=excel' . ($qs !== '' ? '&' . $qs : '');
$fmt = function ($n) {
    return number_format((float) $n, 0, ',', ' ');
};
$colCount = max(1, count($columns));
?>
<style>
    @media print {
        .no-print, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .be-content, .main-content { margin: 0 !important; width: 100% !important; }
    }
</style>
<div class="row">
    <div class="col-12">
        <div class="mb-3 no-print d-flex flex-wrap align-items-center">
            <a class="btn btn-secondary btn-space" href="<?= htmlspecialchars($retour); ?>" id="btn-retour-etat">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
            </a>
            <div class="btn-group btn-space">
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-download"></i>&nbsp;EXPORTER&nbsp;
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?= htmlspecialchars($urlPdf); ?>" target="_blank">PDF</a>
                    <a class="dropdown-item" href="<?= htmlspecialchars($urlCsv); ?>">CSV</a>
                    <a class="dropdown-item" href="<?= htmlspecialchars($urlXls); ?>">Excel</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="window.print(); return false;">Imprimer</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <strong><?= htmlspecialchars($titre); ?></strong>
            </div>
            <div class="card-body table-responsive">
                <?php if (empty($lignes)): ?>
                    <div class="alert alert-warning mb-0">Aucun résultat pour ces critères.</div>
                <?php else: ?>
                    <table class="table table-sm table-striped table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <?php foreach ($columns as $col): ?>
                                    <?php
                                    $align = isset($col['align']) ? $col['align'] : 'left';
                                    $cls = ($align === 'right') ? 'text-right' : (($align === 'center') ? 'text-center' : '');
                                    ?>
                                    <th class="<?= $cls; ?>"><?= htmlspecialchars(isset($col['label']) ? $col['label'] : ''); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lignes as $row): ?>
                                <tr>
                                    <?php foreach ($columns as $col): ?>
                                        <?php
                                        $key = isset($col['key']) ? $col['key'] : '';
                                        $val = ($key !== '' && isset($row[$key])) ? $row[$key] : '';
                                        $align = isset($col['align']) ? $col['align'] : 'left';
                                        $cls = ($align === 'right') ? 'text-right' : (($align === 'center') ? 'text-center' : '');
                                        $isMoney = !empty($col['money']);
                                        ?>
                                        <td class="<?= $cls; ?>">
                                            <?= $isMoney ? $fmt($val) : htmlspecialchars((string) $val); ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="<?= max(1, $colCount - 1); ?>">TOTAL</th>
                                <th class="text-right"><?= $fmt($total); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var btn = document.getElementById('btn-retour-etat');
    if (!btn) return;
    btn.addEventListener('click', function (e) {
        if (window.history.length > 1) {
            e.preventDefault();
            window.history.back();
        }
    });
})();
</script>
