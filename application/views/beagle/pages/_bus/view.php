<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($buses)): ?>
            <!--Il existe des bus non encore affectes-->
            <div class="card card-table">

                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-success md-trigger"
                                data-modal="form-new-bus">
                            <i class="fas fa-left fas fa-bus"></i>
                            AJOUTER UN NOUVEAU BUS
                        </button>
                    </div>

                    <!-- modal for adding a new bus-->
                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-new-bus" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN NOUVEAU BUS</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open('Bus/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>IMMATRICULATION</label>
                                    <input class="form-control form-control-sm" name="_immatriculation" type="text" placeholder="Immatriculation du bus" autocomplete="off" required>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>CATEGORIE</label>
                                    <select class="form-control form-control-sm" name="_categorie">
                                        <option value=""></option>
                                            <? foreach ($categoriebus as $categories): ?>
                                                <option value="<?= $categories->categorie; ?>">
                                                    <?= "{$categories->categorie}"; ?></option>
                                            <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagn">
                                        <option value=""></option>
                                        <? foreach ($compagnies as $compagie): ?>
                                            <option value="<?= $compagie->cle_compagnie; ?>">
                                                <?= "{$compagie->nom_compagnie}"; ?></option>
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

                </div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>COMPAGNIE</th>
                            <th>CATEGORIE BUS</th>
                            <th>IMMATRICULATION</th>
                            <th>NOMBRE PLACES</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>

                        </thead>

                        <tbody>

                        <!-- Les bus non encore affectes -->
                        <? foreach ($buses as $bus): ?>

                            <tr>
                                    <td><?=$bus->nom_compagnie;?></td>
                                    <td class="cell-detail">
                                    <?= $bus->categoriebus; ?>
                                    
                                </td>
                                <td><?= $bus->immatriculation;?></td>
                                <td class="cell-detail">
                                    <?= $bus->nbr_place; ?>
                
                                </td>
                                <td class="actions">
                                    
                                    <a title="Modification<?= urldecode($bus->immatriculation); ?>" class="md-trigger"
                                       data-modal="buse-<?= urldecode($bus->immatriculation); ?>"
                                       href="#?bus/edit/<?= urldecode($bus->immatriculation); ?>">&nbsp;<span
                                                class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="buse-<?= urldecode($bus->immatriculation); ?>" style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR BUS: <?= urldecode($bus->immatriculation); ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            
                                            <?= form_open('Bus/edit_/' . $this->session->company->ekey. '/' . urldecode($bus->immatriculation), array('class' => 'modal-body form')); ?>
                                            <input type="hidden" name="nombre" value="<?= $bus->nbr_place; ?>">

                                            <div class="row">

                                                <div class="form-group col-sm-4">
                                                    <label>IMMATRICULATION</label>
                                                    <input class="form-control form-control-sm" name="_immatriculation"
                                                           type="text" autocomplete="off"
                                                           value="<?= $bus->immatriculation; ?>"
                                                           placeholder="<?= $bus->immatriculation; ?>">
                                                </div>

                                                <div class="form-group col-sm-4">
                                                    <label>CATEGORIE</label>
                                                    <select class="form-control form-control-sm" name="categorie">
                                                        <option value="<?= $bus->categorie; ?>"><?= $bus->categorie; ?></option>
                                                        <? foreach ($categoriebus as $categories): ?>
                                                            <option value="<?= $categories->categorie; ?>">
                                                                <?= "{$categories->categorie}"; ?></option>
                                                        <? endforeach; ?>
                                                    </select>
                                                    
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>COMPAGNIE</label>
                                            
                                                    <select class="form-control form-control-sm" name="compagn">
                                                    <option value="<?= $bus->id_compagniebus; ?>"><?= $bus->nom_compagnie; ?></option>
                                                        <? foreach ($compagnies as $compagie): ?>
                                                            <option value="<?= $compagie->cle_compagnie; ?>">
                                                                <?= "{$compagie->nom_compagnie}"; ?></option>
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
                            data-modal="form-add-bus-menu">
                        <i class="fas fa-left fas fa-bus"></i>
                        AJOUTER UN NOUVEAU BUS
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="form-add-bus-menu" style="perspective: 1300px;">

                    <div class="modal-content">

                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">AJOUTER UN NOUVEAU BUS</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                        </div>
                        
                        <?= form_open('Bus/add/' . $this->session->company->ekey
                            . '/', array('class' => 'modal-body form')); ?>

                        <div class="row">

                            <div class="form-group col-sm-4">
                                <label>IMMATRICULATION</label>
                                <input class="form-control form-control-sm" name="_immatriculation" type="text" placeholder="Immatriculation du bus" autocomplete="off" required>
                            </div>

                            <div class="form-group col-sm-4">
                                <label>CATEGORIE</label>
                                <select class="form-control form-control-sm" name="_categorie">
                                    <option value=""></option>
                                        <? foreach ($categoriebus as $categories): ?>
                                            <option value="<?= $categories->categorie; ?>">
                                                <?= "{$categories->categorie}"; ?></option>
                                        <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                <select class="form-control form-control-sm" name="_compagn">
                                <option value=""></option>
                                    <? foreach ($compagnies as $compagie): ?>
                                        <option value="<?= $compagie->cle_compagnie; ?>">
                                            <?= "{$compagie->nom_compagnie}"; ?></option>
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

            </div>

            <div class="card-body text-center">
                <h2>AUCUN BUS TROUVE</h2>
            </div>

        </div>
    </div>
    <? endif; ?>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_bus/view.php-->