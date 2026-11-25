<?php defined('BASEPATH') OR exit('No direct script access allowed');
$baseUrl = site_url('rapport_arret_compte/' . $ckey);
$voirBase = site_url('rapport_arret_compte/' . $ckey . '/voir');
?>
<div class="container-fluid py-3">
    <h1 class="h3 mb-1">
        Rapports d’arrêt de compte guichet
        <?php if (!empty($current_sg)): ?>
            <small class="text-primary">— <?= htmlspecialchars($current_sg->gareprinceid . ' / ' . $current_sg->nomsousgare, ENT_QUOTES, 'UTF-8'); ?></small>
        <?php endif; ?>
    </h1>
    <p class="text-muted mb-3">
        Consultez et imprimez les arrêts de compte effectués par les guichetiers, filtrés par sous-gare et guichetier.
    </p>

    <form method="get" action="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>" id="form-rapport-arret" class="card card-body mb-3">
        <div class="form-row align-items-end">
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold mb-1">Date début</label>
                <input type="date" name="date_debut" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($date_debut, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold mb-1">Date fin</label>
                <input type="date" name="date_fin" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($date_fin, ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold mb-1">Sous-gare</label>
                <select name="idsousgare" class="form-control form-control-sm"
                        onchange="var r=this.form.querySelector('[name=roleattribut]'); if(r) r.value='0'; this.form.submit();">
                    <option value="0">— Toutes les sous-gares —</option>
                    <?php foreach ($sousgares as $sg): ?>
                        <option value="<?= (int) $sg->idsousgare; ?>" <?= ((int) $idsousgare === (int) $sg->idsousgare) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($sg->gareprinceid . ' — ' . $sg->nomsousgare, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold mb-1">Compte guichetier</label>
                <select name="roleattribut" class="form-control form-control-sm">
                    <option value="0">— Tous les guichetiers —</option>
                    <?php foreach ($guichetiers as $g):
                        $usr = trim((string) $g->username);
                        $nom = trim((string) $g->first_name . ' ' . (string) $g->last_name);
                        $lbl = $usr !== '' ? $usr : ($nom !== '' ? $nom : ('Opérateur #' . $g->roleattribut));
                        if ($nom !== '' && strcasecmp($nom, $usr) !== 0) {
                            $lbl .= ' — ' . $nom;
                        }
                        if (empty($current_sg) && !empty($g->guser)) {
                            $lbl .= ' [' . $g->guser . ']';
                        }
                    ?>
                        <option value="<?= (int) $g->roleattribut; ?>" <?= ((int) $roleattribut === (int) $g->roleattribut) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-sm btn-block">Filtrer</button>
            </div>
        </div>
    </form>

    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="card card-body py-2">
                <div class="small text-muted">Arrêts trouvés</div>
                <div class="h4 mb-0 font-weight-bold"><?= number_format(count($rows), 0, ',', ' '); ?></div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card card-body py-2">
                <div class="small text-muted">Tickets (Aller / Retour)</div>
                <div class="h4 mb-0 font-weight-bold">
                    <?= number_format((int) $total_pass, 0, ',', ' '); ?>
                    <small class="text-muted">/ <?= number_format((int) $total_np, 0, ',', ' '); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card card-body py-2">
                <div class="small text-muted">Montant total arrêté</div>
                <div class="h4 mb-0 font-weight-bold text-success"><?= number_format((float) $total_montant, 0, ',', ' '); ?> F</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:155px;">Date / heure</th>
                        <th>Gare / Sous-gare</th>
                        <th>Guichetier</th>
                        <th class="text-right" style="width:110px;">Tickets (A/R)</th>
                        <th>Détail par compagnie</th>
                        <th class="text-right" style="width:120px;">Total</th>
                        <th style="width:105px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="7" class="text-center text-muted py-3">Aucun arrêt de compte trouvé pour ces critères.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r):
                        $nom = trim((string) $r->first_name . ' ' . (string) $r->last_name);
                        if ($nom === '') {
                            $nom = !empty($r->username) ? $r->username : ('Opérateur #' . $r->roleattribut);
                        }
                        $sgLabel = !empty($r->nomsousgare)
                            ? ($r->gare_code . ' — ' . $r->nomsousgare)
                            : ($r->gare_code . ' (gare entière)');
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($r->created_at, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($sgLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <strong><?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <?php if (!empty($r->username) && $r->username !== $nom): ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($r->username, ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= (int) $r->pass_count; ?> / <?= (int) $r->np_count; ?></td>
                            <td class="small">
                                <?= $r->comp_details ? htmlspecialchars(implode(' · ', $r->comp_details), ENT_QUOTES, 'UTF-8') : '—'; ?>
                            </td>
                            <td class="text-right font-weight-bold"><?= number_format((float) $r->total_amount, 0, ',', ' '); ?> F</td>
                            <td>
                                <a class="btn btn-primary btn-sm"
                                   target="_blank"
                                   href="<?= htmlspecialchars($voirBase . '/' . (int) $r->id, ENT_QUOTES, 'UTF-8'); ?>">
                                    Voir / Impr.
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
