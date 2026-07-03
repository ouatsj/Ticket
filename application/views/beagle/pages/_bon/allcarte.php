<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p>
        <a href="<?= site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
        
    </p>
</div>
<div class="row">

    <!-- Liste des cartes -->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                
            </div>
            <div class="card-body">
                <table class="table table-striped table-borderless" id="table1">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>N° carte</th>
                            <th>Credite</th>
                            <th>Date delivre et expire</th>
                            <th>Nom et Prenom</th>
                            <th>N° cnib/ Date / Lieu</th>
                            <th>Contact</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($cartesvoyage as $item): ?>

                        <tr>
                            <td>
                                <span><?= $item->dateinsert; ?></span>
                            </td>
                            <td>
                                <span><?= $item->num_carte; ?></span>
                            </td>
                            <td>
                                <span><?= number_format($item->creditecompte, 0, '', ' ');?></span>
                            </td>
                            <td>
                                <span><?= $item->date_valide;?><br>
                                <?= $item->date_expire;?>
                                </span>
                            </td>
                            <td>
                                <span>Nom:<?= $item->nom_client;?><br></span>
                                <span>Prénom:<?= $item->prenom_client; ?></span>
                                </span>
                            </td>
                            

                            <td>
                                <span>Cnib:<?= $item->num_CNIB;?></span><br>
                                <span>Délivrée le:<?= $item->date_delivre;?></span>
                                <span>Lieu:<?= $item->lieu_delivre;?></span>
                            </td>

                            <td>
                                <span>Contact:<?= $item->contact_client;?>
                                </span>
                            </td>
                            <td>
                            <a href="<?= "#?{$item->id_carte}&&&"; ?>" class="md-trigger" data-modal="carte-<?= $item->id_carte; ?>">
                                <span class="fas fa-edit text-warning"></span>
                            </a>
                            <a class="icon" title="reimpression"
                                href="<?= site_url('Ticket/printcv/'.$this->session->company->ekey.'/'.$item->id_carte);?>">
                                <i class="fas fa-print"></i>
                            </a>&nbsp;

                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                            id="carte-<?= $item->id_carte; ?>" style="perspective: none;">

                            <div class="modal-content">

                                <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">MODIFICATION COMPTE SUR DE <?= $item->nom_client; ?> <?= $item->prenom_client;?></h3>
                                <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span></button>
                                </div>
                                        
                                <?= form_open('Cartes_Voyage/upcarte/' .$this->session->company->ekey.'/'.$item->id_carte.'/'.$item->comptidcl, array('class' => 'modal-body form')); ?>
                                        
                                    <div class="form-group col-sm-4">
                                        <label>Montant Crédité</label>
                                        <input class="form-control form-control-sm" type="number" name="prixcarte"      autocomplete="off"
                                        value="<?= $item->creditecompte; ?>">
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