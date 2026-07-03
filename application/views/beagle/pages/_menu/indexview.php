<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $gare_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
    </p>
</div>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($satutgaresheures)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>GARE_ARRIVEE</th>
                            <th>HEURE</th>
                            <th>STATUT</th>
                            <th>MODIFIER</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($satutgaresheures as $item): ?>

                            <tr>
                                <td><?= $item->nom_gadest; ?></td>
                                <td><?= $item->heure; ?></td>
                                <td><?= $item->typestatutgare; ?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->idsthg}&"; ?>"
                                       class="md-trigger" data-modal="tarif-edit-<?= $item->idsthg; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="tarif-edit-<?= $item->idsthg; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_gadest;?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Statut_Gares/modif/{$this->session->company->ekey}/{$item->idsthg}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                            
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <div class="form-group col-sm-4">
                                                    <label>GARE D'ARRIVEE</label>           
                                                        <select class="form-control form-control-sm" name="argare">
                                                        <option value="<?= $item->idgarearrive;?>"><?= $item->nom_gadest; ?></option>
                                                            <? foreach ($garearrivees as $arr): ?>
                                                            <option value="<?= $arr->code_gadest; ?>">
                                                            <?= $arr->nom_gadest; ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                        </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE STATUT</label>
                                                    <select class="form-control form-control-sm" name="garestat">
                                                        <option value="<?= $item->idstatgare;?>"><?= $item->typestatutgare; ?></option>
                                                        <? foreach ($statutgares as $sta): ?>
                                                            <option value="<?= $sta->idstatutgare; ?>">
                                                                <?= $sta->typestatutgare; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>HEURE</label>
                                                        <select class="form-control form-control-sm" name="heure">
                                                        <option value="<?= $item->idheure;?>"><?= $item->heure; ?></option>
                                                            <? foreach ($heures as $he): ?>
                                                            <option value="<?= $he->id_heure; ?>">
                                                            <?= $he->heure; ?>
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
        
        <? else: ?>

            <div class="card">

                <div class="card-header card-header-divider">
                    <h1 class="text-info text-center"><?= $this->session->company->nom_entreprise; ?></h1>
                </div>

                <div class="card-body">
                    <p class="text-warning text-center">AUCUN STATUT</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter une nouvel statut heure gare d'arrivée ici</div>
            <?= form_open("Statut_Gares/addstatut/{$this->session->company->ekey}"); ?>

            <div class="card-body">
                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                            
                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                <div class="col-lg-12">
                    <label>GARE D'ARRIVEE</label>
                        <select class="form-control form-control-sm" name="argare">
                        <option value=""></option>
                            <? foreach ($garearrivees as $arr): ?>
                                <option value="<?= $arr->code_gadest; ?>">
                                    <?= $arr->nom_gadest; ?>
                                </option>
                            <? endforeach; ?>
                        </select>
                </div>
                
                <div class="col-lg-12">
                    <label>TYPE STATUT</label>
                    <select class="form-control form-control-sm" name="garestat">
                        <option value=""></option>
                        <? foreach ($statutgares as $sta): ?>
                            <option value="<?= $sta->idstatutgare; ?>">
                                <?= "{$sta->typestatutgare}"; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                
                <div class="col-lg-12">
                    <label>HEURE</label>
                        <select class="form-control form-control-sm" name="heure">
                        <option value=""></option>
                            <? foreach ($heures as $he): ?>
                                <option value="<?= $he->id_heure; ?>">
                                    <?= $he->heure; ?>
                                </option>
                            <? endforeach; ?>
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
    <!--End of file: viewstatut.php-->
    <!--File location: application/views/beagle/pages/_menu/viewstatut.php-->