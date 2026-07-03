<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($tempspositions)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>POSITION</th>
                            <th>TEMPS</th>
                            <th>ACTION</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($tempspositions as $item): ?>

                            <tr>
                                <td><?= $item->possitiongare; ?></td>
                                <td><?= $item->minutetemps; ?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->idinter}&"; ?>"
                                       class="md-trigger" data-modal="edit-<?= $item->idinter; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="edit-<?= $item->idinter; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->possitiongare;?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Gares/modifposit/{$this->session->company->ekey}/{$item->idinter}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                <div class="form-group col-sm-4">
                                                    <label>POSITION</label>
                                                <input class="form-control form-control-sm" name="position" type="text" autocomplete="off" value="<?= $item->possitiongare;?>" placeholder="<?= $item->possitiongare;?>">
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>MUNITE</label>
                                                <input class="form-control form-control-sm" name="tempsminute" type="number" autocomplete="off" value="<?= $item->minutetemps;?>" placeholder="<?= $item->minutetemps;?>">

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
                    <p class="text-warning text-center">AUCUNE POSITION</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter une nouvelle position</div>
            <?= form_open("Gares/addposit/{$this->session->company->ekey}"); ?>

            <div class="card-body">
                
                  
                <div class="form-group">
                    <label>POSITION</label>
                    <input class="form-control form-control-sm" name="position" type="text" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>MUNITE</label>
                    <input class="form-control form-control-sm" name="tempsminute" type="number" autocomplete="off">
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
    <!--End of file: posit.php-->
    <!--File location: application/views/beagle/pages/_gare/posit.php-->