<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<? if (empty($gare_stop) || empty($conex)): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">Impossible d&apos;afficher les tarifs pour cette gare. Revenez à l&apos;accueil.</div>
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url('home'); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
            </a>
        </p>
    </div>
</div>
<? return; endif; ?>
<div class="row">
    <div class="col-12 d-flex flex-wrap align-items-center mb-2 ml-4 pr-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $gare_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        <button type="button" class="btn btn-space btn-info md-trigger" data-modal="add-tarif">
            <span class="icon mdi mdi-plus-1 text-white"></span>
            AJOUTER TARIFICATION
        </button>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="add-tarif" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">AJOUTER UNE TARIFICATION</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open("Tarifs/add/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>
            <input type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <input type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <div class="row">
                <div class="form-group col-sm-6">
                    <label>TYPE TARIF</label>
                    <select class="form-control form-control-sm" name="nomtarif" required>
                        <option value=""></option>
                        <? if (!empty($bases)): ?>
                        <? foreach ($bases as $typetarif): ?>
                            <option value="<?= $typetarif->id_tarifs; ?>"><?= $typetarif->type_tarifs; ?></option>
                        <? endforeach; ?>
                        <? endif; ?>
                    </select>
                </div>
                <div class="form-group col-sm-6">
                    <label>TYPE CLIENT</label>
                    <select class="form-control form-control-sm" name="typeclient" required>
                        <option value=""></option>
                        <? if (!empty($typeclients)): ?>
                        <? foreach ($typeclients as $typeclient): ?>
                            <option value="<?= $typeclient->idtyp; ?>"><?= $typeclient->nom_type; ?></option>
                        <? endforeach; ?>
                        <? endif; ?>
                    </select>
                </div>
                <div class="form-group col-sm-8">
                    <label>DEPART (ligne / heure)</label>
                    <select class="form-control form-control-sm" name="itineraire" required>
                        <option value=""></option>
                        <? if (!empty($lignesheure)): ?>
                        <? foreach ($lignesheure as $ligne): ?>
                            <option value="<?= $ligne->id_ligneheure . '.' . $ligne->ligne_id; ?>">
                                <?= $ligne->nom_ligne.'/'.$ligne->heure; ?>
                            </option>
                        <? endforeach; ?>
                        <? endif; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>MONTANT</label>
                    <input class="form-control form-control-sm" name="prix" type="number" placeholder="prix..." required>
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

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="tarif-edit-0">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">MODIFICATION TARIF</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open('', array('class' => 'modal-body form', 'id' => 'form-tarif-edit')); ?>
        <input type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
        <input type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
        <input type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
        <input type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
        <div class="row">
            <div class="form-group col-sm-4">
                <label>TARIF</label>
                <select class="form-control form-control-sm" name="tarifbase">
                    <? if (!empty($bases)): foreach ($bases as $typetarif): ?>
                        <option value="<?= $typetarif->id_tarifs; ?>"><?= $typetarif->type_tarifs; ?></option>
                    <? endforeach; endif; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE CLIENT</label>
                <select class="form-control form-control-sm" name="typeclient">
                    <? if (!empty($typeclients)): foreach ($typeclients as $typeclient): ?>
                        <option value="<?= $typeclient->idtyp; ?>"><?= $typeclient->nom_type; ?></option>
                    <? endforeach; endif; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>DEPART</label>
                <select class="form-control form-control-sm" name="itineraire"></select>
                <small class="form-text text-muted">Pour changer de départ, créez une nouvelle tarification.</small>
            </div>
            <div class="form-group col-sm-4">
                <label>MONTANT</label>
                <input class="form-control form-control-sm" name="montanttarif" type="number" autocomplete="off">
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
        $tarifications_par_compagnie = !empty($tarifications_par_compagnie) ? $tarifications_par_compagnie : array();
        if (!empty($tarifications_par_compagnie)):
            $group_keys = array_keys($tarifications_par_compagnie);
            $first_key = reset($group_keys);
        ?>

            <div class="card card-table">
                <div class="card-header">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <strong>Tarifs par compagnie d'arrivée</strong>
                        </div>
                        <div class="col-md-6">
                            <input type="search" id="filtre-tarif" class="form-control form-control-sm"
                                   placeholder="Rechercher tarif, ligne, heure…" autocomplete="off">
                        </div>
                    </div>
                    <ul class="nav nav-tabs nav-tabs-primary nav-tabs-classic flex-wrap" role="tablist" id="tabs-tarif-compagnie">
                        <? foreach ($tarifications_par_compagnie as $cle => $groupe):
                            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                            $nb = !empty($groupe['tarifications']) ? count($groupe['tarifications']) : 0;
                            $pane_id = 'tarif-comp-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'tarif-comp-') { $pane_id = 'tarif-comp-sans'; }
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
                        <? foreach ($tarifications_par_compagnie as $cle => $groupe):
                            $pane_id = 'tarif-comp-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'tarif-comp-') { $pane_id = 'tarif-comp-sans'; }
                            $is_active = ($cle === $first_key);
                        ?>
                            <div class="tab-pane fade<?= $is_active ? ' active show' : ''; ?>"
                                 id="<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                 role="tabpanel">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th>TARIF</th>
                                        <th>MONTANT</th>
                                        <th>TYPE CLIENT</th>
                                        <th>DEPART</th>
                                        <th class="actions">MODIFIER</th>
                                        <th>ANNULER</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <? foreach ($groupe['tarifications'] as $item): ?>
                                        <tr>
                                            <td><?= $item->type_tarifs; ?></td>
                                            <td><?= number_format((float) $item->prix, 0, '', ' '); ?></td>
                                            <td><?= $item->nom_type; ?></td>
                                            <td><?= $item->nom_ligne; ?> / <?= $item->heure; ?></td>
                                            <td class="actions">
                                                <a href="#"
                                                   class="md-trigger js-tarif-edit"
                                                   data-modal="tarif-edit-0"
                                                   data-ekey="<?= htmlspecialchars($this->session->company->ekey, ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-id="<?= (int) $item->id_tarification; ?>"
                                                   data-type_tarif="<?= htmlspecialchars($item->type_tarifs, ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-typetarif_id="<?= (int) $item->typetarif_id; ?>"
                                                   data-nom_type="<?= htmlspecialchars($item->nom_type, ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-typeclient_id="<?= (int) $item->typeclient_id; ?>"
                                                   data-ligne_heure_id="<?= (int) $item->ligne_heure_id; ?>"
                                                   data-ligne_id="<?= (int) $item->ligne_id; ?>"
                                                   data-nom_ligne="<?= htmlspecialchars($item->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-heure="<?= htmlspecialchars($item->heure, ENT_QUOTES, 'UTF-8'); ?>"
                                                   data-prix="<?= htmlspecialchars($item->prix, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <span class="fas fa-edit text-warning"></span>
                                                </a>
                                                <a href="<?= site_url('Tarifs/active/' . $this->session->company->ekey . '/' . $item->id_tarification. '/' . $item->actif_taf.'/'.$conex->roleattribut.'/'.$gare_stop->idengare.'/'.$gare_stop->idsousgare);?>" class="btn btn-space btn-secondary">
                                                    <?= ($item->actif_taf === '1') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span class="icon mdi text-success">activer</span>'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?= site_url("Tarifs/supprime/{$this->session->company->ekey}/{$item->id_tarification}/{$item->typeclient_id}/{$item->typetarif_id}/{$conex->roleattribut}/{$gare_stop->idengare}/{$gare_stop->idsousgare}"); ?>" title="supprimer">
                                                    <span class="fas fa-trash-alt text-danger"></span>
                                                </a>
                                            </td>
                                        </tr>
                                    <? endforeach; ?>
                                    </tbody>
                                </table>
                                <p class="text-muted filtre-tarif-vide d-none mb-0">Aucun résultat pour cette recherche.</p>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                var input = document.getElementById('filtre-tarif');
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
                    var emptyMsg = pane.querySelector('.filtre-tarif-vide');
                    if (emptyMsg) {
                        emptyMsg.classList.toggle('d-none', !(q && visible === 0));
                    }
                }
                input.addEventListener('input', filterActivePane);
                var tabLinks = document.querySelectorAll('#tabs-tarif-compagnie a[data-toggle="tab"]');
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
                    <p class="text-warning">AUCUNE TARIFICATION</p>
                    <button type="button" class="btn btn-rounded btn-space btn-success md-trigger" data-modal="add-tarif">
                        <i class="icon icon-left mdi mdi-plus-1"></i>
                        AJOUTER TARIFICATION
                    </button>
                </div>
            </div>

        <? endif; ?>

    </div>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_tarif/view.php-->
