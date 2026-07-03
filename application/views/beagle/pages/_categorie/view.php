<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($categories)): ?>
            <div class="card card-table">

                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-success md-trigger"
                                data-modal="form-new-cat">
                            <i class="fas fa-left fas fa-bus"></i>
                            AJOUTER UNE NOUVELLLE CATEGORIE
                        </button>
                    </div>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-new-cat" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE CATEGORIE</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open('Categories/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>CATEGORIE</label>
                                    <input class="form-control form-control-sm" name="_categorie"
                                           type="text" placeholder="categorie des bus" autocomplete="off" required>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>NOMBRE DE PLACES</label>
                                    <input class="form-control form-control-sm" name="_nbr_place"
                                           type="text"
                                           placeholder="Nombre de place du bus" required autocomplete="off">
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>NOMBRE DE COLONNE</label>
                                    <input class="form-control form-control-sm" name="_nbr_colonne"
                                           type="text" autocomplete="off">
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
                            <th>CATEGORIE</th>
                            <th>NOMBRE DE PLACES</th>
                            <th>NOMBRE COLONNE</th>
                            <th style="width: 10%;">ACTION</th>
                        </tr>

                        </thead>

                        <tbody>

                        <? foreach ($categories as $categorie): ?>

                            <tr>
                                <td class="cell-detail">
                                    <?= "<strong>{$categorie->categorie}</strong>"; ?>
                                </td>
                                <td class="cell-detail">
                                    <?= "{$categorie->nbr_place}"; ?>
                                </td>
                                <td class="cell-detail">
                                    <?= "{$categorie->nbr_colonne}"; ?>
                                </td>
                                <td class="actions">
                                    
                                    <a title="Modification <?= $categorie->categorie; ?>" class="md-trigger"
                                       data-modal="ecat-<?= $categorie->categorie; ?>"
                                       href="#?categories/edit/<?= $categorie->categorie; ?>">&nbsp;<span
                                                class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="ecat-<?= $categorie->categorie; ?>" style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $categorie->categorie; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span></button>
                                            </div>
                                            
                                            <?= form_open('Categories/edit_/' . $this->session->company->ekey.'/'. $categorie->categorie, array('class' => 'modal-body form')); ?>
                                            <div class="row">
                                            <input type="hidden" name="nombre" value="<?= $categorie->nbr_place; ?>">
                                            <input type="hidden" name="_catego" value="<?//= $categorie->idcat_bus; ?>">
                                                <div class="form-group col-sm-4">
                                                    <label>CATEGORIE</label>
                                                    <input class="form-control form-control-sm" name="_categ"
                                                           type="text" autocomplete="off"
                                                           value="<?= $categorie->categorie; ?>"
                                                           placeholder="<?= $categorie->categorie; ?>">
                                                </div>

                                                <div class="form-group col-sm-4">
                                                    <label>NOMBRE PLACE</label>
                                                    <input class="form-control form-control-sm" name="_nbr_place"
                                                           type="text" autocomplete="off"
                                                           value="<?= $categorie->nbr_place; ?>"
                                                           placeholder="<?= $categorie->nbr_place; ?>">
                                                </div>

                                                <div class="form-group col-sm-4">
                                                    <label>NOMBRE COLONNE</label>
                                                    <input class="form-control form-control-sm" name="_nombre_colonne"
                                                           type="text" autocomplete="off"
                                                           value="<?= $categorie->nbr_colonne; ?>"
                                                           placeholder="<?= $categorie->nbr_colonne; ?>">
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
                            data-modal="form-add-categ-menu">
                        <i class="fas fa-left fas fa-bus"></i>
                        AJOUTER UNE NOUVELLE CATEGORIE
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="form-add-categ-menu" style="perspective: 1300px;">

                     <div class="modal-content">

                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">AJOUTER UNE NOUVELLE CATEGORIE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>

                        <?= form_open('Categories/add/' . $this->session->company->ekey
                            . '/', array('class' => 'modal-body form')); ?>

                        <div class="row">

                            <div class="form-group col-sm-4">
                                <label>CATEGORIE</label>
                                <input class="form-control form-control-sm" name="_categorie"
                                    type="text" placeholder="categorie des bus" autocomplete="off" required>
                            </div>

                            <div class="form-group col-sm-4">
                                <label>NOMBRE DE PLACES</label>
                                <input class="form-control form-control-sm" name="_nbr_place"
                                    type="text"
                                    placeholder="Nombre de place du bus" required autocomplete="off">
                            </div>

                            <div class="form-group col-sm-4">
                                <label>NOMBRE DE COLONNE</label>
                                <input class="form-control form-control-sm" name="_nbr_colonne"
                                    type="text" autocomplete="off">
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
                <h2>AUCUNE CATEGORIE TROUVEE</h2>
            </div>

        </div>
    </div>
    <? endif; ?>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_categorie/view.php-->