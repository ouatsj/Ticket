<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p>
        <a href="<?= site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="form-tri-bon" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-warning"></i>&nbsp;TRI ETAT BON&nbsp;
        </button>

        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="form-tri-bons" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-success"></i>&nbsp;TRI ETAT BON&nbsp;
        </button>

        <button class="btn btn-space btn-secondary md-trigger"
                data-modal="edit-factbon" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
            <i class="fas fa-edit text-info"></i>&nbsp;EDITER FACTURE DES BONS MILLITAIRE&nbsp;
        </button>    
    </p>
</div>
<div class="row">

    <!-- Liste des BONS -->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Les bons millitaire du <?= utf8_encode(strftime("%d %b %G", now())); ?></div>

            </div>
            <div class="card-body">
                <table class="table table-striped table-borderless" id="table1">
                    <thead>
                    <tr>
                        <th>Date du bon</th>
                        <th>N° bon</th>
                        <th>Code bon</th>
                        <th>Trajet</th>
                        <th>Nom et Prenom</th>
                        <th>N° cni ou carte / Date / Lieu</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($bonmillitaires as $item): ?>

                        <tr>
                            <td>
                                <span><?= $item->date_bon; ?></span>
                            </td>
                            <td>
                                <span><?= $item->idbon;?>/<?= $item->bonsecondid;?></span>
                            </td>

                            <td>
                                <span><?= $item->code_bon; ?></span>
                            </td>
                            <td>
                                <span><?= $item->nom_gaep;?>-<?= $item->nom_gadest;?></span>
                            </td>
                            <td>
                                <span>Nom:<?= $item->nom_client; ?><br></span>
                                <span>Prénom:<?= $item->prenom_client; ?></span>
                                </span>
                            </td>
                            

                            <td>
                                <span>Cni ou carte:<?= $item->num_CNIB; ?></span><br>
                                <span>Délivrée le:<?= $item->date_delivre; ?></span>
                                <span>Lieu:<?= $item->lieu_delivre; ?></span>
                            </td>

                            <td>
                                <span>Contact:<?= $item->contact_client; ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= "#?{$item->idbon}&&&"; ?>"
                                    class="md-trigger" data-modal="bon-edit-<?= $item->idbon; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                </a>

                                <a class="icon" title="reimpression"
                                    href="<?= site_url('Ticket/printbon/'.$this->session->company->ekey.'/'.$item->idbon); ?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;
                                
                            </td>
                        </tr>
                        
                        <? endforeach; ?>
                        </tbody>
                    
                    </table>
                
                </div>
                    
            </div>
        </div>
    </div>
</div>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
    id="form-tri-bon" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="">ETAT BON MILLITAIRE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Rapport/bon/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
            <div class="form-group col-sm-4">
                <label>Date: du</label>
                <input class="form-control form-control-sm" type="date" name="debutdate">
            </div>
            <div class="form-group col-sm-4">
                <label>au</label>
                <input class="form-control form-control-sm" type="date" name="findate">
            </div>
        </div>
        <div class="form-group row">
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success md-trigger" type="submit"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
    id="form-tri-bons" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="">ETAT BON MILLITAIRE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("bon_millitaire/historique/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
            <div class="form-group col-sm-4">
                <label>Date: du</label>
                <input class="form-control form-control-sm" type="date" name="debutdates">
            </div>
            <div class="form-group col-sm-4">
                <label>au</label>
                <input class="form-control form-control-sm" type="date" name="findates">
            </div>
        </div>
        <div class="form-group row">
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success md-trigger" type="submit"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>
 
 <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
    id="edit-factbon" style="perspective: none;">
    
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="">ETATABLIR FACTURE BON MILLITAIRE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("Rapport/factedit/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>
        <div class="form-group row">
            <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
                <div class="form-group col-sm-4">
                    <label>OBJET</label>
                    <textarea class="form-control form-control-sm"
                        name="objets" autocomplete="off"
                        cols="30" rows="2"></textarea>
                </div>
                <div class="form-group col-sm-4">
                    <label>Du</label>
                    <input class="form-control form-control-sm" type="date" name="datedebut">
                </div>
                <div class="form-group col-sm-4">
                    <label>Au</label>
                    <input class="form-control form-control-sm" type="date" name="datefin">
                </div>
        </div>
        <div class="form-group row">
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success md-trigger" type="submit"
                    data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>   