<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
<? if (!empty($sousgares)) : ?>
        <a href="<?= site_url("gares/{$this->session->company->ekey}"."/gTv/"."{$bus_stop->code_gaexp}"."/prog/". $conex->roleattribut.'/'.$gare_stop->idsousgare .'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    <div class="col-lg-12">
            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>NOM GARE</th>
                            <th>NOM SOUS GARE</th>
                            <th>CONTACT</th>
                            <th>ACTION</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($sousgares as $item): ?>

                            <tr>
                                <td><?= $item->nom_gaep; ?></td>
                                <td><?= $item->nomsousgare; ?></td>
                                <td><?= $item->contactsousgare; ?></td>
                                <td class="actions">
                                    <a href="<?= "#?{$item->idsousgare}&{$item->idsousgare}"; ?>"
                                       class="md-trigger" data-modal="edit-<?= $item->idsousgare; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="edit-<?= $item->idsousgare; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->nomsousgare;?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Programmes/modifsousgare/{$this->session->company->ekey}/{$item->gareprinceid}/{$item->idsousgare}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                <div class="form-group col-sm-4">
                                                <label>NOM SOUS GARE</label>
                                                <input class="form-control form-control-sm"
                                                    type="text"
                                                    name="_nomsousgare" autocomplete="off" value="<?= $item->nomsousgare;?>" placeholder="<?= $item->nomsousgare;?>">
                                                </div>
                                                
                                                <div class="form-group col-sm-4">
                                                    <label>CONTACT</label>
                                                    <input class="form-control form-control-sm" name="contact" type="text" autocomplete="off" value="<?= $item->contactsousgare;?>" placeholder="<?= $item->contactsousgare;?>">

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
                        
                        <?= form_open('Gares/adddepart/' . $this->session->company->ekey, array('class' => 'modal-body form'));?>
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
    <!--End of file: indexsousgare.php-->
    <!--File location: application/views/beagle/pages/_gare/indexsousgare.php-->