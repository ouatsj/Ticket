<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    
    <? if (!empty($gares)) : ?>

        <div class="col-lg-12">
            <div class="card">
                <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'): ?>
                    <div class="card-header">
                    
                        <div class="tools">
                            <button class="btn btn-space btn-info md-trigger" data-modal="add-new-gar">
                                <span class="icon mdi mdi-plus-1 text-white"></span>
                            </button>
                        </div>
                    
                    </div>
                <?endif;?>
                <div class="card-body"></div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="add-new-gar" style="perspective: 1300px;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">NOUVELLE GARE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true">
                                <span class="mdi mdi-close text-white"></span></button>
                        </div>
                        <?= form_open('Gares/updeparts/' . $this->session->company->ekey, array('class' => 'modal-body form'));?>

                        <div class="form-group">
                            <label>NOM GARE</label>
                            <input class="form-control form-control-sm"
                                   type="text"
                                   name="_nomgare"
                                   placeholder="La designation de la gare" autocomplete="off" required>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-4">
                                <label>LOCALISATION DE LA GARE</label>
                                <select name="_villegare" class="form-control form-control-sm">
                                <option value=""></option>
                                        <? foreach ($villes as $local): ?>
                                            <option value="<?= $local->id_ville; ?>">
                                                <?= $local->nom_ville; ?>
                                            </option>
                                        <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                <select name="_compgare" class="form-control form-control-sm">
                                    <option value=""></option>
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
                                 autocomplete="off">
                            </div>
                            <!-- CODE -->
                            <div class="form-group col-sm-4">
                                <label>CODE</label>
                                <input class="form-control form-control-sm" name="codes" type="text"
                                value="" autocomplete="off">
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
        
        <? foreach ($gares as $item): ?>

            <div class="col-lg-3">

                <div class="card card-border card-full">

                    <div class="card-header card-header-divider"> AGENCE DE <?= $item->garenom; ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                        
                            <a href="<?= "#?{$item->idengare}&name={$item->garenom}"; ?>"
                               class="md-trigger" data-modal="edit-gar-<?= $item->idengare; ?>">
                                <span class="fas fa-edit text-white"></span>
                            </a>&nbsp;

                            
                            <!-- edition -->
                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                 id="edit-gar-<?= $item->idengare; ?>" style="">

                                <div class="modal-content">

                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION SUR <?= $item->garenom; ?></h3>
                                        <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span></button>
                                    </div>
                                    
                                    <?= form_open('Gares/editgid_/' . $this->session->company->ekey
                                        . '/' . $item->idengare, array('class' => 'modal-body form')); ?>
                                    <div class="row no-margin-y">
                                        <!-- Designation de la gare-->
                                        <div class="form-group">
                                            <label>NOM GARE </label>
                                            <input class="form-control form-control-sm" name="_garenom"
                                                value="<?= $item->garenom; ?>"
                                                type="text" autocomplete="off" placeholder="<?= $item->idengare; ?>">
                                        </div>

                                        <!-- Localisation-->
                                    
                                        <div class="form-group col-sm-4">
                                            <label>LOCALISATION DE LA GARE</label>
                                            <select class="form-control form-control-sm" name="_glocalise">
                                                <option value="<?= $item->villeid; ?>"><?= $item->nom_ville; ?></option>
                                                
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
                                                <option value="<?= $item->compagniegare; ?>"><?= $item->nom_compagnie; ?></option>
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
                                            <input class="form-control form-control-sm" name="contact" type="text"
                                            value="<?= $item->contactgares; ?>" placeholder="<?= $item->contactgares;?>" autocomplete="off">
                                        </div>

                                        <!-- CODE -->
                                        <div class="form-group col-sm-4">
                                            <label>CODE</label>
                                            <input class="form-control form-control-sm" name="codes" type="text"
                                            value="<?= $item->codegares; ?>" placeholder="<?= $item->codegares;?>" autocomplete="off">
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
                            <p>code:<?= $item->idengare; ?></p>
                            <p>ville:<?= $item->nom_ville; ?></p>
                            <p>contact:<?= $item->contactgares;?></p>
                            
                            <a href="#"
                               class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                               
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
                        <button class="btn btn-rounded btn-space btn-success md-trigger" data-modal="form-ad-gar">
                            <i class="icon icon-left mdi mdi-bus"></i>
                            AJOUTER UNE GARE
                        </button>
                    </p>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-ad-gar" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE GARE</h3>
                                <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open('Gares/updeparts/' . $this->session->company->ekey, array('class' => 'modal-body form')); ?>

                        <div class="form-group">
                            <label>NOM GARE</label>
                            <input class="form-control form-control-sm"
                                   type="text"
                                   name="_nomgare"
                                   placeholder="La designation de la gare" autocomplete="off" required>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-4">
                                <label>LOCALISATION DE LA GARE</label>
                                <select name="_villegare" class="form-control form-control-sm">
                                <option value=""></option>
                                        <? foreach ($villes as $local): ?>
                                            <option value="<?= $local->id_ville; ?>">
                                                <?= $local->nom_ville; ?>
                                            </option>
                                        <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                <select name="_compgare" class="form-control form-control-sm">
                                    <option value=""></option>
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
                                 autocomplete="off">
                            </div>
                            <!-- CODE -->
                            <div class="form-group col-sm-4">
                                <label>CODE</label>
                                <input class="form-control form-control-sm" name="codes" type="text"
                                value="" autocomplete="off">
                            </div>
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
<!--End of file: indview.php-->
<!--File location: application/views/beagle/pages/_gare/indview.php-->