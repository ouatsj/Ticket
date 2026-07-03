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
        
        <? if (!empty($heuresligne)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>LIGNE</th>
                            <th>HEURE</th>
                            <th class="actions">ACTION</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($heuresligne as $item): ?>

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

                </div>

            </div>
        
        <? else: ?>

            <div class="card">

                <div class="card-header card-header-divider">
                    <h1 class="text-info text-center"><?= $this->session->company->nom_entreprise; ?></h1>
                </div>

                <div class="card-body">
                    <p class="text-warning text-center">AUCUNE HEURE</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter une nouvelle heure et ligne ici</div>
            <?= form_open("Ligneheure/add/{$this->session->company->ekey}"); ?>

                <div class="card-body">
                    <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                            
                    <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                    <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                    <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                    <div class="col-lg-12">
                        <label>LIGNE</label>
                            <select class="form-control form-control-sm" name="itineraire">
                            <option value=""></option>
                                <? foreach ($lignes as $ligne): ?>
                                    <option value="<?= $ligne->ident_ligne; ?>">
                                        <?= "{$ligne->nom_ligne}"; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                    </div>
               
                    <div class="col-lg-12">
                        <label>HEURE</label>
                            <select class="form-control form-control-sm" name="heureitine">
                            <option value=""></option>
                                <? foreach ($heures as $hr): ?>
                                    <option value="<?= $hr->id_heure; ?>">
                                        <?= "{$hr->heure}"; ?>
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
    <!--End of file: indexheure.php-->
    <!--File location: application/views/beagle/pages/_heure/indexheure.php-->