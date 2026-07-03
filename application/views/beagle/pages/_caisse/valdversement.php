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

                <div class="title">Les versements</div>
                    <div class="form-group text-center">Les versements de la caisse : <? if($versementsvalid == NULL):?> 0 <? else:?><?=$versementsvalid->montant_verser; ?><? endif; ?></div>
            </div>
            <div class="card-body">

                <div class="table-responsive noSwipe">

                    <table class="table table-striped table-hover" id="table1">
                        <thead>

                        <tr>
                            <th>DATE</th>
                            <th>OPERATEUR</th> 
                            <th></th>
                            <th>TYPE DEPOT</th>
                            <th>NOM</th>
                            <th>MONTANT</th>
                            <th>BORDEREAU</th>
                            <th>COMMENTAIRE</th>
                            <th class="actions"></th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($versements as $item): ?>
                            <tr>
                            <td><span><?= $item->date_versement;?></span></td>
                                <td><span><?= $item->username;?></span></td>
                                <td><span><?= $item->nom_compagnie;?></span></td>
                                <td><span><?= $item->type_versement;?></span></td>
                                <td><span><?= $item->nom_beneficiaire;?></span></td>
                                <td><span><?= $item->montant_verser;?></span></td>
                                <td><span><?= $item->bordereau_verser;?></span></td>
                                <td><span><?= $item->commentaire;?></span></td>
                                <td>
                                    <a href="<?= "#?{$item->id_versements}&&&"; ?>"
                                        class="md-trigger" data-modal="vers-edit-<?= $item->id_versements; ?>" title="Valider">
                                        <span class="fas fa-edit text-success"></span>
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="vers-edit-<?= $item->id_versements; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">VALIDATION SUR VERSEMENT: <?= $item->nom_beneficiaire; ?></h3>
                                                <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Caisses/valversement/{$this->session->company->ekey}/{$item->id_versements}/{$item->validop}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                
                                            <input type="hidden" name="idgar" value="<?= $gare_stop->idengare; ?>">
                                            <input type="hidden" name="iduse" value="<?= $conex->roleattribut; ?>">
                                            <input type="hidden" name="idsousgar" value="<?= $gare_stop->idsousgare; ?>">
                                              
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                    name="autrecommentverse"
                                                    cols="30" rows="2"><?= $item->commentaire; ?></textarea>
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

                                    <a href="<?= "#?{$item->id_versements}&&&"; ?>"
                                        class="md-trigger" data-modal="rejet-vers-edit-<?= $item->id_versements; ?>" title="Rejet">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>
                                    
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="rejet-vers-edit-<?= $item->id_versements; ?>">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">REJET VERSEMENT DE : <?= $item->nom_beneficiaire; ?></h3>
                                                <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Caisses/rejetversement/{$this->session->company->ekey}/{$item->id_versements}/{$item->validop}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                
                                            <input type="hidden" name="idgar" value="<?= $gare_stop->idengare; ?>">
                                            <input type="hidden" name="iduse" value="<?= $conex->roleattribut; ?>">
                                            <input type="hidden" name="idsousgar" value="<?= $gare_stop->idsousgare; ?>">
                                              
                                                <div class="form-group col-sm-4">
                                                    <label>OBSERVATION</label>
                                                    <textarea class="form-control form-control-sm"
                                                    name="autrecommentverse"
                                                    cols="30" rows="2"><?= $item->commentaire; ?></textarea>
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