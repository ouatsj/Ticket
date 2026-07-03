<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bagageescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");?>" class="btn btn-space btn-secondary">
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

            <div class="title">bagages</div>

        </div>
        <div class="card-body">
            <table class="table table-striped table-borderless" id="table1">
                <thead>
                <tr>
                    <th></th>
                    <th>Code</th>
                    <th>Client / Contact</th>
                    <th>Départ / Heure / Axe</th>
                    <th>Contenu</th>
                    <th>Prix</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody class="no-border-x">
                <? foreach ($bagagesesc as $itemesc): ?>
                    <tr>
                        <td>
                            <span><a class="icon" title="epson"
                                href="<?= site_url('Historique_Passagers/pdfepsonbagesc/'.$this->session->company->ekey.'/'.$itemesc->id_bagageesc.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                <i class="fas fa-print"></i>
                            </a>&nbsp;&nbsp;&nbsp;</span>
                        </td>
                        <td>
                            <span><?= $itemesc->codebagesc;?></span>
                        </td>
                        <td>
                            <span>Nom:<?= $itemesc->nom_client;?><br></span>
                            <span>Prénom:<?= $itemesc->prenom_client; ?><br></span>
                            <span>Contact:<?= $itemesc->contact_client;?>
                        </td>

                        <td>
                            <span>Départ:<?= $itemesc->date_createesc; ?></span><br>
                            <span>Heure:<?= $itemesc->heure;?></span><br>
                            <span>Axe:<?= $itemesc->nom_ligne;?> <?= $itemesc->quartier_escal;?></span>
                        </td>
                        <td>
                            <span><?= $itemesc->contenubagageesc;?></span>
                        </td>
                        <td>
                            <span><?= $itemesc->prix_bagageesc; ?></span>
                        </td>
                        <td>
                            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '17'): ?>
                            <a class="icon" title="epson"
                                href="<?= site_url('Historique_Passagers/pdfepsonbagesc/'.$this->session->company->ekey.'/'.$itemesc->id_bagageesc.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                <i class="fas fa-print"></i>
                            </a>
                            <? endif; ?>
                            &nbsp;&nbsp;&nbsp;
                            <? if ($this->session->agent->userole === '1'): ?>
                            <a class="icon" title="ANNULER RECU"
                                href="<?= site_url('Confirmation/annuleesc/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$itemesc->id_bagageesc.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$itemesc->annulebagesc); ?>">
                                <i class="fas fa-trash-alt text-warning"></i>
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="<?= "#?{$itemesc->id_bagageesc}&bagage={$itemesc->annulebagesc}"; ?>"
                                    title="prix" class="md-trigger" data-modal="edite-<?= $itemesc->id_bagageesc; ?>">
                                <i class="fas fa-edit text-warning"></i>
                            </a>&nbsp;
                            
                            <?endif;?>

                            <div
                                class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="edite-<?= $itemesc->id_bagageesc;?>" style="perspective: none;">

                                <div class="modal-content">

                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION PRIX</h3>
                                        <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                                    </div>

                                    <?= form_open('Confirmation/updateprixesc/' . $this->session->company->ekey .'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$itemesc->id_bagageesc, array('class' => 'modal-body form')); ?>

                                    <div class="row">
                                        <div class="form-group col-sm-4">
                                            <label>Prix</label>
                                            <input class="form-control form-control-sm" type="text"
                                            name="prixbagageesc"
                                            value="<?= $itemesc->prix_bagageesc; ?>"
                                            placeholder="<?= $itemesc->prix_bagageesc; ?>"/>
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
                        </td>
                <? endforeach; ?>        
                </tbody>
                
            </table>
            
        </div>
                
    </div>
</div>