<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($compagnie)): ?>
            <div class="card card-table">

                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-info md-trigger"
                                data-modal="plus-compagnie">
                            <i class="fas fa-user-astronaut text-danger"></i>
                            AJOUTER UNE NOUVELLE COMPAGNIE
                        </button>
                    </div>

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="plus-compagnie" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UNE NOUVELLE COMPAGNIE</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open('Compagnies/add/' . $this->session->company->ekey
                                . '/', array('class' => 'modal-body form')); ?>

                            <div class="row">
                                
                                    <!-- NOM compagnie -->
                                <div class="form-group col-sm-4">
                                    <label>NOM COMPAGNIE</label>
                                    <input class="form-control form-control-sm" name="compagnie_nom"
                                        type="text" placeholder="Nom compagnie" autocomplete="off" required>
                                </div>


                                <!-- logo -->
                                <div class="form-group col-sm-4">
                                    <label>LOGO</label>
                                    <input class="form-control form-control-sm" name="logocompagnie" type="file">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LOGO FOND</label>
                                    <input class="form-control form-control-sm" name="logofond" type="file">
                                </div>
                                <!-- slogan -->
                                <div class="form-group col-sm-4">
                                    <label>SLOGAN </label>
                                    <input class="form-control form-control-sm" name="slogan" type="text" autocomplete="off">
                                </div>
                                <!-- adresse -->
                                <div class="form-group col-sm-4">
                                    <label>ADRESSE</label>
                                    <input class="form-control form-control-sm" name="adresse" autocomplete="off" type="text"
                                            placeholder="adresse ...">
                                </div>
                                <!-- Contact -->
                                <div class="form-group col-sm-4">
                                    <label>CONTACT COMPAGNIE</label>
                                    <input class="form-control form-control-sm" name="contact" autocomplete="off" type="text"
                                            placeholder="contact ...">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>CONTACT NATIONAL</label>
                                    <input class="form-control form-control-sm" name="contactnation" autocomplete="off" type="text"
                                            placeholder="contact ...">
                                </div>
                                <!-- Contact secdond-->
                                <div class="form-group col-sm-4">
                                    <label>CONTACT INTERNATIONAL</label>
                                    <input class="form-control form-control-sm" name="contactsecd" autocomplete="off" type="text"
                                            placeholder="contact ...">
                                </div>
                                <div class="form-group col-sm-4">
                                <label>GROUPE/ENTREPRISE</label>
                                    <select class="form-control form-control-sm" name="prise">
                                        <? foreach ($entreprises as $entreprise): ?>
                                            <option value="<?= $entreprise->id_entreprise; ?>">
                                                <?= "{$entreprise->nom_entreprise}"; ?></option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>RCCM</label>
                                    <input class="form-control form-control-sm" name="numrccm"
                                        type="text" placeholder="RCCM" autocomplete="off" >
                                </div>
                                <!-- ifu -->
                                <div class="form-group col-sm-4">
                                    <label>IFU</label>
                                    <input class="form-control form-control-sm" name="numifu" type="text"
                                        placeholder="IFU" autocomplete="off" >
                                </div>
                                <!-- email -->
                                <div class="form-group col-sm-4">
                                    <label>EMAIL</label>
                                    <input class="form-control form-control-sm" name="adresseemail" autocomplete="off" type="text"
                                            placeholder="...">
                                </div>
                                <!-- adresse site -->
                                <div class="form-group col-sm-4">
                                    <label>SITE WEB</label>
                                    <input class="form-control form-control-sm" name="site_web" autocomplete="off" type="text"
                                            placeholder="...">
                                </div>
                                <div class="form-group col-sm-4">
                                <label>PAYS</label>
                                    <select class="form-control form-control-sm" name="payidentif">
                                    <option value=""></option>
                                        <? foreach ($paysidents as $paysident): ?>
                                            <option value="<?= $paysident->id_pays; ?>">
                                                <?= "{$paysident->nom_pays}"; ?>
                                                </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                <label>VILLE</label>
                                    <select class="form-control form-control-sm" name="ville">
                                    <option value=""></option>
                                        <? foreach ($villes as $ville): ?>
                                            <option value="<?= $ville->id_ville; ?>">
                                                <?= "{$ville->nom_ville}"; ?>
                                                </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
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
                            <th>CLE COMPAGNIE</th>
                            <th>ENTREPRISE</th>
                            <th>COMPAGNIE</th>
                            <th>LOGO</th>
                            <th>SLOGAN</th>
                            <th>ADRESSE</th>
                            <th>CONTACT</th>
                            <th>IFU</th>
                            <th>RCCM</th>
                            <th>EMAIL</th>
                            <th>SITE WEB</th>
                            <th>ACTIONS</th>
                        </tr>

                        </thead>

                        <tbody>
                        
                        <? foreach ($compagnie as $item): ?>

                            <tr>
                                <td class="cell-detail">
                                        <?= $item->cle_compagnie; ?>
                                </td>
                                
                                <td class="cell-detail">
                                        <?= $item->nom_entreprise; ?>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->nom_compagnie; ?><br>
                                        <?= $item->nom_pays; ?><br>
                                        <?= $item->nom_ville; ?>
                                </td>
                                <td class="cell-detail"><?=$item->logo; ?>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->slogan; ?>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->adresse; ?>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->contact_comp; ?>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->num_ifu_comp; ?>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->num_rccm_comp; ?>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->mail_comp; ?>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->siteweb_comp; ?>
                                </td>
                                <td class="actions">
                                    
                                    <a href="<?= "#?{$item->id_compagnie}&id={$item->id_compagnie}&={$item->nom_compagnie}"; ?>"
                                       class="md-trigger" data-modal="cp-edit-<?= $item->id_compagnie; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="cp-edit-<?= $item->id_compagnie; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_compagnie; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Compagnies/upedit/{$this->session->company->ekey}/{$item->id_compagnie}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                 <!-- NOM compagnie -->
                                                <div class="form-group col-sm-4">
                                                    <label>NOM COMPAGNIE</label>
                                                    <input class="form-control form-control-sm" name="compagnie_nom" value="<?= $item->nom_compagnie; ?>"
                                                        type="text" placeholder="<?= $item->nom_compagnie; ?>" autocomplete="off" required>
                                                </div>


                                                <!-- logo -->
                                                <div class="form-group col-sm-4">
                                                    <label>LOGO</label>
                                                    <input class="form-control form-control-sm" name="logocompagnie" type="file" value="<?= $item->logo; ?>"
                                                placeholder="<?= $item->logo; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>LOGO FOND</label>
                                                    <input class="form-control form-control-sm" name="logofond" type="file" value="<?= $item->logofond; ?>"
                                                        placeholder="<?= $item->logofond; ?>">
                                                </div>
                                                <!-- slogan -->
                                                <div class="form-group col-sm-4">
                                                    <label>SLOGAN </label>
                                                    <input class="form-control form-control-sm" name="slogan" type="text" 
                                                    autocomplete="off" value="<?= $item->slogan; ?>" placeholder="<?= $item->slogan; ?>">
                                                </div>
                                                <!-- adresse -->
                                                <div class="form-group col-sm-4">
                                                    <label>ADRESSE</label>
                                                    <input class="form-control form-control-sm" name="adresse" autocomplete="off" type="text"
                                                    value="<?= $item->adresse; ?>" placeholder="<?= $item->adresse; ?>">
                                                </div>
                                                <!-- Contact -->
                                                <div class="form-group col-sm-4">
                                                    <label>CONTACT COMPAGNIE</label>
                                                    <input class="form-control form-control-sm" name="contact" autocomplete="off" type="text"
                                                    value="<?= $item->contact_comp; ?>" placeholder="<?= $item->contact_comp; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>CONTACT NATIONAL</label>
                                                    <input class="form-control form-control-sm" name="contactnation" autocomplete="off" type="text"
                                                    value="<?= $item->contact_national; ?>" placeholder="<?= $item->contact_national; ?>">
                                                </div>
                                                
                                                <!-- Contact secdond-->
                                                <div class="form-group col-sm-4">
                                                    <label>CONTACT INTERNATIONAL</label>
                                                    <input class="form-control form-control-sm" name="contactsecd" autocomplete="off" type="text"
                                                    value="<?= $item->contact_inter; ?>" placeholder="<?= $item->contact_inter; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>GROUPE/ENTREPRISE</label>
                                                    <select class="form-control form-control-sm" name="prise">
                                                    <option value="<?= $item->id_entrep; ?>"><?= $item->nom_entreprise; ?></option>
                                                        <? foreach ($entreprises as $entreprise): ?>
                                                    <option value="<?= $entreprise->id_entreprise; ?>">
                                                    <?= "{$entreprise->nom_entreprise}"; ?></option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>RCCM</label>
                                                    <input class="form-control form-control-sm" name="numrccm"
                                                        type="text" placeholder="<?= $item->num_rccm_comp; ?>" autocomplete="off" >
                                                </div>
                                                <!-- ifu -->
                                                <div class="form-group col-sm-4">
                                                    <label>IFU</label>
                                                    <input class="form-control form-control-sm" name="numifu" type="text"
                                                placeholder="<?= $item->num_ifu_comp; ?>" autocomplete="off" >
                                                </div>
                                                <!-- email -->
                                                <div class="form-group col-sm-4">
                                                <label>EMAIL</label>
                                                    <input class="form-control form-control-sm" name="adresseemail" autocomplete="off" type="text"
                                                placeholder="<?= $item->mail_comp; ?>">
                                                </div>
                                                <!-- adresse site -->
                                                <div class="form-group col-sm-4">
                                                    <label>SITE WEB</label>
                                                    <input class="form-control form-control-sm" name="site_web" autocomplete="off" type="text"
                                                            placeholder="<?= $item->siteweb_comp; ?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>PAYS</label>
                                                    <select class="form-control form-control-sm" name="payidentif">
                                                    <option value="<?=$item->idpayscomp;?>"><?=$item->nom_pays;?></option>
                                                    <? foreach ($paysidents as $paysident): ?>
                                                    <option value="<?= $paysident->id_pays; ?>">
                                                    <?= $paysident->nom_pays; ?>
                                                    </option>
                                                    <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>VILLE</label>
                                                    <select class="form-control form-control-sm" name="ville">
                                                    <option value="<?=$item->vilcompag?>"><?=$item->nom_ville; ?></option>
                                                        <? foreach ($villes as $ville): ?>
                                                    <option value="<?= $ville->id_ville; ?>">
                                                    <?= "{$ville->nom_ville}"; ?>
                                                        </option>
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
    <div class="col-lg-10 offset-lg-1">

        <div class="card">

            <div class="card-header card-header-divider">
                <?= $this->session->company->nom_entreprise; ?>

                <div class="tools">
                    <button class="btn btn-rounded btn-space btn-success md-trigger"
                            data-modal="add-comp">
                        <i class="fas fa-user-astronaut text-danger"></i>
                        AJOUTER UNE NOUVELLE COMPAGNIE
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="add-comp" style="perspective: none;">

                    <div class="modal-content">

                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">AJOUTER UNE NOUVELLE COMPAGNIE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                        </div>
                        
                        <?= form_open('Compagnies/add/' . $this->session->company->ekey
                            . '/', array('class' => 'modal-body form')); ?>

                        <div class="row">
                                <!-- NOM compagnie -->
                            <div class="form-group col-sm-4">
                                <label>NOM COMPAGNIE</label>
                                <input class="form-control form-control-sm" name="compagnie_nom"
                                       type="text" placeholder="Nom compagnie" autocomplete="off" required>
                            </div>


                            <!-- logo -->
                            <div class="form-group col-sm-4">
                                <label>LOGO</label>
                                <input class="form-control form-control-sm" name="logocompagnie" type="file">
                            </div>
                            <div class="form-group col-sm-4">
                                    <label>LOGO FOND</label>
                                    <input class="form-control form-control-sm" name="logofond" type="file">
                            </div>
                            <!-- slogan -->
                            <div class="form-group col-sm-4">
                                <label>SLOGAN </label>
                                <input class="form-control form-control-sm" name="slogan" type="text" autocomplete="off">
                            </div>
                            <!-- adresse -->
                            <div class="form-group col-sm-4">
                                <label>ADRESSE</label>
                                <input class="form-control form-control-sm" name="adresse" autocomplete="off" type="text"
                                        placeholder="adresse ...">
                            </div>
                            <!-- Contact -->
                            <div class="form-group col-sm-4">
                                    <label>CONTACT COMPAGNIE</label>
                                    <input class="form-control form-control-sm" name="contact" autocomplete="off" type="text"
                                            placeholder="contact ...">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>CONTACT NATIONAL</label>
                                <input class="form-control form-control-sm" name="contactnation" autocomplete="off" type="text"
                                        placeholder="contact ...">
                            </div>
                            <!-- Contact secdond-->
                            <div class="form-group col-sm-4">
                                <label>CONTACT INTERNATIONAL</label>
                                <input class="form-control form-control-sm" name="contactsecd" autocomplete="off" type="text"
                                        placeholder="contact ...">
                            </div>
                            <div class="form-group col-sm-4">
                            <label>GROUPE/ENTREPRISE</label>
                                <select class="form-control form-control-sm" name="prise">
                                    <? foreach ($entreprises as $entreprise): ?>
                                        <option value="<?= $entreprise->id_entreprise; ?>">
                                            <?= $entreprise->nom_entreprise; ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>RCCM</label>
                                <input class="form-control form-control-sm" name="numrccm"
                                       type="text" placeholder="RCCM" autocomplete="off" >
                            </div>
                            <!-- ifu -->
                            <div class="form-group col-sm-4">
                                <label>IFU</label>
                                <input class="form-control form-control-sm" name="numifu" type="text"
                                       placeholder="IFU" autocomplete="off" >
                            </div>
                            <!-- email -->
                            <div class="form-group col-sm-4">
                                <label>EMAIL</label>
                                <input class="form-control form-control-sm" name="adresseemail" autocomplete="off" type="text"
                                        placeholder="...">
                            </div>
                            <!-- adresse site -->
                            <div class="form-group col-sm-4">
                                <label>SITE WEB</label>
                                <input class="form-control form-control-sm" name="site_web" autocomplete="off" type="text"
                                        placeholder="...">
                            </div>
                            <div class="form-group col-sm-4">
                            <label>PAYS</label>
                                <select class="form-control form-control-sm" name="payidentif">
                                <option value=""></option>
                                    <? foreach ($paysidents as $paysident): ?>
                                        <option value="<?= $paysident->id_pays; ?>">
                                            <?= "{$paysident->nom_pays}"; ?>
                                            </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                            <label>VILLE</label>
                                <select class="form-control form-control-sm" name="ville">
                                <option value=""></option>
                                    <? foreach ($villes as $ville): ?>
                                        <option value="<?= $ville->id_ville; ?>">
                                            <?= "{$ville->nom_ville}"; ?>
                                            </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
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
                <h2>AUCUNE COMPAGNIE TROUVEE</h2>
            </div>

        </div>

    </div>
    
    <? endif; ?>

</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_compagnie/view.php-->
