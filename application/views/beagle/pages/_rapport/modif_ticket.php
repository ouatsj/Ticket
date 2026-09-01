<?php defined('BASEPATH') OR exit('No direct script access allowed');
$f = isset($filters) ? $filters : array();
$stats = isset($stats) ? $stats : array();
$lignes = isset($lignes) ? $lignes : array();
$gares = isset($gares) ? $gares : array();
$types = isset($types) ? $types : array();
$qs = isset($filters_qs) ? $filters_qs : '';
$onglet = isset($onglet) ? $onglet : 'modifies';
$code_recherche = isset($code_recherche) ? $code_recherche : '';
$hist_lignes = isset($hist_lignes) ? $hist_lignes : array();
$hist_stats = isset($hist_stats) ? $hist_stats : array();
$hist_passager = isset($hist_passager) ? $hist_passager : null;
$hist_codes = isset($hist_codes) ? $hist_codes : array();
$ckey = $this->session->company->ekey;
$base_url = site_url('rapport_modif_ticket/' . $ckey);
$url_export_modifies = $base_url . '/export' . ($qs !== '' ? ('?' . $qs) : '');
$url_export_hist = $base_url . '/export?onglet=historique&code=' . rawurlencode($code_recherche);
$is_modifies = ($onglet !== 'historique');
?>
<style>
    @media print {
        .no-print, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .be-content, .main-content { margin: 0 !important; width: 100% !important; }
    }
    .modif-ticket-tabs .nav-link { font-weight: 600; }
    .hist-passager-box { background: #f7f9fc; border: 1px solid #e3e8ef; border-radius: 4px; padding: 12px 14px; }
    .hist-passager-box dl { margin: 0; }
    .hist-passager-box dt { font-size: 11px; text-transform: uppercase; color: #6c757d; margin-top: 6px; }
    .hist-passager-box dd { margin: 0 0 2px; font-weight: 600; }
</style>
<div class="row">
    <div class="col-12">
        <div class="card mb-3 no-print">
            <div class="card-header">
                <ul class="nav nav-tabs nav-tabs-primary nav-tabs-classic modif-ticket-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link<?= $is_modifies ? ' active show' : ''; ?>"
                           href="<?= htmlspecialchars($base_url . '?onglet=modifies' . ($qs !== '' ? '&' . $qs : '')); ?>">
                            Tickets modifiés
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= !$is_modifies ? ' active show' : ''; ?>"
                           href="<?= htmlspecialchars($base_url . '?onglet=historique' . ($code_recherche !== '' ? '&code=' . rawurlencode($code_recherche) : '')); ?>">
                            Historique ticket
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <?php if ($is_modifies): ?>
            <div class="card mb-3 no-print">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <span>Tickets modifiés</span>
                    <a class="btn btn-sm btn-success" href="<?= htmlspecialchars($url_export_modifies); ?>">Exporter CSV</a>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Journal des modifications (infos client, départ, gare/quartier, prix, code, siège, reprogrammation, confirmation).
                        Enregistrement à partir de la mise en place — pas d’historique rétroactif.
                    </p>
                    <?= form_open($base_url, array(
                        'method' => 'get',
                        'class' => 'form-inline flex-wrap',
                    )); ?>
                        <input type="hidden" name="onglet" value="modifies">
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
                                            <br>
                                            <a class="small" href="<?= htmlspecialchars($base_url . '?onglet=historique&code=' . rawurlencode((string) ($l->code_ticket ?: $l->code_passager))); ?>">
                                                Voir historique
                                            </a>
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

        <?php else: ?>
            <div class="card mb-3 no-print">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <span>Historique ticket</span>
                    <?php if ($code_recherche !== ''): ?>
                        <a class="btn btn-sm btn-success" href="<?= htmlspecialchars($url_export_hist); ?>">Exporter CSV</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Saisissez le <strong>code ticket</strong> ou le <strong>code passager</strong> pour afficher
                        toute la chronologie des modifications enregistrées.
                    </p>
                    <?= form_open($base_url, array(
                        'method' => 'get',
                        'class' => 'form-inline flex-wrap',
                    )); ?>
                        <input type="hidden" name="onglet" value="historique">
                        <label class="mr-2 mb-2">Code</label>
                        <input type="text" name="code" class="form-control form-control-sm mr-2 mb-2"
                               style="min-width: 220px;"
                               placeholder="ex. 0717204M20 ou 26071722BAN3M20"
                               value="<?= htmlspecialchars($code_recherche); ?>"
                               autofocus>
                        <button type="submit" class="btn btn-sm btn-primary mb-2">Rechercher</button>
                    <?= form_close(); ?>
                </div>
            </div>

            <?php if ($code_recherche === ''): ?>
                <div class="card">
                    <div class="card-body text-muted text-center py-5">
                        Entrez un code ticket pour consulter son historique.
                    </div>
                </div>
            <?php else: ?>
                <?php if ($hist_passager): ?>
                    <div class="card mb-3">
                        <div class="card-header">État actuel du ticket</div>
                        <div class="card-body">
                            <div class="hist-passager-box">
                                <div class="row">
                                    <div class="col-md-3 col-sm-6">
                                        <dl>
                                            <dt>Code ticket</dt>
                                            <dd><?= htmlspecialchars((string) $hist_passager->code_ticket); ?></dd>
                                            <dt>Code passager</dt>
                                            <dd><?= htmlspecialchars((string) $hist_passager->code_passager); ?></dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <dl>
                                            <dt>Programme</dt>
                                            <dd><?= htmlspecialchars((string) $hist_passager->code_pro); ?></dd>
                                            <dt>Siège / cat.</dt>
                                            <dd><?= htmlspecialchars((string) $hist_passager->num_siege_categorie); ?>
                                                / <?= htmlspecialchars((string) $hist_passager->num_cat); ?></dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <dl>
                                            <dt>Prix</dt>
                                            <dd><?= htmlspecialchars((string) $hist_passager->prixvente); ?></dd>
                                            <dt>Statuts</dt>
                                            <dd>
                                                <?= htmlspecialchars((string) $hist_passager->statut_code); ?>
                                                <?php if (!empty($hist_passager->statut_reprog)): ?>
                                                    · <?= htmlspecialchars((string) $hist_passager->statut_reprog); ?>
                                                <?php endif; ?>
                                                <?php if (!empty($hist_passager->statut_confirme)): ?>
                                                    · <?= htmlspecialchars((string) $hist_passager->statut_confirme); ?>
                                                <?php endif; ?>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <dl>
                                            <dt>Émis le</dt>
                                            <dd><?= htmlspecialchars((string) $hist_passager->date_emis); ?></dd>
                                            <dt>Gare embarq. / quartier</dt>
                                            <dd><?= htmlspecialchars((string) $hist_passager->departclient_idgare); ?>
                                                / <?= htmlspecialchars((string) $hist_passager->quart); ?></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card mb-3">
                    <div class="card-body py-2 small">
                        <strong>Recherche :</strong> <?= htmlspecialchars($code_recherche); ?>
                        <?php if (!empty($hist_codes)): ?>
                            — codes liés : <?= htmlspecialchars(implode(', ', $hist_codes)); ?>
                        <?php endif; ?>
                        — <strong>événements :</strong> <?= (int) ($hist_stats['total'] ?? 0); ?>
                        <?php if (!empty($hist_stats['par_type']) && is_array($hist_stats['par_type'])): ?>
                            <?php foreach ($hist_stats['par_type'] as $tk => $n): ?>
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
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Gare</th>
                                    <th>Opérateur</th>
                                    <th>Motif</th>
                                    <th>Ordre donné par</th>
                                    <th>Détail (avant → après)</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($hist_lignes)): ?>
                                    <tr>
                                        <td colspan="8" class="text-muted text-center py-4">
                                            <?php if ($hist_passager): ?>
                                                Ticket trouvé, mais aucune modification journalisée pour ce code
                                                (historique disponible uniquement après mise en place du journal).
                                            <?php else: ?>
                                                Aucun ticket ni historique trouvé pour ce code.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $i = 1; foreach ($hist_lignes as $l): ?>
                                        <tr>
                                            <td><?= $i++; ?></td>
                                            <td class="text-nowrap"><?= htmlspecialchars($l->created_at); ?></td>
                                            <td><?= htmlspecialchars($l->type_label); ?></td>
                                            <td><?= htmlspecialchars((string) $l->garenom); ?></td>
                                            <td class="small">
                                                <?= htmlspecialchars((string) $l->username); ?>
                                                <?php if (!empty($l->roleattribut)): ?>
                                                    <br><span class="text-muted">#<?= (int) $l->roleattribut; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small"><?= htmlspecialchars((string) ($l->motif ?? '')); ?></td>
                                            <td class="small"><?= htmlspecialchars((string) ($l->ordre_par ?? '')); ?></td>
                                            <td class="small">
                                                <?= htmlspecialchars($l->resume); ?>
                                                <?php
                                                $meta = isset($l->meta) && is_array($l->meta) ? $l->meta : array();
                                                $pa = isset($meta['programme_avant']['dateheure_prog']) ? $meta['programme_avant']['dateheure_prog'] : '';
                                                $pb = isset($meta['programme_apres']['dateheure_prog']) ? $meta['programme_apres']['dateheure_prog'] : '';
                                                if ($pa !== '' || $pb !== ''):
                                                ?>
                                                    <br><span class="text-muted">
                                                        Départ :
                                                        <?= htmlspecialchars($pa !== '' ? $pa : '—'); ?>
                                                        →
                                                        <?= htmlspecialchars($pb !== '' ? $pb : '—'); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
