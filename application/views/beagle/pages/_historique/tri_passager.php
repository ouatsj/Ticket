<?php
    
defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
<?php if ($this->session->flashdata('sale_error')): ?>
    <div class="row">
        <div class="col-12 px-4">
            <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('sale_error')); ?></div>
        </div>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('sale_success')): ?>
    <div class="row">
        <div class="col-12 px-4">
            <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('sale_success')); ?></div>
        </div>
    </div>
<?php endif; ?>
<?php
    $__peut_repositionner = isset($this->session->agent->userole)
        && in_array((string) $this->session->agent->userole, array('1', '2', '5', '15'), true);
    if (!isset($historiques_direct) || !is_array($historiques_direct)) {
        $historiques_direct = array();
    }
    if (!isset($historiques_transit) || !is_array($historiques_transit)) {
        $historiques_transit = array();
    }
    if (empty($historiques_direct) && empty($historiques_transit) && !empty($historiques)) {
        foreach ($historiques as $__row) {
            if (!empty($__row->est_transit)) {
                $historiques_transit[] = $__row;
            } else {
                $historiques_direct[] = $__row;
            }
        }
    }
    $__n_direct = count($historiques_direct);
    $__n_transit = count($historiques_transit);
?>
<div class="row">
    <!-- Liste des passagers -->
    <div class="col-12">

        <div class="card card-table">

            <div class="card-header">

                <div class="tools dropdown">

                    <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                        <span class="icon mdi mdi-more-vert"></span>

                    </a>

                </div>

                <div class="title">Passagers — gare
                    <?= isset($bus_stop->idengare) ? htmlspecialchars($bus_stop->idengare) : ''; ?>
                </div>

            </div>
            <div class="card-body">
                <div class="row align-items-center mb-3">
                    <div class="col-md-7 col-lg-6">
                        <label class="sr-only" for="filtre-tri-passager">Recherche instantanée</label>
                        <input type="search"
                               id="filtre-tri-passager"
                               class="form-control"
                               placeholder="Filtrer : nom, téléphone, code, siège, axe, date…"
                               autocomplete="off"
                               autofocus>
                    </div>
                    <div class="col-md-5 col-lg-6 mt-2 mt-md-0">
                        <span class="text-muted" id="filtre-tri-passager-count"></span>
                    </div>
                </div>
                <style>
                    #triPassagerTabs.nav-tabs {
                        border-bottom: 2px solid #dee2e6;
                    }
                    #triPassagerTabs .nav-link {
                        color: #5a5c69;
                        background: #f1f3f5;
                        border: 1px solid #dee2e6;
                        border-bottom: none;
                        margin-right: 4px;
                        border-radius: 4px 4px 0 0;
                        font-weight: 600;
                        padding: 0.55rem 1rem;
                    }
                    #triPassagerTabs .nav-link:hover {
                        background: #e9ecef;
                        color: #343a40;
                    }
                    #triPassagerTabs .nav-link.active {
                        background: #4285f4;
                        border-color: #4285f4;
                        color: #fff !important;
                        box-shadow: none;
                    }
                    #triPassagerTabs .nav-link.active .badge {
                        background: #fff !important;
                        color: #4285f4 !important;
                    }
                    #triPassagerTabs .nav-link .badge {
                        margin-left: 0.35rem;
                    }
                </style>
                <ul class="nav nav-tabs nav-tabs-classic mb-3" id="triPassagerTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-tri-direct" data-toggle="tab" href="#pane-tri-direct" role="tab">
                            Tickets directs
                            <span class="badge badge-primary"><?= (int) $__n_direct; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-tri-transit" data-toggle="tab" href="#pane-tri-transit" role="tab">
                            Tickets transit
                            <span class="badge badge-info"><?= (int) $__n_transit; ?></span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pane-tri-direct" role="tabpanel">
                        <p class="small text-muted mb-2">Impression : ticket unique de la ligne.</p>
                        <table class="table table-striped table-borderless" id="table1-direct">
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
                                $historiques = $historiques_direct;
                                $__tri_print_mode = 'direct';
                                include APPPATH . 'views/beagle/pages/_historique/_tri_passager_rows.php';
                            ?>
                            </tbody>
                        </table>
                        <p class="text-muted filtre-tri-vide d-none mb-0">Aucun ticket direct pour cette recherche.</p>
                    </div>
                    <div class="tab-pane fade" id="pane-tri-transit" role="tabpanel">
                        <p class="small text-muted mb-2">Liste détaillée des jambes — impression globale de tous les tickets du voyage.</p>
                        <table class="table table-striped table-borderless" id="table1-transit">
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
                                $historiques = $historiques_transit;
                                $__tri_print_mode = 'transit';
                                include APPPATH . 'views/beagle/pages/_historique/_tri_passager_rows.php';
                            ?>
                            </tbody>
                        </table>
                        <p class="text-muted filtre-tri-vide d-none mb-0">Aucun ticket transit pour cette recherche.</p>
                    </div>
                </div>
                
            </div>
                
        </div>
    </div>
</div>
<script>
(function () {
    var input = document.getElementById('filtre-tri-passager');
    var countEl = document.getElementById('filtre-tri-passager-count');
    if (!input) { return; }

    function filterPane(pane) {
        if (!pane) { return { visible: 0, total: 0 }; }
        var q = (input.value || '').toLowerCase().trim();
        var rows = pane.querySelectorAll('tbody tr[data-search], tbody tr');
        var visible = 0;
        var total = rows.length;
        for (var i = 0; i < rows.length; i++) {
            var hay = (rows[i].getAttribute('data-search') || rows[i].textContent || '').toLowerCase();
            var show = !q || hay.indexOf(q) !== -1;
            rows[i].style.display = show ? '' : 'none';
            if (show) { visible++; }
        }
        var emptyMsg = pane.querySelector('.filtre-tri-vide');
        if (emptyMsg) {
            emptyMsg.classList.toggle('d-none', !(q && visible === 0));
        }
        return { visible: visible, total: total };
    }

    function applyFilter() {
        var pane = document.querySelector('#triPassagerTabs + .tab-content > .tab-pane.active')
            || document.querySelector('.tab-content > .tab-pane.active');
        // Filtrer les deux onglets pour garder le filtre au changement d'onglet
        var panes = document.querySelectorAll('#pane-tri-direct, #pane-tri-transit');
        for (var p = 0; p < panes.length; p++) {
            filterPane(panes[p]);
        }
        if (countEl && pane) {
            var q = (input.value || '').trim();
            var rows = pane.querySelectorAll('tbody tr');
            var visible = 0;
            for (var i = 0; i < rows.length; i++) {
                if (rows[i].style.display !== 'none') { visible++; }
            }
            if (q) {
                countEl.textContent = visible + ' / ' + rows.length + ' résultat(s)';
            } else {
                countEl.textContent = rows.length + ' passager(s)';
            }
        }
    }

    input.addEventListener('input', applyFilter);
    input.addEventListener('search', applyFilter);
    var tabLinks = document.querySelectorAll('#triPassagerTabs a[data-toggle="tab"]');
    for (var t = 0; t < tabLinks.length; t++) {
        tabLinks[t].addEventListener('shown.bs.tab', applyFilter);
        if (window.jQuery) {
            window.jQuery(tabLinks[t]).on('shown.bs.tab', applyFilter);
        }
    }
    applyFilter();
})();
</script>

    <div
        class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="ticket-0" style="perspective: none;">

        <div class="modal-content">
    
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="mtaTitle"></h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
            </div>
    
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'mtaForm')); ?>
    
            <div class="row">
                <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
                <input class="form-control-sm" type="hidden" name="identifyclient" value="" id="identifyclientid" />
                <input class="form-control-sm" type="hidden" name="identifycontact" value="" id="identifycontactid" />
                <input class="form-control-sm" type="hidden" name="force_create_client" value="0" id="force_create_client" />
                <div class="form-group col-sm-4">
                    <label>Conctact</label>
                    <input class="form-control form-control-sm" type="text"
                            name="rclient_contact"
                            id="uclient_contact"
                            autocomplete="off"
                            value=""
                            placeholder=""/>
                </div>
                <div class="form-group col-sm-4">
                    <label>Nom</label>
                    <input class="form-control form-control-sm" type="text"
                            name="rclient"
                            id="uclient"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Prénom</label>
                    <input class="form-control form-control-sm" type="text"
                            name="prclient"
                            id="uprnclient"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
        
                <div class="form-group col-sm-4">
                    <label>Cni ou Passport</label>
                    <input class="form-control form-control-sm" type="text"
                            name="cnib" id="ucnib" autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Délivré(e) le</label>
                    <input class="form-control form-control-sm" type="date"
                            name="date_cnib" id="udate_cnib"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Lieu</label>
                    <input class="form-control form-control-sm" type="text"
                            name="lieu" id="ulieudelivre"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-12" id="force_create_client_wrap" style="display:none;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="force_create_client_chk">
                        <label class="form-check-label" for="force_create_client_chk">
                            Contact inconnu : créer une <strong>nouvelle fiche client</strong> et rattacher le ticket
                        </label>
                    </div>
                    <small class="text-muted">
                        Sans cette case, les infos sont enregistrées sur le client déjà lié au ticket.
                    </small>
                </div>
                <?= historique_modif_ticket_motif_fields_html('infos'); ?>
    
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
        id="ticketp-0" style="perspective: none;">

        <div class="modal-content">
    
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="mtaTitlep"></h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
            </div>
    
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'mtaFormp')); ?>
    
            <div class="row">
                <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
                <input class="form-control-sm" type="hidden" name="identifyclient" value="" id="identifyclientid" />
                <input class="form-control-sm" type="hidden" name="identifycontact" value="" id="identifycontactid" />
                <div class="form-group col-sm-4">
                    <label>Conctact</label>
                    <input class="form-control form-control-sm" type="text"
                            name="rclient_contactp"
                            id="uclient_contactp"
                            autocomplete="off"
                            value=""
                            placeholder=""/>
                </div>
                <div class="form-group col-sm-4">
                    <label>Nom</label>
                    <input class="form-control form-control-sm" type="text"
                            name="rclientp"
                            id="uclientp"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Prénom</label>
                    <input class="form-control form-control-sm" type="text"
                            name="prclientp"
                            id="uprnclientp"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
        
                <div class="form-group col-sm-4">
                    <label>Cni ou Passport</label>
                    <input class="form-control form-control-sm" type="text"
                            name="cnibp" id="ucnibp" autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Délivré(e) le</label>
                    <input class="form-control form-control-sm" type="date"
                            name="date_cnibp" id="udate_cnibp"
                            value=""
                            placeholder="">
                </div>
                <div class="form-group col-sm-4">
                    <label>Lieu</label>
                    <input class="form-control form-control-sm" type="text"
                            name="lieup" id="ulieudelivrep"
                            autocomplete="off"
                            value=""
                            placeholder="">
                </div>
                <?= historique_modif_ticket_motif_fields_html('client'); ?>
    
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
        id="updepart-0" style="perspective: none;">

        <div class="modal-content">
    
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="mtickTitle"></h3>
                <button class="close modal-close" type="button"
                        data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
            </div>
    
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'mdtickForm')); ?>
                <input type="hidden" id="siegselect">
                <input type="hidden" id="idtamposelect">
            <div class="row">
                <div class="form-group col-sm-3">
                    <input type="hidden" name='codeancien' id="ancien">
                    <input type="hidden" name='siegancien' id="anciensieg">
                    <input type="hidden" name='progancien' id="ancienprog">
                    <input type="hidden" name='categbus' id="categbuse">
                    <input type="hidden" name='sousgre' id="sousgr">

                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm-4">
                    <input type="hidden" id="pfinvendabl">
                    <input type="hidden" id="siegfinvendabl">
                    <input type="hidden" id="reserveheur">
                    <input type="hidden" id="directreserv">
                    <input type="hidden" id="datereserv">
                </div>
            </div>
            <div class="row">
                <input class="form-control-sm" type="hidden" name="stop" value="<?=$bus_stop->idengare;?>" />
                <input class="form-control-sm" type="hidden" name="useridconn" value="<?=$conex->cpuser_id;?>" />
                <input class="form-control-sm" type="hidden" name="useridconnected" value="<?=$conex->roleattribut;?>" />
                <input class="form-control-sm" type="hidden" name="sousgd" value="<?=$bus_stop->idsousgare;?>" />
                <div class="form-group col-sm-4">
                    <label>Sousgare</label>
                    <select class="form-control form-control-sm" name="deparsousgareidentif" id="sgares">
                        <option value=""></option>
                        <? foreach ($garedeparts as $garedepart): ?>
                            <option value="<?= $garedepart->idsousgare; ?>">
                                <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>Quartier</label>
                    <select class="form-control form-control-sm" name="quartier" id="idquartier">
                        <option value=""></option>
                        
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>Depart</label>
                    <select class="form-control form-control-sm" name="departs" id="departclient">
                        <option value=""></option>
                        
                    </select>
                </div>
                <div class="form-group col-sm-4">
                    <label>Siege</label>
                    <select class="form-control form-control-sm" name="siege" id="siegeclient">
                        <option value=""></option>
                        
                    </select>

                </div>
                <div class="col-sm-4 text-center text-danger" style="display:none"
                    id="messieg">
                    <p id="erreurmessieg"></p>
                </div>
                <?= historique_modif_ticket_motif_fields_html('depart'); ?>
                
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

    <div class="modal-container colored-header colored-header-warning custom-width modal-effect-7"
         id="motif-action-0" style="perspective: none;">
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="motifActionTitle">Confirmer la modification</h3>
                <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                    <span class="mdi mdi-close text-white"></span>
                </button>
            </div>
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'motifActionForm', 'method' => 'post')); ?>
            <div class="row">
                <?= historique_modif_ticket_motif_fields_html('action'); ?>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-warning" type="submit">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;CONFIRMER&nbsp;
                </button>
            </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>
<script>
(function () {
    document.querySelectorAll('.motif-action').forEach(function (el) {
        el.addEventListener('click', function () {
            var form = document.getElementById('motifActionForm');
            var title = document.getElementById('motifActionTitle');
            if (form && el.dataset.action) {
                form.setAttribute('action', el.dataset.action);
            }
            if (title && el.dataset.title) {
                title.textContent = el.dataset.title;
            }
            var motif = form ? form.querySelector('[name="motif_modif"]') : null;
            var ordre = form ? form.querySelector('[name="ordre_par"]') : null;
            if (motif) { motif.value = ''; }
            if (ordre) { ordre.value = ''; }
        });
    });
})();
</script>