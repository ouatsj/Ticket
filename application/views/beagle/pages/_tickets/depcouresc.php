<?php defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space" data-modal="">
            <i class="fas fa-arrow-circle-left text-info"></i>
            &nbsp;RETOUR ACCUEIL&nbsp;
        </a>
        </p>
</div>
<div class="row">
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Tous les courriers envoyés</div>

            </div>

            <div class="card-body">
                
                <table class="table table-striped table-borderless" id="table1">

                    <thead>

                    <tr>

                        <th>Code</th>

                        <th>Ligne</th>

                        <th>Expéditeur / Contact</th>

                        <th>Montant</th>

                        <th>Type de courrier / Valeur</th>

                        <th>Date facturation</th>

                        <th>Date d'expédition / Heure</th>

                        <th class="actions" style="width:5%;">Actions</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($departcourriersesc as $colis): ?>

                        <tr>
                            <td>
                                <span><?= $colis->num_couresc; ?></span><br><a class="icon" title="recu" href="<?= site_url('Historiquesescal/reditpdfesc/'.$this->session->company->ekey.'/'.$colis->courrierexpidesc.'/'.$colis->departcolisesc.'/'.$colis->expditid.'/'.$colis->receptid.'/'.$colis->type_client.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;
                            </td>

                            <td>
                                <span><?= $colis->nom_ligne; ?></span>
                            </td>

                            <td>
                                <span><?= $colis->nom_client; ?>&nbsp;<?= $colis->prenom_client; ?></span>
                                <span><?= $colis->contact_client; ?></span><br></span>
                            </td>

                            <td>
                                <span><?= number_format($colis->prixcolisesc, 0, '', ' '); ?> F</span>
                            </td>
                            <td>
                                <span><?= $colis->nombrecolis; ?><?= $colis->naturecoli; ?></span> <?= $colis->naturecourrieresc;?><br>
                                <span><?= $colis->valeurscoli; ?></span>
                            </td>

                            <td>
                                <span><?= utf8_encode(strftime("%d %b %G", strtotime($colis->dateenvoiesc))); ?></span>
                            </td>
                            <td>
                                <span><?= utf8_encode(strftime("%d %b %G", strtotime($colis->dateenvoiesc))); ?></span><br>
                                <span><?= $colis->heure; ?></span>
                            </td>

                            <td>
                                <a href="<?= "#?{$colis->courrierexpidesc}&client={$colis->prenom_client}"; ?>"
                                        title="prix" class="md-trigger" data-modal="editesc-<?= $colis->courrierexpidesc; ?>">
                                    <i class="fas fa-edit text-success"></i>
                                </a>&nbsp;
                                <div
                                    class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="editesc-<?= $colis->courrierexpidesc; ?>" style="perspective: none;">

                                    <div class="modal-content">

                                        <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION</h3>
                                            <button class="close modal-close" type="button"
                                            data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                                        </div>

                                            <?= form_open('Confirmation/updateprixesce/' . $this->session->company->ekey . '/' . $colis->courrierexpidesc.'/'.$colis->num_couresc.'/'.$colis->departcolisesc, array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                            <input class="form-control form-control-sm" type="hidden" name="gareattribuer" value="<?=$bus_stop->idengare;?>">
        
                                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                            <input class="form-control form-control-sm" type="hidden" name="user_names" value="<?=$conex->roleattribut;?>">
                                            <div class="form-group col-sm-4">
                                            <label>Prix</label>
                                            <input class="form-control form-control-sm" type="text" name="prixcoliesc"
                                            value="<?= $colis->prixcolisesc; ?>" placeholder="<?= $colis->prixcolisesc; ?>"/>
                                            </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                                                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                            </button>
                                            <button class="btn btn-success modal-close" type="submit" data-dismiss="modal">
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
<!--End of file: depcoursesc.php-->
<!--File location: application/views/beagle/pages/_tickets/depcoursesc.php-->