<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($roleuser)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th style="width:40%;">Rôle</th>
                            <th class="actions">Actions</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($roleuser as $item): ?>

                            <tr>

                                <td><?= "{$item->type_rols}"; ?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->id_rols}&id={$item->id_rols}&type={$item->type_rols}"; ?>"
                                       class="md-trigger" data-modal="fonction-edit-<?= $item->id_rols; ?>">
                                        <span class="icon mdi mdi-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="fonction-edit-<?= $item->id_rols; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->type_rols; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Role_User/edit_/{$this->session->company->ekey}/{$item->id_rols}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                <div class="form-group col-sm-3">
                                                    <label>Type de rôle</label>
                                                    <input class="form-control form-control-sm" name="type" id="type"
                                                           value="<?= "{$item->type_rols}"; ?>"
                                                           type="text" autocomplete="off"
                                                           placeholder="<?= $item->type_rols; ?>">
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
                    <p class="text-warning text-center">AUCUN RÔLE</p>
                </div>

            </div>
        
        <? endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-header">Ajouter un nouvel rôle ici</div>
            <?= form_open("Role_User/add/{$this->session->company->ekey}"); ?>

            <div class="card-body">
                <div class="col-lg-12">
                    <label>RÔLE</label>
                    <input class="form-control form-control-sm"
                           name="rol"
                           autocomplete="off"
                           type="text"
                           placeholder="Rôle" autocomplete="off" required>
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
    <!--File location: application/views/beagle/pages/_role/view.php-->