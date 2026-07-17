<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$users_zone_tabs_enabled = (bool) $this->config->item('users_zone_tabs_enabled');
?>

<div class="row" id="users-filter-list">
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
    <? if (!empty($authusers)): ?>
        <div class="col-lg-12">
            <div class="card">

                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-7 col-lg-8 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><span class="mdi mdi-search"></span></span>
                                </div>
                                <input type="search"
                                       class="form-control"
                                       id="user-search-input"
                                       data-user-filter-input="users-filter-list"
                                       data-user-filter-label="utilisateur(s)"
                                       placeholder="Rechercher (nom, prénom, contact, email)…"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <small id="users-filter-list-count" class="text-muted"></small>
                        </div>
                        <div class="col-md-2 text-md-right tools">
                            <button class="btn btn-space btn-info md-trigger" data-modal="add-new-user">
                                <span class="icon mdi mdi-plus-1 text-white"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body"></div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="add-new-user" style="perspective: none;">

                    <div class="modal-content">

                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">AJOUTER UN UTILISATEUR</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span></button>
                        </div>
                        <?= form_open('Utilisateurs/adduse/' . $this->session->company->ekey, array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm" type="text"
                                       name="firstname" required=""
                                       placeholder="Nom"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Prénom:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm" type="text"
                                       name="lastname" required=""
                                       placeholder="Prénom"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm"
                                       type="tel" name="phone" required=""
                                       placeholder="Numéro de téléphone"
                                       autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact2:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm"
                                        type="tel" name="phone2" required=""
                                        autocomplete="off">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Email:</label>
                            <div class="col-12 col-sm-8 col-lg-6">
                                <input class="form-control form-control-sm" type="text"
                                       name="email" required=""
                                       placeholder="prenom@rakieta.com"
                                       autocomplete="off">
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

        <?php if ($users_zone_tabs_enabled): ?>
            <div class="col-lg-12 mb-3">
                <ul class="nav nav-tabs" data-user-zone-tabs="users-filter-list" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" data-user-zone="bobo">
                            Zone Bobo-Dioulasso
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-user-zone="banfora">
                            Zone Banfora
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-user-zone="ouagadougou">
                            Zone Ouagadougou
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-user-zone="disabled">
                            Toutes les gares désactivées
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" data-user-zone="multiple">
                            Plusieurs zones actives
                        </button>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
        
        <? foreach ($authusers as $item): ?>
            <?php
            $compte_st = compte_arret_compte_card_status($item);
            $user_zones = array();
            $gare_ids = array_filter(explode(',', (string) ($item->active_gare_ids ?? '')));
            $active_gare_count = (int) ($item->active_gare_count ?? 0);
            $assigned_gare_count = (int) ($item->assigned_gare_count ?? 0);
            $bobo_gare_ids = array('BOB1', 'DIS10');
            $banfora_gare_ids = array('BAN3', 'NIA5');
            $known_zone_gare_ids = array_merge($bobo_gare_ids, $banfora_gare_ids);
            $has_bobo_zone = (bool) array_intersect($gare_ids, $bobo_gare_ids);
            $has_banfora_zone = (bool) array_intersect($gare_ids, $banfora_gare_ids);
            $has_ouagadougou_zone = (bool) array_diff($gare_ids, $known_zone_gare_ids);
            $active_zone_count = (int) $has_bobo_zone
                + (int) $has_banfora_zone
                + (int) $has_ouagadougou_zone;

            if ($active_zone_count > 1) {
                $user_zones[] = 'multiple';
            } else {
                if ($has_bobo_zone) {
                    $user_zones[] = 'bobo';
                }
                if ($has_banfora_zone) {
                    $user_zones[] = 'banfora';
                }
                if ($has_ouagadougou_zone) {
                    $user_zones[] = 'ouagadougou';
                }
                if ($assigned_gare_count > 0 && $active_gare_count === 0) {
                    $user_zones[] = 'disabled';
                }
            }

            $user_search = strtolower(trim(
                $item->first_name . ' ' . $item->last_name . ' '
                . $item->phone . ' ' . $item->phone2 . ' ' . $item->email . ' '
                . ($item->username ?? '') . ' ' . $compte_st['label'] . ' ' . $compte_st['motif']
            ));
            ?>
            <div class="col-lg-3 user-filter-card" data-user-filter-item="1"
                 data-user-zones="<?= htmlspecialchars(implode(' ', $user_zones), ENT_QUOTES, 'UTF-8'); ?>"
                 data-search="<?= htmlspecialchars($user_search, ENT_QUOTES, 'UTF-8'); ?>">

                <div class="card card-border card-contrast">

                    <?php $this->load->view('beagle/pages/_users/_compte_status_badge', ['item' => $item]); ?>

                    <div class="card-header card-header-contrast"><?= $item->first_name; ?> <?= $item->last_name; ?>

                        <div class="tools">
                            <a href="<?= "#?{$item->uid}&name={$item->first_name}&prenom={$item->last_name}&contact={$item->phone}&email={$item->email}"; ?>"
                               class=" md-trigger" data-modal="edit-user-<?= $item->uid; ?>" title="Modifier">
                                <span class="icon mdi mdi-edit text-warning"></span>
                            </a>&nbsp;&nbsp;
                            <a href="<?= "#?{$item->uid}&name={$item->first_name}&prenom={$item->last_name}&contact={$item->phone}&email={$item->email}"; ?>"
                               class=" md-trigger" data-modal="compt-user-<?= $item->uid; ?>" title="Créer compte">
                                <span class="icon mdi mdi-edit text-success"></span>
                            </a>
                            <!-- modification -->
                            <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                 id="edit-user-<?= $item->uid; ?>" style="">
                                <div class="modal-content">
                                    <div class="modal-header modal-header-colored">
                                        <h3 class="modal-title">MODIFICATION
                                            SUR <?= $item->first_name; ?> <?= $item->last_name; ?></h3>
                                        <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                        </button>
                                    </div>
                                    <?= form_open('Utilisateurs/edit_use/' . $this->session->company->ekey . '/' . $item->uid,
                                        array('class' => 'modal-body form')); ?>

                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm" type="text"
                                                   name="firstname" required=""
                                                   value="<?= $item->first_name; ?>"
                                                   placeholder="<?= $item->first_name; ?>"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Prénom:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm" type="text"
                                                   name="lastname" required=""
                                                   value="<?= $item->last_name; ?>"
                                                   placeholder="<?= $item->last_name; ?>"
                                                   autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm"
                                                   type="tel" name="phone" required=""
                                                   value="<?= $item->phone; ?>"
                                                   placeholder="<?= $item->phone; ?>"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact2:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm"
                                                   type="tel" name="phone2" required=""
                                                   value="<?= $item->phone2; ?>"
                                                   placeholder="<?= $item->phone2; ?>"
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Email:</label>
                                        <div class="col-12 col-sm-8 col-lg-6">
                                            <input class="form-control form-control-sm"
                                                   type="email" name="email" required=""
                                                   value="<?= $item->email; ?>"
                                                   placeholder="<?= $item->email; ?>"
                                                   autocomplete="off">
                                        </div>
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
                        </div>
                    </div>
                    <div class="card-body">
                        <p>Nom:<?= $item->first_name; ?>&nbsp;<?= $item->last_name; ?></p>
                        <p class="text-danger"></p>
                        <p>Contact: <?= $item->phone; ?></p>
                        <p>Contact2: <?= $item->phone2; ?></p>
                        <a href="<?= site_url('utilisateurs/'
                                . $this->session->company->ekey . '/gTv/'
                                . $item->uid. '/compte/' . mdate("%d/%m/%Y", now('UTC'))); ?>" 
                            class="btn btn-block btn-rounded text-dark bg-info">
                            <span class="fas fa-edit"></span>
                            VOIR COMPTE
                        </a>
                    </div>
                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="compt-user-<?= $item->uid; ?>" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN COMPTE UTILISATEUR</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            <div class="card-body">
                                
                                <?= form_open('Utilisateurs/add/' . $this->session->company->ekey.'/'.$item->uid
                                    , array('class' => 'modal-body form')); ?>
                                
                               
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom
                                        Utilisateur:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm" type="text"
                                               name="username" required=""
                                               placeholder="Nom utilisateur"
                                               autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row signup-password">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Mot de
                                        passe:</label>
                                    <div class="col-12 col-sm-8 col-lg-6 row ">
                                        <div class="col-6">
                                            <input class="form-control form-control-sm" name="pass1" type="password" required=""
                                                   placeholder="Mot de passe" autocomplete="off">

                                        </div>
                                        <div class="col-6">
                                            <input class="form-control form-control-sm" name="confirm"
                                                   required=""
                                                   type="password"
                                                   placeholder="Confirmez le mot de passe" autocomplete="off">
                                        </div>
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

                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="compt-attgare-<?= $item->uid; ?>" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">ATTRIBUER UNE GARE A UN COMPTE UTILISATEUR</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            <div class="card-body">
                                
                                <?= form_open('Utilisateurs/addprofil_/' . $this->session->company->ekey.'/'.$item->uid
                                    , array('class' => 'modal-body form')); ?>
                                
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Compte_Utilisateur:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <select class="form-control form-control-sm" name="fonction" id="fonction">
                                            <?php
                                            $visibleAccountsSql = "SELECT cpuser_id, username FROM compte_user cu";
                                            if ($this->db->table_exists('super_admin_accounts')
                                                && !super_admin_is_current()
                                            ) {
                                                $visibleAccountsSql .= " WHERE NOT EXISTS (
                                                    SELECT 1 FROM super_admin_accounts sa
                                                    WHERE sa.cpuser_id = cu.cpuser_id
                                                )";
                                            }
                                            $userlogins = $this->db->query($visibleAccountsSql)->result();
                                            ?>
                                            
                                            <? foreach ($userlogins as $logus): ?>
                                                <option value="<?= $logus->cpuser_id; ?>"><?= $logus->username; ?></option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Gare:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <select class="form-control form-control-sm" name="gareuser" id="gareuse">
                                            <? foreach ($gares as $itemgare): ?>
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
        <? endforeach; ?>

        <div class="col-lg-12" id="users-filter-list-empty" style="display: none;">
            <div class="card card-border">
                <div class="card-body text-center text-muted py-4">
                    Aucun utilisateur ne correspond à cette zone ou à votre recherche.
                </div>
            </div>
        </div>
    
    <? else: ?>
        <div class="col-lg-4 offset-lg-4">
            <div class="card">
                <div class="card-header card-header-divider"><?= $this->session->company->nom_entreprise; ?></div>
                <div class="card-body text-center text-capitalize">
                    <h2>AUCUN UTILISATEUR</h2>
                    <p>Vous pouvez en ajoutez ici
                        <button class="btn btn-rounded btn-space btn-success md-trigger" data-modal="form-users">
                            <i class="icon icon-left mdi mdi-face"></i>AJOUTER UTILISATEUR
                        </button>
                    </p>
                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="form-users" style="perspective: none;">

                        <div class="modal-content">

                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">AJOUTER UN UTILISATEUR</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            <div class="card-body">
                                
                                <?= form_open('Utilisateurs/adduse/' . $this->session->company->ekey
                                    , array('class' => 'modal-body form')); ?>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Nom:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm" type="text"
                                               name="firstname" required=""
                                               placeholder="Nom"
                                               autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Prénom:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm" type="text"
                                               name="lastname" required=""
                                               placeholder="Prénom"
                                               autocomplete="off">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm"
                                               type="tel" name="phone" required=""
                                               placeholder="Numéro de téléphone"
                                               autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Contact2:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm"
                                                type="tel" name="phone2" required=""
                                                autocomplete="off">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-12 col-sm-3 col-form-label text-left text-sm-right">Email:</label>
                                    <div class="col-12 col-sm-8 col-lg-6">
                                        <input class="form-control form-control-sm"
                                                type="email" name="email" required=""
                                                autocomplete="off">
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
    <? endif; ?>
</div>

<!--End of file: compt.php-->
<!--File location: application/views/beagle/pages/_users/compt.php-->                            
