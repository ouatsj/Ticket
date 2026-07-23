<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$f = isset($filters) ? $filters : array();
$stats = isset($stats) ? $stats : array();
$lignes = isset($lignes) ? $lignes : array();
$gares = isset($gares) ? $gares : array();
$compagnies = isset($compagnies) ? $compagnies : array();
$qs = isset($filters_qs) ? $filters_qs : http_build_query(array_filter(array(
    'date_debut' => $f['date_debut'] ?? null,
    'date_fin' => $f['date_fin'] ?? null,
    'gare' => $f['gare'] ?? null,
    'type' => $f['type'] ?? null,
    'compagnie' => $f['compagnie'] ?? null,
    'arret' => $f['arret'] ?? null,
)));
$ckey = $this->session->company->ekey;
$url_export = site_url('rapport_autre_vente/' . $ckey . '/export') . ($qs !== '' ? ('?' . $qs) : '');
$url_print = site_url('rapport_autre_vente/' . $ckey . '/imprimer') . ($qs !== '' ? ('?' . $qs) : '');
$fmt = function ($n) {
    if ($n === null || $n === '') {
        return '—';
    }
    return number_format((float) $n, 0, ',', ' ') . ' F';
};
?>
<style>
    @media print {
        .no-print, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .be-content, .main-content { margin: 0 !important; width: 100% !important; }
        .col-actions { display: none !important; }
    }
</style>
<div class="row">
    <div class="col-12">
        <div class="card mb-3 no-print">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <span>Autres ventes — tous tickets</span>
                <span>
                    <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($url_print); ?>" target="_blank">
                        Imprimer
                    </a>
                    <a class="btn btn-sm btn-success" href="<?= htmlspecialchars($url_export); ?>">
                        Exporter CSV
                    </a>
                </span>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    Toutes les <strong>autres ventes</strong> (table ordres), toutes compagnies (CBT, CMT, CIT, VIP…),
                    avec mention <strong>Arrêté / Non arrêté</strong> (<code>statutvente</code>).
                </p>
                <?= form_open(site_url('rapport_autre_vente/' . $ckey), array(
                    'method' => 'get',
                    'class' => 'form-inline flex-wrap',
                )); ?>
                    <label class="mr-2 mb-2">Du</label>
                    <input type="date" name="date_debut" class="form-control form-control-sm mr-2 mb-2"
                           value="<?= htmlspecialchars($f['date_debut'] ?? ''); ?>">
                    <label class="mr-2 mb-2">au</label>
                    <input type="date" name="date_fin" class="form-control form-control-sm mr-2 mb-2"
                           value="<?= htmlspecialchars($f['date_fin'] ?? ''); ?>">
                    <label class="mr-2 mb-2">Compagnie</label>
                    <select name="compagnie" class="form-control form-control-sm mr-2 mb-2">
                        <option value="">Toutes</option>
                        <?php foreach ($compagnies as $c): ?>
                            <option value="<?= htmlspecialchars($c->cle_compagnie); ?>"
                                <?= (($f['compagnie'] ?? '') == $c->cle_compagnie) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($c->nom_compagnie); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="mr-2 mb-2">Gare vendeur</label>
                    <select name="gare" class="form-control form-control-sm mr-2 mb-2">
                        <option value="">Toutes</option>
                        <?php foreach ($gares as $g): ?>
                            <option value="<?= htmlspecialchars($g->idengare); ?>"
                                <?= (($f['gare'] ?? '') === $g->idengare) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($g->garenom ?: $g->idengare); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="mr-2 mb-2">Prix</label>
                    <select name="type" class="form-control form-control-sm mr-2 mb-2">
                        <option value="all" <?= (($f['type'] ?? '') === 'all') ? 'selected' : ''; ?>>Tous</option>
                        <option value="anomalies" <?= (($f['type'] ?? '') === 'anomalies') ? 'selected' : ''; ?>>Anomalies (0 F / hors tarif)</option>
                        <option value="gratuit" <?= (($f['type'] ?? '') === 'gratuit') ? 'selected' : ''; ?>>0 F (gratuit)</option>
                        <option value="hors" <?= (($f['type'] ?? '') === 'hors') ? 'selected' : ''; ?>>Hors tarif</option>
                        <option value="conforme" <?= (($f['type'] ?? '') === 'conforme') ? 'selected' : ''; ?>>Conforme catalogue</option>
                    </select>
                    <label class="mr-2 mb-2">Arrêt</label>
                    <select name="arret" class="form-control form-control-sm mr-2 mb-2">
                        <option value="all" <?= (($f['arret'] ?? '') === 'all') ? 'selected' : ''; ?>>Tous</option>
                        <option value="non" <?= (($f['arret'] ?? '') === 'non') ? 'selected' : ''; ?>>Non arrêté</option>
                        <option value="oui" <?= (($f['arret'] ?? '') === 'oui') ? 'selected' : ''; ?>>Arrêté</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary mb-2">Filtrer</button>
                <?= form_close(); ?>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2 col-6 mb-2">
                <div class="border rounded p-2 text-center">
                    <div class="text-muted small">Total</div>
                    <strong><?= (int) ($stats['total'] ?? 0); ?></strong>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="border rounded p-2 text-center">
                    <div class="text-muted small">Gratuits</div>
                    <strong><?= (int) ($stats['gratuits'] ?? 0); ?></strong>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="border rounded p-2 text-center">
                    <div class="text-muted small">Hors tarif</div>
                    <strong><?= (int) ($stats['hors_tarif'] ?? 0); ?></strong>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="border rounded p-2 text-center">
                    <div class="text-muted small">Conformes</div>
                    <strong><?= (int) ($stats['conformes'] ?? 0); ?></strong>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="border rounded p-2 text-center">
                    <div class="text-muted small">Non arrêtés</div>
                    <strong><?= (int) ($stats['non_arretes'] ?? 0); ?></strong>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-2">
                <div class="border rounded p-2 text-center">
                    <div class="text-muted small">Arrêtés</div>
                    <strong><?= (int) ($stats['arretes'] ?? 0); ?></strong>
                </div>
            </div>
        </div>

        <div class="card card-table">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <span>
                    Détail
                    <?php if (!empty($stats['date_debut'])): ?>
                        <span class="text-muted small">
                            (<?= htmlspecialchars($stats['date_debut']); ?>
                            → <?= htmlspecialchars($stats['date_fin']); ?>)
                        </span>
                    <?php endif; ?>
                </span>
                <span class="no-print">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($url_print); ?>" target="_blank">
                        Imprimer
                    </a>
                    <a class="btn btn-sm btn-success" href="<?= htmlspecialchars($url_export); ?>">
                        Exporter CSV
                    </a>
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Compagnie</th>
                            <th>Gare vendeur</th>
                            <th>Vendeur (chef)</th>
                            <th>Rôle</th>
                            <th>Ticket</th>
                            <th>Bénéficiaire</th>
                            <th>Départ</th>
                            <th>Transit</th>
                            <th class="text-right">Prix saisi</th>
                            <th class="text-right">Prix programme</th>
                            <th class="text-right">Écart</th>
                            <th>Type</th>
                            <th>Arrêt</th>
                            <th>P/O ou n° CV</th>
                            <th class="col-actions"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($lignes)): ?>
                            <tr>
                                <td colspan="16" class="text-muted">
                                    Aucune autre vente pour cette période / ces filtres.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lignes as $l): ?>
                                <?php
                                $voir = site_url(
                                    'rapport_autre_vente/' . $ckey
                                    . '/voir/' . rawurlencode($l['code_passager'])
                                );
                                if ($qs !== '') {
                                    $voir .= '?' . $qs;
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($l['date']); ?></td>
                                    <td>
                                        <?= htmlspecialchars($l['compagnie'] ?? '—'); ?>
                                        <?php if (!empty($l['compagnie_exp'])): ?>
                                            <br><small class="text-muted">exp. <?= htmlspecialchars($l['compagnie_exp']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($l['gare']); ?></td>
                                    <td>
                                        <?= htmlspecialchars($l['utilisateur']); ?>
                                        <?php if (!empty($l['role_note'])): ?>
                                            <br><small class="text-warning"><?= htmlspecialchars($l['role_note']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($l['role_ok'])): ?>
                                            <span class="badge badge-success"><?= htmlspecialchars($l['role_libelle'] ?? 'Chef guichet'); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><?= htmlspecialchars($l['role_libelle'] ?? 'Non autorisé'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($l['ticket']); ?></code></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($voir); ?>">
                                            <?= htmlspecialchars($l['beneficiaire'] ?? '—'); ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($l['depart']); ?></td>
                                    <td>
                                        <?php if ($l['transit'] === 'Oui'): ?>
                                            <span class="badge badge-info">Oui</span>
                                            <?php if (!empty($l['transit_detail'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($l['transit_detail']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Non
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right"><?= htmlspecialchars($fmt($l['prix_saisi'])); ?></td>
                                    <td class="text-right"><?= htmlspecialchars($fmt($l['prix_programme'])); ?></td>
                                    <td class="text-right">
                                        <?php if ($l['ecart'] === null): ?>
                                            —
                                        <?php else: ?>
                                            <?= htmlspecialchars($fmt($l['ecart'])); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($l['type'] === 'Gratuit'): ?>
                                            <span class="badge badge-warning">Gratuit</span>
                                        <?php elseif ($l['type'] === 'Hors tarif'): ?>
                                            <span class="badge badge-danger">Hors tarif</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Conforme</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($l['arrete'])): ?>
                                            <span class="badge badge-secondary">Arrêté</span>
                                        <?php else: ?>
                                            <span class="badge badge-primary">Non arrêté</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($l['pourordre']); ?></td>
                                    <td class="col-actions">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="<?= htmlspecialchars($voir); ?>">Voir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
