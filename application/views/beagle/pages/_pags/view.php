<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($pages)): ?>
            <div class="card card-table">

                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-success md-trigger"
                                data-modal="form-new-pg">
                            <i class="fas fa-left fas fa-edit"></i>
                            AJOUTER UNE NOUVELLE PAGE
                        </button>
                    </div>

                    <!-- modal for adding a new bus-->
                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-new-pg" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE PAGE</h3>
                                <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open('Pages/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>TYPE PAGE</label>
                                    <input class="form-control form-control-sm" name="pagecompte" type="text" autocomplete="off" required>
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

                </div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>TYPE PAGE</th>
                            
                            <th style="width: 10%;">Actions</th>
                        </tr>

                        </thead>

                        <tbody>

                        <!-- Les bus non encore affectes -->
                        <? foreach ($pages as $ps): ?>

                            <tr>
                                    <td><?=$ps->typedossier;?></td>
                                    
                                <td class="actions">
                                    
                                    <a title="Modification<?= urldecode($ps->typedossier); ?>" class="md-trigger"
                                       data-modal="pse-<?= urldecode($ps->typedossier); ?>"
                                       href="#?pags/edit/<?= urldecode($ps->typedossier); ?>">&nbsp;<span
                                                class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="pse-<?= urldecode($ps->typedossier); ?>" style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION</h3>
                                                <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            
                                            <?= form_open('Pages/editp_/' . $this->session->company->ekey. '/' . urldecode($ps->typedossier), array('class' => 'modal-body form')); ?>
                                            
                                            <div class="row">
                                                <div class="form-group col-sm-4">
                                                    <label>TYPE DE PAGE</label>
                                                    <input class="form-control form-control-sm" name="upagecompte" type="text" autocomplete="off" required value="<?= $ps->typedossier; ?>">

                                                
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
        <? else: ?>
    </div>
    <!--auncun bus dans la bd-->
    <div class="col-lg-10 offset-lg-1">
        <div class="card">

            <div class="card-header card-header-divider">
                <?= $this->session->company->nom_entreprise; ?>

                <div class="tools">
                    <button class="btn btn-rounded btn-space btn-success md-trigger"
                            data-modal="form-add-page-menu">
                        <i class="fas fa-left fas fa-edit"></i>
                        AJOUTER UNE NOUVELLE PAGE
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="form-add-page-menu" style="perspective: 1300px;">

                    <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE PAGE</h3>
                                <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open('Pages/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>TYPE PAGE</label>
                                    <input class="form-control form-control-sm" name="pagecompte" type="text" autocomplete="off" required>
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

            </div>

            <div class="card-body text-center">
                <h2>AUCUNE PAGE TROUVE</h2>
            </div>

        </div>
    </div>
    <? endif; ?>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_pags/view.php-->