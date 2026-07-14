<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="tools">
        <a href="<?= site_url('utilisateurs/'.$this->session->company->ekey.'/gTv/'.$conex->uid.'/compte/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </div>
</div>
<div class="row">

    <? foreach ($comptegareattrib as $item): ?>
        <div class="col-lg-3">

            <div class="card card-border card-contrast">

                <?php $this->load->view('beagle/pages/_users/_compte_status_badge', ['item' => $item]); ?>

                <div class="card-header card-header-contrast"><?= $item->first_name; ?>

                    <div class="tools">
                        <a href="<?= site_url('Utilisateurs/actif/'.$this->session->company->ekey.'/'.$item->uid_login.'/'.$item->uid.'/'.$item->cpuser_id.'/'.$item->comptactif);?> "class="btn btn-space btn-secondary">
                            <?= ($item->comptactif === '0') ? '<span class="icon mdi text-success">Activer</span>' : '<span
                            class="icon mdi text-danger">Désactiver</span>' ?>
                        </a>&nbsp;
                        &nbsp;
                        
                        <a href="<?= "#?{$item->uid}&name={$item->first_name}&prenom={$item->last_name}&contact={$item->phone}&email={$item->email}"; ?>"
                            class=" md-trigger" data-modal="edit-<?= $item->uid_login ; ?>" title="Modifier">
                            <span class="icon mdi mdi-edit text-warning"></span>
                        </a>
                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="edit-<?= $item->uid_login ; ?>" style="perspective: none;">

                            <div class="modal-content">

                                <div class="modal-header modal-header-colored">
                                    <h3 class="modal-title">MODIFIER</h3>
                                    <button class="close modal-close" type="button"
                                            data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span></button>
                                </div>
                                <div class="card-body">
                                    
                                    <?= form_open('Utilisateurs/edit_pro/' . $this->session->company->ekey.'/'.$item->uid.'/'.$item->uid_login 
                                        , array('class' => 'modal-body form')); ?>
                                    
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Gare:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <select class="form-control form-control-sm" name="gareuser" id="">
                                                <option value="<?= $item->guser; ?>"><?= $item->garenom; ?></option>
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
                <div class="card-body">
                    <p>Nom:<?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                    <p class="text-danger"></p>
                    <p>Contact: <?= $item->phone; ?></p>
                    <p>Contact2: <?= $item->phone2; ?></p>
                    <p>GARE: <?= $item->garenom; ?></p>
                    <p><?= ($item->comptactif === '1') ? '<span
                            class="icon mdi text-danger"> Désactivé</span>' : '<span
                            class="icon mdi text-success"> Activé</span>' ?>
                    </p>
                        <a href="<?= site_url('utilisateurs/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->uid_login.'/'
                                . $item->guser. '/rolecompte/'. mdate("%d/%m/%Y", now('UTC'))); ?>" 
                            class="btn btn-block btn-rounded text-dark bg-info">
                            <span class="fas fa-edit"></span>
                            VOIR ROLE ATTRIBUER
                        </a>
                        <a href="#"
                            class="btn btn-block btn-rounded text-dark bg-info md-trigger" data-modal="attribrole-<?= $item->uid_login; ?>" title="Attribution Role">
                            <span class="fas fa-edit"></span>ATTRIBUER ROLE
                        </a>
                        
                        
                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="attribrole-<?= $item->uid_login; ?>" style="perspective: none;">
                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">ATTRIBUER UN ROLE</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            <div class="card-body">
                                
                                <?= form_open('Utilisateurs/addattrb/' . $this->session->company->ekey.'/'.$item->uid_login
                                    , array('class' => 'modal-body form')); ?>
                                
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Rôle:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <select class="form-control form-control-sm" name="fonction">
                                            <option value=""></option>
                                            <? $roles = $this->db->query(
                                                "SELECT * FROM user_roles")->result(); ?>
                                            <? foreach ($roles as $ro): ?>
                                                <option value="<?= $ro->id_rols ; ?>"><?= $ro->type_rols; ?></option>
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
    <? endforeach; ?>
</div>                   
