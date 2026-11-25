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

                <div class="title">Passager escale
                    <?php if (!empty($tri_esc_debut) || !empty($tri_esc_fin)): ?>
                        <small class="text-muted">
                            (<?= htmlspecialchars((string) $tri_esc_debut, ENT_QUOTES, 'UTF-8'); ?>
                            → <?= htmlspecialchars((string) $tri_esc_fin, ENT_QUOTES, 'UTF-8'); ?>)
                        </small>
                    <?php endif; ?>
                </div>

            </div>
            <div class="card-body">
                <p class="text-muted small mb-2 px-1">
                    Icône <i class="fas fa-redo text-danger"></i> :
                    envoyer le ticket au vendeur escale pour <strong>réimpression</strong>
                    (il apparaît alors dans son menu Réimpression).
                    Vert = déjà autorisé (recliquer pour annuler).
                </p>
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
                        $__reimpr = isset($item->reimpr) ? (int) $item->reimpr : 0;
                        $__lh = !empty($item->id_lgeheur) ? $item->id_lgeheur : '0';
                        $__tf = !empty($item->typtarifesc) ? $item->typtarifesc : '0';
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
                            $__reimpr === 1 ? 'reimpression autorisee' : '',
                        ))));
                        ?>
                        <tr data-search="<?= htmlspecialchars($__search, ENT_QUOTES, 'UTF-8'); ?>">
                            <td>
                                <span><?= $item->idclescal; ?></span>
                                <?php if ($__reimpr === 1): ?>
                                    <br><span class="badge badge-success">Réimp. OK</span>
                                <?php endif; ?>
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
                                <span>Heure:<?= isset($item->heure) ? $item->heure : '—'; ?></span></span>
                                <span>Axe:<?= function_exists('ticket_axe_label') ? ticket_axe_label($item) : $item->nom_ligne; ?> <?= $item->quartier_escal; ?></span>
                            </td>

                            <td>
                                <span><?= number_format($item->prixescal, 0, '', ' '); ?></span>
                            </td>
                            <td>
                                 
                                <a class="icon" title="Imprimer le ticket escale"
                                    href="<?= site_url('Historique_Passagers/pdfepsonescal/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$__tf.'/'.$__lh.'/'.$bus_stop->idengare.'/'.$conex->roleattribut .'/'.$bus_stop->idsousgare);?>">
                                    <i class="fas fa-print"></i>
                                </a>&nbsp;

                                <a class="icon"
                                    title="<?= $__reimpr === 1
                                        ? 'Annuler l’autorisation de réimpression'
                                        : 'Envoyer au vendeur pour réimpression'; ?>"
                                    href="<?= site_url('Ventescales/reimpri/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$__reimpr.'/'.$__lh.'/'.$bus_stop->idengare.'/'.$conex->roleattribut .'/'.$bus_stop->idsousgare);?>"
                                    onclick="return confirm('<?= $__reimpr === 1
                                        ? 'Annuler l’autorisation de réimpression pour ce ticket ?'
                                        : 'Envoyer ce ticket au vendeur escale pour une réimpression ?'; ?>');">
                                    <i class="fas fa-redo <?= $__reimpr === 1 ? 'text-success' : 'text-danger'; ?>"></i>
                                </a>&nbsp;

                                <a class="icon" title="SUPPRIMER TICKET ESCALE"
                                    href="<?= site_url('ventescales/supprimescal/'.$this->session->company->ekey.'/'.rawurlencode($item->idclescal).'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                                    onclick="return confirm('Supprimer définitivement ce ticket escale ?');">
                                    <i class="fas fa-trash-alt text-danger"></i>
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
