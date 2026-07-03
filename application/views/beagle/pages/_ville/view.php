<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($villes)): ?>
            <div class="card card-table">

                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-success md-trigger"
                                data-modal="form-new-ville">
                            <i class="fas fa-left fas fa-edit"></i>
                            AJOUTER UNE NOUVELLLE VILLE
                        </button>
                    </div>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-new-ville" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE VILLE</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open('Villes/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>PAYS</label>
                                    <select class="form-control form-control-sm" name="paysid">
                                    <option value=""></option>
                                        <? foreach ($pays as $idem): ?>
                                            <option value="<?= $idem->id_pays; ?>">
                                                <?= "{$idem->nom_pays}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>                                
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>VILLE</label>
                                    <input class="form-control form-control-sm" name="ville"
                                           type="text"
                                           placeholder="ville" required autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>CODE VILLE</label>
                                    <input class="form-control form-control-sm" name="codeville"
                                           type="text"required autocomplete="off">
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
                            <th>PAYS</th>
                            <th>VILLE</th>
                            <th>CODE_VILLE</th>
                            <th style="width: 10%;">ACTION</th>
                        </tr>

                        </thead>

                        <tbody>

                        <? foreach ($villes as $item): ?>

                            <tr>
                            
                                <td class="cell-detail">
                                    <?= "{$item->nom_pays}"; ?>
                                </td>
                                <td class="cell-detail">
                                    <?= "{$item->nom_ville}"; ?>
                                </td>
                                <td class="cell-detail">
                                    <?= "{$item->codville}"; ?>
                                </td>
                                <td class="actions">
                                
                                    <a title="Modification <?= $item->id_ville; ?>" class="md-trigger"
                                       data-modal="edit-<?= $item->id_ville; ?>"
                                       href="#?ville/edit/<?= $item->nom_ville; ?>">&nbsp;<span
                                                class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="edit-<?= $item->id_ville; ?>" style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_ville; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span></button>
                                            </div>
                                            
                                            <?= form_open('Villes/edit_/' . $this->session->company->ekey. '/' . $item->id_ville, array('class' => 'modal-body form')); ?>
                                            <div class="row">

                                                <div class="form-group col-sm-4">
                                                    <label>PAYS</label>
                                                    <select class="form-control form-control-sm" name="idpays">
                                                    <option value="<?= $item->id_pay; ?>"><?=$item->nom_pays; ?></option>
                                                        <? foreach ($pays as $idem): ?>
                                                            <option value="<?= $idem->id_pays; ?>">
                                                                <?= $idem->nom_pays; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>

                                            
                                                <div class="form-group col-sm-4">
                                                    <label>VILLE</label>
                                                    <input class="form-control form-control-sm" name="nomville"
                                                           type="text" autocomplete="off"
                                                           value="<?= $item->nom_ville; ?>"
                                                           placeholder="<?= $item->nom_ville; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>CODE VILLE</label>
                                                    <input class="form-control form-control-sm" name="codeville"
                                                           type="text"required value="<?= $item->codville; ?>" autocomplete="off" placeholder="<?= $item->codville; ?>">
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
                        AJOUTER UNE NOUVELLE VILLE
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-add" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE VILLE</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open('Villes/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>PAYS</label>
                                    <select class="form-control form-control-sm" name="paysid">
                                    <option value=""></option>
                                        <? foreach ($pays as $idem): ?>
                                            <option value="<?= $idem->id_pays; ?>">
                                                <?= "{$idem->nom_pays}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>                                
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>VILLE</label>
                                    <input class="form-control form-control-sm" name="ville"
                                           type="text"
                                           placeholder="ville" required autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>CODE VILLE</label>
                                    <input class="form-control form-control-sm" name="codeville"
                                           type="text"required autocomplete="off">
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
                <h2>AUCUNE VILLE TROUVEE</h2>
            </div>

        </div>
    </div>
    <? endif; ?>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_ville/view.php-->