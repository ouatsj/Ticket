<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($statutgares)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>TYPE STATUT</th>
                            <th>MODIFIER</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($statutgares as $item): ?>

                            <tr>
                                <td><?= $item->typestatutgare; ?></td>
                                
                                <td class="actions">
                                    <a href="<?= "#?{$item->idstatutgare}&"; ?>"
                                       class="md-trigger" data-modal="tarif-edit-<?= $item->idstatutgare; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="tarif-edit-<?= $item->idstatutgare; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->typestatutgare;?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Statut_Gares/edit_/{$this->session->company->ekey}/{$item->idstatutgare}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE STATUT</label>
                                                    <input class="form-control form-control-sm" name="statuttype"
                                                           value="<?= $item->typestatutgare; ?>"
                                                           type="text" required autocomplete="off"
                                                           placeholder="<?= $item->typestatutgare; ?>">
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
            <div class="card-header">Ajouter un nouvel statut ici</div>
            <?= form_open("Statut_Gares/add/{$this->session->company->ekey}"); ?>

            <div class="card-body">
                
                <div class="col-lg-12">
                        <label>TYPE STATUT</label>
                        <input class="form-control form-control-sm" name="statut"
                        type="text" required autocomplete="off"
                        placeholder="principale ou secondaire...">
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
    <!--File location: application/views/beagle/pages/_menu/index.php-->