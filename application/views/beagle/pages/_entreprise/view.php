<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-table">
            <div class="card-header card-header-divider">
                <?= $this->session->company->nom_entreprise; ?>
                <div class="tools">
                    <button type="button" class="btn btn-rounded btn-space btn-success md-trigger"
                            data-modal="add-entrep">
                        <i class="fas fa-user-astronaut text-danger"></i>
                        AJOUTER UNE NOUVELLE ENTREPRISE
                    </button>
                </div>
            </div>

            <div class="card-body">
                <? if (empty($entreprises)): ?>
                    <div class="text-center py-4">
                        <h2>AUCUNE ENTREPRISE TROUVEE</h2>
                    </div>
                <? else: ?>
                    <div class="table-responsive noSwipe">
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
                                    <td class="cell-detail"><?= $item->nom_entreprise; ?></td>
                                    <td><?= $item->nom_pays; ?></td>
                                    <td class="cell-detail"><?= $item->nom_ville; ?></td>
                                    <td class="cell-detail"><span><?= $item->num_RCCM; ?></span></td>
                                    <td class="cell-detail"><?= $item->num_IFU; ?></td>
                                    <td class="cell-detail"><?= $item->adresseentre; ?></td>
                                    <td class="cell-detail"><?= $item->boitepostal; ?></td>
                                    <td class="cell-detail"><?= $item->contact; ?></td>
                                    <td class="cell-detail"><span><?= $item->regime; ?></span></td>
                                    <td class="cell-detail"><span><?= $item->licence_ent; ?></span></td>
                                    <td class="cell-detail"><span><?= $item->agrement; ?></span></td>
                                    <td class="cell-detail"><span><?= $item->email_ent; ?></span></td>
                                    <td class="cell-detail"><span><?= $item->siteweb; ?></span></td>
                                    <td class="actions">
                                        <a href="#ent-edit-<?= (int) $item->id_entreprise; ?>"
                                           class="md-trigger"
                                           data-modal="ent-edit-<?= (int) $item->id_entreprise; ?>"
                                           title="Modifier">
                                            <span class="fas fa-edit text-warning"></span>
                                        </a>

                                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                             id="ent-edit-<?= (int) $item->id_entreprise; ?>"
                                             style="perspective: none;">
                                            <div class="modal-content">
                                                <div class="modal-header modal-header-colored">
                                                    <h3 class="modal-title">MODIFICATION SUR <?= htmlspecialchars($item->nom_entreprise, ENT_QUOTES, 'UTF-8'); ?></h3>
                                                    <button class="close modal-close" type="button"
                                                            data-dismiss="modal" aria-hidden="true">
                                                        <span class="mdi mdi-close text-white"></span>
                                                    </button>
                                                </div>
                                                <?= form_open(
                                                    "Entreprises/edit_/{$this->session->company->ekey}/{$item->id_entreprise}",
                                                    array('class' => 'modal-body form', 'enctype' => 'multipart/form-data')
                                                ); ?>
                                                <div class="row">
                                                    <div class="form-group col-sm-4">
                                                        <label>GROUPE/ENTREPRISE</label>
                                                        <input class="form-control form-control-sm" name="nom_entreprise"
                                                               type="text" value="<?= htmlspecialchars($item->nom_entreprise, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off" required>
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>NUMERO RCCM</label>
                                                        <input class="form-control form-control-sm" name="num_rccm"
                                                               type="text" value="<?= htmlspecialchars($item->num_RCCM, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off" required>
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>NUMERO IFU</label>
                                                        <input class="form-control form-control-sm" name="numeroifu" type="text"
                                                               value="<?= htmlspecialchars($item->num_IFU, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off" required>
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>ADRESSE</label>
                                                        <input class="form-control form-control-sm" name="adresse" type="text"
                                                               value="<?= htmlspecialchars($item->adresseentre, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>BOITE POSTALE</label>
                                                        <input class="form-control form-control-sm" name="postale" type="text"
                                                               value="<?= htmlspecialchars($item->boitepostal, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>CONTACT</label>
                                                        <input class="form-control form-control-sm" name="contact" type="text"
                                                               value="<?= htmlspecialchars($item->contact, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>REGIME</label>
                                                        <input class="form-control form-control-sm" name="regime" type="text"
                                                               value="<?= htmlspecialchars($item->regime, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off" required>
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>LICENCE</label>
                                                        <input class="form-control form-control-sm" name="licence" type="text"
                                                               value="<?= htmlspecialchars($item->licence_ent, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>AGREMENT</label>
                                                        <input class="form-control form-control-sm" name="agrement" type="text"
                                                               value="<?= htmlspecialchars($item->agrement, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>EMAIL</label>
                                                        <input class="form-control form-control-sm" name="email" type="text"
                                                               value="<?= htmlspecialchars($item->email_ent, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>SITE WEB</label>
                                                        <input class="form-control form-control-sm" name="siteweb" type="text"
                                                               value="<?= htmlspecialchars($item->siteweb, ENT_QUOTES, 'UTF-8'); ?>"
                                                               autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>PAYS</label>
                                                        <select class="form-control form-control-sm" name="paysidentif">
                                                            <option value="<?= (int) $item->pays_id; ?>"><?= htmlspecialchars($item->nom_pays, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <? foreach ($paysidents as $paysident): ?>
                                                                <option value="<?= (int) $paysident->id_pays; ?>">
                                                                    <?= htmlspecialchars($paysident->nom_pays, ENT_QUOTES, 'UTF-8'); ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>SIEGE</label>
                                                        <select class="form-control form-control-sm" name="ville">
                                                            <option value="<?= (int) $item->id_ville_ent; ?>"><?= htmlspecialchars($item->nom_ville, ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <? foreach ($villes as $ville): ?>
                                                                <option value="<?= (int) $ville->id_ville; ?>">
                                                                    <?= htmlspecialchars($ville->nom_ville, ENT_QUOTES, 'UTF-8'); ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>LOGO</label>
                                                        <input class="form-control form-control-sm" name="logoentreprise" type="file">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                    </button>
                                                    <button class="btn btn-success" type="submit">
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
                <? endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="add-entrep" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">AJOUTER UNE NOUVELLE ENTREPRISE</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open(
            'Entreprises/add/' . $this->session->company->ekey . '/',
            array('class' => 'modal-body form', 'enctype' => 'multipart/form-data')
        ); ?>
        <div class="row">
            <div class="form-group col-sm-4">
                <label>GROUPE/ENTREPRISE</label>
                <input class="form-control form-control-sm" name="nom_entreprise"
                       type="text" placeholder="nom" autocomplete="off" required>
            </div>
            <div class="form-group col-sm-4">
                <label>NUMERO RCCM</label>
                <input class="form-control form-control-sm" name="num_rccm"
                       type="text" placeholder="RCCM" autocomplete="off" required>
            </div>
            <div class="form-group col-sm-4">
                <label>NUMERO IFU</label>
                <input class="form-control form-control-sm" name="numeroifu" type="text"
                       placeholder="IFU" autocomplete="off" required>
            </div>
            <div class="form-group col-sm-4">
                <label>ADRESSE</label>
                <input class="form-control form-control-sm" name="adresse" type="text"
                       placeholder="adresse ..." autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>BOITE POSTALE</label>
                <input class="form-control form-control-sm" name="postale" type="text"
                       placeholder="boite postal ..." autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>CONTACT</label>
                <input class="form-control form-control-sm" name="contact" type="text"
                       placeholder="contact ..." autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>REGIME</label>
                <input class="form-control form-control-sm" name="regime" type="text"
                       placeholder="" autocomplete="off" required>
            </div>
            <div class="form-group col-sm-4">
                <label>LICENCE</label>
                <input class="form-control form-control-sm" name="licence" type="text"
                       placeholder="" autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>AGREMENT</label>
                <input class="form-control form-control-sm" name="agrement" type="text" autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>EMAIL</label>
                <input class="form-control form-control-sm" name="email" type="text"
                       placeholder="..." autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>SITE WEB</label>
                <input class="form-control form-control-sm" name="siteweb" type="text"
                       placeholder="..." autocomplete="off">
            </div>
            <div class="form-group col-sm-4">
                <label>PAYS</label>
                <select class="form-control form-control-sm" name="paysidentif">
                    <option value=""></option>
                    <? foreach ($paysidents as $paysident): ?>
                        <option value="<?= (int) $paysident->id_pays; ?>">
                            <?= htmlspecialchars($paysident->nom_pays, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>SIEGE</label>
                <select class="form-control form-control-sm" name="ville">
                    <option value=""></option>
                    <? foreach ($villes as $ville): ?>
                        <option value="<?= (int) $ville->id_ville; ?>">
                            <?= htmlspecialchars($ville->nom_ville, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>LOGO</label>
                <input class="form-control form-control-sm" name="logoentreprise" type="file">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
            </button>
            <button class="btn btn-success" type="submit">
                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
            </button>
        </div>
        <?= form_close(); ?>
    </div>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_entreprise/view.php-->
