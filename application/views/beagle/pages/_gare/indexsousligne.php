<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
        <a href="<?= site_url("gares/{$this->session->company->ekey}"."/gTv/"."{$bus_stop->code_gaexp}"."/prog/". $conex->roleattribut.'/'.$gare_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    <div class="col-lg-12">
            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>NOM SOUS GARE</th>
                            <th>LIGNE</th>
                            <th>POSITION</th>
                            <th>TEMPS</th>
                            <th>HEURE</th>
                            <th>ACTION</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($positionlignes as $item): ?>

                            <tr>
                                
                                <td><?= $item->nomsousgare; ?></td>
                                <td><?= $item->nom_ligne; ?></td>
                                <td><?= $item->possitiongare; ?></td>
                                <td><?= $item->minutetemps; ?></td>
                                <td><?= $item->heure; ?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->idposition}&{$item->idposition}"; ?>"
                                       class="md-trigger" data-modal="edit-<?= $item->idposition; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="edit-<?= $item->idposition; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->nomsousgare;?></h3>
                                                <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Programmes/updateligne/{$this->session->company->ekey}/{$item->gareprinceid}/{$item->idposition}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                               <div class="form-group col-sm-4">
                                                    <label>NOM SOUS GARE</label>
                                                    <select class="form-control form-control-sm" name="_nomsousgare">
                                                        <option value="<?= $item->idsousgar;?>"><?= $item->nomsousgare;?></option>
                                                        <? foreach ($sousgares as $sous): ?>
                                                        <option value="<?= $sous->idsousgare; ?>">
                                                            <?= $sous->nomsousgare; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>LIGNE</label>
                                                    <select class="form-control form-control-sm" name="_nomligne">
                                                        <option value="<?= $item->idligne; ?>"><?= $item->nom_ligne; ?></option>
                                                        <? foreach ($lignes as $depart): ?>
                                                            <option value="<?= $depart->ident_ligne; ?>">
                                                            <?= $depart->nom_ligne; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>POSITION</label>
                                                    <select class="form-control form-control-sm" name="position">
                                                        <option value="<?= $item->idposit; ?>"><?= $item->possitiongare; ?></option>
                                                        <? foreach ($positions as $posit): ?>
                                                            <option value="<?= $posit->idinter; ?>">
                                                                <?= $posit->possitiongare; ?> / <?= $posit->minutetemps; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>HEURE</label>
                                                        <select class="form-control form-control-sm" name="heureidprog">
                                                        <option value="<?= $item->lgheures; ?>"><?= $item->heure; ?></option>
                                                        <? foreach ($lignesheure as $ligne): ?>
                                                        <option value="<?= $ligne->id_ligneheure; ?>">
                                                    <?= $ligne->nom_ligne.'/'.$ligne->heure; ?>
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

                </div>

            </div>
        
    </div>
</div>
    <!--End of file: indexsousligne.php-->
    <!--File location: application/views/beagle/pages/_gare/indexsousligne.php-->