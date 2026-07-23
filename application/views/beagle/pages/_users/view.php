<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="tools">
        <a href="<?= site_url('utilisateurs/' . $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR 
        </a>
        <a href="<?= site_url('utilisateurs/voirprofil/'. $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-success"></i>&nbsp;VOIR LES PROFILS
        </a>

        <a href="<?= site_url('utilisateurs/voirprofilgare/'. $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-success"></i>&nbsp;VOIR LES GARES ATTRIBUER AU COMPTE
        </a>
        <a href="<?= site_url('utilisateurs/voirprofilpage/'. $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;VOIR LES PAGES ATTRIBUER AU COMPTE
        </a>
    </div>
</div>
<div class="row" id="comptes-filter-list">
    <?php if ($this->session->flashdata('compte_error')): ?>
        <div class="col-lg-12">
            <div class="alert alert-danger alert-dismissible" role="alert">
                <?= htmlspecialchars($this->session->flashdata('compte_error'), ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('compte_success')): ?>
        <div class="col-lg-12">
            <div class="alert alert-success alert-dismissible" role="alert">
                <?= htmlspecialchars($this->session->flashdata('compte_success'), ENT_QUOTES, 'UTF-8'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    <?php endif; ?>
    <div class="col-lg-12 mb-3">
        <div class="input-group input-group-sm" style="max-width: 480px;">
            <div class="input-group-prepend">
                <span class="input-group-text"><span class="mdi mdi-search"></span></span>
            </div>
            <input type="search"
                   class="form-control"
                   data-user-filter-input="comptes-filter-list"
                   data-user-filter-label="compte(s)"
                   placeholder="Rechercher un compte (nom, login, contact)…"
                   autocomplete="off">
        </div>
        <small id="comptes-filter-list-count" class="text-muted d-block mt-1"></small>
    </div>

    <? foreach ($authcompte as $item): ?>
        <?php
        $compte_st = compte_arret_compte_card_status($item);
        $compte_search = strtolower(trim(
            $item->first_name . ' ' . $item->last_name . ' '
            . $item->username . ' ' . $item->phone . ' ' . $item->email . ' '
            . $compte_st['label'] . ' ' . $compte_st['motif']
        ));
        ?>
        <div class="col-lg-3" data-user-filter-item="1"
             data-search="<?= htmlspecialchars($compte_search, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="card card-border card-contrast">

                <?php $this->load->view('beagle/pages/_users/_compte_status_badge', ['item' => $item]); ?>

                <div class="card-header card-header-contrast"><?= $item->first_name; ?> <?= $item->last_name; ?>

                    <div class="tools">
                        <a href="<?= site_url('Utilisateurs/active/' . $this->session->company->ekey . '/' . $item->cpuser_id. '/' . $item->uid. '/' . $item->activer);?> "class="btn btn-space btn-secondary">
                            <?= ($item->activer === '0') ? '<span class="icon mdi text-success">Activer</span>' : '<span
                            class="icon mdi text-danger">Désactiver</span>' ?>
                        </a>&nbsp;
                        &nbsp;
                        
                        <a href="<?= "#?{$item->uid}&name={$item->first_name}&prenom={$item->last_name}&contact={$item->phone}&email={$item->email}"; ?>"
                            class=" md-trigger" data-modal="edit-user-<?= $item->cpuser_id; ?>" title="Modifier">
                            <span class="icon mdi mdi-edit text-warning"></span>
                        </a>
                        
                        <!-- modification -->
                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                id="edit-user-<?= $item->cpuser_id; ?>" style="">
                            <div class="modal-content">
                                <div class="modal-header modal-header-colored">
                                    <h3 class="modal-title">MODIFICATION
                                        SUR <?= $item->first_name; ?> <?= $item->last_name; ?></h3>
                                    <button class="close modal-close" type="button"
                                        data-dismiss="modal"aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                                    </button>
                                </div>
                                <div class="card-body">
                                    <?= form_open('Utilisateurs/edit_/' . $this->session->company->ekey . '/' . $item->cpuser_id. '/' . $item->uid,
                                        array('class' => 'modal-body form')); ?>

                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom
                                            Utilisateur:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm" type="text" name="username"
                                                required=""
                                                value="<?= $item->username; ?>"
                                                placeholder="<?= $item->username; ?>"
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row signup-password">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Mot de
                                            passe:</label>
                                        <div class="col-12 col-sm-8 col-lg-6 row ">
                                            <div class="col-6">
                                                <input class="form-control form-control-sm" name="pass1"
                                                    id="pass1" type="password"
                                                    value=""
                                                    placeholder="Laisser vide pour conserver le mot de passe" autocomplete="new-password">

                                            </div>
                                            <div class="col-6">
                                            <input class="form-control form-control-sm" id="confirm" name="confirm"
                                                type="password"
                                                value=""
                                                placeholder="Confirmer le nouveau mot de passe" autocomplete="new-password">
                                        </div>
                                        </div>
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset"
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
                        </div>

                        
                    </div>
                </div>
                <div class="card-body">
                    <p>Nom:<?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                    <p class="text-danger"></p>
                    <p>Login: <?= htmlspecialchars($item->username, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p>Contact: <?= $item->phone; ?></p>
                    <p>Contact2: <?= $item->phone2; ?></p>
                    <p><?= ($item->is_conect === '1') ? '<span
                            class="icon mdi text-success">En ligne</span>' : '<span
                            class="icon mdi text-danger">Déconnecté</span>&nbsp;<i class="fas fa-power-off text-danger"></i>' ?></p>
                    <? if (!empty($item->derniere_activite_at)): ?>
                    <p><small class="text-muted">Dernière activité : <?= htmlspecialchars($item->derniere_activite_at, ENT_QUOTES, 'UTF-8'); ?></small></p>
                    <? endif; ?>
                    <? if (!empty($item->desactivation_motif) && (string) $item->activer !== '0'): ?>
                    <p class="text-danger"><small>Motif désactivation : <?= htmlspecialchars($item->desactivation_motif, ENT_QUOTES, 'UTF-8'); ?></small></p>
                    <? endif; ?>
                    <? if (!empty($item->autorisation_vente_forcee) && $item->autorisation_vente_forcee === '1'): ?>
                    <p class="text-warning"><span class="icon mdi mdi-shield-check"></span>
                        Dérogation vente jusqu'au <?= htmlspecialchars($item->autorisation_vente_jusquau, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <? endif; ?>
                    <? if (in_array((string) $this->session->agent->userole, ['1', '2'], true)): ?>
                        <a href="#"
                            class="btn btn-block btn-rounded text-dark bg-warning md-trigger"
                            data-modal="autorisation-vente-<?= $item->cpuser_id; ?>">
                            <span class="fas fa-shield-alt"></span> DÉROGATION VENTE (ADMIN)
                        </a>
                        <div class="modal-container colored-header colored-header-warning custom-width modal-effect-7"
                             id="autorisation-vente-<?= $item->cpuser_id; ?>" style="perspective: none;">
                            <div class="modal-content">
                                <div class="modal-header modal-header-colored">
                                    <h3 class="modal-title">Dérogation vente — <?= $item->first_name; ?> <?= $item->last_name; ?></h3>
                                    <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                                        <span class="mdi mdi-close text-white"></span>
                                    </button>
                                </div>
                                <?= form_open('Utilisateurs/autorisationvente/' . $this->session->company->ekey . '/' . $item->cpuser_id . '/' . $item->uid, ['class' => 'modal-body form']); ?>
                                <div class="form-group">
                                    <label><input type="checkbox" name="autorisation_vente_forcee" value="1"
                                        <?= (!empty($item->autorisation_vente_forcee) && $item->autorisation_vente_forcee === '1') ? 'checked' : ''; ?>>
                                        Autoriser la vente malgré les règles d'arrêt</label>
                                </div>
                                <div class="form-group">
                                    <label>Valide jusqu'au (obligatoire si coché)</label>
                                    <input class="form-control form-control-sm" type="datetime-local"
                                           name="autorisation_vente_jusquau"
                                           value="<?= !empty($item->autorisation_vente_jusquau) ? date('Y-m-d\TH:i', strtotime($item->autorisation_vente_jusquau)) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Motif</label>
                                    <input class="form-control form-control-sm" type="text" name="autorisation_vente_motif"
                                           value="<?= htmlspecialchars($item->autorisation_vente_motif ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" name="exempt_desactivation_auto" value="1"
                                        <?= (!empty($item->exempt_desactivation_auto) && $item->exempt_desactivation_auto === '1') ? 'checked' : ''; ?>>
                                        Exempt de désactivation auto (inactivité 3 jours)</label>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">Annuler</button>
                                    <button class="btn btn-success" type="submit">Enregistrer</button>
                                </div>
                                <?= form_close(); ?>
                            </div>
                        </div>
                    <? endif; ?>
                        <a href="<?= site_url('utilisateurs/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->uid.'/'
                                . $item->cpuser_id. '/garecompte/' . mdate("%d/%m/%Y", now('UTC'))); ?>" 
                            class="btn btn-block btn-rounded text-dark bg-info">
                            <span class="fas fa-edit"></span>
                            VOIR GARE ATTRIBUER AU COMPTE
                        </a>
                        
                        <a href="#"
                            class="btn btn-block btn-rounded text-dark bg-info md-trigger" data-modal="attribgare-<?= $item->cpuser_id; ?>" title="Attribuer gare">
                            <span class="fas fa-edit"></span> ATTRIBUER UNE GARE
                        </a>
                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="attribgare-<?= $item->cpuser_id; ?>" style="perspective: none;">

                            <div class="modal-content">

                                <div class="modal-header modal-header-colored">
                                    <h3 class="modal-title">ATTRIBUER UNE GARE A UN COMPTE UTILISATEUR</h3>
                                    <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                                </div>
                                <div class="card-body">
                                    
                                    <?= form_open('Utilisateurs/addprofil_/' . $this->session->company->ekey.'/'.$item->cpuser_id
                                        , array('class' => 'modal-body form')); ?>
                                    
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Gare:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <select class="form-control form-control-sm" name="gareuser" id="">
                                                <option value=""></option>
                                                <? foreach ($garees as $itemgare): ?>
                                                    <option value="<?= $itemgare->idengare; ?>">
                                                    <?= "{$itemgare->garenom}"; ?>
                                                    </option>
                                                <? endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
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
                        </div>
                        
                    </div>
                </div>

            </div>

        </div>
    <? endforeach; ?>

    <div class="col-lg-12" id="comptes-filter-list-empty" style="display: none;">
        <div class="card card-border">
            <div class="card-body text-center text-muted py-4">
                Aucun compte ne correspond à votre recherche.
            </div>
        </div>
    </div>
</div>

<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_users/view.php-->                            
