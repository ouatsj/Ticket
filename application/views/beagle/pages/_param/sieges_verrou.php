<?php defined('BASEPATH') OR exit('No direct script access allowed');
$ckey = $this->session->company->ekey;
$heures_par_cie = !empty($heures_par_cie) ? $heures_par_cie : array();
$selected_lh = isset($selected_lh) ? (int) $selected_lh : 0;
$selected_row = !empty($selected_row) ? $selected_row : null;
$selected_sieges = !empty($selected_sieges) && is_array($selected_sieges) ? $selected_sieges : array();
$max_places = isset($max_places) ? (int) $max_places : 55;
$verrou_set = array();
foreach ($selected_sieges as $n) {
    $verrou_set[(int) $n] = true;
}
?>
<div class="row">
    <div class="col-12 mb-3">
        <h4 class="mb-1">Sièges verrouillés (admin)</h4>
        <p class="text-muted mb-0">
            Choisissez une <strong>ligne de départ</strong> et son <strong>heure</strong>,
            puis cochez les numéros à verrouiller. Ces sièges ne seront pas vendables
            sur les programmes de cette heure. Seul l’admin peut déverrouiller.
        </p>
    </div>
</div>

<?php if (!empty($saved)): ?>
    <div class="alert alert-success">
        Verrous enregistrés.
        <?php if (!empty($propagated)): ?>
            Appliqués à <strong><?= (int) $propagated; ?></strong> programme(s) futur(s).
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if ($msg = $this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-5 col-12 mb-3">
        <div class="card">
            <div class="card-header">Lignes &amp; heures de départ</div>
            <div class="card-body" style="max-height:70vh;overflow:auto;">
                <?php if (empty($heures_par_cie)): ?>
                    <p class="text-muted mb-0">Aucune ligne / heure active.</p>
                <?php else: ?>
                    <?php foreach ($heures_par_cie as $cie => $items): ?>
                        <h6 class="text-primary mt-2 mb-2"><?= htmlspecialchars($cie, ENT_QUOTES, 'UTF-8'); ?></h6>
                        <div class="list-group mb-3">
                            <?php foreach ($items as $it):
                                $h = $it['row'];
                                $lh = (int) $h->id_ligneheure;
                                $active = ($lh === $selected_lh);
                                $label = trim(
                                    (!empty($h->nom_ligne) ? $h->nom_ligne : $h->ligne_id)
                                    . ' · '
                                    . (!empty($h->heure) ? $h->heure : '')
                                );
                            ?>
                                <a href="<?= site_url('param_sieges_verrou/' . $ckey . '?lh=' . $lh); ?>"
                                   class="list-group-item list-group-item-action py-2 <?= $active ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ((int) $it['nb_verrou'] > 0): ?>
                                            <span class="badge badge-danger"><?= (int) $it['nb_verrou']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="<?= $active ? 'text-white-50' : 'text-muted'; ?>">
                                        #<?= $lh; ?>
                                        <?php if (!empty($h->nom_compagnie_arrivee)): ?>
                                            → <?= htmlspecialchars($h->nom_compagnie_arrivee, ENT_QUOTES, 'UTF-8'); ?>
                                        <?php endif; ?>
                                    </small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7 col-12 mb-3">
        <div class="card">
            <div class="card-header">Plan de sièges à verrouiller</div>
            <div class="card-body">
                <?php if (!$selected_row): ?>
                    <p class="text-muted mb-0">Sélectionnez une ligne / heure à gauche.</p>
                <?php else:
                    $lab = trim(
                        (!empty($selected_row->nom_ligne) ? $selected_row->nom_ligne : '')
                        . ' · '
                        . (!empty($selected_row->heure) ? $selected_row->heure : '')
                    );
                ?>
                    <p class="mb-3">
                        <strong><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <span class="text-muted">(#<?= (int) $selected_row->id_ligneheure; ?>)</span>
                    </p>
                    <?= form_open('param_sieges_verrou/' . $ckey . '/save', array('class' => 'form')); ?>
                    <input type="hidden" name="id_ligneheure" value="<?= (int) $selected_row->id_ligneheure; ?>">
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="sv-check-all">Tout verrouiller</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="sv-uncheck-all">Tout déverrouiller</button>
                        <small class="text-muted ml-2" id="sv-count"><?= count($selected_sieges); ?> verrouillé(s)</small>
                    </div>
                    <div class="row" id="sv-grid">
                        <?php for ($n = 1; $n <= $max_places; $n++):
                            $on = isset($verrou_set[$n]);
                        ?>
                            <div class="col-3 col-md-2 mb-1">
                                <label class="sv-seat <?= $on ? 'is-verrou' : ''; ?>"
                                       style="display:block;padding:4px 6px;border-radius:4px;cursor:pointer;font-weight:400;
                                       <?= $on
                                           ? 'background:#f8d7da;border:1px solid #dc3545;'
                                           : 'border:1px solid #dee2e6;'; ?>">
                                    <input type="checkbox" name="sieges[]" value="<?= $n; ?>" class="sv-cb"
                                           <?= $on ? 'checked' : ''; ?>>
                                    <strong><?= $n; ?></strong>
                                    <?php if ($on): ?>
                                        <span class="sv-tag" style="color:#721c24;font-size:10px;font-weight:700;">VERROUILLÉ</span>
                                    <?php else: ?>
                                        <span class="sv-tag" style="display:none;color:#721c24;font-size:10px;font-weight:700;">VERROUILLÉ</span>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="form-group mt-3">
                        <label class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="appliquer_futurs" value="1" checked>
                            <span class="custom-control-label">
                                Appliquer aussi aux <strong>programmes futurs</strong> de cette heure
                                (ajoute les verrous sans retirer les blocages manuels).
                            </span>
                        </label>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-lock"></i> Enregistrer les verrous
                        </button>
                        <a href="<?= site_url('param_sieges_verrou/' . $ckey); ?>" class="btn btn-secondary">Annuler</a>
                    </div>
                    <?= form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var grid = document.getElementById('sv-grid');
    if (!grid) return;
    var countEl = document.getElementById('sv-count');
    function refresh() {
        var n = 0;
        grid.querySelectorAll('.sv-cb').forEach(function (cb) {
            var lab = cb.closest('label');
            var tag = lab ? lab.querySelector('.sv-tag') : null;
            if (cb.checked) {
                n++;
                if (lab) {
                    lab.style.background = '#f8d7da';
                    lab.style.borderColor = '#dc3545';
                    lab.classList.add('is-verrou');
                }
                if (tag) tag.style.display = '';
            } else {
                if (lab) {
                    lab.style.background = '';
                    lab.style.border = '1px solid #dee2e6';
                    lab.classList.remove('is-verrou');
                }
                if (tag) tag.style.display = 'none';
            }
        });
        if (countEl) countEl.textContent = n + ' verrouillé(s)';
    }
    grid.addEventListener('change', refresh);
    var a = document.getElementById('sv-check-all');
    var b = document.getElementById('sv-uncheck-all');
    if (a) a.addEventListener('click', function () {
        grid.querySelectorAll('.sv-cb').forEach(function (cb) { cb.checked = true; });
        refresh();
    });
    if (b) b.addEventListener('click', function () {
        grid.querySelectorAll('.sv-cb').forEach(function (cb) { cb.checked = false; });
        refresh();
    });
})();
</script>
