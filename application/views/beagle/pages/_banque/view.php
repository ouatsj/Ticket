<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($banques)): ?>
                <div class="card card-table">

                    <div class="card-header card-header-divider">
                        <?= $this->session->company->nom_entreprise; ?>
                        <div class="tools">
                            <button class="btn btn-rounded btn-space btn-success md-trigger"
                                    data-modal="form-banque">
                                <i class="fas fa-edit"></i>
                                AJOUTER UNE NOUVELLE BANQUE
                            </button>
                        </div>

                        <!-- modal for adding a new banque-->
                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="form-banque" style="perspective: 1300px;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE BANQUE</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open('Banques/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">

                                <!-- entreprise-->
                                <div class="form-group col-sm-4">
                                    <label>ENTREPRISE</label>
                                        <select class="form-control form-control-sm" name="idententrep">
                                            <? foreach ($entreprises as $entrep): ?>
                                                <option value="<?= $entrep->id_entreprise; ?>">
                                                    <?= "{$entrep->nom_entreprise}"; ?>
                                                    </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="compagn">
                                        <? foreach ($compagnies as $compagie): ?>
                                            <option value="<?= $compagie->cle_compagnie; ?>">
                                                <?= "{$compagie->nom_compagnie}"; ?></option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <!-- nom -->
                                <div class="form-group col-sm-4">
                                    <label>NOM</label>
                                    <input class="form-control form-control-sm" name="nombanque"
                                        type="text" autocomplete="off" required>
                                </div>
                                <!-- code -->
                                <div class="form-group col-sm-4">
                                    <label>CODE_BANQUE</label>
                                    <input class="form-control form-control-sm" name="codebanq"
                                        type="text" autocomplete="off">
                                </div>
                                <!-- code agence -->
                                <div class="form-group col-sm-4">
                                    <label>CODE_AGENCE</label>
                                    <input class="form-control form-control-sm" name="codeagent"
                                        type="text" autocomplete="off"
                                        placeholder="">
                                </div>
                                <!-- Numero -->
                                <div class="form-group col-sm-4">
                                    <label>NUM_COMPTE</label>
                                    <input class="form-control form-control-sm" name="numerocompte"
                                        type="text" autocomplete="off" required>
                                </div>
                                
                                <!-- Cle -->
                                <div class="form-group col-sm-4">
                                    <label>CLE_BANQUE</label>
                                    <input class="form-control form-control-sm" name="cle"
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
                            <th>NOM</th>
                            <th>CODE_BANQUE</th>
                            <th>CODE_AGENCE</th>
                            <th>NUMERO_BANQUE</th>
                            <th>CLE_BANQUE</th>
                            <th style="width: 10%;">ACTION</th>
                        </tr>

                        </thead>

                        <tbody>

                        <!-- Les bus non encore affectes -->
                        <? foreach ($banques as $item): ?>

                            <tr>
                                <td>
                                    <?= $item->nom_bank; ?> / 
                                    <?= $item->nom_compagnie; ?>
                                </td>
                                <td>
                                    <?= $item->code_bank; ?>
                                </td>
                                <td>
                                    <?= $item->code_agence; ?>
                                </td>
                                <td>
                                    <?= $item->num_compte; ?>
                                </td>
                                <td>
                                    <?= $item->cle_RIB; ?>
                                </td>
                                <td>
                                    <a title="Modification <?= $item->id_bank; ?>" class="md-trigger"
                                       data-modal="edit-<?= $item->id_bank; ?>"
                                       href="#">&nbsp;<span
                                    class="fas fa-edit text-warning"></span>
                                    </a>&nbsp;
                                    <!-- modal for editing a banque-->
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="edit-<?= $item->id_bank; ?>" style="perspective: none;">

                                        <div class="modal-content">

                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR : <?= $item->nom_bank; ?></h3>
                                                <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span></button>
                                            </div>
                                            
                                            <?= form_open("Banques/editbank/{$this->session->company->ekey}/{$item->id_bank}", 
                                            array('class' => 'modal-body form')); ?>
                                            <div class="row">

                                                <!-- nom -->
                                                <!-- entreprise-->
                                                <div class="form-group col-sm-4">
                                                    <label>ENTREPRISE</label>
                                                    <select class="form-control form-control-sm" name="_idententrep">
                                                    <option value="<?= $item->id_entrepriseb; ?>"><?= $item->nom_entreprise; ?></option>
                                                    <? foreach ($entreprises as $entrep): ?>
                                                            <option value="<?= $entrep->id_entreprise; ?>">
                                                                <?= "{$entrep->nom_entreprise}"; ?>
                                                                </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>COMPAGNIE</label>
                                                    <select class="form-control form-control-sm" name="compagn">
                                                        <option value="<?= $item->idcompagn; ?>"><?= $item->nom_compagnie; ?></option> 
                                                        <? foreach ($compagnies as $compagie): ?>
                                                            <option value="<?= $compagie->cle_compagnie; ?>">
                                                                <?= "{$compagie->nom_compagnie}"; ?></option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>NOM</label>
                                                    <input class="form-control form-control-sm" name="_nom"
                                                    type="text" autocomplete="off" value="<?= $item->nom_bank; ?>"
                                                    placeholder="<?= $item->nom_bank; ?>">
                                                </div>
                                                <!-- code -->
                                                <div class="form-group col-sm-4">
                                                    <label>CODE_BANQUE</label>
                                                    <input class="form-control form-control-sm" name="_codebanq"
                                                        type="text" autocomplete="off" value="<?= $item->code_bank; ?>"
                                                        placeholder="<?= $item->code_bank; ?>">
                                                </div>
                                                <!-- code agence -->
                                                <div class="form-group col-sm-4">
                                                    <label>CODE_AGENCE</label>
                                                    <input class="form-control form-control-sm" name="_code"
                                                    type="text" autocomplete="off"
                                                    value="<?= $item->code_agence; ?>"
                                                    placeholder="<?= $item->code_agence; ?>">
                                                </div>
                                                <!-- Numero -->
                                                <div class="form-group col-sm-4">
                                                    <label>NUM_COMPTE</label>
                                                    <input class="form-control form-control-sm" name="numeros"
                                                    type="text" autocomplete="off" value="<?= $item->num_compte; ?>"
                                                    placeholder="<?= $item->num_compte; ?>">
                                                </div>
                                                
                                                <!-- Cle -->
                                                <div class="form-group col-sm-4">
                                                    <label>CLE_BANQUE</label>
                                                    <input class="form-control form-control-sm" name="_clef"
                                                        type="text" autocomplete="off" value="<?= $item->cle_RIB; ?>"
                                                        placeholder="<?= $item->cle_RIB; ?>">
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
    <!--auncun banque dans la bd-->
    <div class="col-lg-10 offset-lg-1">
        <div class="card">

            <div class="card-header card-header-divider">
                <?= $this->session->company->nom_entreprise; ?>

                <div class="tools">
                    <button class="btn btn-rounded btn-space btn-success md-trigger"
                            data-modal="form-add-banque">
                        <i class="fas fa-edit"></i>
                        AJOUTER UNE NOUVELLE BANQUE
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="form-add-banque" style="perspective: 1300px;">

                    <div class="modal-content">

                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">AJOUTER UNE NOUVELLE BANQUE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                        </div>
                        
                        <?= form_open('Banques/add/' . $this->session->company->ekey
                            . '/', array('class' => 'modal-body form')); ?>

                        <div class="row">
                            <!-- entreprise-->
                            <div class="form-group col-sm-4">
                            <label>ENTREPRISE</label>
                                <select class="form-control form-control-sm" name="idententrep">
                                    <? foreach ($entreprises as $entrep): ?>
                                        <option value="<?= $entrep->id_entreprise; ?>">
                                            <?= "{$entrep->nom_entreprise}"; ?>
                                            </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                            <label>COMPAGNIE</label>
                                <select class="form-control form-control-sm" name="compagn">
                                    <? foreach ($compagnies as $compagie): ?>
                                        <option value="<?= $compagie->cle_compagnie; ?>">
                                            <?= "{$compagie->nom_compagnie}"; ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <!-- nom -->
                            <div class="form-group col-sm-4">
                                <label>NOM</label>
                                <input class="form-control form-control-sm" name="nombanque"
                                    type="text" autocomplete="off" required>
                            </div>
                            <!-- code -->
                            <div class="form-group col-sm-4">
                                <label>CODE_BANQUE</label>
                                <input class="form-control form-control-sm" name="codebanq"
                                    type="text" autocomplete="off">
                            </div>
                            <!-- code agence -->
                            <div class="form-group col-sm-4">
                                <label>CODE_AGENCE</label>
                                <input class="form-control form-control-sm" name="codeagent"
                                    type="text" autocomplete="off"
                                    placeholder="">
                            </div>
                            <!-- Numero -->
                            <div class="form-group col-sm-4">
                                <label>NUM_COMPTE</label>
                                <input class="form-control form-control-sm" name="numerocompte"
                                    type="text" autocomplete="off" required>
                            </div>
                            
                            <!-- Cle -->
                            <div class="form-group col-sm-4">
                                <label>CLE_BANQUE</label>
                                <input class="form-control form-control-sm" name="cle"
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
                <h2>AUCUNE BANQUE TROUVEE</h2>
            </div>

        </div>
    </div>
    <? endif; ?>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_banque/view.php-->