<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<? if (!empty($compteroleattrib)): ?>
<div class="row">
    <div class="tools">
        <a href="<?= site_url('utilisateurs/'.$this->session->company->ekey.'/gTv/'.$conex->uid.'/'.$conex->cpuser_id.'/garecompte/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>

    </div>
</div>
    <div class="row">

        <? foreach ($compteroleattrib as $item): ?>
            <div class="col-lg-3">

                <div class="card card-border card-contrast">

                    <div class="card-header card-header-contrast"><?= $item->first_name; ?>

                        <div class="tools">
                            <a href="<?= site_url('utilisateurs/actifs/'.$this->session->company->ekey.'/'.$item->roleattribut.'/'.$item->uid_login.'/'.$item->guser.'/'.$item->activer_role);?> "class="btn btn-space btn-secondary">
                                <?= ($item->activer_role === '0') ? '<span class="icon mdi text-success">Activer</span>' : '<span
                                class="icon mdi text-danger">Désactiver</span>' ?>
                            </a>&nbsp;
                            &nbsp;
                            
                            <a href="#"
                                class=" md-trigger" data-modal="edit-<?= $item->uid_login ; ?>" title="Modifier">
                                <span class="icon mdi mdi-edit text-warning"></span>
                            </a>
                            <a href="<?= "#?{$item->uid}&name={$item->first_name}&prenom={$item->last_name}"; ?>"
                                class=" md-trigger" data-modal="edit-cpt-<?= $item->id_rols; ?>" title="Attribuerapp">
                                <span class="icon mdi mdi-edit text-success"></span>
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
                                            
                                            <?= form_open('Utilisateurs/addattrbs/' . $this->session->company->ekey.'/'.$item->roleattribut
                                            , array('class' => 'modal-body form')); ?>
                                        
                                            <div class="form-group row">
                                                <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Rôle:</label>
                                                <div class="col-12 col-sm-8 col-lg-6">
                                                    <select class="form-control form-control-sm" name="fonction">
                                                        <option value="<?= $item->userole; ?>"><?= $item->type_rols; ?></option>
                                                        
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
                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="edit-cpt-<?= $item->id_rols; ?>" style="">
                                <div class="modal-content">
                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">ATTRIBUER
                                            PAGE A <?= $item->first_name; ?> <?= $item->last_name; ?></h3>
                                        <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                    <?= form_open('Utilisateurs/editpage_/' . $this->session->company->ekey . '/' . $item->cpuser_id. '/' . $item->id_rols. '/' . $item->uid. '/' . $item->guser,  
                                        array('class' => 'modal-body form')); ?>
                                        <div class="form-group row">
                                            <label>Dossier:</label>
                                            <div class="col-12 col-sm-8 col-lg-6">
                                                <select class="form-control form-control-sm" name="roledosattr" id="">
                                                    <option value="">Choisissez app</option>
                                                    <? foreach ($dossiers as $do): ?>
                                                        <option value="<?= $do->iddoss ; ?>"><?= $do->typedossier; ?></option>
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
                        <p>PROFIL: <?= $item->type_rols; ?></p>
                        <p><?= ($item->is_conect === '1' AND $item->activeattrib === '1') ? '<span
                                class="icon mdi text-success">En ligne</span>' : '<span
                                class="icon mdi text-danger">Déconnecté</span>&nbsp;<i class="fas fa-power-off text-danger"></i>' ?></p>
                        <p><?= ($item->activer_role === '1') ? '<span
                                class="icon mdi text-danger">Compte désactivé</span>' : '<span
                                class="icon mdi text-success">Compte activé</span>' ?>
                        </p>
                       
                    </div>

                </div>

            </div>
        <? endforeach; ?>
    </div>
<? else: ?>
        
        <div class="col-lg-4 offset-lg-4">
            <div class="card">
                <div class="card-header card-header-divider"><?= $this->session->company->nom_entreprise; ?></div>
                <div class="card-body text-center text-capitalize">
                    <h2>AUCUN ROLE</h2>
                </div>
            </div>
        </div>
<? endif; ?>
