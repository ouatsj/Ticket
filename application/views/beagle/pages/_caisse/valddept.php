<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
        <p class="mt-0 mb-2 ml-4">
            
            <a href="<?= site_url("caisses/caissieres/{$this->session->company->ekey}"."/". $conex->roleattribut.'/'.$gare_stop->idengare.'/'.$gare_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR A LA CAISSE&nbsp;
            </a>
        </p>
</div>
<div class="row">

    <div class="col-lg-12">

        <div class="card card-table">
            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Les dépots</div>
                    <div class="form-group text-center">Les depots de la caisse : <? if($depotsvalid == NULL):?> 0 <? else:?><?=$depotsvalid->montant_depot; ?><? endif; ?></div>
            </div>
            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">
                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th></th>
                            <th>TYPE DEPOT</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>COMMENTAIRE</th>
                            <th class="actions"></th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($depots as $alldepots): ?>

                            <tr>
                                <td>
                                    <?= $alldepots->datedepot; ?>
                                </td>
                                <td>
                                    <?= $alldepots->nom_compagnie; ?>
                                </td>
                                <td>
                                    <?= $alldepots->type_depot;?>
                                </td>
                                
                                <td>
                                    <?= $alldepots->nom_pre;?>
                                </td>
                                <td>
                                    <?= number_format($alldepots->montant_depot, 0, '', ' '); ?>
                                </td>

                                <td>
                                    <?= $alldepots->commentaire_depot; ?>
                                </td>
                                
                                <td>
                                    <a href="<?= "#?{$alldepots->id_depot}&&&"; ?>"
                                        class="md-trigger" data-modal="depval-edit-<?= $alldepots->id_depot; ?>" title="Valider">
                                        <span class="fas fa-edit text-success"></span>
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="depval-edit-<?= $alldepots->id_depot; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">VALIDATION DEPOT: <?= $alldepots->nom_pre; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Depots/valdepot/{$this->session->company->ekey}/{$alldepots->id_depot}/{$alldepots->opvalid}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                
                                            
                                            <input type="hidden" name="idgar" value="<?= $gare_stop->idengare; ?>">
                                            <input type="hidden" name="iduse" value="<?= $conex->roleattribut; ?>">
                                            <input type="hidden" name="idsousgar" value="<?= $gare_stop->idsousgare; ?>">
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="comment"
                                                            cols="30" rows="2"><?= $alldepots->commentaire_depot; ?></textarea>
                                                </div>
												
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset"
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

                                    <a href="<?= "#?{$alldepots->id_depot}&&&"; ?>"
                                        class="md-trigger" data-modal="deprejet-edit-<?= $alldepots->id_depot; ?>" title="Rejet">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="deprejet-edit-<?= $alldepots->id_depot; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">REJETER DEPOT DE : <?= $alldepots->nom_pre; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Depots/rejetdepots/{$this->session->company->ekey}/{$alldepots->id_depot}/{$alldepots->opvalid}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                
                                            
                                            <input type="hidden" name="idgar" value="<?= $gare_stop->idengare; ?>">
                                            <input type="hidden" name="iduse" value="<?= $conex->roleattribut; ?>">
                                            <input type="hidden" name="idsousgar" value="<?= $gare_stop->idsousgare; ?>">
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                            name="comment"
                                                            cols="30" rows="2"><?= $alldepots->commentaire_depot; ?></textarea>
                                                </div>
												
                                            </div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset"
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
</div>


    