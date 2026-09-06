<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (!isset($historiques) || !is_array($historiques)) { $historiques = array(); }
if (!isset($__tri_print_mode)) { $__tri_print_mode = 'direct'; }
if (!isset($__peut_repositionner)) { $__peut_repositionner = false; }
?>
                    <? foreach ($historiques as $item): ?>

                        <tr>
                            <?php if (($__tri_print_mode ?? 'direct') === 'transit'): ?>
                            <td>
                                <span class="badge badge-info">Jambe <?= (int) (isset($item->num_jambe) ? $item->num_jambe : 1); ?>/<?= (int) (isset($item->nbr_jambes) ? $item->nbr_jambes : 1); ?></span>
                            </td>
                            <?php endif; ?>
                            <td>
                                <span><?= $item->num_siege_categorie; ?></span>
                            </td>

                            <td>
                                <span><?= $item->tamponcod; ?>/<?= $item->code_ticket; ?></span><br>
								
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
                                <span><?= number_format($item->prixvente, 0, '', ' '); ?></span>
                            </td>
                            <td>
                                <?php if ($__peut_repositionner): ?>
                                    <a class="icon" title="Repositionner cette ligne seule pour réimpression guichet (1 fois)"
                                       href="<?= site_url('Historique_Passagers/repositionner/' . $this->session->company->ekey . '/' . rawurlencode($item->code_passager) . '/' . $bus_stop->idengare . '/' . $conex->roleattribut . '/' . (isset($item->departclient_idgare) && $item->departclient_idgare ? $item->departclient_idgare : $bus_stop->idsousgare)); ?>"
                                       onclick="return confirm('Repositionner UNIQUEMENT cette ligne (siège <?= htmlspecialchars((string) $item->num_siege_categorie); ?> / <?= htmlspecialchars((string) $item->code_ticket); ?>) pour une impression unique au guichet (bouton TICKET) ?');">
                                        <i class="fas fa-redo text-danger"></i>
                                    </a>&nbsp;
                                <?php endif; ?>
                                <?php if (($__tri_print_mode ?? 'direct') === 'direct'): ?>
                                    <?php if ($item->prixretour === null): ?>
                                    <a class="icon" title="Imprimer le ticket"
                                        href="<?= site_url('Historique_Passagers/editpdfepson/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->typetarif. '/' . $item->id_ligneheure.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print"></i>
                                    </a>&nbsp;
                                    <?php else: ?>
                                    <a class="icon" title="Imprimer le ticket A/R"
                                        href="<?= site_url('Historique_Passagers/epsonalretour/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->typetarif. '/' . $item->tamponcod. '/' . $item->id_ligneheure.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print"></i>
                                    </a>&nbsp;
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ((int) (isset($item->num_jambe) ? $item->num_jambe : 1) === 1): ?>
                                    <?php if ($item->prixretour === null): ?>
                                    <a class="icon" title="Imprimer tous les tickets du transit"
                                        href="<?= site_url('Historique_Passagers/reditpdfepson/' . $this->session->company->ekey . '/' . $item->tamponcodtr.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print text-success"></i>
                                    </a>&nbsp;
                                    <?php else: ?>
                                    <a class="icon" title="Imprimer tous les tickets A/R du transit"
                                        href="<?= site_url('Historique_Passagers/repsonalretour/' . $this->session->company->ekey . '/' . $item->tamponcodtr.'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print text-success"></i>
                                    </a>&nbsp;
                                    <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <a href="<?= "#?{$item->id_client}&&{$item->nom_client}"; ?>"
                                       data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                        data-id_client="<?= $item->id_client; ?>"
                                        data-tamponcod="<?= $item->tamponcod; ?>"
                                        data-passagecod="<?= $item->code_passager; ?>"
                                        data-cdligneh="<?= $item->id_ligneheure; ?>"
                                        data-ticketcod="<?= $item->code_ticket; ?>"
                                        data-ticketcodnp="<?= isset($item->codeticket) ? $item->codeticket : ''; ?>"
                                        data-nom="<?= $item->nom_client; ?>"
                                        data-prenom="<?= $item->prenom_client; ?>"
                                        data-type="<?= $item->type_client; ?>"
                                        data-contact="<?= $item->contact_client; ?>"
                                        data-cni="<?= $item->num_CNIB; ?>"
                                        data-cnideliver="<?= $item->date_delivre; ?>"
                                        data-cnideliverzone="<?= $item->lieu_delivre; ?>"
                                        class="updateticket md-trigger" title="MODIFIER INFOS CLIENT"
                                        data-modal="ticket-0">&nbsp;
                                        <span class="fas fa-edit text-warning"></span>
                                </a>&nbsp;
                                            
                                <a href="#" class="updatedticket md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                    data-siege="<?= $item->num_siege_categorie; ?>"
                                    data-codepro="<?= $item->code_pro; ?>"
                                    data-nom="<?= $item->nom_client; ?>"
                                    data-ancdepart="<?= $item->ligne_id; ?>"
                                    data-codticket="<?= $item->code_ticket; ?>"
                                    data-departsousg="<?= $item->departclient_idgare; ?>"
                                    data-passagecod="<?= $item->code_passager; ?>" title="MODIFIER DEPART"
                                    data-modal="updepart-0">
                                    <i class="fas fa-edit text-success"></i>
                                </a>&nbsp;
                                <a href="#" class="md-trigger motif-action"
                                    title="DESACTIVER TICKET"
                                    data-modal="motif-action-0"
                                    data-action="<?= site_url('Historique_Passagers/desactivecode/' . $this->session->company->ekey . '/' . $item->tamponcod. '/' . $item->is_activecode.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                                    data-title="Désactiver / réactiver le code ticket">
                                    <i class="fas fa-trash-alt text-danger"></i>
                                </a>&nbsp;
                                <a href="#" class="md-trigger motif-action"
                                    title="ANNULER SIEGE"
                                    data-modal="motif-action-0"
                                    data-action="<?= site_url('Historique_Passagers/suprime/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                                    data-title="Annuler le siège du ticket">
                                    <i class="fas fa-trash-alt text-warning"></i>
                                </a>&nbsp;
                                <? if ($this->session->agent->userole === '1'): ?>

                                    <a class="icon" title="SUPPRIMER TICKET"
                                        href="<?= site_url('Historique_Passagers/supprimerticket/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </a>&nbsp;
                                <?endif;?>
                                <? if (super_admin_can('sales.price.free')): ?>

                                    <a href="<?= "#?{$item->id_client_pass}&client={$item->prenom_client}"; ?>"
                                            title="prix" class="md-trigger" data-modal="edit-<?= $item->code_passager; ?>">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>&nbsp;
                                    
                                <?endif;?>
                                        <a href="<?= "#?{$item->id_client_pass}&client={$item->prenom_client}"; ?>"
                                            title="gare quartier" class="md-trigger" data-modal="edit-<?= $item->quart; ?>">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>&nbsp;
                                        <div
                                            class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="edit-<?= $item->code_passager; ?>" style="perspective: none;">

                                            <div class="modal-content">

                                                <div class="modal-header modal-header-colored">
                                                    <h3 class="modal-title">MODIFICATION <?=$item->nom_client;?> <?= $item->prenom_client; ?></h3>
                                                    <button class="close modal-close" type="button"
                                                            data-dismiss="modal" aria-hidden="true"><span
                                                            class="mdi mdi-close text-white"></span></button>
                                                </div>

                                                <?= form_open('Historique_Passagers/updateticket/' . $this->session->company->ekey . '/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$item->codeticket, array('class' => 'modal-body form')); ?>

                                                <div class="row">
                                                    <div class="form-group col-sm-4">
                                                        <label>Prix</label>
                                                        <input class="form-control form-control-sm" type="number" min="0" step="0.01"
                                                        name="prixticket"
                                                        value="<?= $item->prixvente; ?>"
                                                        placeholder="<?= $item->prixvente; ?>"/>
                                                    </div>
                                                    <?= historique_modif_ticket_motif_fields_html('prix_' . $item->code_passager); ?>
                                                    <?php if (function_exists('sales_price_controls_enabled') && sales_price_controls_enabled()): ?>
<div class="form-group col-sm-12">
                                                        <label>
                                                            <input type="checkbox" name="confirmation_zero" value="1">
                                                            Je confirme une éventuelle modification à 0 F
                                                        </label>
                                                    </div>
                                                    <?php endif; ?>
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
                                    <div
                                        class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="edit-<?= $item->quart; ?>" style="perspective: none;">

                                        <div class="modal-content">

                                                <div class="modal-header modal-header-colored">
                                                <h3 class="modal-title">MODIFICATION GARE OU QUARTIER DE <?=$item->nom_client;?> <?= $item->prenom_client; ?></h3>
                                                <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span></button>
                                                </div>

                                                <?= form_open('Historique_Passagers/upgarequart/'.$this->session->company->ekey.'/' . $item->code_passager.'/'.$item->code_ticket.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare, array('class' => 'modal-body form')); ?>

                                            <div class="row">
                                                    
                                            <div class="form-group col-sm-4">
                                                <label>Sousgare</label>
                                                <select class="form-control form-control-sm" name="deparsousgareidentifs">
                                                <option value="<?= $item->departclient_idgare; ?>"><?= $item->nomsousgare; ?></option>
                                                <? foreach ($garedeparts as $garedepart): ?>                          <option value="<?= $garedepart->idsousgare;?>">
                                                <?= $garedepart->nom_gaep;?>/<?= $garedepart->nomsousgare;?>
                                                </option>
                                                <? endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label>QUARTIER</label>
                                                <? $cid=$this->session->company->ekey;
                                                        $quartiers = $this->db->query("SELECT * FROM quartier q
                                                            JOIN ville v ON q.id_ville_qua = v.id_ville
                                                            JOIN gare_dest ga ON ga.id_villega = v.id_ville
                                                            JOIN lignes lg ON lg.gadest_lg = ga.code_gadest
                                                            JOIN compagnies c ON ga.id_compaga = c.cle_compagnie
                                                            JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                                            WHERE e.ekey = '$cid'
                                                            AND lg.ident_ligne = '$item->ident_ligne'"
                                                            )->result();?>
                                                    <select class="form-control form-control-sm" name="idquarts">
                                                    <option value="<?= $item->quart; ?>"><?= $item->quart; ?></option>
                                                    <? foreach ($quartiers as $qrt): ?>
                                                    <option value="<?= $qrt->nom_quartier; ?>">
                                                    <?= $qrt->nom_quartier;?>
                                                        </option>
                                                    <? endforeach; ?>
                                                        </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <?= historique_modif_ticket_motif_fields_html('gq_' . $item->code_passager); ?>
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
