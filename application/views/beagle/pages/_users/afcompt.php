<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="tools">
        <a href="<?= site_url('utilisateurs/' . $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR 
        </a>
        
        
    </div>
    <div class="col-lg-12">

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>Nom utilisateur</th>
                            <th>Gare</th>
                            <th>Actions</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($profilusers as $us): ?>
                            <tr>
                                <td>
                                    <span><?= $us->username; ?></span>
                                </td>

                                <td>
                                    <span><?= $us->garenom; ?></span>

                                </td>
                                <td>
                                    
                                    <a href="<?= site_url('Utilisateurs/activeprofil/' . $this->session->company->ekey . '/' . $us->uid_login. '/' . $us->cpuser_id. '/' . $us->uid. '/' . $us->comptactif);?> "class="btn btn-space btn-secondary">
                                        <?= ($us->comptactif === '0') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                        class="icon mdi text-success">activer</span>' ?>
                                    </a>&nbsp;
                                    &nbsp;
                                    
                                    <a href="<?= "#?{$us->uid}&idrole={$us->userole}&"; ?>"
                                        class=" md-trigger" data-modal="role-<?= $us->uid_login; ?>" title="Modifier">
                                        <span class="icon mdi mdi-edit text-warning"></span>
                                    </a>
                                    
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="role-<?= $us->uid_login; ?>" style="">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION</h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <?= form_open('Utilisateurs/edit_pro/' . $this->session->company->ekey . '/' . $us->uid_login . '/' . $us->uid,
                                                    array('class' => 'modal-body form')); ?>

                                                
                                                <div class="form-group row">
                                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom_Utilisateur:</label>
                                                    <div class="col-12 col-sm-8 col-lg-6">
                                                        <select class="form-control form-control-sm" name="nomuser" id="">
                                                            <option value="<?= $us->uid_login; ?>"><?= $us->username; ?></option>
                                                            <? $userlogins = $this->db->query(
                                                                "SELECT * FROM compte_user")->result(); ?>
                                                            
                                                            <? foreach ($userlogins as $logus): ?>
                                                                <option value="<?= $logus->cpuser_id; ?>"><?= $logus->username; ?></option>
                                                            <? endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Gare:</label>
                                                    <div class="col-12 col-sm-8 col-lg-6">
                                                        <select class="form-control form-control-sm" name="gareuser" id="">
                                                            <option value="<?= $us->guser; ?>"><?= $us->garenom; ?></option>
                                                            <? foreach ($garees as $itemgare): ?>
                                                                <option value="<?= $itemgare->idengare; ?>">
                                                                    <?= "{$itemgare->garenom}"; ?>
                                                                </option>
                                                            <? endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="reset"
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
                                </td>
                            </tr>
                        <? endforeach; ?>
                        </tbody>

                    </table>

                </div>

            </div>
    </div>

    
</div>
   
    <!--End of file: afcompt.php-->
    <!--File location: application/views/beagle/pages/_users/afcompt.php-->