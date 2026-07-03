<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($quartiers)): ?>
            <div class="card card-table">

                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-success md-trigger"
                                data-modal="form-new-quart">
                            <i class="fas fa-left fas fa-edit"></i>
                            AJOUTER UN NOUVEL QUARTIER
                        </button>
                    </div>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-new-quart" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN NOUVEL QUARTIER</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open('Villes/addquart/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>VILLE</label>
                                    <select class="form-control form-control-sm" name="paysid">
                                    <option value=""></option>
                                        <? foreach ($villes as $idem): ?>
                                            <option value="<?= $idem->id_ville; ?>">
                                                <?= "{$idem->nom_ville}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>                                
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>QUARTIER</label>
                                    <input class="form-control form-control-sm" name="quartier"
                                           type="text"
                                           placeholder="zad" required autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>CODE QUARTIER</label>
                                    <input class="form-control form-control-sm" name="codquartier"
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
                            <th>VILLE</th>
                            <th>QUARTIER</th>
                            <th>CODE_QUARTIER</th>
                            <th style="width: 10%;">ACTION</th>
                        </tr>

                        </thead>

                        <tbody>

                        <? foreach ($quartiers as $item): ?>

                            <tr>
                            
                                <td class="cell-detail">
                                    <?= "{$item->nom_ville}"; ?>
                                </td>
                                <td class="cell-detail">
                                    <?= "{$item->nom_quartier}"; ?>
                                </td>
                                <td class="cell-detail">
                                    <?= "{$item->code_quart}"; ?>
                                </td>
                                <td class="actions">
                                
                                    <a title="Modification <?= $item->id_quartier; ?>" class="md-trigger"
                                       data-modal="editq-<?= $item->id_quartier; ?>"
                                       href="#?ville/editq/<?= $item->nom_quartier; ?>">&nbsp;<span
                                                class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                    <div class="modal-container colored-header colored-header-success custom-width  modal-effect-7"
                                         id="editq-<?= $item->id_quartier; ?>" style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_quartier; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span></button>
                                            </div>
                                            
                                            <?= form_open('Villes/editqua_/' . $this->session->company->ekey. '/' . $item->id_quartier, array('class' => 'modal-body form')); ?>
                                            <div class="row">

                                                <div class="form-group col-sm-4">
                                                    <label>VILLE</label>
                                                    <select class="form-control form-control-sm" name="idville">
                                                    <option value="<?= $item->id_ville_qua; ?>"><?= "{$item->nom_ville}"; ?></option>
                                                        <? foreach ($villes as $idem): ?>
                                                            <option value="<?= $idem->id_ville; ?>">
                                                                <?= "{$idem->nom_ville}"; ?>
                                                            </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>

                                            
                                                <div class="form-group col-sm-4">
                                                    <label>QUARTIER</label>
                                                    <input class="form-control form-control-sm" name="nomquartier"
                                                           type="text" autocomplete="off"
                                                           value="<?= $item->nom_quartier; ?>"
                                                           placeholder="<?= $item->nom_quartier; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>CODE QUARTIER</label>
                                                    <input class="form-control form-control-sm" name="codquartier"
                                                           type="text"required autocomplete="off" value="<?= $item->code_quart; ?>"
                                                           placeholder="<?= $item->code_quart; ?>">
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
                        AJOUTER UN NOUVEL QUARTIER
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-add" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN NOUVEL QUARTIER</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            
                            <?= form_open('Villes/addquart/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <div class="form-group col-sm-4">
                                    <label>VILLE</label>
                                    <select class="form-control form-control-sm" name="paysid">
                                    <option value=""></option>
                                        <? foreach ($villes as $idem): ?>
                                            <option value="<?= $idem->id_ville; ?>">
                                                <?= "{$idem->nom_ville}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>                                
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>QUARTIER</label>
                                    <input class="form-control form-control-sm" name="quartier"
                                           type="text"
                                           placeholder="zad" required autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>CODE QUARTIER</label>
                                    <input class="form-control form-control-sm" name="codquartier"
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