<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (!isset($historiquesconfirme) || !is_array($historiquesconfirme)) {
    $historiquesconfirme = array();
}
if (!isset($__conf_print_mode)) {
    $__conf_print_mode = 'direct';
}
?>
                    <? foreach ($historiquesconfirme as $item): ?>
                        <tr>
                            <?php if ($__conf_print_mode === 'transit'): ?>
                            <td>
                                <span class="badge badge-info">Jambe <?= (int) (isset($item->num_jambe) ? $item->num_jambe : 1); ?>/<?= (int) (isset($item->nbr_jambes) ? $item->nbr_jambes : 1); ?></span>
                            </td>
                            <?php endif; ?>
                            <td>
                                <span><?= $item->num_siege_categorie; ?></span>
                            </td>
                            <td>
                                <span><?= $item->tamponcod; ?>/<?= $item->code_ticket; ?></span>
                            </td>
                            <td>
                                <span>Nom:<?= $item->nom_client; ?><br></span>
                                <span>Prénom:<?= $item->prenom_client; ?><br></span>
                                <span>Contact:<?= $item->contact_client; ?>
                            </td>
                            <td>
                                <span>Cni ou passport:<?= $item->num_CNIB; ?></span><br>
                                <span>Délivrée le:<?= $item->date_delivre; ?></span>
                                <span>Lieu:<?= $item->lieu_delivre; ?></span>
                            </td>
                            <td>
                                <span>Départ:<?= $item->date_progr; ?></span><br>
                                <span>Heure:<?= $item->heure; ?></span><br>
                                <span>Axe:<?= ticket_axe_label($item); ?> <?= $item->quart; ?></span>
                            </td>
                            <td>
                                <span>0</span>
                            </td>
                            <td>
                               <a class="icon" title="Réimprimer Epson"
                                    href="<?= site_url('Historique_Passagers/printep_conf/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->typetarif. '/' . $item->id_ligneheure.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                    <i class="fas fa-print text-success"></i>
                                </a>&nbsp;
                                    <a class="icon" title="ANNULER SIEGE"
                                        href="<?= site_url('Historique_Passagers/suprimeconf/' . $this->session->company->ekey . '/' .$item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>">
                                        <i class="fas fa-trash-alt text-warning"></i>
                                    </a>&nbsp;

                                    <a href="<?= "#?{$item->id_client_pass}&client={$item->prenom_client}"; ?>"
                                       class="md-trigger" data-modal="edit-conf-<?= $item->code_passager; ?>">
                                        <span class="fas fa-edit text-warning"></span>
                                    </a>
                                    <? if ($this->session->agent->userole === '1'): ?>

                                        <a class="icon" title="SUPPRIMER TICKET"
                                            href="<?= site_url('Historique_Passagers/supprimerticketconf/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </a>&nbsp;
                                    <?endif;?>
                                <div
                                    class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="edit-conf-<?= $item->code_passager; ?>" style="perspective: none;">

                                    <div class="modal-content">

                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">MODIFICATION <?=$item->nom_client;?> <?= $item->prenom_client; ?></h3>
                                            <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span></button>
                                        </div>

                                        <?= form_open('Historique_Passagers/updateconf/' . $this->session->company->ekey.'/'. $item->id_client_pass.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare, array('class' => 'modal-body form')); ?>

                                        <div class="row">
                                            <div class="form-group col-sm-4">
                                                <label>Conctact</label>
                                                <input class="form-control form-control-sm" type="text"
                                                        name="rclient_contact"
                                                        value="<?= $item->contact_client; ?>"
                                                        placeholder="<?= $item->contact_client; ?>"/>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>Nom</label>
                                                <input class="form-control form-control-sm" type="text"
                                                        name="rclient"
                                                        autocomplete="off"
                                                        value="<?= $item->nom_client; ?>"
                                                        placeholder="<?= $item->nom_client; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>Prénom</label>
                                                <input class="form-control form-control-sm" type="text"
                                                        name="prclient"
                                                        autocomplete="off"
                                                        value="<?= $item->prenom_client; ?>"
                                                        placeholder="<?= $item->prenom_client; ?>">
                                            </div>
                                    
                                            <div class="form-group col-sm-4">
                                                <label>Cni ou Passport</label>
                                                <input class="form-control form-control-sm" type="text"
                                                        name="cnib" autocomplete="off"
                                                        value="<?= $item->num_CNIB; ?>"
                                                        placeholder="<?= $item->num_CNIB; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>Délivré(e) le</label>
                                                <input class="form-control form-control-sm" type="date"
                                                        name="date_cnib"
                                                        value="<?= $item->date_delivre; ?>"
                                                        placeholder="<?= $item->date_delivre; ?>">
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>Lieu</label>
                                                <input class="form-control form-control-sm" type="text"
                                                        name="lieu" 
                                                        autocomplete="off"
                                                        value="<?= $item->lieu_delivre; ?>"
                                                        placeholder="<?= $item->lieu_delivre; ?>">
                                            </div>
                                            <?= historique_modif_ticket_motif_fields_html('conf_' . $item->code_passager); ?>

                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-secondary modal-close" type="button"
                                                    data-dismiss="modal">
                                                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                            </button>
                                            <button class="btn btn-success" type="submit">
                                                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                            </button>
                                        </div>

                                        <?= form_close(); ?>

                                    </div>

                                </div>
                            </td>

                        </tr>
                        
                    <? endforeach; ?>
