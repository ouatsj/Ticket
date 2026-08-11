<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-12 d-flex flex-wrap align-items-center mb-2 ml-4 pr-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $gare_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <button type="button" class="btn btn-space btn-info md-trigger" data-modal="add-ligneheure">
            <span class="icon mdi mdi-plus-1 text-white"></span>
            AJOUTER LIGNE / HEURE
        </button>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="add-ligneheure" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">AJOUTER UNE NOUVELLE HEURE ET LIGNE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open("Ligneheure/add/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">

            <div class="row">
                <div class="form-group col-sm-6">
                    <label>LIGNE</label>
                    <select class="form-control form-control-sm" name="itineraire" required>
                        <option value=""></option>
                        <? foreach ($lignes as $ligne): ?>
                            <option value="<?= $ligne->ident_ligne; ?>">
                                <?= "{$ligne->nom_ligne}"; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label>HEURE</label>
                    <select class="form-control form-control-sm" name="heureitine" required>
                        <option value=""></option>
                        <? foreach ($heures as $hr): ?>
                            <option value="<?= $hr->id_heure; ?>">
                                <?= "{$hr->heure}"; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
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
        $heuresligne_par_compagnie = !empty($heuresligne_par_compagnie) ? $heuresligne_par_compagnie : array();
        if (!empty($heuresligne_par_compagnie)):
            $group_keys = array_keys($heuresligne_par_compagnie);
            $first_key = reset($group_keys);
        ?>

            <div class="card card-table">
                <div class="card-header">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <strong>Lignes / heures par compagnie d'arrivée</strong>
                        </div>
                        <div class="col-md-6">
                            <input type="search"
                                   id="filtre-ligneheure"
                                   class="form-control form-control-sm"
                                   placeholder="Rechercher ligne ou heure…"
                                   autocomplete="off">
                        </div>
                    </div>
                    <ul class="nav nav-tabs nav-tabs-primary nav-tabs-classic flex-wrap" role="tablist" id="tabs-compagnie-arrivee">
                        <?
                        foreach ($heuresligne_par_compagnie as $cle => $groupe):
                            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                            $nb = !empty($groupe['heureslignes']) ? count($groupe['heureslignes']) : 0;
                            $pane_id = 'comp-arr-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'comp-arr-') {
                                $pane_id = 'comp-arr-sans';
                            }
                            $is_active = ($cle === $first_key);
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
                        <? foreach ($heuresligne_par_compagnie as $cle => $groupe):
                            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                            $pane_id = 'comp-arr-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'comp-arr-') {
                                $pane_id = 'comp-arr-sans';
                            }
                            $is_active = ($cle === $first_key);
                            $table_id = 'table-' . $pane_id;
                        ?>
                            <div class="tab-pane fade<?= $is_active ? ' active show' : ''; ?>"
                                 id="<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                 role="tabpanel"
                                 data-compagnie="<?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?>">

                                <table class="table table-striped table-hover table-ligneheure"
                                       id="<?= htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8'); ?>">
                                    <thead>
                                    <tr>
                                        <th>LIGNE</th>
                                        <th>HEURE</th>
                                        <th class="actions">ACTION</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <? foreach ($groupe['heureslignes'] as $item): ?>
                                        <tr>
                                            <td><?= $item->nom_ligne; ?></td>
                                            <td><?= $item->heure; ?></td>
                                            <td class="actions">
                                                <a href="<?= "#?{$item->id_ligneheure}&"; ?>"
                                                   class="md-trigger" data-modal="heure-edit-<?= $item->id_ligneheure; ?>">
                                                    <span class="fas fa-edit text-warning"></span>
                                                </a>

                                                <a href="<?= site_url('Ligneheure/active/' . $this->session->company->ekey . '/' . $item->id_ligneheure. '/' . $item->actif_lh.'/'.$conex->roleattribut.'/'.$gare_stop->idengare.'/'.$gare_stop->idsousgare);?> "class="btn btn-space btn-secondary">
                                                        <?= ($item->actif_lh === '1') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                                        class="icon mdi text-success">activer</span>' ?>
                                                    </a>&nbsp;
                                                    &nbsp;
                                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                                     id="heure-edit-<?= $item->id_ligneheure; ?>">
                                                    <div class="modal-content">
                                                        <div class="modal-header modal-header-colored">
                                                            <h3 class="modal-title">MODIFICATION</h3>
                                                            <button class="close modal-close" type="button"
                                                                    data-dismiss="modal" aria-hidden="true"><span
                                                                        class="mdi mdi-close text-white"></span>
                                                            </button>
                                                        </div>
                                                        <?= form_open("Ligneheure/edit_/{$this->session->company->ekey}/{$item->id_ligneheure}",
                                                            array('class' => 'modal-body form')); ?>

                                                        <div class="row">
                                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                            <div class="form-group col-sm-3">
                                                                <label>LIGNE</label>
                                                                    <select class="form-control form-control-sm" name="itineraire">
                                                                    <option value="<?= $item->ligne_id; ?>"><?= $item->nom_ligne; ?></option>
                                                                        <? foreach ($lignes as $ligne): ?>
                                                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                                                <?= "{$ligne->nom_ligne}"; ?>
                                                                            </option>
                                                                        <? endforeach; ?>
                                                                    </select>
                                                            </div>
                                                            <div class="form-group col-sm-3">
                                                                <label>HEURE</label>
                                                                    <select class="form-control form-control-sm" name="heureitine">
                                                                    <option value="<?= $item->heure_identif; ?>"><?= $item->heure; ?></option>
                                                                        <? foreach ($heures as $hr): ?>
                                                                            <option value="<?= $hr->id_heure; ?>">
                                                                                <?= "{$hr->heure}"; ?>
                                                                            </option>
                                                                        <? endforeach; ?>
                                                                    </select>
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
                                <p class="text-muted filtre-ligneheure-vide d-none mb-0">Aucun résultat pour cette recherche.</p>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                var input = document.getElementById('filtre-ligneheure');
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
                    var emptyMsg = pane.querySelector('.filtre-ligneheure-vide');
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
                    <p class="text-warning">AUCUNE HEURE</p>
                    <button type="button" class="btn btn-rounded btn-space btn-success md-trigger" data-modal="add-ligneheure">
                        <i class="icon icon-left mdi mdi-plus-1"></i>
                        AJOUTER LIGNE / HEURE
                    </button>
                </div>

            </div>
        
        <? endif; ?>

    </div>
</div>
<!--End of file: indexheure.php-->
<!--File location: application/views/beagle/pages/_heure/indexheure.php-->
