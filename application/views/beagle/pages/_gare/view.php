<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    
    <? if (!empty($bus_stop)) : ?>

        <div class="col-lg-12">
            <div class="card">

                <div class="card-header">

                    <div class="tools">
                        <button class="btn btn-space btn-info md-trigger" data-modal="add-new-gare">
                            <span class="icon mdi mdi-plus-1 text-white"></span>
                        </button>
                    </div>

                </div>

                <div class="card-body"></div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="add-new-gare" style="perspective: 1300px;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">NOUVELLE GARE D'ARRIVEE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true">
                                <span class="mdi mdi-close text-white"></span></button>
                        </div>
                        <?= form_open('Gares/add/' . $this->session->company->ekey, array('class' => 'modal-body form'));?>
                        <div class="form-group col-sm-4">
                            <label>GARE</label>
                            <select name="gareselected" class="form-control form-control-sm">
                            <option value=""></option>
                                <? foreach ($gares as $gnom): ?>
                                        <option value="<?= $gnom->idengare; ?>">
                                        <?= $gnom->garenom; ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>NOM GARE</label>
                            <input class="form-control form-control-sm"
                                type="text" name="nomgare"
                                   placeholder="La designation de la gare" autocomplete="off" required>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-4">
                                <label>LOCALISATION DE LA GARE</label>
                                <select name="villegare" class="form-control form-control-sm">
                                <option value=""></option>
                                    <? if (!empty($villes)): ?>
                                        <? foreach ($villes as $local): ?>
                                            <option value="<?= $local->id_ville; ?>">
                                                <?= $local->nom_ville; ?>
                                            </option>
                                        <? endforeach; ?>
                                    <? else: ?>
                                    <? endif; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                <select name="compgare" class="form-control form-control-sm">
                                <option value=""></option>
                                    <? if (!empty($compagnies)): ?>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= $compagnie->nom_compagnie; ?>
                                            </option>
                                        <? endforeach; ?>
                                    <? else: ?>
                                    <? endif; ?>
                                </select>
                            </div>
                            <!-- CONTACT -->
                            <div class="form-group col-sm-4">
                                <label>CONTACT</label>
                                <input class="form-control form-control-sm" name="contact" type="text"
                                    placeholder="" autocomplete="off">
                            </div>
                            
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER
                            </button>
                            <button class="btn btn-success md_trigger" type="submit" data-dismiss="modal">OK
                            </button>
                        </div>
                        
                        <?= form_close(); ?>

                    </div>

                </div>

            </div>
        </div>
        
        <? foreach ($bus_stop as $item): ?>

            <div class="col-lg-3">

                <div class="card card-border card-full">

                    <div class="card-header card-header-divider"><?= $item->nom_gadest; ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                        
                            <a href="<?= "#?{$item->code_gadest}&name={$item->nom_gadest}"; ?>"
                               class="md-trigger" data-modal="edit-gare-<?= $item->code_gadest; ?>">
                                <span class="fas fa-edit text-white"></span>
                            </a>&nbsp;
                            <!-- edition -->
                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                 id="edit-gare-<?= $item->code_gadest; ?>" style="">

                                <div class="modal-content">

                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_gadest; ?></h3>
                                        <button class="close modal-close" type="button"
                                            data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                                    </div>
                                    
                                    <?= form_open('Gares/edit_/' . $this->session->company->ekey
                                        . '/' . $item->code_gadest, array('class' => 'modal-body form')); ?>

                                    <div class="form-group col-sm-4">
                                        <label>GARE</label>
                                        <select name="gareselected" class="form-control form-control-sm">
                                            <option value="<?= $item->idgaresdest; ?>"><?= $item->nom_gadest; ?></option>
                                            <? foreach ($gares as $gnom): ?>
                                                <option value="<?= $gnom->idengare; ?>">
                                                    <?= $gnom->garenom; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row no-margin-y">
                                        <!-- Designation de la gare-->
                                        <div class="form-group">
                                            <label>NOM GARE </label>
                                            <input class="form-control form-control-sm" name="_garenom"
                                                value="<?= $item->nom_gadest; ?>"
                                                type="text" autocomplete="off" placeholder="<?= $item->nom_gadest; ?>">
                                        </div>

                                        <!-- Localisation-->
                                    
                                        <div class="form-group col-sm-4">
                                            <label>LOCALISATION DE LA GARE</label>
                                            <select class="form-control form-control-sm" name="_glocalise">

                                                <option value="<?= $item->id_villega; ?>"><?= $item->nom_ville; ?></option>
                                                
                                                <? foreach ($villes as $local): ?>
                                                    <option value="<?= $local->id_ville; ?>">
                                                        <?= $local->nom_ville; ?>
                                                    </option>
                                                <? endforeach; ?>

                                            </select>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label>COMPAGNIE</label>
                                            <select name="_compagare" class="form-control form-control-sm">
                                                <option value="<?= $item->id_compaga; ?>"><?= $item->nom_compagnie; ?></option>
                                                    <? foreach ($compagnies as $compagnie): ?>
                                                        <option value="<?= $compagnie->cle_compagnie; ?>">
                                                            <?= $compagnie->nom_compagnie; ?>
                                                        </option>
                                                    <? endforeach; ?>
                                                
                                            </select>
                                        </div>
                                        <!-- CONTACT -->
                                        <div class="form-group col-sm-4">
                                            <label>CONTACT</label>
                                            <input class="form-control form-control-sm" name="_contact" type="text"
                                            value="<?= $item->contactgare; ?>" placeholder="<?= $item->contactgare;?>" autocomplete="off">
                                        </div>
                                       
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi text-dark mdi-undo"></i>&nbsp;ANNULER&nbsp;
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
                        <p>code:<?= $item->code_gadest; ?></p>
                        <p>ville:<?= $item->nom_ville; ?></p>
                        <p>contact:<?= $item->contactgare; ?></p>
                        <a href="#"
                           class="btn btn-block btn-rounded text-dark bg-white">
                            <span class="fas fa-eye"></span>
                            VOIR
                        </a>
                    </div>

                </div>

            </div>
        
        <? endforeach; ?>
    
    <? else: ?>

        <div class="col-lg-4 offset-lg-4">

            <div class="card">

                <div class="card-header card-header-divider"><?= $this->session->company->nom_entreprise; ?></div>

                <div class="card-body text-center text-capitalize">
                    <h2>AUCUNE GARE TROUVEE</h2>
                    <p>Vous pouvez en ajouter par ici
                        <button class="btn btn-rounded btn-space btn-success md-trigger" data-modal="form-add-gare">
                            <i class="icon icon-left mdi mdi-bus"></i>
                            AJOUTER UNE GARE D'ARRIVEE
                        </button>
                    </p>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-add-gare" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE GARE D'ARRIVEE</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open('Gares/add/' . $this->session->company->ekey, array('class' => 'modal-body form'));?>
                        <div class="form-group col-sm-4">
                            <label>GARE</label>
                            <select name="gareselected" class="form-control form-control-sm">
                            <option value=""></option>
                                    <? foreach ($gares as $gnom): ?>
                                        <option value="<?= $gnom->idengare; ?>">
                                            <?= $gnom->garenom; ?>
                                        </option>
                                    <? endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>NOM GARE</label>
                            <input class="form-control form-control-sm"
                                   type="text"
                                   name="nomgare"
                                   placeholder="La designation de la gare" autocomplete="off" required>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-4">
                                <label>LOCALISATION DE LA GARE</label>
                                <select name="villegare" class="form-control form-control-sm">
                                <option value=""></option>
                                    <? if (!empty($villes)): ?>
                                        <? foreach ($villes as $local): ?>
                                            <option value="<?= $local->id_ville; ?>">
                                                <?= $local->nom_ville; ?>
                                            </option>
                                        <? endforeach; ?>
                                    <? else: ?>
                                    <? endif; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                <select name="compgare" class="form-control form-control-sm">
                                <option value=""></option>
                                    <? if (!empty($compagnies)): ?>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= $compagnie->nom_compagnie; ?>
                                            </option>
                                        <? endforeach; ?>
                                    <? else: ?>
                                    <? endif; ?>
                                </select>
                            </div>
                            <!-- CONTACT -->
                            <div class="form-group col-sm-4">
                                <label>CONTACT</label>
                                <input class="form-control form-control-sm" name="contact" type="text" autocomplete="off">
                            </div>
                            <!--<div class="form-group col-sm-4">
                                <label>Type gare</label>
                                <select name="typegare" class="form-control form-control-sm">
                                            <option value="principale">Principale</option>
                                            <option value="secondaire">Secondaire</option>
                                </select>
                            </div>-->
                        </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success modal-close" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                </button>
                            </div>
                            
                            <?= form_close(); ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    
    <? endif; ?>

</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_gare/view.php-->