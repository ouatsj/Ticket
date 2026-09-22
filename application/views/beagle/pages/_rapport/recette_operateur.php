<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Affichage in-app : recette tickets par opérateur.
 */
$titre = isset($titre) ? $titre : 'Recette par opérateur';
$lignes = isset($lignes) && is_array($lignes) ? $lignes : array();
$total = isset($total) ? (float) $total : 0;
$retour = isset($retour_url) ? $retour_url : '#';
$qs = isset($filters_qs) ? $filters_qs : '';
$ckey = isset($ckey) ? $ckey : $this->session->company->ekey;
$gidUrl = isset($gid_url) ? $gid_url : '';
$baseExport = site_url('Rapport/triencaissement_export/' . rawurlencode($ckey) . '/' . rawurlencode($gidUrl));
$urlPdf = $baseExport . '?format=pdf' . ($qs !== '' ? '&' . $qs : '');
$urlCsv = $baseExport . '?format=csv' . ($qs !== '' ? '&' . $qs : '');
$urlXls = $baseExport . '?format=excel' . ($qs !== '' ? '&' . $qs : '');
$fmt = function ($n) {
    return number_format((float) $n, 0, ',', ' ');
};
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
            <a class="btn btn-secondary btn-space" href="<?= htmlspecialchars($retour); ?>" id="btn-retour-recette-op">
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
                    <table class="table table-sm table-striped table-bordered mb-0" id="table-recette-operateur">
                        <thead class="thead-light">
                            <tr>
                                <th>Nom</th>
                                <th>Ligne</th>
                                <th class="text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lignes as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars(isset($row['nom']) ? $row['nom'] : ''); ?></td>
                                    <td><?= htmlspecialchars(isset($row['ligne']) ? $row['ligne'] : ''); ?></td>
                                    <td class="text-right"><?= $fmt(isset($row['montant']) ? $row['montant'] : 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">TOTAL</th>
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
    var btn = document.getElementById('btn-retour-recette-op');
    if (!btn) return;
    btn.addEventListener('click', function (e) {
        if (window.history.length > 1) {
            e.preventDefault();
            window.history.back();
        }
    });
})();
</script>
