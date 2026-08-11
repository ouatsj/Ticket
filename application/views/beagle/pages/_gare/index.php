<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    
    <? if (!empty($busarrive_stop)) : ?>

        <div class="col-lg-12">
            <div class="card">
                <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'): ?>
                    <div class="card-header">
                    
                        <div class="tools">
                            <button class="btn btn-space btn-info md-trigger" data-modal="add-new-gare">
                                <span class="icon mdi mdi-plus-1 text-white"></span>
                            </button>
                        </div>
                    
                    </div>
                <?endif;?>
                <div class="card-body"></div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="add-new-gare" style="perspective: 1300px;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">NOUVELLE GARE DE DEPART</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true">
                                <span class="mdi mdi-close text-white"></span></button>
                        </div>
                        <?= form_open('Gares/adddepart/' . $this->session->company->ekey, array('class' => 'modal-body form')) ?>
                        <div class="form-group col-sm-4">
                            <label>GARE</label>
                            <select name="gareselect" class="form-control form-control-sm">
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
                                <input class="form-control form-control-sm" name="_contact" type="text" autocomplete="off">
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
        
        <?
        $departs_par_compagnie = !empty($departs_par_compagnie) ? $departs_par_compagnie : array();
        foreach ($departs_par_compagnie as $groupe):
            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
            $nb_gares = !empty($groupe['gares']) ? count($groupe['gares']) : 0;
        ?>
            <div class="col-lg-12">
                <div class="card card-border">
                    <div class="card-header card-header-divider">
                        COMPAGNIE <?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?>
                        <span class="text-muted">&nbsp;(<?= (int) $nb_gares; ?> gare<?= $nb_gares > 1 ? 's' : ''; ?>)</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <? foreach ($groupe['gares'] as $item): ?>
                                <div class="col-lg-3">
                                    <div class="card card-border card-full">
                                        <div class="card-header card-header-divider"><?= $item->nom_gaep; ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <a href="<?= "#?{$item->code_gaexp}&name={$item->nom_gaep}"; ?>"
                                               class="md-trigger" data-modal="edit-gare-<?= $item->code_gaexp; ?>">
                                                <span class="fas fa-edit text-white"></span>
                                            </a>&nbsp;

                                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                                 id="edit-gare-<?= $item->code_gaexp; ?>" style="">
                                                <div class="modal-content">
                                                    <div class="modal-header modal-header-colored">
                                                        <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_gaep; ?></h3>
                                                        <button class="close modal-close" type="button"
                                                            data-dismiss="modal" aria-hidden="true"><span
                                                        class="mdi mdi-close text-white"></span></button>
                                                    </div>

                                                    <?= form_open('Gares/editexp_/' . $this->session->company->ekey
                                                        . '/' . $item->code_gaexp, array('class' => 'modal-body form')); ?>

                                                    <div class="form-group col-sm-4">
                                                        <label>GARE</label>
                                                        <select name="gareselect" class="form-control form-control-sm">
                                                            <option value="<?= $item->idengare; ?>"><?= $item->garenom; ?></option>
                                                            <? foreach ($gares as $gnom): ?>
                                                                <option value="<?= $gnom->idengare; ?>">
                                                                    <?= $gnom->garenom; ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="row no-margin-y">
                                                        <div class="form-group">
                                                            <label>NOM GARE </label>
                                                            <input class="form-control form-control-sm" name="_garenom"
                                                                value="<?= $item->nom_gaep; ?>"
                                                                type="text" autocomplete="off" placeholder="<?= $item->nom_gaep; ?>">
                                                        </div>

                                                        <div class="form-group col-sm-4">
                                                            <label>LOCALISATION DE LA GARE</label>
                                                            <select class="form-control form-control-sm" name="_glocalise">
                                                                <option value="<?= $item->id_villegd; ?>"><?= $item->nom_ville; ?></option>
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
                                                                <option value="<?= $item->id_compagd; ?>"><?= $item->nom_compagnie; ?></option>
                                                                <? foreach ($compagnies as $compagnie): ?>
                                                                    <option value="<?= $compagnie->cle_compagnie; ?>">
                                                                        <?= $compagnie->nom_compagnie; ?>
                                                                    </option>
                                                                <? endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col-sm-4">
                                                            <label>CONTACT</label>
                                                            <input class="form-control form-control-sm" name="_contact" type="text"
                                                            value="<?= $item->contactgdepart; ?>" placeholder="<?= $item->contactgdepart;?>" autocomplete="off">
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
                                            <p>code:<?= $item->code_gaexp; ?></p>
                                            <p>ville:<?= $item->nom_ville; ?></p>
                                            <p>contact:<?= $item->contactgdepart;?></p>
                                        </div>
                                    </div>
                                </div>
                            <? endforeach; ?>
                        </div>
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
                            AJOUTER UNE GARE DE DEPART
                        </button>
                    </p>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-add-gare" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE GARE DE DEPART</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open('Gares/adddepart/' . $this->session->company->ekey, array('class' => 'modal-body form')) ?>
                        <div class="form-group col-sm-4">
                            <label>GARE</label>
                            <select name="gareselect" class="form-control form-control-sm">
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
<!--End of file: index.php-->
<!--File location: application/views/beagle/pages/_gare/index.php-->