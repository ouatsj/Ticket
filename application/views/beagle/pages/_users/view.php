<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="tools">
        <a href="<?= site_url('utilisateurs/' . $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR 
        </a>
        <a href="<?= site_url('utilisateurs/voirprofil/'. $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-success"></i>&nbsp;VOIR LES PROFILS
        </a>

        <a href="<?= site_url('utilisateurs/voirprofilgare/'. $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-success"></i>&nbsp;VOIR LES GARES ATTRIBUER AU COMPTE
        </a>
        <a href="<?= site_url('utilisateurs/voirprofilpage/'. $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;VOIR LES PAGES ATTRIBUER AU COMPTE
        </a>
    </div>
</div>
<div class="row">

    <? foreach ($authcompte as $item): ?>
        <div class="col-lg-3">

            <div class="card card-border card-contrast">

                <div class="card-header card-header-contrast"><?= $item->first_name; ?>

                    <div class="tools">
                        <a href="<?= site_url('Utilisateurs/active/' . $this->session->company->ekey . '/' . $item->cpuser_id. '/' . $item->uid. '/' . $item->activer);?> "class="btn btn-space btn-secondary">
                            <?= ($item->activer === '0') ? '<span class="icon mdi text-success">Activer</span>' : '<span
                            class="icon mdi text-danger">Désactiver</span>' ?>
                        </a>&nbsp;
                        &nbsp;
                        
                        <a href="<?= "#?{$item->uid}&name={$item->first_name}&prenom={$item->last_name}&contact={$item->phone}&email={$item->email}"; ?>"
                            class=" md-trigger" data-modal="edit-user-<?= $item->cpuser_id; ?>" title="Modifier">
                            <span class="icon mdi mdi-edit text-warning"></span>
                        </a>
                        
                        <!-- modification -->
                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="edit-user-<?= $item->cpuser_id; ?>" style="">
                            <div class="modal-content">
                                <div class="modal-header modal-header-colored">
                                    <h3 class="modal-title">MODIFICATION
                                        SUR <?= $item->first_name; ?> <?= $item->last_name; ?></h3>
                                    <button class="close modal-close" type="button"
                                        data-dismiss="modal"aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                                    </button>
                                </div>
                                <div class="card-body">
                                    <?= form_open('Utilisateurs/edit_/' . $this->session->company->ekey . '/' . $item->cpuser_id. '/' . $item->uid,
                                        array('class' => 'modal-body form')); ?>

                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom
                                            Utilisateur:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm" type="text" name="username"
                                                required=""
                                                value="<?= $item->username; ?>"
                                                placeholder="<?= $item->username; ?>"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row signup-password">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Mot de
                                            passe:</label>
                                        <div class="col-12 col-sm-8 col-lg-6 row ">
                                            <div class="col-6">
                                                <input class="form-control form-control-sm" name="pass1"
                                                    id="pass1" type="password" required=""
                                                    value="<?= $item->upassword; ?>"
                                                    placeholder="<?= $item->upassword; ?>" autocomplete="off">

                                            </div>
                                            <div class="col-6">
                                            <input class="form-control form-control-sm" id="confirm" name="confirm"
                                                required=""
                                                type="password"
                                                value="<?= $item->confirm_password; ?>"
                                                placeholder="<?= $item->confirm_password; ?>" autocomplete="off">
                                        </div>
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

                        
                    </div>
                </div>
                <div class="card-body">
                    <p>Nom:<?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                    <p class="text-danger"></p>
                    <p>Contact: <?= $item->phone; ?></p>
                    <p>Contact2: <?= $item->phone2; ?></p>
                    <p><?= ($item->is_conect === '1') ? '<span
                            class="icon mdi text-success">En ligne</span>' : '<span
                            class="icon mdi text-danger">Déconnecté</span>&nbsp;<i class="fas fa-power-off text-danger"></i>' ?></p>
                    <p><?= ($item->activer === '1') ? '<span
                            class="icon mdi text-danger">Compte désactivé</span>' : '<span
                            class="icon mdi text-success">Compte activé</span>' ?>
                    </p>
                        <a href="<?= site_url('utilisateurs/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->uid.'/'
                                . $item->cpuser_id. '/garecompte/' . mdate("%d/%m/%Y", now('UTC'))); ?>" 
                            class="btn btn-block btn-rounded text-dark bg-info">
                            <span class="fas fa-edit"></span>
                            VOIR GARE ATTRIBUER AU COMPTE
                        </a>
                        
                        <a href="#"
                            class="btn btn-block btn-rounded text-dark bg-info md-trigger" data-modal="attribgare-<?= $item->cpuser_id; ?>" title="Attribuer gare">
                            <span class="fas fa-edit"></span> ATTRIBUER UNE GARE
                        </a>
                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="attribgare-<?= $item->cpuser_id; ?>" style="perspective: none;">

                            <div class="modal-content">

                                <div class="modal-header modal-header-colored">
                                    <h3 class="modal-title">ATTRIBUER UNE GARE A UN COMPTE UTILISATEUR</h3>
                                    <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                                </div>
                                <div class="card-body">
                                    
                                    <?= form_open('Utilisateurs/addprofil_/' . $this->session->company->ekey.'/'.$item->cpuser_id
                                        , array('class' => 'modal-body form')); ?>
                                    
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Gare:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <select class="form-control form-control-sm" name="gareuser" id="">
                                                <option value=""></option>
                                                <? foreach ($garees as $itemgare): ?>
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

            </div>

        </div>
    <? endforeach; ?>
</div>

<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_users/view.php-->                            
