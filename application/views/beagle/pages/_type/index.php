<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($typeclients)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th style="width:40%;">TYPES</th>
                            <th class="actions">ACTION</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($typeclients as $item): ?>

                            <tr>

                                <td><?= "{$item->nom_type}"; ?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->idtyp}&type={$item->nom_type}"; ?>"
                                       class="md-trigger" data-modal="fonction-edit-<?= $item->idtyp; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="fonction-edit-<?= $item->idtyp; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_type; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Types/editcl_/{$this->session->company->ekey}/{$item->idtyp}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                <div class="form-group col-sm-3">
                                                    <label>TYPE CLIENT</label>
                                                    <input class="form-control form-control-sm" name="type"
                                                           value="<?= "{$item->nom_type}"; ?>"
                                                           type="text" autocomplete="off"
                                                           placeholder="<?= $item->nom_type; ?>">
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
                    <p class="text-warning text-center">AUCUN TYPE</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter un nouvel type ici</div>
            <?= form_open("Types/addclient/{$this->session->company->ekey}"); ?>

            <div class="card-body">
                <div class="col-lg-12">
                    <label>TYPE CLIENT</label>
                    <input class="form-control form-control-sm"
                           name="type_client"
                           autocomplete="off"
                           type="text"
                           placeholder="" autocomplete="off" required>
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
    <!--File location: application/views/beagle/pages/_type/index.php-->