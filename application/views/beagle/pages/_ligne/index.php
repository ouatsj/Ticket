<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($itineraires)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th></th>
                            <th>IDENTIFIANT/LIGNE</th>
                            <th>GARE DEPART</th>
                            <th>GARE ARRIVEE</th>
                            <th>SOUS LIGNE</th>
                            <th class="actions">ACTION</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($itineraires as $item): ?>

                            <tr>
                                <td><?= "{$item->id_tabitinligne}"; ?></td>

                                <td><?= "{$item->code_itineraires}"; ?>/<?= "{$item->nom_ligne}"; ?></td>
                                <td><?= "{$item->depart_itine}"; ?></td>
                                <td><?= "{$item->arrive_itine}"; ?></td>
                                <td><?= "{$item->nom_itineraires}"; ?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->id_itineraire}"; ?>"
                                       class="md-trigger" data-modal="tarif-edit-<?= $item->id_itineraire; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                        &nbsp;
                                        <a href="<?= site_url('Lignes/activeit/' . $this->session->company->ekey . '/' . $item->id_itineraire. '/' . $item->id_tabitinligne. '/' . $item->actiftine. '/' . $item->actifint);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->actifint === '1') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                            class="icon mdi text-success">activer</span>' ?>
                                        </a>&nbsp;
                                        &nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="tarif-edit-<?= $item->id_itineraire; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION</h3>
                                                <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Lignes/editsous_/{$this->session->company->ekey}/{$item->id_itineraire}/{$item->id_tabitinligne}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                <div class="form-group col-sm-3">
                                                    <label>LIGNE</label>
                                                    <select class="form-control form-control-sm" name="ligne">
                                                    <option value="<?= $item->id_lignes; ?>"><?= "{$item->nom_ligne}"; ?></option>
                                                    <? foreach ($lignes as $items): ?>
                                                        <option value="<?= $items->ident_ligne; ?>">
                                                            <?= "{$items->nom_ligne}"; ?>
                                                        </option>
                                                    <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label>GARE DEPART</label>
                                                    <select class="form-control form-control-sm" name="garedepart">
                                                        <option value="<?= $item->code_gaexp . '.' . $item->nom_gaep; ?>">
                                                    <?= "{$item->depart_itine}"; ?></option>
                                                    <? foreach ($garedeparts as $garedepart): ?><option value="<?= $garedepart->code_gaexp. '.' .$garedepart->nom_gaep; ?>">              <?= "{$garedepart->nom_gaep}"; ?>                                 </option>
                                                    <? endforeach; ?>
                                                </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>GARE ARRIVEE</label>
                                                    <select class="form-control form-control-sm" name="garearrivee">
                                                        <option value="<?= $item->code_gadest . '.' . $item->nom_gadest; ?>">
                                                            <?= "{$item->arrive_itine}"; ?></option>
                                                            <? foreach ($garearrivees as $garearrive): ?>  <option value="<?= $garearrive->code_gadest. '.' .$garearrive->nom_gadest; ?>">               <?= "{$garearrive->nom_gadest}"; ?></option>
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

                </div>

            </div>
        
        <? else: ?>

            <div class="card">

                <div class="card-header card-header-divider">
                    <h1 class="text-info text-center"><?= $this->session->company->nom_entreprise; ?></h1>
                </div>

                <div class="card-body">
                    <p class="text-warning text-center">PAS DE SOUS LIGNE</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter une sous ligne ici</div>
            <?= form_open("Lignes/additine/{$this->session->company->ekey}"); ?>

            <div class="card-body">
                <div class="col-lg-12">
                    <label>LIGNE</label>
                    <select class="form-control form-control-sm" name="ligne">
                    <option value=""></option>
                    <? foreach ($lignes as $items): ?>
                        <option value="<?= $items->ident_ligne; ?>">
                            <?= "{$items->nom_ligne}"; ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-12">
                    <label>GARE DEPART</label>
                    <select class="form-control form-control-sm" name="garedepart">
                    <option value=""></option>
                    <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp. '.' .$garedepart->nom_gaep; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?>
                            </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-12">
                    <label>GARE DE TRANSITE</label>
                    <select class="form-control form-control-sm" name="garearrivee">
                    <option value=""></option>
                        <? foreach ($garearrivees as $garearrive): ?>
                                <option value="<?= $garearrive->code_gadest. '.' .$garearrive->nom_gadest; ?>">
                                    <?= "{$garearrive->nom_gadest}"; ?>
                                </option>
                        <? endforeach; ?>
                    </select>
                    </select>
                </div>
                <div class="col-lg-12">
                    <label>GARE DEPART</label>
                    <select class="form-control form-control-sm" name="garedepartsecond">
                    <option value=""></option>
                    <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp. '.' .$garedepart->nom_gaep; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?>
                            </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-12">
                    <label>GARE ARRIVEE</label>
                    <select class="form-control form-control-sm" name="garearriveesecond">
                    <option value=""></option>
                        <? foreach ($garearrivees as $garearrive): ?>
                                <option value="<?= $garearrive->code_gadest. '.' .$garearrive->nom_gadest; ?>">
                                    <?= "{$garearrive->nom_gadest}"; ?>
                                </option>
                        <? endforeach; ?>
                        </select>
                    </select>
                </div>
                <div class="col-lg-12">
                    <label>GARE DEPART2</label>
                    <select class="form-control form-control-sm" name="garedepartsecond2">
                    <option value=""></option>
                    <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp. '.' .$garedepart->nom_gaep; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?>
                            </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-12">
                    <label>GARE DE TRANSITE2</label>
                    <select class="form-control form-control-sm" name="garearrivee2">
                    <option value=""></option>
                        <? foreach ($garearrivees as $garearrive): ?>
                                <option value="<?= $garearrive->code_gadest. '.' .$garearrive->nom_gadest; ?>">
                                    <?= "{$garearrive->nom_gadest}"; ?>
                                </option>
                        <? endforeach; ?>
                    </select>
                    </select>
                </div>
                <div class="col-lg-12">
                    <label>GARE DEPART3</label>
                    <select class="form-control form-control-sm" name="garedepartsecond3">
                    <option value=""></option>
                    <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->code_gaexp. '.' .$garedepart->nom_gaep; ?>">
                                <?= "{$garedepart->nom_gaep}"; ?>
                            </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-12">
                    <label>GARE ARRIVEE3</label>
                    <select class="form-control form-control-sm" name="garearriveesecond3">
                    <option value=""></option>
                        <? foreach ($garearrivees as $garearrive): ?>
                                <option value="<?= $garearrive->code_gadest. '.' .$garearrive->nom_gadest; ?>">
                                    <?= "{$garearrive->nom_gadest}"; ?>
                                </option>
                        <? endforeach; ?>
                        </select>
                    </select>
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
    <!--End of file: index.php-->
    <!--File location: application/views/beagle/pages/_ligne/index.php-->