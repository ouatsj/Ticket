<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($heures)): ?>

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>HEURE</th>
                            <th class="actions">ACTION</th>

                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($heures as $item): ?>

                            <tr>
                                <td><?= $item->heure; ?></td>

                                <td class="actions">
                                    <a href="<?= "#?{$item->id_heure}&"; ?>"
                                       class="md-trigger" data-modal="heure-edit-<?= $item->id_heure; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    
                                    <a href="<?= site_url('Heures/active/' . $this->session->company->ekey . '/' . $item->id_heure. '/' . $item->h_active);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->h_active === '1') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                            class="icon mdi text-success">activer</span>' ?>
                                        </a>&nbsp;
                                        &nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="heure-edit-<?= $item->id_heure; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION</h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Heures/edit_/{$this->session->company->ekey}/{$item->id_heure}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                <div class="form-group col-sm-3">
                                                    <label>HEURE</label>
                                                    <input class="form-control form-control-sm" name="heure_ligne"
                                                           value="<?= "{$item->heure}"; ?>"
                                                           type="text" autocomplete="off"
                                                           placeholder="<?= $item->heure; ?>">
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
            <div class="card-header">Ajouter une nouvelle heure ici</div>
            <?= form_open("Heures/add/{$this->session->company->ekey}"); ?>

                <div class="card-body">
                    <div class="col-lg-12">
                        <label>HEURE</label>
                        <input class="form-control form-control-sm" name="heur"
                        type="time" required>
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
</div>
    <!--End of file: index.php-->
    <!--File location: application/views/beagle/pages/_heure/index.php-->