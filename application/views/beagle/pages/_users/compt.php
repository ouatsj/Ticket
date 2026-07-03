<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <? if (!empty($authusers)): ?>
        <div class="col-lg-12">
            <div class="card">

                <div class="card-header">

                    <div class="tools">
                        <button class="btn btn-space btn-info md-trigger" data-modal="add-new-user">
                            <span class="icon mdi mdi-plus-1 text-white"></span>
                        </button>
                    </div>

                </div>

                <div class="card-body"></div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="add-new-user" style="perspective: none;">

                    <div class="modal-content">

                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">AJOUTER UN UTILISATEUR</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                        </div>
                        <?= form_open('Utilisateurs/adduse/' . $this->session->company->ekey, array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm" type="text"
                                       name="firstname" required=""
                                       placeholder="Nom"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Prénom:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm" type="text"
                                       name="lastname" required=""
                                       placeholder="Prénom"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm"
                                       type="tel" name="phone" required=""
                                       placeholder="Numéro de téléphone"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact2:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm"
                                        type="tel" name="phone2" required=""
                                        autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Email:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm" type="text"
                                       name="email" required=""
                                       placeholder="prenom@rakieta.com"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
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
        </div>
        
        <? foreach ($authusers as $item): ?>
            <div class="col-lg-3">

                <div class="card card-border card-contrast">

                    <div class="card-header card-header-contrast"><?= $item->first_name; ?>

                        <div class="tools">
                            <a href="<?= "#?{$item->uid}&name={$item->first_name}&prenom={$item->last_name}&contact={$item->phone}&email={$item->email}"; ?>"
                               class=" md-trigger" data-modal="edit-user-<?= $item->uid; ?>" title="Modifier">
                                <span class="icon mdi mdi-edit text-warning"></span>
                            </a>&nbsp;&nbsp;
                            <a href="<?= "#?{$item->uid}&name={$item->first_name}&prenom={$item->last_name}&contact={$item->phone}&email={$item->email}"; ?>"
                               class=" md-trigger" data-modal="compt-user-<?= $item->uid; ?>" title="Créer compte">
                                <span class="icon mdi mdi-edit text-success"></span>
                            </a>
                            <!-- modification -->
                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                 id="edit-user-<?= $item->uid; ?>" style="">
                                <div class="modal-content">
                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION
                                            SUR <?= $item->first_name; ?> <?= $item->last_name; ?></h3>
                                        <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                        </button>
                                    </div>
                                    <?= form_open('Utilisateurs/edit_use/' . $this->session->company->ekey . '/' . $item->uid,
                                        array('class' => 'modal-body form')); ?>

                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm" type="text"
                                                   name="firstname" required=""
                                                   value="<?= $item->first_name; ?>"
                                                   placeholder="<?= $item->first_name; ?>"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Prénom:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm" type="text"
                                                   name="lastname" required=""
                                                   value="<?= $item->last_name; ?>"
                                                   placeholder="<?= $item->last_name; ?>"
                                                   autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm"
                                                   type="tel" name="phone" required=""
                                                   value="<?= $item->phone; ?>"
                                                   placeholder="<?= $item->phone; ?>"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact2:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm"
                                                   type="tel" name="phone2" required=""
                                                   value="<?= $item->phone2; ?>"
                                                   placeholder="<?= $item->phone2; ?>"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Email:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm"
                                                   type="email" name="email" required=""
                                                   value="<?= $item->email; ?>"
                                                   placeholder="<?= $item->email; ?>"
                                                   autocomplete="off">
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
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Nom:<?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                        <p class="text-danger"></p>
                        <p>Contact: <?= $item->phone; ?></p>
                        <p>Contact2: <?= $item->phone2; ?></p>
                        <a href="<?= site_url('utilisateurs/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->uid. '/compte/' . mdate("%d/%m/%Y", now('UTC'))); ?>" 
                            class="btn btn-block btn-rounded text-dark bg-info">
                            <span class="fas fa-edit"></span>
                            VOIR COMPTE
                        </a>
                    </div>
                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="compt-user-<?= $item->uid; ?>" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN COMPTE UTILISATEUR</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            <div class="card-body">
                                
                                <?= form_open('Utilisateurs/add/' . $this->session->company->ekey.'/'.$item->uid
                                    , array('class' => 'modal-body form')); ?>
                                
                               
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom
                                        Utilisateur:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm" type="text"
                                               name="username" required=""
                                               placeholder="Nom utilisateur"
                                               autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row signup-password">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Mot de
                                        passe:</label>
                                    <div class="col-12 col-sm-8 col-lg-6 row ">
                                        <div class="col-6">
                                            <input class="form-control form-control-sm" name="pass1" type="password" required=""
                                                   placeholder="Mot de passe" autocomplete="off">

                                        </div>
                                        <div class="col-6">
                                            <input class="form-control form-control-sm" name="confirm"
                                                   required=""
                                                   type="password"
                                                   placeholder="Confirmez le mot de passe" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
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

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="compt-attgare-<?= $item->uid; ?>" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">ATTRIBUER UNE GARE A UN COMPTE UTILISATEUR</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            <div class="card-body">
                                
                                <?= form_open('Utilisateurs/addprofil_/' . $this->session->company->ekey.'/'.$item->uid
                                    , array('class' => 'modal-body form')); ?>
                                
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Compte_Utilisateur:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <select class="form-control form-control-sm" name="fonction" id="fonction">
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
                                        <select class="form-control form-control-sm" name="gareuser" id="gareuse">
                                            <? foreach ($gares as $itemgare): ?>
                                                <option value="<?= $itemgare->idengare; ?>">
                                                    <?= "{$itemgare->garenom}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
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

                    
                </div>

            </div>
        <? endforeach; ?>
    
    <? else: ?>
        <div class="col-lg-4 offset-lg-4">
            <div class="card">
                <div class="card-header card-header-divider"><?= $this->session->company->nom_entreprise; ?></div>
                <div class="card-body text-center text-capitalize">
                    <h2>AUCUN UTILISATEUR</h2>
                    <p>Vous pouvez en ajoutez ici
                        <button class="btn btn-rounded btn-space btn-success md-trigger" data-modal="form-users">
                            <i class="icon icon-left mdi mdi-face"></i>AJOUTER UTILISATEUR
                        </button>
                    </p>
                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-users" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN UTILISATEUR</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            <div class="card-body">
                                
                                <?= form_open('Utilisateurs/adduse/' . $this->session->company->ekey
                                    , array('class' => 'modal-body form')); ?>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm" type="text"
                                               name="firstname" required=""
                                               placeholder="Nom"
                                               autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Prénom:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm" type="text"
                                               name="lastname" required=""
                                               placeholder="Prénom"
                                               autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm"
                                               type="tel" name="phone" required=""
                                               placeholder="Numéro de téléphone"
                                               autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact2:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm"
                                                type="tel" name="phone2" required=""
                                                autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Email:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm"
                                                type="email" name="email" required=""
                                                autocomplete="off">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
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
                </div>
            </div>
        </div>
    <? endif; ?>
</div>

<!--End of file: compt.php-->
<!--File location: application/views/beagle/pages/_users/compt.php-->                            
