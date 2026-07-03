<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($menus)): ?>
            <div class="card card-table">

                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-success md-trigger"
                                data-modal="form-new-menu">
                            <i class="fas fa-left fas fa-edit"></i>
                            AJOUTER UN NOUVEL MENU
                        </button>
                    </div>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-new-menu" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN NOUVEL MENU</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open('Menus/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                <label>NOM_BOUTON</label>
                                    <input class="form-control form-control-sm" name="namebouton"
                                           type="text"
                                           placeholder="bouton" required autocomplete="off">
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>URL</label>
                                    <input class="form-control form-control-sm" name="adressebouton"
                                           type="text"
                                           placeholder="url" required autocomplete="off">
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
                            <th>NOM BOUTON</th>
                            <th>URL</th>
                            <th style="width: 10%;">ACTION</th>
                        </tr>

                        </thead>

                        <tbody>

                        <? foreach ($menus as $item): ?>

                            <tr>
                            
                                <td class="cell-detail">
                                    <?= $item->nom_attrib; ?>
                                </td>
                                <td class="cell-detail">
                                    <?= $item->url_attribut; ?>
                                </td>
                                <td class="actions">
                                    
                                    <a title="Modification <?= $item->id_menu; ?>" class="md-trigger"
                                       data-modal="edit-<?= $item->id_menu; ?>"
                                       href="#?menu/edit/<?= $item->nom_attrib; ?>">&nbsp;<span
                                                class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="edit-<?= $item->id_menu; ?>" style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR LE BOUTON <?= $item->nom_attrib; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span></button>
                                            </div>
                                            
                                            <?= form_open('Menus/edit_/' . $this->session->company->ekey.'/' . $item->id_menu, array('class' => 'modal-body form')); ?>
                                            <div class="row">

                                                <div class="form-group col-sm-4">
                                                    <label>NOM_BOUTON</label>
                                                        <input class="form-control form-control-sm" name="namebouton"
                                                            type="text" value="<?= $item->nom_attrib; ?>"
                                                            placeholder="<?= $item->nom_attrib; ?>" required autocomplete="off">
                                                </div>

                                                <div class="form-group col-sm-4">
                                                    <label>URL</label>
                                                    <input class="form-control form-control-sm" name="adressebouton"
                                                        type="text" value="<?= $item->url_attribut; ?>"
                                                        placeholder="<?= $item->url_attribut; ?>" required autocomplete="off">
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
    <div class="col-lg-10 offset-lg-1">
        <div class="card">

            <div class="card-header card-header-divider">
                <?= $this->session->company->nom_entreprise; ?>

                <div class="tools">
                    <button class="btn btn-rounded btn-space btn-success md-trigger"
                            data-modal="form-add">
                        <i class="fas fa-left fas fa-bus"></i>
                        AJOUTER UN NOUVEL MENU
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-add" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN NOUVEL MENU</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open('Menus/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                <label>NOM_BOUTON</label>
                                    <input class="form-control form-control-sm" name="namebouton"
                                           type="text"
                                           placeholder="bouton" required autocomplete="off">
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>URL</label>
                                    <input class="form-control form-control-sm" name="adressebouton"
                                           type="text"
                                           placeholder="url" required autocomplete="off">
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
                <h2>AUCUN MENU TROUVE</h2>
            </div>

        </div>
    </div>
    <? endif; ?>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_menu/view.php-->