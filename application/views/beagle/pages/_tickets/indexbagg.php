<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <?php $this->load->view('_partials/btn_retour', array(
            'fallback' => retour_bagage_mobile_url(
                $this->session->company->ekey,
                $conex->roleattribut,
                $bus_stop->idengare,
                $bus_stop->idsousgare
            ),
        )); ?>
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
            <div class="title">bagages avec ticket</div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-borderless" id="table1">
                <thead>
                <tr>
                    <th></th>
                    <th>N° reçu</th>
                    <th>Code</th>
                    <th>Client / Contact</th>
                    <th>Départ / Axe</th>
                    <th>Contenu</th>
                    <th>Prix</th>
                    <th>Action</th>
                </tr>
                </thead>

                <tbody class="no-border-x">
                <? foreach ($bagages as $item): ?>

                    <tr>
                        
                        <td>
                            <span>
                            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '10' OR $this->session->agent->userole === '12'): ?>
                                <a class="icon" title="epson"
                                href="<?= site_url('Historique_Passagers/bagsave/'.$this->session->company->ekey.'/'.$item->id_bagage.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                <i class="fas fa-print"></i>
                            </a>&nbsp;&nbsp;&nbsp;</span><? endif; ?>
                        </td>

                        <td>
                            <?php $recuParts = explode('-', (string) $item->id_bagage); $num_recu = $recuParts[0]; ?>
                            <span><?= html_escape($num_recu); ?></span>
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
                            <span><?= $item->prix_bagage; ?></span>
                        </td>
                        <td>
                            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '10' OR $this->session->agent->userole === '12'): ?>
                                <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/bagsave/'.$this->session->company->ekey.'/'.$item->id_bagage.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;&nbsp;&nbsp;
                            <? endif; ?>
                            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '6'): ?>
                                <a class="icon" title="epson"
                                href="<?= site_url('Historique_Passagers/bagsaveguich/'.$this->session->company->ekey.'/'.$item->id_bagage.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                <i class="fas fa-print"></i>
                            <? endif; ?>
                            &nbsp;&nbsp;&nbsp;
                            <? if ($this->session->agent->userole === '1'): ?>
                            <?php $this->load->view('beagle/pages/_tickets/_bagage_facture_actions', array('item' => $item, 'conex' => $conex, 'bus_stop' => $bus_stop, 'tri_bagage' => array(), 'num_recu' => $num_recu)); ?>
                            <?endif;?>
                        </td>
                <? endforeach; ?>        
                </tbody>
            </table>
        </div>   
    </div>
</div>