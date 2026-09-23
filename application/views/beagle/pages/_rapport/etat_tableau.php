<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Affichage générique d’un état (tableau) + exports + retour.
 *
 * Attendu :
 * - $titre, $columns[{key,label,align?,money?}], $lignes[], $total
 * - $export_base (URL sans ?format=), $filters_qs, $retour_url
 * - $page_label (optionnel)
 * - $bordereau_envoi, $signature_agent, $signature_convoyeur (optionnel, bordereau bagages)
 */
$titre = isset($titre) ? $titre : 'État';
$page_label = isset($page_label) ? $page_label : $titre;
$columns = isset($columns) && is_array($columns) ? $columns : array();
$lignes = isset($lignes) && is_array($lignes) ? $lignes : array();
$total = isset($total) ? (float) $total : 0;
$retour = isset($retour_url) ? $retour_url : '#';
$qs = isset($filters_qs) ? $filters_qs : '';
$exportBase = isset($export_base) ? $export_base : '#';
$bordereauEnvoi = !empty($bordereau_envoi);
$sigAgent = isset($signature_agent) ? trim((string) $signature_agent) : '';
$sigConvoyeur = isset($signature_convoyeur) ? trim((string) $signature_convoyeur) : '';
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
    <?php if ($bordereauEnvoi): ?>
    .etat-bordereau-envoi {
        font-size: 15px;
        line-height: 1.35;
    }
    .etat-bordereau-envoi .card-header strong {
        font-size: 18px;
    }
    .etat-bordereau-envoi table.table {
        font-size: 14px;
    }
    .etat-bordereau-envoi table.table th,
    .etat-bordereau-envoi table.table td {
        padding: 0.55rem 0.65rem;
        vertical-align: middle;
    }
    .etat-signatures {
        display: flex;
        justify-content: space-between;
        gap: 2rem;
        margin-top: 2.5rem;
        page-break-inside: avoid;
    }
    .etat-signatures .sig-bloc {
        width: 48%;
        min-height: 110px;
    }
    .etat-signatures .sig-ligne {
        margin-top: 1.75rem;
        border-bottom: 1px solid #333;
        min-height: 1.5rem;
    }
    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        .no-print, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .be-content, .main-content { margin: 0 !important; width: 100% !important; padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .etat-bordereau-envoi {
            font-size: 13pt;
        }
        .etat-bordereau-envoi .card-header strong {
            font-size: 16pt;
        }
        .etat-bordereau-envoi table.table {
            font-size: 12pt;
        }
        .etat-bordereau-envoi table.table th,
        .etat-bordereau-envoi table.table td {
            padding: 6px 8px !important;
        }
    }
    <?php else: ?>
    @media print {
        .no-print, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .be-content, .main-content { margin: 0 !important; width: 100% !important; }
    }
    <?php endif; ?>
</style>
<div class="row<?= $bordereauEnvoi ? ' etat-bordereau-envoi' : ''; ?>">
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
                    <a class="dropdown-item" href="<?= htmlspecialchars($urlPdf); ?>" target="_blank">PDF (A4 paysage)</a>
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

                    <?php if ($bordereauEnvoi): ?>
                        <div class="etat-signatures">
                            <div class="sig-bloc text-left">
                                <div><strong>AGENT (bordereau)</strong></div>
                                <div>Nom : <strong><?= $sigAgent !== '' ? htmlspecialchars($sigAgent) : '……………………………………'; ?></strong></div>
                                <div class="sig-ligne">Signature</div>
                            </div>
                            <div class="sig-bloc text-right">
                                <div><strong>CONVOYEUR</strong></div>
                                <div>Nom : <strong><?= $sigConvoyeur !== '' ? htmlspecialchars($sigConvoyeur) : '……………………………………'; ?></strong></div>
                                <div class="sig-ligne">Signature</div>
                            </div>
                        </div>
                    <?php endif; ?>
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
