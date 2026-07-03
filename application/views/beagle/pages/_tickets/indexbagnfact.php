<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bagagenonfact/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
<div class="col-12">

    <div class="card card-table">

        <div class="card-header">

            <div class="tools dropdown">

                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                    <span class="icon mdi mdi-more-vert"></span>

                </a>

            </div>

            <div class="title">bagages avec ticket non facturable</div>

        </div>
        <div class="card-body">
            <table class="table table-striped table-borderless" id="table1">
                <thead>
                <tr>
                    <th></th>
                    <th>Code</th>
                    <th>Client / Contact</th>
                    <th>Départ / Axe</th>
                    <th>Contenu</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody class="no-border-x">
                <? foreach ($bagagesnonfact as $item): ?>

                    <tr>
                        <td>
                            <span><? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '12'): ?>
                                <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/bagnfsave/'.$this->session->company->ekey.'/'.$item->id_bagage.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print"></i>
                                </a>
                                <? endif; ?>
                            </span>
                        </td>

                        <td>
                            <span><?= $item->codebag; ?></span>
                        </td>
                        <td>
                            <span>Nom:<?= $item->nom_client; ?><br></span>
                            <span>Prénom:<?= $item->prenom_client; ?><br></span>
                            <span>Contact:<?= $item->contact_client; ?>
                        </td>

                        <td>
                            <span>Départ:<?= $item->date_create; ?></span><br>
                            <span>Axe:<?= $item->nom_ligne; ?> <?= $item->quartarr_bg; ?></span>
                        </td>
                        <td>
                            <span><?= $item->contenubagage; ?></span>
                        </td>
                        <td>
                            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '12'): ?>
                            <a class="icon" title="epson"
                                href="<?= site_url('Historique_Passagers/bagnfsave/'.$this->session->company->ekey.'/'.$item->id_bagage.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                <i class="fas fa-print"></i>
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <? endif; ?>
                            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '6'): ?>
                            <a class="icon" title="epson"
                                href="<?= site_url('Historique_Passagers/bagsavenfguich/'.$this->session->company->ekey.'/'.$item->id_bagage.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                <i class="fas fa-print"></i>
                            </a>
                            <? endif; ?>
                            &nbsp;&nbsp;&nbsp;
                            <? if ($this->session->agent->userole === '1'): ?>
                            <a class="icon" title="ANNULER RECU"
                                href="<?= site_url('Confirmation/annulenf/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$item->id_bagage.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$item->annulebag); ?>">
                                <i class="fas fa-trash-alt text-warning"></i>
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="<?= "#?{$item->id_bagage}&bagage={$item->annulebag}"; ?>"
                                    title="prix" class="md-trigger" data-modal="edite-<?= $item->id_bagage; ?>">
                                <i class="fas fa-edit text-warning"></i>
                            </a>&nbsp;
                            
                            <?endif;?>

                            <div
                                class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="edite-<?= $item->id_bagage; ?>" style="perspective: none;">

                                <div class="modal-content">

                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION PRIX</h3>
                                        <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                                    </div>

                                    <?= form_open('Confirmation/updateprixf/' . $this->session->company->ekey .'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$item->id_bagage, array('class' => 'modal-body form')); ?>

                                    <div class="row">
                                        <div class="form-group col-sm-4">
                                            <label>Prix</label>
                                            <input class="form-control form-control-sm" type="text"
                                            name="prixbagage"
                                            value="<?= $item->prix_bagage; ?>"
                                            placeholder="<?= $item->prix_bagage; ?>"/>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success modal-close" type="submit"
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