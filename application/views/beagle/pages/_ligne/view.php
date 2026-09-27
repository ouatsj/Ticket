<?php defined('BASEPATH') OR exit('No direct script access allowed');
$ligne_error = $this->session->flashdata('ligne_error');
$ligne_ok = $this->session->flashdata('ligne_ok');
?>
<div class="row">
    <div class="col-12 d-flex flex-wrap align-items-center mb-2 ml-4 pr-4">
        <button type="button" class="btn btn-space btn-info md-trigger" data-modal="add-ligne">
            <span class="icon mdi mdi-plus-1 text-white"></span>
            AJOUTER UNE LIGNE
        </button>
    </div>
</div>
<?php if (!empty($ligne_error)): ?>
<div class="row">
    <div class="col-12 ml-4 pr-4 mb-2">
        <div class="alert alert-danger py-2 mb-0"><?= htmlspecialchars($ligne_error, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
</div>
<?php endif; ?>
<?php if (!empty($ligne_ok)): ?>
<div class="row">
    <div class="col-12 ml-4 pr-4 mb-2">
        <div class="alert alert-success py-2 mb-0"><?= htmlspecialchars($ligne_ok, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
</div>
<?php endif; ?>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="add-ligne" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">AJOUTER UNE LIGNE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open("Lignes/add/{$this->session->company->ekey}", array('class' => 'modal-body form', 'id' => 'form-add-ligne')); ?>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label for="add-ligne-compagnie">COMPAGNIE DÉPART <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm" name="cle_compagnie" id="add-ligne-compagnie" required>
                        <option value="">— Choisir la compagnie de départ —</option>
                        <?php foreach ((!empty($compagnies) ? $compagnies : array()) as $cie): ?>
                            <option value="<?= htmlspecialchars($cie->cle_compagnie, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($cie->nom_compagnie, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label for="add-ligne-compagnie-arrivee">COMPAGNIE ARRIVÉE <span class="text-danger">*</span></label>
                    <select class="form-control form-control-sm" name="cle_compagnie_arrivee" id="add-ligne-compagnie-arrivee" required>
                        <option value="">— Choisir la compagnie d’arrivée —</option>
                        <?php foreach ((!empty($compagnies) ? $compagnies : array()) as $cie): ?>
                            <option value="<?= htmlspecialchars($cie->cle_compagnie, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($cie->nom_compagnie, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label>GARE DEPART</label>
                    <select class="form-control form-control-sm" name="garedepart" id="add-ligne-garedepart" required disabled>
                        <option value="">— Choisir la compagnie de départ d’abord —</option>
                        <?php foreach ((!empty($gares_param) ? $gares_param : array()) as $gareParam):
                            $cieGare = isset($gareParam->compagniegare) ? (string) $gareParam->compagniegare : '';
                        ?>
                            <option value="<?= htmlspecialchars($gareParam->idengare, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-compagnie="<?= htmlspecialchars($cieGare, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($gareParam->garenom, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label>GARE ARRIVEE</label>
                    <select class="form-control form-control-sm" name="garearrivee" id="add-ligne-garearrivee" required disabled>
                        <option value="">— Choisir la compagnie d’arrivée d’abord —</option>
                        <?php foreach ((!empty($gares_param) ? $gares_param : array()) as $gareParam):
                            $cieGare = isset($gareParam->compagniegare) ? (string) $gareParam->compagniegare : '';
                        ?>
                            <option value="<?= htmlspecialchars($gareParam->idengare, ENT_QUOTES, 'UTF-8'); ?>"
                                    data-compagnie="<?= htmlspecialchars($cieGare, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($gareParam->garenom, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label>DISTANCE</label>
                    <input class="form-control form-control-sm" type="text"
                           name="distance" autocomplete="off" value="">
                </div>
                <div class="form-group col-sm-6">
                    <label>PRIX</label>
                    <input class="form-control form-control-sm" type="number"
                           name="distanceprix" autocomplete="off" value="">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success" type="submit">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
        <?= form_close(); ?>
    </div>
</div>

<script>
(function () {
    var cieDep = document.getElementById('add-ligne-compagnie');
    var cieArr = document.getElementById('add-ligne-compagnie-arrivee');
    var depSel = document.getElementById('add-ligne-garedepart');
    var arrSel = document.getElementById('add-ligne-garearrivee');
    if (!cieDep || !cieArr || !depSel || !arrSel) return;

    function filterSelect(sel, cie, readyPlaceholder, emptyPlaceholder) {
        var first = sel.querySelector('option[value=""]');
        var opts = Array.prototype.slice.call(sel.querySelectorAll('option'));
        var groups = Array.prototype.slice.call(sel.querySelectorAll('optgroup'));
        opts.forEach(function (opt) {
            if (opt.value === '') return;
            var oc = opt.getAttribute('data-compagnie') || '';
            var show = !!cie && oc === cie;
            opt.hidden = !show;
            opt.disabled = !show;
            if (!show && opt.selected) opt.selected = false;
        });
        groups.forEach(function (og) {
            var gCie = og.getAttribute('data-compagnie') || '';
            var show = !!cie && gCie === cie;
            og.hidden = !show;
            og.disabled = !show;
        });
        if (first) {
            first.textContent = cie ? readyPlaceholder : emptyPlaceholder;
        }
        sel.disabled = !cie;
        if (!cie) sel.value = '';
    }

    cieDep.addEventListener('change', function () {
        filterSelect(depSel, cieDep.value || '', '— Gare de départ —', '— Choisir la compagnie de départ d’abord —');
        depSel.value = '';
    });
    cieArr.addEventListener('change', function () {
        filterSelect(arrSel, cieArr.value || '', '— Gare d’arrivée —', '— Choisir la compagnie d’arrivée d’abord —');
        arrSel.value = '';
    });
    filterSelect(depSel, '', '— Gare de départ —', '— Choisir la compagnie de départ d’abord —');
    filterSelect(arrSel, '', '— Gare d’arrivée —', '— Choisir la compagnie d’arrivée d’abord —');
})();
</script>

<div class="row">
    <div class="col-lg-12">

        <?
        $lignes_par_compagnie_arrivee = !empty($lignes_par_compagnie_arrivee) ? $lignes_par_compagnie_arrivee : array();
        if (!empty($lignes_par_compagnie_arrivee)):
            $group_keys = array_keys($lignes_par_compagnie_arrivee);
            $first_key = reset($group_keys);
            $tab_pref = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $this->input->get('tab'));
        ?>

            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="ligne-edit" style="perspective: 1300px;">
                <div class="modal-content">
                    <div class="modal-header modal-header-colored">
                        <h3 class="modal-title">MODIFIER LA LIGNE</h3>
                        <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                            <span class="mdi mdi-close text-white"></span>
                        </button>
                    </div>
                    <?= form_open('Lignes/edit_/' . $this->session->company->ekey, array('class' => 'modal-body form', 'id' => 'form-edit-ligne')); ?>
                        <input type="hidden" name="tab" id="edit-ligne-tab" value="">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="edit-ligne-compagnie">COMPAGNIE DÉPART</label>
                                <select class="form-control form-control-sm" name="cle_compagnie" id="edit-ligne-compagnie" required>
                                    <option value="">— Choisir la compagnie de départ —</option>
                                    <?php foreach ((!empty($compagnies) ? $compagnies : array()) as $cie): ?>
                                        <option value="<?= htmlspecialchars($cie->cle_compagnie, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($cie->nom_compagnie, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="edit-ligne-compagnie-arrivee">COMPAGNIE ARRIVÉE</label>
                                <select class="form-control form-control-sm" name="cle_compagnie_arrivee" id="edit-ligne-compagnie-arrivee" required>
                                    <option value="">— Choisir la compagnie d’arrivée —</option>
                                    <?php foreach ((!empty($compagnies) ? $compagnies : array()) as $cie): ?>
                                        <option value="<?= htmlspecialchars($cie->cle_compagnie, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($cie->nom_compagnie, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-6">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="garedepart" id="edit-ligne-garedepart" required>
                                    <option value="">— Choisir la compagnie de départ d’abord —</option>
                                    <?php foreach ((!empty($gares_param) ? $gares_param : array()) as $gareParam): ?>
                                        <option value="<?= htmlspecialchars($gareParam->idengare, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-compagnie="<?= htmlspecialchars((string) $gareParam->compagniegare, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($gareParam->garenom, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-6">
                                <label>GARE ARRIVEE</label>
                                <select class="form-control form-control-sm" name="garearrivee" id="edit-ligne-garearrivee" required>
                                    <option value="">— Choisir la compagnie d’arrivée d’abord —</option>
                                    <?php foreach ((!empty($gares_param) ? $gares_param : array()) as $gareParam): ?>
                                        <option value="<?= htmlspecialchars($gareParam->idengare, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-compagnie="<?= htmlspecialchars((string) $gareParam->compagniegare, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($gareParam->garenom, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-6">
                                <label>DISTANCE</label>
                                <input class="form-control form-control-sm" type="text" name="distance" id="edit-ligne-distance" autocomplete="off" value="">
                            </div>
                            <div class="form-group col-sm-6">
                                <label>PRIX</label>
                                <input class="form-control form-control-sm" type="number" name="distanceprix" id="edit-ligne-prix" autocomplete="off" value="">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                            </button>
                            <button class="btn btn-success" type="submit">
                                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                            </button>
                        </div>
                    <?= form_close(); ?>
                </div>
            </div>
            <script>
            (function () {
                var form = document.getElementById('form-edit-ligne');
                var cieDep = document.getElementById('edit-ligne-compagnie');
                var cieArr = document.getElementById('edit-ligne-compagnie-arrivee');
                var depSel = document.getElementById('edit-ligne-garedepart');
                var arrSel = document.getElementById('edit-ligne-garearrivee');
                var distance = document.getElementById('edit-ligne-distance');
                var prix = document.getElementById('edit-ligne-prix');
                var tabInput = document.getElementById('edit-ligne-tab');
                var base = <?= json_encode(site_url('Lignes/edit_/' . $this->session->company->ekey)); ?>;
                if (!form || !cieDep || !cieArr || !depSel || !arrSel) return;

                function filterSelect(sel, cie, keepValue) {
                    var opts = sel.querySelectorAll('option');
                    for (var i = 0; i < opts.length; i++) {
                        if (opts[i].value === '') continue;
                        var show = !!cie && opts[i].getAttribute('data-compagnie') === String(cie);
                        opts[i].hidden = !show;
                        opts[i].disabled = !show;
                    }
                    sel.disabled = !cie;
                    if (keepValue && sel.querySelector('option[value="' + keepValue.replace(/"/g, '\\"') + '"]')) {
                        var kept = sel.querySelector('option[value="' + keepValue.replace(/"/g, '\\"') + '"]');
                        if (kept && kept.getAttribute('data-compagnie') === String(cie)) {
                            kept.hidden = false;
                            kept.disabled = false;
                            sel.value = keepValue;
                            return;
                        }
                    }
                    sel.value = '';
                }

                cieDep.addEventListener('change', function () {
                    filterSelect(depSel, cieDep.value || '', '');
                });
                cieArr.addEventListener('change', function () {
                    filterSelect(arrSel, cieArr.value || '', '');
                });

                document.addEventListener('click', function (e) {
                    var link = e.target && e.target.closest ? e.target.closest('.js-ligne-edit') : null;
                    if (!link) return;
                    e.preventDefault();
                    var ident = link.getAttribute('data-ident') || '';
                    var cieD = link.getAttribute('data-cie-dep') || '';
                    var cieA = link.getAttribute('data-cie-arr') || '';
                    var gareD = link.getAttribute('data-gare-dep') || '';
                    var gareA = link.getAttribute('data-gare-arr') || '';
                    form.action = base + '/' + encodeURIComponent(ident);
                    tabInput.value = link.getAttribute('data-tab') || '';
                    cieDep.value = cieD;
                    cieArr.value = cieA;
                    filterSelect(depSel, cieD, gareD);
                    filterSelect(arrSel, cieA, gareA);
                    distance.value = link.getAttribute('data-distance') || '';
                    prix.value = link.getAttribute('data-prix') || '';
                    if (window.jQuery && window.jQuery.fn.niftyModal) {
                        window.jQuery('#ligne-edit').niftyModal();
                    }
                });
            })();
            </script>

            <div class="card card-table">
                <div class="card-header">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <strong>Lignes par compagnie d'arrivée</strong>
                        </div>
                        <div class="col-md-6">
                            <input type="search"
                                   id="filtre-ligne"
                                   class="form-control form-control-sm"
                                   placeholder="Rechercher ligne, départ, arrivée…"
                                   autocomplete="off">
                        </div>
                    </div>
                    <ul class="nav nav-tabs nav-tabs-primary nav-tabs-classic flex-wrap" role="tablist" id="tabs-compagnie-arrivee">
                        <? foreach ($lignes_par_compagnie_arrivee as $cle => $groupe):
                            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                            $nb = !empty($groupe['lignes']) ? count($groupe['lignes']) : 0;
                            $pane_id = 'comp-arr-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'comp-arr-') {
                                $pane_id = 'comp-arr-sans';
                            }
                            $is_active = ($tab_pref !== '')
                                ? ($pane_id === $tab_pref)
                                : ($cle === $first_key);
                        ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $is_active ? ' active show' : ''; ?>"
                                   href="#<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-toggle="tab"
                                   role="tab"
                                   aria-selected="<?= $is_active ? 'true' : 'false'; ?>">
                                    <?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?>
                                    <span class="badge badge-pill badge-primary"><?= (int) $nb; ?></span>
                                </a>
                            </li>
                        <? endforeach; ?>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">
                        <? foreach ($lignes_par_compagnie_arrivee as $cle => $groupe):
                            $pane_id = 'comp-arr-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'comp-arr-') {
                                $pane_id = 'comp-arr-sans';
                            }
                            $is_active = ($tab_pref !== '')
                                ? ($pane_id === $tab_pref)
                                : ($cle === $first_key);
                            $table_id = 'table-' . $pane_id;
                        ?>
                            <div class="tab-pane fade<?= $is_active ? ' active show' : ''; ?>"
                                 id="<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                 role="tabpanel">

                                <table class="table table-striped table-hover table-lignes"
                                       id="<?= htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8'); ?>">
                                    <thead>
                                    <tr>
                                        <th>IDENTIFIANT</th>
                                        <th>GARE DEPART</th>
                                        <th>GARE ARRIVEE</th>
                                        <th>DISTANCE(KM)</th>
                                        <th>PRIX</th>
                                        <th>LIGNE</th>
                                        <th>STATUT</th>
                                        <th class="actions">ACTION</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <? foreach ($groupe['lignes'] as $item):
                                        $actif_lg = (!isset($item->actif_lg) || (string) $item->actif_lg === '1' || (int) $item->actif_lg === 1) ? 1 : 0;
                                    ?>
                                        <tr class="<?= $actif_lg ? '' : 'table-secondary text-muted'; ?>">
                                            <td><?= $item->ident_ligne; ?></td>
                                            <td><?= $item->nom_gaep; ?></td>
                                            <td><?= $item->nom_gadest; ?></td>
                                            <td><?= $item->distancekm; ?></td>
                                            <td><?= number_format($item->prixkm, 0, '', ' '); ?></td>
                                            <td><?= $item->nom_ligne; ?></td>
                                            <td>
                                                <? if ($actif_lg): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <? else: ?>
                                                    <span class="badge badge-secondary">Désactivée</span>
                                                <? endif; ?>
                                            </td>
                                            <td class="actions">
                                                <a href="#"
                                                   class="js-ligne-edit"
                                                   title="Modifier"
                                                   data-ident="<?= htmlspecialchars($item->ident_ligne, ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-cie-dep="<?= htmlspecialchars(isset($item->cle_compagnie_depart) ? $item->cle_compagnie_depart : '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-cie-arr="<?= htmlspecialchars(isset($item->cle_compagnie_arrivee) ? $item->cle_compagnie_arrivee : '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-gare-dep="<?= htmlspecialchars(isset($item->garesid) ? $item->garesid : '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-gare-arr="<?= htmlspecialchars(isset($item->idgaresdest) ? $item->idgaresdest : '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-distance="<?= htmlspecialchars(isset($item->distancekm) ? $item->distancekm : '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-prix="<?= htmlspecialchars(isset($item->prixkm) ? $item->prixkm : '', ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-tab="<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <span class="fas fa-edit text-warning"></span>
                                                </a>
                                                <a href="<?= site_url('Lignes/active/' . $this->session->company->ekey . '/' . rawurlencode($item->ident_ligne) . '/' . $actif_lg) . '?tab=' . rawurlencode($pane_id); ?>"
                                                   class="btn btn-space btn-secondary btn-sm"
                                                   title="<?= $actif_lg ? 'Masquer cette ligne du guichet' : 'Réafficher cette ligne au guichet'; ?>">
                                                    <?= $actif_lg
                                                        ? '<span class="icon mdi text-danger">désactiver</span>'
                                                        : '<span class="icon mdi text-success">activer</span>'; ?>
                                                </a>
                                                <a href="<?= site_url('Lignes/delete/' . $this->session->company->ekey . '/' . rawurlencode($item->ident_ligne)) . '?tab=' . rawurlencode($pane_id); ?>"
                                                   class="btn btn-space btn-outline-danger btn-sm"
                                                   title="Supprimer cette ligne pour la recréer"
                                                   onclick="return confirm('Supprimer cette ligne pour pouvoir la recréer ?');">
                                                    <span class="icon mdi mdi-delete text-danger">supprimer</span>
                                                </a>

                                            </td>
                                        </tr>
                                    <? endforeach; ?>
                                    </tbody>
                                </table>
                                <p class="text-muted filtre-ligne-vide d-none mb-0">Aucun résultat pour cette recherche.</p>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                var input = document.getElementById('filtre-ligne');
                if (!input) { return; }

                function filterActivePane() {
                    var q = (input.value || '').toLowerCase().trim();
                    var pane = document.querySelector('.tab-content > .tab-pane.active');
                    if (!pane) { return; }
                    var rows = pane.querySelectorAll('tbody tr');
                    var visible = 0;
                    for (var i = 0; i < rows.length; i++) {
                        var text = (rows[i].textContent || '').toLowerCase();
                        var show = !q || text.indexOf(q) !== -1;
                        rows[i].style.display = show ? '' : 'none';
                        if (show) { visible++; }
                    }
                    var emptyMsg = pane.querySelector('.filtre-ligne-vide');
                    if (emptyMsg) {
                        if (q && visible === 0) {
                            emptyMsg.classList.remove('d-none');
                        } else {
                            emptyMsg.classList.add('d-none');
                        }
                    }
                }

                input.addEventListener('input', filterActivePane);
                var tabLinks = document.querySelectorAll('#tabs-compagnie-arrivee a[data-toggle="tab"]');
                for (var t = 0; t < tabLinks.length; t++) {
                    tabLinks[t].addEventListener('shown.bs.tab', filterActivePane);
                    if (window.jQuery) {
                        window.jQuery(tabLinks[t]).on('shown.bs.tab', filterActivePane);
                    }
                }
            })();
            </script>

        <? else: ?>

            <div class="card">
                <div class="card-header card-header-divider">
                    <h1 class="text-info text-center"><?= $this->session->company->nom_entreprise; ?></h1>
                </div>
                <div class="card-body text-center">
                    <p class="text-warning">PAS DE LIGNE</p>
                    <button type="button" class="btn btn-rounded btn-space btn-success md-trigger" data-modal="add-ligne">
                        <i class="icon icon-left mdi mdi-plus-1"></i>
                        AJOUTER UNE LIGNE
                    </button>
                </div>
            </div>

        <? endif; ?>

    </div>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_ligne/view.php-->
