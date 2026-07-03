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
                            <th>Profil</th>
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
                                    <span><?= $us->type_rols; ?></span>
                                </td>
                                

                                <td>
                                    <span><?= $us->garenom; ?></span>

                                </td>
                                <td>
                                    
                                    <a href="<?= site_url('Utilisateurs/activeprofilgd/' . $this->session->company->ekey .'/' . $us->roleattribut. '/'. '/' . $us->uid_login. '/' . $us->cpuser_id. '/' . $us->uid. '/' . $us->activeattrib);?> "class="btn btn-space btn-secondary">
                                        <?= ($us->activeattrib === '0') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                        class="icon mdi text-success">activer</span>' ?>
                                    </a>&nbsp;
                                    &nbsp;
                                    
                                    <a href="<?= "#?{$us->uid}&idrole={$us->userole}&";?>"
                                        class=" md-trigger" data-modal="rgid-<?= $us->roleattribut; ?>" title="Modifier">
                                        <span class="icon mdi mdi-edit text-warning"></span>
                                    </a>
                                    
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                        id="rgid-<?= $us->roleattribut; ?>" style="">
                                        <div class="modal-content">
                                            <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION</h3>
                                                <button class="close modal-close" type="button"
                                                        data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span>
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <?= form_open('Utilisateurs/edit_progare/' . $this->session->company->ekey . '/' . $us->roleattribut . '/' . $us->uid_login,
                                                    array('class' => 'modal-body form')); ?>

                                                
                                                <div class="form-group row">
                                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Rôle:</label>
                                                    <div class="col-12 col-sm-8 col-lg-6">
                                                        <select class="form-control form-control-sm" name="fonction" id="fonction">
                                                            <option value="<?= $us->userole; ?>"><?= $us->type_rols; ?></option>
                                                            <? $roles = $this->db->query(
                                                                "SELECT * FROM user_roles")->result(); ?>
                                                            
                                                            <? foreach ($roles as $role): ?>
                                                                <option value="<?= $role->id_rols; ?>"><?= $role->type_rols; ?></option>
                                                            <? endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Compte/Gare:</label>
                                                    <div class="col-12 col-sm-8 col-lg-6">
                                                        <select class="form-control form-control-sm" name="usergares">
                                                <option value="<?= $us->idgestcompte; ?>"><?= $us->username; ?>/<?= $us->garenom; ?></option>
                                                            <? $comptegares = $this->db->query(
                                                                "SELECT * FROM user_login u
                                                                JOIN compte_user c ON u.uid_usercpte = c.cpuser_id
                                                                JOIN gares g ON u.guser = g.idengare")->result(); ?>
                                                            <? foreach ($comptegares as $cptgare): ?>
                                                    <option value="<?= $cptgare->uid_login; ?>">
                                    <?=$cptgare->username; ?>/<?=$cptgare->garenom; ?>
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
   
    <!--End of file: afcomptgarerole.php-->
    <!--File location: application/views/beagle/pages/_users/afcomptgarerole.php-->