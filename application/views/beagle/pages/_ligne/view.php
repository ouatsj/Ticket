<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($lignes)): ?>

            <div class="card card-table">

            <div class="card-header"></div>

            <div class="card-body">

                <table class="table table-striped table-hover" id="table1">

                    <thead>

                    <tr>
                        <th>IDENTIFIANT</th>
                        <th>GARE DEPART</th>
                        <th>GARE ARRIVEE</th>
                        <th>DISTANCE(KM)</th>
                        <th>PRIX</th>
                        <th>LIGNE</th>
                        <th class="actions">ACTION</th>
                    </tr>

                    </thead>

                    <tbody>
                    <? foreach ($lignes as $item): ?>

                        <tr>

                            <td><?= $item->ident_ligne; ?></td>
                            <td><?= $item->nom_gaep; ?></td>
                            <td><?= $item->nom_gadest; ?></td>
                            <td><?= $item->distancekm; ?></td>
                            <td><?= number_format($item->prixkm, 0, '', ' '); ?></td>
                            <td><?= $item->nom_ligne; ?></td>
                            <td class="actions">
                                <a href="<?= "#?{$item->ident_ligne}"; ?>"
                                   class="md-trigger" data-modal="tarif-edit-<?= $item->ident_ligne; ?>">
                                    <span class="fas fa-edit text-warning"></span>
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
                                                    <? foreach ($garearrivees as $garearrivee): ?>
                                                        <option value="<?= $garearrivee->code_gadest . '.' . $garearrivee->nom_gadest; ?>">
                                                            <?= "{$garearrivee->nom_gadest}"; ?></option>
                                                    <? endforeach; ?>
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

            </div>

        </div>
    
    <? else: ?>

        <div class="card">

            <div class="card-header card-header-divider">
                <h1 class="text-info text-center"><?= $this->session->company->nom_entreprise; ?></h1>
            </div>

            <div class="card-body">
                <p class="text-warning text-center">PAS DE LIGNE</p>
            </div>

        </div>
    
    <? endif; ?>

</div>

<div class="col-lg-4">
    <div class="card text-center">
        <div class="card-header">Ajouter une ligne ici</div>
        <?= form_open("Lignes/add/{$this->session->company->ekey}"); ?>

        <div class="card-body">
            <div class="col-lg-12">
                <label>GARE DEPART</label>
                <select class="form-control form-control-sm" name="garedepart">
                <option value=""></option>
                <? foreach ($garedeparts as $garedepart): ?>
                    <option value="<?= $garedepart->code_gaexp. '.' .$garedepart->nom_gaep; ?>">
                        <?= "{$garedepart->nom_gaep}"; ?></option>
                <? endforeach; ?>
                </select>
            </div>
            <div class="col-lg-12">
                <label>GARE ARRIVEE</label>
                <select class="form-control form-control-sm" name="garearrivee">
                <option value=""></option>
                    <? foreach ($garearrivees as $garearrivee): ?>
                        <option value="<?= $garearrivee->code_gadest. '.' .$garearrivee->nom_gadest; ?>">
                            <?= "{$garearrivee->nom_gadest}"; ?></option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="col-lg-12">
                <label>DISTANCE</label>
                <input class="form-control form-control-sm" type="text"
                name="distance" autocomplete="off" value="">
            </div>
            <div class="col-lg-12">
                <label>PRIX</label>
                <input class="form-control form-control-sm" type="number"
                name="distanceprix" autocomplete="off" value="">
            </div>
            <div class="card-footer">
                <button class="btn btn-primary btn-big" type="submit">
                    <i class="mdi mdi-icon mdi-plus-1 mdi-hc-2x"></i>
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>

</div>
    <!--End of file: view.php-->
    <!--File location: application/views/beagle/pages/_tarif/view.php-->