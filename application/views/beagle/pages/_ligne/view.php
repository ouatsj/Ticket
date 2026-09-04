<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-12 d-flex flex-wrap align-items-center mb-2 ml-4 pr-4">
        <button type="button" class="btn btn-space btn-info md-trigger" data-modal="add-ligne">
            <span class="icon mdi mdi-plus-1 text-white"></span>
            AJOUTER UNE LIGNE
        </button>
    </div>
</div>

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
        <?= form_open("Lignes/add/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>
            <div class="row">
                <div class="form-group col-sm-6">
                    <label>GARE DEPART</label>
                    <select class="form-control form-control-sm" name="garedepart" required>
                        <option value=""></option>
                        <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp. '.' .$garedepart->nom_gaep; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?></option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label>GARE ARRIVEE</label>
                    <select class="form-control form-control-sm" name="garearrivee" required>
                        <option value=""></option>
                        <?php
                            $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                'value_format' => 'code_nom',
                            ));
                        ?>
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

<div class="row">
    <div class="col-lg-12">

        <?
        $lignes_par_compagnie_arrivee = !empty($lignes_par_compagnie_arrivee) ? $lignes_par_compagnie_arrivee : array();
        if (!empty($lignes_par_compagnie_arrivee)):
            $group_keys = array_keys($lignes_par_compagnie_arrivee);
            $first_key = reset($group_keys);
            $tab_pref = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $this->input->get('tab'));
        ?>

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
                                                <a href="<?= "#?{$item->ident_ligne}"; ?>"
                                                   class="md-trigger" data-modal="tarif-edit-<?= $item->ident_ligne; ?>">
                                                    <span class="fas fa-edit text-warning"></span>
                                                </a>
                                                <a href="<?= site_url('Lignes/active/' . $this->session->company->ekey . '/' . rawurlencode($item->ident_ligne) . '/' . $actif_lg) . '?tab=' . rawurlencode($pane_id); ?>"
                                                   class="btn btn-space btn-secondary btn-sm"
                                                   title="<?= $actif_lg ? 'Masquer cette ligne du guichet' : 'Réafficher cette ligne au guichet'; ?>">
                                                    <?= $actif_lg
                                                        ? '<span class="icon mdi text-danger">désactiver</span>'
                                                        : '<span class="icon mdi text-success">activer</span>'; ?>
                                                </a>

                                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                                     id="tarif-edit-<?= $item->ident_ligne; ?>">
                                                    <div class="modal-content">
                                                        <div class="modal-header modal-header-colored">
                                                        <h3 class="modal-title">MODIFICATION</h3>
                                                            <button class="close modal-close" type="button"
                                                            data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                            </button>
                                                        </div>
                                                        <?= form_open("Lignes/edit_/{$this->session->company->ekey}/{$item->ident_ligne}" ,array('class' => 'modal-body form')); ?>

                                                        <div class="row">
                                                            <div class="form-group col-sm-3">
                                                                <label>GARE DEPART</label>
                                                                <select class="form-control form-control-sm" name="garedepart">
                                                                <option value="<?= $item->gaexp_lg . '.' . $item->nom_gaep; ?>">
                                                                    <?= "{$item->nom_gaep}"; ?></option>
                                                                    <? foreach ($garedeparts as $garedepart): ?>
                                                                    <option value="<?= $garedepart->code_gaexp. '.'.$garedepart->nom_gaep; ?>">
                                                                    <?= "{$garedepart->nom_gaep}"; ?></option>
                                                                    <? endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-sm-4">
                                                                <label>GARE ARRIVEE</label>
                                                                <select class="form-control form-control-sm" name="garearrivee">
                                                                    <option value="<?= $item->gadest_lg . '.' . $item->nom_gadest; ?>">
                                                                    <?= "{$item->nom_gadest}"; ?></option>
                                                                    <?php
                                                                        $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                                                            'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                                                            'value_format' => 'code_nom',
                                                                        ));
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-sm-4">
                                                                <label>DISTANCE</label>
                                                                <input class="form-control form-control-sm" type="text"
                                                                    name="distance" autocomplete="off" value="<?= "$item->distancekm"; ?>">
                                                            </div>
                                                            <div class="form-group col-sm-4">
                                                                <label>PRIX</label>
                                                                <input class="form-control form-control-sm" type="number" name="distanceprix" autocomplete="off" value="<?= "$item->prixkm"; ?>">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button class="btn btn-secondary modal-close" type="button"
                                                                    data-dismiss="modal">
                                                                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                            </button>
                                                            <button class="btn btn-success md-trigger" type="submit"
                                                                    data-dismiss="modal">
                                                                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                                            </button>
                                                        </div>
                                                        <?= form_close(); ?>
                                                    </div>
                                                </div>
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
