<?php defined('BASEPATH') OR exit('No direct script access allowed');
$rapport = !empty($rapport) && is_array($rapport) ? $rapport : array();
$status_label = array('ok' => 'OK', 'warning' => 'Attention', 'danger' => 'Alerte', 'info' => 'Info');
$status_class = array('ok' => 'success', 'warning' => 'warning', 'danger' => 'danger', 'info' => 'info');

/** Affiche anomalies + infos de contexte ; masque le RAS (ok). */
$is_anomaly_item = function ($it) {
    $n = isset($it['niveau']) ? $it['niveau'] : '';
    return in_array($n, array('alerte', 'avertissement', 'info'), true);
};
$is_anomaly_row = function ($row) {
    $n = isset($row['niveau']) ? $row['niveau'] : '';
    // Lignes tableau : anomalies seulement (pas les validés / ok)
    return in_array($n, array('alerte', 'avertissement'), true)
        || ($n !== 'ok' && $n !== '');
};

$sections_ok = array();
$sections_ko = array();
if (!empty($rapport['sections']) && is_array($rapport['sections'])) {
    foreach ($rapport['sections'] as $sec) {
        $st = isset($sec['status']) ? $sec['status'] : 'ok';
        if (in_array($st, array('danger', 'warning', 'info'), true)) {
            $sections_ko[] = $sec;
        } else {
            $sections_ok[] = $sec;
        }
    }
}
?>
<style>
    .audit-section { margin-bottom: 1.5rem; page-break-inside: avoid; }
    .audit-section h4 { border-bottom: 1px solid #ddd; padding-bottom: .35rem; }
    .audit-item { margin: .25rem 0; padding: .35rem .55rem; border-left: 3px solid #ccc; background: #fafafa; }
    .audit-item.alerte { border-color: #c62828; background: #ffebee; }
    .audit-item.avertissement { border-color: #ef6c00; background: #fff3e0; }
    .audit-item.ok { border-color: #2e7d32; background: #e8f5e9; }
    .audit-item.info { border-color: #1565c0; background: #e3f2fd; }
    .audit-stats span { display: inline-block; margin-right: 1rem; }
    .audit-table-wrap { overflow-x: auto; max-height: 70vh; margin: .75rem 0 1rem; border: 1px solid #e0e0e0; }
    .audit-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .audit-table th, .audit-table td { border: 1px solid #e0e0e0; padding: .35rem .45rem; vertical-align: top; }
    .audit-table th { background: #f5f5f5; position: sticky; top: 0; z-index: 1; white-space: nowrap; }
    .audit-table tr.alerte { background: #ffebee; }
    .audit-table tr.avertissement { background: #fff8e1; }
    .audit-table tr.info { background: #e3f2fd; }
    .audit-table .col-comment { min-width: 220px; max-width: 320px; }
    .audit-ok-summary { font-size: 13px; color: #2e7d32; }
    @media print {
        .no-print, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .be-content, .main-content { margin: 0 !important; width: 100% !important; }
        .audit-table-wrap { max-height: none; overflow: visible; }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="no-print mb-3">
            <a class="btn btn-secondary" href="<?= site_url('audit_quotidien/' . $this->session->company->ekey); ?>">← Liste</a>
            <button type="button" class="btn btn-primary" onclick="window.print();">Imprimer</button>
        </div>

        <div class="card">
            <div class="card-header">
                Audit quotidien — <?= htmlspecialchars($row->date_rapport); ?>
                <span class="text-muted small">(anomalies uniquement)</span>
            </div>
            <div class="card-body">
                <p>
                    Généré le <strong><?= htmlspecialchars($row->generated_at); ?></strong>
                    — Alertes : <span class="badge badge-danger"><?= (int) $row->nb_alertes; ?></span>
                    — Avertissements : <span class="badge badge-warning"><?= (int) $row->nb_avertissements; ?></span>
                </p>

                <?php if (!empty($rapport['suggestions_globales'])): ?>
                    <div class="alert alert-<?= ((int) $row->nb_alertes + (int) $row->nb_avertissements) > 0 ? 'warning' : 'success'; ?>">
                        <strong>Actions prioritaires</strong>
                        <ul class="mb-0">
                            <?php foreach ($rapport['suggestions_globales'] as $sg): ?>
                                <li><?= htmlspecialchars($sg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sections_ok)): ?>
                    <p class="audit-ok-summary mb-3">
                        <strong>Sections sans anomalie :</strong>
                        <?php
                        $ok_titles = array();
                        foreach ($sections_ok as $s) {
                            $ok_titles[] = $s['titre'];
                        }
                        echo htmlspecialchars(implode(' · ', $ok_titles));
                        ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($sections_ko)): ?>
                    <?php foreach ($sections_ko as $sec): ?>
                        <?php
                        $st = isset($sec['status']) ? $sec['status'] : 'info';
                        $badge = isset($status_class[$st]) ? $status_class[$st] : 'secondary';
                        $lab = isset($status_label[$st]) ? $status_label[$st] : $st;
                        $items_anom = array();
                        if (!empty($sec['items']) && is_array($sec['items'])) {
                            foreach ($sec['items'] as $it) {
                                if ($is_anomaly_item($it)) {
                                    $items_anom[] = $it;
                                }
                            }
                        }
                        $rows_anom = array();
                        if (!empty($sec['tableau']) && is_array($sec['tableau'])) {
                            foreach ($sec['tableau'] as $rt) {
                                if ($is_anomaly_row($rt)) {
                                    $rows_anom[] = $rt;
                                }
                            }
                        }
                        ?>
                        <div class="audit-section">
                            <h4>
                                <?= htmlspecialchars($sec['titre']); ?>
                                <span class="badge badge-<?= $badge; ?>"><?= htmlspecialchars($lab); ?></span>
                            </h4>
                            <?php if (!empty($sec['stats'])): ?>
                                <div class="audit-stats mb-2">
                                    <?php foreach ($sec['stats'] as $k => $v): ?>
                                        <span><strong><?= htmlspecialchars($k); ?>:</strong> <?= htmlspecialchars((string) $v); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($items_anom as $it): ?>
                                <div class="audit-item <?= htmlspecialchars($it['niveau']); ?>">
                                    <?= htmlspecialchars($it['texte']); ?>
                                </div>
                            <?php endforeach; ?>

                            <?php if (!empty($rows_anom)): ?>
                                <?php
                                $cols = !empty($sec['tableau_colonnes']) && is_array($sec['tableau_colonnes'])
                                    ? $sec['tableau_colonnes']
                                    : array(
                                        array('key' => 'mois', 'label' => 'Mois'),
                                        array('key' => 'type', 'label' => 'Type'),
                                        array('key' => 'gare', 'label' => 'Gare'),
                                        array('key' => 'nb', 'label' => 'Lignes'),
                                        array('key' => 'montant_fmt', 'label' => 'Montant'),
                                        array('key' => 'date_envoi', 'label' => 'Date arrêt caissier'),
                                        array('key' => 'retard_j', 'label' => 'Retard'),
                                        array('key' => 'caissiers', 'label' => 'Caissier(s)'),
                                        array('key' => 'superviseur', 'label' => 'Superviseur de site'),
                                        array('key' => 'commentaire', 'label' => 'Commentaire', 'class' => 'col-comment'),
                                        array('key' => 'suggestion', 'label' => 'Suggestion', 'class' => 'col-comment'),
                                    );
                                ?>
                                <div class="audit-table-wrap">
                                    <table class="audit-table">
                                        <thead>
                                        <tr>
                                            <?php foreach ($cols as $col): ?>
                                                <th class="<?= htmlspecialchars(isset($col['class']) ? $col['class'] : ''); ?>">
                                                    <?= htmlspecialchars($col['label']); ?>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($rows_anom as $row_t): ?>
                                            <?php $niv = isset($row_t['niveau']) ? $row_t['niveau'] : ''; ?>
                                            <tr class="<?= htmlspecialchars($niv); ?>">
                                                <?php foreach ($cols as $col): ?>
                                                    <?php
                                                    $key = $col['key'];
                                                    $val = isset($row_t[$key]) ? $row_t[$key] : '';
                                                    $cls = isset($col['class']) ? $col['class'] : '';
                                                    if ($key === 'date_envoi' && ($val === '' || $val === null)) {
                                                        $val = '—';
                                                    }
                                                    if ($key === 'retard_j') {
                                                        if ($val === null || $val === '') {
                                                            $val = '—';
                                                        } else {
                                                            $val = ((int) $val) . ' j';
                                                        }
                                                    }
                                                    ?>
                                                    <td class="<?= htmlspecialchars($cls); ?>">
                                                        <?= htmlspecialchars((string) $val); ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($sec['suggestions'])): ?>
                                <p class="mt-2 mb-1"><em>Actions :</em></p>
                                <ul>
                                    <?php foreach ($sec['suggestions'] as $sg): ?>
                                        <li><?= htmlspecialchars($sg); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (!empty($sections_ok)): ?>
                    <div class="alert alert-success mb-0">Aucune anomalie détectée sur les sections auditées.</div>
                <?php else: ?>
                    <p>Rapport vide ou illisible.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
