<?php defined('BASEPATH') OR exit('No direct script access allowed');
$f = isset($filters) ? $filters : array();
$stats = isset($stats) ? $stats : array();
$lignes = isset($lignes) ? $lignes : array();
$gares = isset($gares) ? $gares : array();
$types = isset($types) ? $types : array();
$qs = isset($filters_qs) ? $filters_qs : '';
$ckey = $this->session->company->ekey;
$url_export = site_url('rapport_modif_ticket/' . $ckey . '/export') . ($qs !== '' ? ('?' . $qs) : '');
?>
<style>
    @media print {
        .no-print, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .be-content, .main-content { margin: 0 !important; width: 100% !important; }
    }
</style>
<div class="row">
    <div class="col-12">
        <div class="card mb-3 no-print">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <span>Modifications de tickets</span>
                <a class="btn btn-sm btn-success" href="<?= htmlspecialchars($url_export); ?>">Exporter CSV</a>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    Journal des modifications (infos client, départ, gare/quartier, prix, code, siège).
                    Enregistrement à partir de la mise en place — pas d’historique rétroactif.
                </p>
                <?= form_open(site_url('rapport_modif_ticket/' . $ckey), array(
                    'method' => 'get',
                    'class' => 'form-inline flex-wrap',
                )); ?>
                    <label class="mr-2 mb-2">Du</label>
                    <input type="date" name="date_debut" class="form-control form-control-sm mr-2 mb-2"
                           value="<?= htmlspecialchars($f['date_debut'] ?? ''); ?>">
                    <label class="mr-2 mb-2">au</label>
                    <input type="date" name="date_fin" class="form-control form-control-sm mr-2 mb-2"
                           value="<?= htmlspecialchars($f['date_fin'] ?? ''); ?>">
                    <label class="mr-2 mb-2">Gare</label>
                    <select name="gare" class="form-control form-control-sm mr-2 mb-2">
                        <option value="">Toutes</option>
                        <?php foreach ($gares as $g): ?>
                            <option value="<?= htmlspecialchars($g->idengare); ?>"
                                <?= (($f['gare'] ?? '') === $g->idengare) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($g->garenom ?: $g->idengare); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="mr-2 mb-2">Type</label>
                    <select name="type" class="form-control form-control-sm mr-2 mb-2">
                        <option value="all" <?= (($f['type'] ?? '') === 'all') ? 'selected' : ''; ?>>Tous</option>
                        <?php foreach ($types as $tk => $tl): ?>
                            <option value="<?= htmlspecialchars($tk); ?>"
                                <?= (($f['type'] ?? '') === $tk) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($tl); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label class="mr-2 mb-2">Opérateur</label>
                    <input type="text" name="operateur" class="form-control form-control-sm mr-2 mb-2"
                           placeholder="username ou id"
                           value="<?= htmlspecialchars($f['operateur'] ?? ''); ?>">
                    <button type="submit" class="btn btn-sm btn-primary mb-2">Filtrer</button>
                <?= form_close(); ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body py-2 small">
                <strong>Total :</strong> <?= (int) ($stats['total'] ?? 0); ?>
                <?php if (!empty($stats['par_type']) && is_array($stats['par_type'])): ?>
                    <?php foreach ($stats['par_type'] as $tk => $n): ?>
                        <?php if ((int) $n <= 0) { continue; } ?>
                        — <?= htmlspecialchars(isset($types[$tk]) ? $types[$tk] : $tk); ?> :
                        <?= (int) $n; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Ticket</th>
                            <th>Gare</th>
                            <th>Opérateur</th>
                            <th>Motif</th>
                            <th>Ordre donné par</th>
                            <th>Détail (avant → après)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($lignes)): ?>
                            <tr>
                                <td colspan="8" class="text-muted text-center py-4">
                                    Aucune modification sur la période.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lignes as $l): ?>
                                <tr>
                                    <td class="text-nowrap"><?= htmlspecialchars($l->created_at); ?></td>
                                    <td><?= htmlspecialchars($l->type_label); ?></td>
                                    <td class="small">
                                        <?= htmlspecialchars((string) $l->code_passager); ?>
                                        <?php if (!empty($l->code_ticket)): ?>
                                            <br><span class="text-muted"><?= htmlspecialchars((string) $l->code_ticket); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars((string) $l->garenom); ?></td>
                                    <td class="small">
                                        <?= htmlspecialchars((string) $l->username); ?>
                                        <?php if (!empty($l->roleattribut)): ?>
                                            <br><span class="text-muted">#<?= (int) $l->roleattribut; ?>
                                            (rôle <?= (int) $l->userole; ?>)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small"><?= htmlspecialchars((string) ($l->motif ?? '')); ?></td>
                                    <td class="small"><?= htmlspecialchars((string) ($l->ordre_par ?? '')); ?></td>
                                    <td class="small"><?= htmlspecialchars($l->resume); ?></td>
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
