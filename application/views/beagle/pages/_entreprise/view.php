<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-lg-12">
        <div class="card">
            <? if (!empty($entreprises)): ?>
            <div class="card card-table">

                <div class="card-header card-header-divider">
                    <?= $this->session->company->nom_entreprise; ?>
                    <div class="tools">
                    <button class="btn btn-rounded btn-space btn-success md-trigger"
                            data-modal="add-entrep">
                        <i class="fas fa-user-astronaut text-danger"></i>
                        AJOUTER UNE NOUVELLE ENTREPRISE
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="add-entrep" style="perspective: none;">

                    <div class="modal-content">

                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">AJOUTER UNE NOUVELLE ENTREPRISE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                        </div>
                        
                        <?= form_open('Entreprises/add/' . $this->session->company->ekey
                            . '/', array('class' => 'modal-body form')); ?>

                        <div class="row">
                            <!-- nom entreprise -->
                            <div class="form-group col-sm-4">
                                <label>GROUPE/ENTREPRISE</label>
                                <input class="form-control form-control-sm" name="nom_entreprise"
                                       type="text" placeholder="nom" autocomplete="off" required>
                            </div>
                                <!--  -->
                            <div class="form-group col-sm-4">
                                <label>NUMERO RCCM</label>
                                <input class="form-control form-control-sm" name="num_rccm"
                                       type="text" placeholder="RCCM" autocomplete="off" required>
                            </div>


                            <!-- ifu -->
                            <div class="form-group col-sm-4">
                                <label>NUMERO IFU</label>
                                <input class="form-control form-control-sm" name="numeroifu" type="text"
                                       placeholder="IFU" autocomplete="off" required>
                            </div>
                            <!-- adresse -->
                            <div class="form-group col-sm-4">
                                <label>ADRESSE</label>
                                <input class="form-control form-control-sm" name="adresse" autocomplete="off" type="text"
                                        placeholder="adresse ...">
                            </div>
                            <!-- boite -->
                            <div class="form-group col-sm-4">
                                <label>BOITE POSTALE</label>
                                <input class="form-control form-control-sm" name="postale" autocomplete="off" type="text"
                                        placeholder="boite postal ...">
                            </div>
                            <!-- Contact -->
                            <div class="form-group col-sm-4">
                                <label>CONTACT</label>
                                <input class="form-control form-control-sm" name="contact" autocomplete="off" type="text"
                                        placeholder="contact ...">
                            </div>
                            <!-- regime -->
                            <div class="form-group col-sm-4">
                                <label>REGIME</label>
                                <input class="form-control form-control-sm" name="regime" type="text"
                                       placeholder="" autocomplete="off" required>
                            </div>
                            <!-- licence -->
                            <div class="form-group col-sm-4">
                                <label>LICENCE</label>
                                <input class="form-control form-control-sm" name="licence" autocomplete="off" type="text"
                                        placeholder="">
                            </div>
                            <!-- agrement -->
                            <div class="form-group col-sm-4">
                                <label>AGREMENT</label>
                                <input class="form-control form-control-sm" name="agrement" autocomplete="off" type="text">
                            </div>
                            <!-- email -->
                            <div class="form-group col-sm-4">
                                <label>EMAIL</label>
                                <input class="form-control form-control-sm" name="email" autocomplete="off" type="text"
                                        placeholder="...">
                            </div>
                            <!-- adresse site -->
                            <div class="form-group col-sm-4">
                                <label>SITE WEB</label>
                                <input class="form-control form-control-sm" name="siteweb" autocomplete="off" type="text"
                                        placeholder="...">
                            </div>
                            <div class="form-group col-sm-4">
                            <label>PAYS</label>
                                <select class="form-control form-control-sm" name="paysidentif">
                                <option value=""></option>
                                    <? foreach ($paysidents as $paysident): ?>
                                        <option value="<?= $paysident->id_pays; ?>">
                                            <?= "{$paysident->nom_pays}"; ?>
                                            </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                            <label>SIEGE</label>
                                <select class="form-control form-control-sm" name="ville">
                                <option value=""></option>
                                    <? foreach ($villes as $ville): ?>
                                        <option value="<?= $ville->id_ville; ?>">
                                            <?= "{$ville->nom_ville}"; ?>
                                            </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <!-- logo -->
                            <div class="form-group col-sm-4">
                                <label>LOGO</label>
                                <input class="form-control form-control-sm" name="logoentreprise" type="file">
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
                            <th>ENTREPRISE</th>
                            <th>PAYS</th>
                            <th>VILLE</th>
                            <th>RCCM</th>
                            <th>IFU</th>
                            <th>ADRESSE</th>
                            <th>BOITE</th>
                            <th>CONTACT</th>
                            <th>REGIME</th>
                            <th>LICENCE</th>
                            <th>AGREMENT</th>
                            <th>EMAIL</th>
                            <th>SITE WEB</th>
                            <th>ACTIONS</th>
                        </tr>

                        </thead>

                        <tbody>
                        
                        <? foreach ($entreprises as $item): ?>

                            <tr>
                                <td class="cell-detail">
                                        <?= $item->nom_entreprise; ?>
                                </td>
                                <td>
                                    <?= $item->nom_pays;?>
                                </td>
                                
                                <td class="cell-detail">
                                        <?= $item->nom_ville; ?>
                                </td>
                                <td class="cell-detail">
                                        <span><?= $item->num_RCCM; ?></span>
                                </td>
                                <td class="cell-detail">
                                        <?= $item->num_IFU; ?>
                                </td>
                                <td class="cell-detail">
                                        <?=$item->adresseentre; ?>
                                </td>
                                <td class="cell-detail">
                                        <?=$item->boitepostal; ?>
                                </td>
                                
                                <td class="cell-detail">
                                        <?=$item->contact; ?>
                                </td>
                                <td class="cell-detail">
                                        <span><?= $item->regime; ?></span>
                                </td>
                                <td class="cell-detail">
                                        <span><?= $item->licence_ent; ?></span>
                                </td>
                                <td class="cell-detail">
                                        <span><?= $item->agrement; ?></span>
                                </td>
                                <td class="cell-detail">
                                        <span><?= $item->email_ent; ?></span>
                                </td>
                                <td class="cell-detail">
                                        <span><?= $item->siteweb; ?></span>
                                </td>
                                <td class="actions">
                                    
                                    <a href="<?= "#?{$item->id_entreprise}&id={$item->id_entreprise}&={$item->nom_entreprise}"; ?>"
                                       class="md-trigger" data-modal="ent-edit-<?= $item->id_entreprise; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>

                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                         id="ent-edit-<?= $item->id_entreprise; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $item->nom_entreprise; ?></h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Entreprises/edit_/{$this->session->company->ekey}/{$item->id_entreprise}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">

                                                    <!-- nom entreprise -->
                                                <div class="form-group col-sm-4">
                                                    <label>GROUPE/ENTREPRISE</label>
                                                    <input class="form-control form-control-sm" name="nom_entreprise"
                                                        type="text" value="<?= $item->nom_entreprise; ?>" placeholder="<?= $item->nom_entreprise; ?>" autocomplete="off" required>
                                                </div>
                                                    <!--  -->
                                                <div class="form-group col-sm-4">
                                                    <label>NUMERO RCCM</label>
                                                    <input class="form-control form-control-sm" name="num_rccm"
                                                        type="text" value="<?= $item->num_RCCM; ?>" placeholder="<?= $item->num_RCCM; ?>" autocomplete="off" required>
                                                </div>


                                                <!-- ifu -->
                                                <div class="form-group col-sm-4">
                                                    <label>NUMERO IFU</label>
                                                    <input class="form-control form-control-sm" name="numeroifu" type="text"
                                                    value="<?= $item->num_IFU; ?>" placeholder="<?= $item->num_IFU; ?>" autocomplete="off">
                                                </div>
                                                <!-- adresse -->
                                                <div class="form-group col-sm-4">
                                                    <label>ADRESSE</label>
                                                    <input class="form-control form-control-sm" name="adresse" autocomplete="off" type="text"
                                                    value="<?= $item->adresseentre; ?>" placeholder="<?= $item->adresseentre; ?>">
                                                </div>
                                                <!-- boite -->
                                                <div class="form-group col-sm-4">
                                                    <label>BOITE POSTALE</label>
                                                    <input class="form-control form-control-sm" name="postale" autocomplete="off" type="text"
                                                            value="<?=$item->boitepostal?>" placeholder="<?=$item->boitepostal?>">
                                                </div>
                                                <!-- Contact -->
                                                <div class="form-group col-sm-4">
                                                    <label>CONTACT</label>
                                                    <input class="form-control form-control-sm" name="contact" autocomplete="off" type="text"
                                                    value="<?=$item->contact; ?>" placeholder="<?= $item->contact; ?>">
                                                </div>
                                                <!-- regime -->
                                                <div class="form-group col-sm-4">
                                                    <label>REGIME</label>
                                                    <input class="form-control form-control-sm" name="regime" type="text"
                                                        placeholder="<?=$item->regime;?>" autocomplete="off">
                                                </div>
                                                <!-- licence -->
                                                <div class="form-group col-sm-4">
                                                    <label>LICENCE</label>
                                                    <input class="form-control form-control-sm" name="licence" autocomplete="off" type="text"
                                                            placeholder="<?=$item->licence_ent;?>">
                                                </div>
                                                <!-- agrement -->
                                                <div class="form-group col-sm-4">
                                                    <label>AGREMENT</label>
                                                    <input class="form-control form-control-sm" name="agrement" autocomplete="off" type="text" placehoder="<?=$item->agrement;?>">
                                                </div>
                                                <!-- email -->
                                                <div class="form-group col-sm-4">
                                                    <label>EMAIL</label>
                                                    <input class="form-control form-control-sm" name="email" autocomplete="off" type="text"
                                                            placeholder="<?=$item->email_ent;?>">
                                                </div>
                                                <!-- adresse site -->
                                                <div class="form-group col-sm-4">
                                                    <label>SITE WEB</label>
                                                    <input class="form-control form-control-sm" name="siteweb" autocomplete="off" type="text"
                                                            placeholder="<?$item->siteweb;?>">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>PAYS</label>
                                                    <select class="form-control form-control-sm" name="paysidentif">
                                                    <option value="<?= $item->pays_id; ?>"><?= "{$item->nom_pays}"; ?></option>
                                                        <? foreach ($paysidents as $paysident): ?>
                                                            <option value="<?= $paysident->id_pays; ?>">
                                                                <?= "{$paysident->nom_pays}"; ?>
                                                                </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                <label>SIEGE</label>
                                                    <select class="form-control form-control-sm" name="ville">
                                                    <option value="<?= $item->id_ville_ent; ?>"><?= "{$item->nom_ville}"; ?></option>
                                                        <? foreach ($villes as $ville): ?>
                                                            <option value="<?= $ville->id_ville; ?>">
                                                                <?= "{$ville->nom_ville}"; ?>
                                                                </option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <!-- logo -->
                                                <div class="form-group col-sm-4">
                                                    <label>LOGO</label>
                                                    <input class="form-control form-control-sm" value="<?=$item->logoent;?>" name="logoentreprise" type="file">
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
                            data-modal="add-entrep">
                        <i class="fas fa-user-astronaut text-danger"></i>
                        AJOUTER UNE NOUVELLE ENTREPRISE
                    </button>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="add-entrep" style="perspective: none;">

                    <div class="modal-content">

                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">AJOUTER UNE NOUVELLE ENTREPRISE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                        </div>
                        
                        <?= form_open('Entreprises/add/' . $this->session->company->ekey
                            . '/', array('class' => 'modal-body form')); ?>

                        <div class="row">
                            <!-- nom entreprise -->
                            <div class="form-group col-sm-4">
                                <label>GROUPE/ENTREPRISE</label>
                                <input class="form-control form-control-sm" name="nom_entreprise"
                                       type="text" placeholder="nom" autocomplete="off" required>
                            </div>
                                <!--  -->
                            <div class="form-group col-sm-4">
                                <label>NUMERO RCCM</label>
                                <input class="form-control form-control-sm" name="num_rccm"
                                       type="text" placeholder="RCCM" autocomplete="off" required>
                            </div>


                            <!-- ifu -->
                            <div class="form-group col-sm-4">
                                <label>NUMERO IFU</label>
                                <input class="form-control form-control-sm" name="numeroifu" type="text"
                                       placeholder="IFU" autocomplete="off" required>
                            </div>
                            <!-- adresse -->
                            <div class="form-group col-sm-4">
                                <label>ADRESSE</label>
                                <input class="form-control form-control-sm" name="adresse" autocomplete="off" type="text"
                                        placeholder="adresse ...">
                            </div>
                            <!-- boite -->
                            <div class="form-group col-sm-4">
                                <label>BOITE POSTALE</label>
                                <input class="form-control form-control-sm" name="postale" autocomplete="off" type="text"
                                        placeholder="boite postal ...">
                            </div>
                            <!-- Contact -->
                            <div class="form-group col-sm-4">
                                <label>CONTACT</label>
                                <input class="form-control form-control-sm" name="contact" autocomplete="off" type="text"
                                        placeholder="contact ...">
                            </div>

                            <!-- regime -->
                            <div class="form-group col-sm-4">
                                <label>REGIME</label>
                                <input class="form-control form-control-sm" name="regime" type="text"
                                       placeholder="" autocomplete="off" required>
                            </div>
                            <!-- licence -->
                            <div class="form-group col-sm-4">
                                <label>LICENCE</label>
                                <input class="form-control form-control-sm" name="licence" autocomplete="off" type="text"
                                        placeholder="">
                            </div>
                            <!-- agrement -->
                            <div class="form-group col-sm-4">
                                <label>AGREMENT</label>
                                <input class="form-control form-control-sm" name="agrement" autocomplete="off" type="text">
                            </div>
                            <!-- email -->
                            <div class="form-group col-sm-4">
                                <label>EMAIL</label>
                                <input class="form-control form-control-sm" name="email" autocomplete="off" type="text"
                                        placeholder="...">
                            </div>
                            <!-- adresse site -->
                            <div class="form-group col-sm-4">
                                <label>SITE WEB</label>
                                <input class="form-control form-control-sm" name="siteweb" autocomplete="off" type="text"
                                        placeholder="...">
                            </div>
                            <div class="form-group col-sm-4">
                            <label>PAYS</label>
                                <select class="form-control form-control-sm" name="paysidentif">
                                <option value=""></option>
                                    <? foreach ($paysidents as $paysident): ?>
                                        <option value="<?= $paysident->id_pays; ?>">
                                            <?= "{$paysident->nom_pays}"; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>SIEGE</label>
                                <select class="form-control form-control-sm" name="ville">
                                <option value=""></option>
                                    <? foreach ($villes as $ville): ?>
                                        <option value="<?= $ville->id_ville; ?>">
                                            <?= "{$ville->nom_ville}"; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <!-- logo -->
                            <div class="form-group col-sm-4">
                                <label>LOGO</label>
                                <input class="form-control form-control-sm" name="logoentreprise" type="file">
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
                <h2>AUCUNE ENTREPRISE TROUVEE</h2>
            </div>

        </div>

    </div>
    
    <? endif; ?>

</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_entreprise/view.php-->
