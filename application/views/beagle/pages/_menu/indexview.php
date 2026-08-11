<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-12 d-flex flex-wrap align-items-center mb-2 ml-4 pr-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $gare_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <button type="button" class="btn btn-space btn-info md-trigger" data-modal="add-statut-heure">
            <span class="icon mdi mdi-plus-1 text-white"></span>
            AJOUTER STATUT HEURE
        </button>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="add-statut-heure" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">AJOUTER UN STATUT HEURE GARE D'ARRIVÉE</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open("Statut_Gares/addstatut/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>
            <input type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <input type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <div class="row">
                <div class="form-group col-sm-4">
                    <label>GARE D'ARRIVEE</label>
                    <select class="form-control form-control-sm" name="argare" required>
                        <option value=""></option>
                        <? foreach ($garearrivees as $arr): ?>
                            <option value="<?= $arr->code_gadest; ?>"><?= $arr->nom_gadest; ?></option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>TYPE STATUT</label>
                    <select class="form-control form-control-sm" name="garestat" required>
                        <option value=""></option>
                        <? foreach ($statutgares as $sta): ?>
                            <option value="<?= $sta->idstatutgare; ?>"><?= $sta->typestatutgare; ?></option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>HEURE</label>
                    <select class="form-control form-control-sm" name="heure" required>
                        <option value=""></option>
                        <? foreach ($heures as $he): ?>
                            <option value="<?= $he->id_heure; ?>"><?= $he->heure; ?></option>
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
        $statuts_par_compagnie = !empty($statuts_par_compagnie) ? $statuts_par_compagnie : array();
        if (!empty($statuts_par_compagnie)):
            $group_keys = array_keys($statuts_par_compagnie);
            $first_key = reset($group_keys);
        ?>

            <div class="card card-table">
                <div class="card-header">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <strong>Statuts heure par compagnie d'arrivée</strong>
                        </div>
                        <div class="col-md-6">
                            <input type="search" id="filtre-statut-heure" class="form-control form-control-sm"
                                   placeholder="Rechercher gare, heure, statut…" autocomplete="off">
                        </div>
                    </div>
                    <ul class="nav nav-tabs nav-tabs-primary nav-tabs-classic flex-wrap" role="tablist" id="tabs-statut-compagnie">
                        <? foreach ($statuts_par_compagnie as $cle => $groupe):
                            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                            $nb = !empty($groupe['statuts']) ? count($groupe['statuts']) : 0;
                            $pane_id = 'statut-comp-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'statut-comp-') { $pane_id = 'statut-comp-sans'; }
                            $is_active = ($cle === $first_key);
                        ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $is_active ? ' active show' : ''; ?>"
                                   href="#<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-toggle="tab" role="tab"
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
                        <? foreach ($statuts_par_compagnie as $cle => $groupe):
                            $pane_id = 'statut-comp-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'statut-comp-') { $pane_id = 'statut-comp-sans'; }
                            $is_active = ($cle === $first_key);
                        ?>
                            <div class="tab-pane fade<?= $is_active ? ' active show' : ''; ?>"
                                 id="<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                 role="tabpanel">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>GARE_ARRIVEE</th>
                                        <th>HEURE</th>
                                        <th>STATUT</th>
                                        <th>MODIFIER</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <? foreach ($groupe['statuts'] as $item): ?>
                                        <tr>
                                            <td><?= $item->nom_gadest; ?></td>
                                            <td><?= $item->heure; ?></td>
                                            <td><?= $item->typestatutgare; ?></td>
                                            <td class="actions">
                                                <a href="<?= "#?{$item->idsthg}&"; ?>"
                                                   class="md-trigger" data-modal="statut-edit-<?= $item->idsthg; ?>">
                                                    <span class="fas fa-edit text-warning"></span>
                                                </a>

                                                <? /* Modal léger : pas de listes complètes gares×heures (causait HTTP 500). */ ?>
                                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                                     id="statut-edit-<?= $item->idsthg; ?>">
                                                    <div class="modal-content">
                                                        <div class="modal-header modal-header-colored">
                                                            <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_gadest;?></h3>
                                                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                                                                <span class="mdi mdi-close text-white"></span>
                                                            </button>
                                                        </div>
                                                        <?= form_open("Statut_Gares/modif/{$this->session->company->ekey}/{$item->idsthg}", array('class' => 'modal-body form')); ?>
                                                        <input type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                                        <input type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                                        <input type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                        <input type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                        <div class="row">
                                                            <div class="form-group col-sm-4">
                                                                <label>GARE D'ARRIVEE</label>
                                                                <select class="form-control form-control-sm" name="argare">
                                                                    <option value="<?= $item->idgarearrive;?>"><?= $item->nom_gadest; ?></option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-sm-4">
                                                                <label>TYPE STATUT</label>
                                                                <select class="form-control form-control-sm" name="garestat">
                                                                    <option value="<?= $item->idstatgare;?>"><?= $item->typestatutgare; ?></option>
                                                                    <? foreach ($statutgares as $sta): ?>
                                                                        <? if ((string)$sta->idstatutgare === (string)$item->idstatgare) continue; ?>
                                                                        <option value="<?= $sta->idstatutgare; ?>"><?= $sta->typestatutgare; ?></option>
                                                                    <? endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-sm-4">
                                                                <label>HEURE</label>
                                                                <select class="form-control form-control-sm" name="heure">
                                                                    <option value="<?= $item->idheure;?>"><?= $item->heure; ?></option>
                                                                </select>
                                                                <small class="form-text text-muted">Pour changer gare/heure, créez un nouvel enregistrement.</small>
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
                                            </td>
                                        </tr>
                                    <? endforeach; ?>
                                    </tbody>
                                </table>
                                <p class="text-muted filtre-statut-vide d-none mb-0">Aucun résultat pour cette recherche.</p>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                var input = document.getElementById('filtre-statut-heure');
                if (!input) { return; }
                function filterActivePane() {
                    var q = (input.value || '').toLowerCase().trim();
                    var pane = document.querySelector('.tab-content > .tab-pane.active');
                    if (!pane) { return; }
                    var rows = pane.querySelectorAll('tbody tr');
                    var visible = 0;
                    for (var i = 0; i < rows.length; i++) {
                        var show = !q || (rows[i].textContent || '').toLowerCase().indexOf(q) !== -1;
                        rows[i].style.display = show ? '' : 'none';
                        if (show) { visible++; }
                    }
                    var emptyMsg = pane.querySelector('.filtre-statut-vide');
                    if (emptyMsg) {
                        emptyMsg.classList.toggle('d-none', !(q && visible === 0));
                    }
                }
                input.addEventListener('input', filterActivePane);
                var tabLinks = document.querySelectorAll('#tabs-statut-compagnie a[data-toggle="tab"]');
                for (var t = 0; t < tabLinks.length; t++) {
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
                    <p class="text-warning">AUCUN STATUT</p>
                    <button type="button" class="btn btn-rounded btn-space btn-success md-trigger" data-modal="add-statut-heure">
                        <i class="icon icon-left mdi mdi-plus-1"></i>
                        AJOUTER STATUT HEURE
                    </button>
                </div>
            </div>

        <? endif; ?>

    </div>
</div>
<!--End of file: indexview.php-->
<!--File location: application/views/beagle/pages/_menu/indexview.php-->
