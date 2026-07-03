<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-lg-12">
        <div class="card">
                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>CONTACT</th>
                            <th>TYPE CLIENT</th>
                            <th>NOM PRENOM</th>
                            <th>ADRESSE</th>
                            <th>ACTIONS</th>
                        </tr>

                        </thead>

                        <tbody>
                        
                        <? foreach ($partenaires as $personnel): ?>

                            <tr>
                                <td class="cell-detail">
                                        <?=$personnel->contact_client; ?></span>
                                </td>
                                <td class="cell-detail">
                                <span><?=$personnel->type_client; ?></span>
                                </td>
                                
                                <td class="cell-detail">
                                    <span><?=$personnel->nom_client.' '.$personnel->prenom_client; ?></span>
                                </td>
                                <td class="cell-detail">
                                    <span><?=$personnel->lieu_delivre; ?></span>
                                </td>
                                <td class="actions">
                                    <a href="<?= "#?{$personnel->id_client}&partenaire={$personnel->nom_client}"; ?>"
                                       class="md-trigger" data-modal="modif-edit-<?= $personnel->id_client; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>
                                
                                    <a href="<?= site_url('Personnels/activeclient/' . $this->session->company->ekey . '/' . $personnel->id_client. '/' . $personnel->actifclient);?> "class="btn btn-space btn-secondary">
                                    <?= ($personnel->actifclient === '1') ? '<span class="icon mdi text-success">activer</span>' : '<span
                                        class="icon mdi text-danger">désactiver</span>' ?>
                                    </a>&nbsp;
                                
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="modif-edit-<?= $personnel->id_client; ?>">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION SUR <?= $personnel->nom_client; ?></h3>
                                                <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <?= form_open("Personnels/editcl_/{$this->session->company->ekey}/{$personnel->id_client}",
                                                array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                            
                                                <div class="form-group col-sm-4">
                                                <label>TYPE_PERSONNE</label>
                                                    <select class="form-control form-control-sm" name="typeperso">
                                                        <option value="<?= $personnel->type_client; ?>"><?= $personnel->type_client; ?></option>
                                                            <option value="client">Client</option>
                                                            <option value="autrepersonnel">Autrepersonnel</option>
                                                    </select>
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label >Contact</label>
                                                    <input class="form-control form-control-sm" type="text" name="perso_tel" value="<?= $personnel->contact_client; ?>"
                                                        autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Nom</label>
                                                    <input class="form-control form-control-sm" type="text" name="ruclient" value="<?= $personnel->nom_client; ?>"
                                                        autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Prénom</label>
                                                    <input class="form-control form-control-sm" type="text" name="prclient" value="<?= $personnel->prenom_client; ?>"
                                                        autocomplete="off">
                                                </div>
                                                <div class="form-group col-sm-4">
                                                    <label>Adresse</label>
                                                <input class="form-control form-control-sm" type="text" name="lieu"
                                                        autocomplete="off"
                                                        value="<?= $personnel->lieu_delivre; ?>">
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
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_personnel/view.php-->