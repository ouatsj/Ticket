<?php
    
    defined('BASEPATH') OR exit('No direct script access allowed');
    if (!isset($historiquesconfirme_direct) || !is_array($historiquesconfirme_direct)) {
        $historiquesconfirme_direct = array();
    }
    if (!isset($historiquesconfirme_transit) || !is_array($historiquesconfirme_transit)) {
        $historiquesconfirme_transit = array();
    }
    if (empty($historiquesconfirme_direct) && empty($historiquesconfirme_transit) && !empty($historiquesconfirme)) {
        foreach ($historiquesconfirme as $__row) {
            if (!empty($__row->est_transit)) {
                $historiquesconfirme_transit[] = $__row;
            } else {
                $historiquesconfirme_direct[] = $__row;
            }
        }
    }
    $__n_direct = count($historiquesconfirme_direct);
    $__n_transit = count($historiquesconfirme_transit);
?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <!-- Liste des passagers confirmer-->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Confirmations de la gare
                    <?= isset($bus_stop->idengare) ? htmlspecialchars((string) $bus_stop->idengare) : ''; ?>
                    <span class="text-muted font-weight-normal">(agent ayant confirmé)</span>
                    <?php if (!empty($tri_conf_debut) && !empty($tri_conf_fin)): ?>
                        <span class="text-muted font-weight-normal">
                            · du <?= htmlspecialchars((string) $tri_conf_debut); ?>
                            au <?= htmlspecialchars((string) $tri_conf_fin); ?>
                        </span>
                    <?php endif; ?>
                </div>

            </div>
            <div class="card-body">
                <style>
                    #triConfTabs.nav-tabs {
                        border-bottom: 2px solid #dee2e6;
                    }
                    #triConfTabs .nav-link {
                        color: #5a5c69;
                        background: #f1f3f5;
                        border: 1px solid #dee2e6;
                        border-bottom: none;
                        margin-right: 4px;
                        border-radius: 4px 4px 0 0;
                        font-weight: 600;
                        padding: 0.55rem 1rem;
                    }
                    #triConfTabs .nav-link:hover {
                        background: #e9ecef;
                        color: #343a40;
                    }
                    #triConfTabs .nav-link.active {
                        background: #4285f4;
                        border-color: #4285f4;
                        color: #fff !important;
                        box-shadow: none;
                    }
                    #triConfTabs .nav-link.active .badge {
                        background: #fff !important;
                        color: #4285f4 !important;
                    }
                    #triConfTabs .nav-link .badge {
                        margin-left: 0.35rem;
                    }
                </style>
                <ul class="nav nav-tabs nav-tabs-classic mb-3" id="triConfTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-conf-direct" data-toggle="tab" href="#pane-conf-direct" role="tab">
                            Confirmations direct
                            <span class="badge badge-primary"><?= (int) $__n_direct; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-conf-transit" data-toggle="tab" href="#pane-conf-transit" role="tab">
                            Confirmations transit
                            <span class="badge badge-info"><?= (int) $__n_transit; ?></span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pane-conf-direct" role="tabpanel">
                        <p class="small text-muted mb-2">Réimpression uniquement via Epson (ticket unique).</p>
                        <table class="table table-striped table-borderless" id="table1-conf-direct">
                            <thead>
                            <tr>
                                <th>N° siège</th>
                                <th>Code</th>
                                <th>Client / Contact</th>
                                <th>N° cni ou passport / Date / Lieu</th>
                                <th>Départ / Heure / Axe</th>
                                <th>Prix</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody class="no-border-x">
                            <?php
                                $historiquesconfirme = $historiquesconfirme_direct;
                                $__conf_print_mode = 'direct';
                                include APPPATH . 'views/beagle/pages/_historique/_tri_confpassager_rows.php';
                            ?>
                            </tbody>
                        </table>
                        <?php if ($__n_direct === 0): ?>
                            <p class="text-muted mb-0">Aucune confirmation directe sur cette période.</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="pane-conf-transit" role="tabpanel">
                        <p class="small text-muted mb-2">Toutes les jambes du voyage confirmé dans cette gare — chacune a son bouton Epson.</p>
                        <table class="table table-striped table-borderless" id="table1-conf-transit">
                            <thead>
                            <tr>
                                <th>Jambe</th>
                                <th>N° siège</th>
                                <th>Code</th>
                                <th>Client / Contact</th>
                                <th>N° cni ou passport / Date / Lieu</th>
                                <th>Départ / Heure / Axe</th>
                                <th>Prix</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody class="no-border-x">
                            <?php
                                $historiquesconfirme = $historiquesconfirme_transit;
                                $__conf_print_mode = 'transit';
                                include APPPATH . 'views/beagle/pages/_historique/_tri_confpassager_rows.php';
                            ?>
                            </tbody>
                        </table>
                        <?php if ($__n_transit === 0): ?>
                            <p class="text-muted mb-0">Aucune confirmation transit sur cette période.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
