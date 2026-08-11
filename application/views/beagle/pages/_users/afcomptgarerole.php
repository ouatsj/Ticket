<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
<div class="tools">
        <a href="<?= site_url('utilisateurs/' . $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR 
        </a>
        
    </div>
    <div class="col-lg-12">

            <div class="card card-table">

                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="mdi mdi-search"></span></span>
                                </div>
                                <input type="search"
                                       class="form-control"
                                       data-user-table-filter="#table1"
                                       data-user-table-count="#profils-gare-filter-count"
                                       data-user-table-empty="#profils-gare-filter-empty"
                                       data-user-filter-label="attribution(s)"
                                       placeholder="Rechercher (utilisateur, rôle, gare)…"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <small id="profils-gare-filter-count" class="text-muted"></small>
                        </div>
                    </div>
                </div>

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
                            <?php
                            $role_search = strtolower(trim(
                                $us->username . ' ' . $us->type_rols . ' ' . $us->garenom
                            ));
                            ?>
                            <tr data-user-filter-item="1"
                                data-search="<?= htmlspecialchars($role_search, ENT_QUOTES, 'UTF-8'); ?>">
                                <td>
                                    <?php $this->load->view('beagle/pages/_users/_compte_status_inline', ['item' => $us]); ?>
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

                    <p id="profils-gare-filter-empty" class="text-center text-muted py-3 mb-0" style="display: none;">
                        Aucune attribution ne correspond à votre recherche.
                    </p>

                </div>

            </div>
    </div>
</div>
   
    <!--End of file: afcomptgarerole.php-->
    <!--File location: application/views/beagle/pages/_users/afcomptgarerole.php-->