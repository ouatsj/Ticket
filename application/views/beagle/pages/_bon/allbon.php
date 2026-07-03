<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p>
        <a href="<?= site_url('bon_millitaire/etatbons/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'. $bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
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

                <div class="title">Les bons millitaire</div>

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
                                <span><?= $item->date_bon;?></span>
                            </td>
                            <td>
                                <span><?= $item->idbon;?>/<?= $item->bonsecondid;?></span>
                            </td>
                            <td>
                                <span><?= $item->code_bon;?></span>
                            </td>
                            <td>
                                <span><?= $item->nom_gaep;?>-<?= $item->nom_gadest;?></span>
                            </td>
                            <td>
                                <span>Nom:<?= $item->nom_client;?><br></span>
                                <span>Prénom:<?= $item->prenom_client; ?></span>
                                </span>
                            </td>
                            
                            <td>
                                <span>Cni ou carte:<?= $item->num_CNIB;?></span><br>
                                <span>Délivrée le:<?= $item->date_delivre;?></span>
                                <span>Lieu:<?= $item->lieu_delivre;?></span>
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
                                    href="<?= site_url('Ticket/printbon/' . $this->session->company->ekey . '/' . $item->idbon); ?>">
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