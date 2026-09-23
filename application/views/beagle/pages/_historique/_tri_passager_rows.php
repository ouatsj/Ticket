<?php defined('BASEPATH') OR exit('No direct script access allowed');
if (!isset($historiques) || !is_array($historiques)) { $historiques = array(); }
if (!isset($__tri_print_mode)) { $__tri_print_mode = 'direct'; }
if (!isset($__peut_repositionner)) { $__peut_repositionner = false; }
?>
                    <? foreach ($historiques as $item): ?>
                        <?php
                        $__search = strtolower(trim(implode(' ', array(
                            isset($item->num_siege_categorie) ? $item->num_siege_categorie : '',
                            isset($item->tamponcod) ? $item->tamponcod : '',
                            isset($item->code_ticket) ? $item->code_ticket : '',
                            isset($item->tamponcodtr) ? $item->tamponcodtr : '',
                            isset($item->nom_client) ? $item->nom_client : '',
                            isset($item->prenom_client) ? $item->prenom_client : '',
                            isset($item->contact_client) ? $item->contact_client : '',
                            isset($item->num_CNIB) ? $item->num_CNIB : '',
                            isset($item->date_progr) ? $item->date_progr : '',
                            isset($item->heure) ? $item->heure : '',
                            function_exists('ticket_axe_label') ? ticket_axe_label($item) : '',
                            isset($item->quart) ? $item->quart : '',
                            isset($item->prixvente) ? $item->prixvente : '',
                        ))));
                        ?>
                        <tr data-search="<?= htmlspecialchars($__search, ENT_QUOTES, 'UTF-8'); ?>">
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
                                    <?php
                                    // Impression globale une fois par voyage (1ʳᵉ jambe visible).
                                    // Si une seule jambe de la gare est listée, elle porte num_jambe local = 1.
                                    $__tr_code = isset($item->tamponcodtr) ? trim((string) $item->tamponcodtr) : '';
                                    $__show_print_tr = ($__tr_code !== '')
                                        && ((int) (isset($item->num_jambe) ? $item->num_jambe : 1) === 1);
                                    ?>
                                    <?php if ($__show_print_tr): ?>
                                    <?php if ($item->prixretour === null): ?>
                                    <a class="icon" title="Imprimer tous les tickets du transit"
                                        href="<?= site_url('Historique_Passagers/reditpdfepson/' . $this->session->company->ekey . '/' . rawurlencode($__tr_code) .'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print text-success"></i>
                                    </a>&nbsp;
                                    <?php else: ?>
                                    <a class="icon" title="Imprimer tous les tickets A/R du transit"
                                        href="<?= site_url('Historique_Passagers/repsonalretour/' . $this->session->company->ekey . '/' . rawurlencode($__tr_code) .'/'. $bus_stop->idengare.'/'. $conex->roleattribut .'/'. $bus_stop->idsousgare); ?>">
                                        <i class="fas fa-print text-success"></i>
                                    </a>&nbsp;
                                    <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (isset($this->session->agent->userole) && (string) $this->session->agent->userole === '1'): ?>
                                <?php
                                    $__axe = function_exists('ticket_axe_label') ? ticket_axe_label($item) : (isset($item->nom_ligne) ? $item->nom_ligne : '');
                                    $__arrivee = !empty($item->nom_dest_vente)
                                        ? $item->nom_dest_vente
                                        : (isset($item->nom_gadest) ? $item->nom_gadest : '');
                                ?>
                                <a href="#"
                                   class="btn btn-sm btn-outline-primary py-0 px-2 align-middle mr-1 updatedticket md-trigger"
                                   title="Modifier le ticket<?= !empty($item->num_jambe) ? ' (jambe ' . (int) $item->num_jambe . ')' : ''; ?>"
                                   data-modal="modif-admin-0"
                                   data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                   data-id_client="<?= $item->id_client; ?>"
                                   data-tamponcod="<?= $item->tamponcod; ?>"
                                   data-passagecod="<?= $item->code_passager; ?>"
                                   data-cdligneh="<?= $item->id_ligneheure; ?>"
                                   data-ticketcod="<?= $item->code_ticket; ?>"
                                   data-ticketcodnp="<?= isset($item->codeticket) ? $item->codeticket : ''; ?>"
                                   data-nom="<?= htmlspecialchars((string) $item->nom_client, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-prenom="<?= htmlspecialchars((string) $item->prenom_client, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-type="<?= $item->type_client; ?>"
                                   data-contact="<?= htmlspecialchars((string) $item->contact_client, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-cni="<?= htmlspecialchars((string) $item->num_CNIB, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-cnideliver="<?= $item->date_delivre; ?>"
                                   data-cnideliverzone="<?= htmlspecialchars((string) $item->lieu_delivre, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-siege="<?= $item->num_siege_categorie; ?>"
                                   data-numcat="<?= isset($item->num_cat) ? $item->num_cat : ''; ?>"
                                   data-codepro="<?= $item->code_pro; ?>"
                                   data-dateprogr="<?= htmlspecialchars((string) $item->date_progr, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-heure="<?= htmlspecialchars((string) $item->heure, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-quartier="<?= htmlspecialchars((string) $item->quart, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-ancdepart="<?= $item->ligne_id; ?>"
                                   data-codticket="<?= $item->code_ticket; ?>"
                                   data-departsousg="<?= $item->departclient_idgare; ?>"
                                   data-prix="<?= htmlspecialchars((string) $item->prixvente, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-axe="<?= htmlspecialchars((string) $__axe, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-arrivee="<?= htmlspecialchars((string) $__arrivee, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-jambe="<?= isset($item->num_jambe) ? (int) $item->num_jambe : 1; ?>">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <?php endif; ?>
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
                            </td>
                        </tr>
                    
                    <? endforeach; ?>
