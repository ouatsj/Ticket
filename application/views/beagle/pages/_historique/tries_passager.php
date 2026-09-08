<?php
    
defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
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

                <div class="title">Passager</div>

            </div>
            <div class="card-body">
                <div class="row align-items-center mb-3">
                    <div class="col-md-7 col-lg-6">
                        <label class="sr-only" for="filtre-tri-passager-esc">Recherche instantanée</label>
                        <input type="search"
                               id="filtre-tri-passager-esc"
                               class="form-control"
                               placeholder="Filtrer : nom, téléphone, code, axe, date…"
                               autocomplete="off"
                               autofocus>
                    </div>
                    <div class="col-md-5 col-lg-6 mt-2 mt-md-0">
                        <span class="text-muted" id="filtre-tri-passager-esc-count"></span>
                    </div>
                </div>
                <table class="table table-striped table-borderless" id="table-tri-escale">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Client / Contact</th>
                        <th>N° cni ou passport / Date / Lieu</th>
                        <th>Départ / Heure / Axe</th>
                        <th>Prix</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody class="no-border-x">
                    <? foreach ($historiqueses as $item): ?>
                        <?php
                        $__search = strtolower(trim(implode(' ', array(
                            isset($item->idclescal) ? $item->idclescal : '',
                            isset($item->nom_client) ? $item->nom_client : '',
                            isset($item->prenom_client) ? $item->prenom_client : '',
                            isset($item->contact_client) ? $item->contact_client : '',
                            isset($item->num_CNIB) ? $item->num_CNIB : '',
                            isset($item->datedepescal) ? $item->datedepescal : '',
                            isset($item->heure) ? $item->heure : '',
                            function_exists('ticket_axe_label') ? ticket_axe_label($item) : (isset($item->nom_ligne) ? $item->nom_ligne : ''),
                            isset($item->quartier_escal) ? $item->quartier_escal : '',
                            isset($item->prixescal) ? $item->prixescal : '',
                        ))));
                        ?>
                        <tr data-search="<?= htmlspecialchars($__search, ENT_QUOTES, 'UTF-8'); ?>">
                            <td>
                                <span><?= $item->idclescal; ?></span>
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
                                <span>Départ:<?= $item->datedepescal; ?><br>
                                <span>Heure:<?= $item->heure; ?></span></span>
                                <span>Axe:<?= function_exists('ticket_axe_label') ? ticket_axe_label($item) : $item->nom_ligne; ?> <?= $item->quartier_escal; ?></span>
                            </td>

                            <td>
                                <span><?= number_format($item->prixescal, 0, '', ' '); ?></span>
                            </td>
                            <td>
                                 
                                <a class="icon" title="epson"
                                    href="<?= site_url('Historique_Passagers/pdfepsonescal/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$item->typtarifesc.'/'.$item->id_lgeheur.'/'.$bus_stop->idengare.'/'.$conex->roleattribut .'/'.$bus_stop->idsousgare);?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;

                                <a class="icon" title="valider_reimpression"
                                    href="<?= site_url('Ventescales/reimpri/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$item->reimpr.'/'.$item->id_lgeheur.'/'.$bus_stop->idengare.'/'.$conex->roleattribut .'/'.$bus_stop->idsousgare);?>">
                                    <i class="fas fa-edit"></i>
                                </a>&nbsp;
                            </td>
                        </tr>
                    
                    <? endforeach; ?>
                    </tbody>
                    
                </table>
                <p class="text-muted filtre-tri-esc-vide d-none mb-0">Aucun résultat pour cette recherche.</p>
                
            </div>
                
        </div>
    </div>
</div>
<script>
(function () {
    var input = document.getElementById('filtre-tri-passager-esc');
    var countEl = document.getElementById('filtre-tri-passager-esc-count');
    var table = document.getElementById('table-tri-escale');
    var emptyMsg = document.querySelector('.filtre-tri-esc-vide');
    if (!input || !table) { return; }

    function applyFilter() {
        var q = (input.value || '').toLowerCase().trim();
        var rows = table.querySelectorAll('tbody tr');
        var visible = 0;
        for (var i = 0; i < rows.length; i++) {
            var hay = (rows[i].getAttribute('data-search') || rows[i].textContent || '').toLowerCase();
            var show = !q || hay.indexOf(q) !== -1;
            rows[i].style.display = show ? '' : 'none';
            if (show) { visible++; }
        }
        if (countEl) {
            countEl.textContent = q
                ? (visible + ' / ' + rows.length + ' résultat(s)')
                : (rows.length + ' passager(s)');
        }
        if (emptyMsg) {
            emptyMsg.classList.toggle('d-none', !(q && visible === 0));
        }
    }

    input.addEventListener('input', applyFilter);
    input.addEventListener('search', applyFilter);
    applyFilter();
})();
</script>