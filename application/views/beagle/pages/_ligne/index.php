<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$tab = isset($_GET['tab']) ? (string) $_GET['tab'] : '';
if ($tab !== 'escales' && $tab !== 'tpe' && $tab !== 'transit') {
    $tab = 'transit';
}
$lignes_par_cie = !empty($lignes_par_compagnie_arrivee) ? $lignes_par_compagnie_arrivee : array();
$lignes_list = !empty($lignes) ? $lignes : array();
$garearrivees_list = !empty($garearrivees) ? $garearrivees : array();
$escales_tpe = !empty($escales_tpe) ? $escales_tpe : array();

/**
 * Libellés communs pour une ligne d'escale.
 *
 * @param object $esc
 * @param string $mode tarif|tpe
 * @return array
 */
$escale_labels = function ($esc, $mode = 'tarif') {
    $nom_esc = trim((string) $esc->nom_escale);
    if ($nom_esc === '' && !empty($esc->arrivee_escale)) {
        $nom_esc = trim((string) $esc->arrivee_escale);
    }
    $nom_orig = !empty($esc->depart_parent) ? trim((string) $esc->depart_parent) : '';
    $nom_term = !empty($esc->arrivee_parent) ? trim((string) $esc->arrivee_parent) : '';
    if (!empty($esc->nom_ligne_parent) && strpos($esc->nom_ligne_parent, '-') !== false) {
        $bits = explode('-', $esc->nom_ligne_parent);
        if ($nom_orig === '') {
            $nom_orig = trim($bits[0]);
        }
        if ($nom_term === '') {
            $nom_term = trim($bits[count($bits) - 1]);
        }
    }
    $label_dest = $nom_esc . ($nom_term !== '' ? '–' . $nom_term : '');
    $label_orig = $nom_esc . ($nom_orig !== '' ? '–' . $nom_orig : '');
    if ($mode === 'tpe') {
        $prix_dest = (isset($esc->prix_escale_tpe) && $esc->prix_escale_tpe !== null && $esc->prix_escale_tpe !== '')
            ? (float) $esc->prix_escale_tpe
            : null;
    } else {
        $prix_dest = (float) $esc->prix_escale;
    }
    $prix_orig = (isset($esc->prix_escale_origine) && $esc->prix_escale_origine !== null && $esc->prix_escale_origine !== '')
        ? (float) $esc->prix_escale_origine
        : null;
    return compact('nom_esc', 'nom_orig', 'nom_term', 'label_dest', 'label_orig', 'prix_dest', 'prix_orig');
};
?>
<style>
#itineraireTabs.nav-tabs { border-bottom: 2px solid #e9ecef; margin-bottom: 1rem; }
#itineraireTabs .nav-link {
    font-weight: 600; color: #495057; border: none; border-bottom: 3px solid transparent;
    padding: .75rem 1.25rem;
}
#itineraireTabs .nav-link.active { color: #0d6efd; border-bottom-color: #0d6efd; background: transparent; }
#itineraireTabs .nav-link:hover { color: #0d6efd; }
.itin-tab-toolbar { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.itin-tab-toolbar .itin-tab-title { margin: 0; font-size: 1rem; }
.itin-tab-toolbar .itin-tab-title small { font-weight: 400; color: #6c757d; }
.itin-tab-actions { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; }
.itin-search-wrap { position: relative; min-width: 220px; max-width: 360px; flex: 1 1 220px; }
.itin-search-wrap .itin-search-ico {
    position: absolute; left: .65rem; top: 50%; transform: translateY(-50%);
    color: #6c757d; pointer-events: none; font-size: .9rem;
}
.itin-search-wrap input { padding-left: 2rem; padding-right: 2rem; }
.itin-search-wrap .itin-search-clear {
    position: absolute; right: .4rem; top: 50%; transform: translateY(-50%);
    border: 0; background: transparent; color: #adb5bd; cursor: pointer; padding: .2rem .4rem;
    display: none; line-height: 1;
}
.itin-search-wrap.has-value .itin-search-clear { display: inline-block; }
.itin-search-meta { font-size: .8rem; color: #6c757d; margin: 0 0 .75rem; }
.itin-search-meta[hidden] { display: none !important; }
.itin-modal .form-group label { font-weight: 600; font-size: .85rem; }
.itin-modal .itin-cie-filter { margin-bottom: 1rem; }
.itin-modal .itin-hint { font-size: .85rem; color: #6c757d; margin-bottom: 1rem; }
</style>

<div class="row">
    <div class="col-12">
        <ul class="nav nav-tabs nav-tabs-classic" id="itineraireTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'transit' ? 'active' : ''; ?>"
                   id="tab-transit" data-toggle="tab" href="#pane-transit" role="tab"
                   aria-controls="pane-transit" aria-selected="<?= $tab === 'transit' ? 'true' : 'false'; ?>">
                    <i class="fas fa-route mr-1"></i>Composition transit
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'escales' ? 'active' : ''; ?>"
                   id="tab-escales" data-toggle="tab" href="#pane-escales" role="tab"
                   aria-controls="pane-escales" aria-selected="<?= $tab === 'escales' ? 'true' : 'false'; ?>">
                    <i class="fas fa-map-marker-alt mr-1"></i>Escales tarifées
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'tpe' ? 'active' : ''; ?>"
                   id="tab-tpe" data-toggle="tab" href="#pane-tpe" role="tab"
                   aria-controls="pane-tpe" aria-selected="<?= $tab === 'tpe' ? 'true' : 'false'; ?>">
                    <i class="fas fa-ticket-alt mr-1"></i>Escale TPE
                </a>
            </li>
        </ul>

        <div class="tab-content" id="itineraireTabsContent">

            <!-- ========== ONGLET 1 : COMPOSITION TRANSIT ========== -->
            <div class="tab-pane fade <?= $tab === 'transit' ? 'show active' : ''; ?>"
                 id="pane-transit" role="tabpanel" aria-labelledby="tab-transit">

                <div class="itin-tab-toolbar">
                    <p class="itin-tab-title">
                        Composition transit
                        <small>— chaque étape est un itinéraire (ligne) existant</small>
                    </p>
                    <button type="button" class="btn btn-primary btn-space md-trigger"
                            data-modal="modal-compose-transit">
                        <i class="fas fa-plus"></i>&nbsp;Composer / remplacer un transit
                    </button>
                </div>

                <div class="card card-table">
                    <div class="card-body table-responsive">
                        <?php if (!empty($itineraires)): ?>
                        <table class="table table-striped table-hover" id="table1">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>LIGNE CONTENEUR</th>
                                <th>ORDRE</th>
                                <th>ITINÉRAIRE (JAMBE)</th>
                                <th>DÉPART</th>
                                <th>ARRIVÉE</th>
                                <th class="actions">ACTION</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($itineraires as $item): ?>
                                <tr>
                                    <td><?= (int) $item->id_tabitinligne; ?></td>
                                    <td>
                                        <?= htmlspecialchars($item->nom_ligne); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($item->id_lignes); ?></small>
                                    </td>
                                    <td><?= (int) $item->ordre_etape; ?></td>
                                    <td>
                                        <?= htmlspecialchars($item->nom_itineraires); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($item->code_itineraires); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($item->depart_itine); ?></td>
                                    <td><?= htmlspecialchars($item->arrive_itine); ?></td>
                                    <td class="actions">
                                        <a href="<?= site_url('Lignes/activeit/' . $this->session->company->ekey . '/' . $item->id_itineraire . '/' . $item->id_tabitinligne . '/0/' . $item->actifint); ?>?tab=transit"
                                           class="btn btn-sm btn-secondary">
                                            <?= ($item->actifint == '1' || $item->actifint === 1)
                                                ? '<span class="text-danger">désactiver</span>'
                                                : '<span class="text-success">activer</span>' ?>
                                        </a>
                                        &nbsp;
                                        <a href="<?= site_url('lignes/itineraires/' . $this->session->company->ekey . '/delete/' . (int) $item->id_tabitinligne); ?>?tab=transit"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer cette étape de composition transit ?');"
                                           title="Supprimer">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p class="text-warning text-center mb-0 py-4">AUCUNE COMPOSITION TRANSIT</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ========== ONGLET 2 : ESCALES TARIFÉES (vente classique) ========== -->
            <div class="tab-pane fade <?= $tab === 'escales' ? 'show active' : ''; ?>"
                 id="pane-escales" role="tabpanel" aria-labelledby="tab-escales">

                <div class="itin-tab-toolbar">
                    <p class="itin-tab-title">
                        Escales tarifées
                        <small>— prix utilisé quand on coche « Vente escale » au guichet classique</small>
                    </p>
                    <div class="itin-tab-actions">
                        <div class="itin-search-wrap" id="escales-search-wrap">
                            <i class="fas fa-search itin-search-ico" aria-hidden="true"></i>
                            <input type="search" class="form-control form-control-sm" id="escales-search"
                                   placeholder="Rechercher (OD, escale, prix…)"
                                   autocomplete="off" aria-label="Recherche instantanée escales tarifées">
                            <button type="button" class="itin-search-clear" id="escales-search-clear"
                                    title="Effacer" aria-label="Effacer la recherche">&times;</button>
                        </div>
                        <button type="button" class="btn btn-success btn-space md-trigger"
                                data-modal="modal-add-escale">
                            <i class="fas fa-plus"></i>&nbsp;Ajouter une escale + prix
                        </button>
                    </div>
                </div>
                <p class="itin-search-meta" id="escales-search-meta" hidden></p>

                <div class="card card-table">
                    <div class="card-body table-responsive">
                        <?php if (!empty($escales)): ?>
                        <table class="table table-striped table-hover" id="table-escales">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>ITINÉRAIRE PARENT</th>
                                <th>ORDRE</th>
                                <th>ESCALE (DESTINATION)</th>
                                <th>PRIX</th>
                                <th class="actions">ACTION</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($escales as $esc):
                                $L = $escale_labels($esc);
                                $search_blob = strtolower(implode(' ', array(
                                    (string) $esc->id_escale,
                                    (string) $esc->nom_ligne_parent,
                                    (string) $esc->id_lignes,
                                    (string) $esc->ordre_escale,
                                    $L['nom_esc'],
                                    (string) $esc->code_gadest,
                                    (string) (int) $L['prix_dest'],
                                    number_format($L['prix_dest'], 0, '', ' '),
                                )));
                                ?>
                                <tr data-search="<?= htmlspecialchars($search_blob, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td><?= (int) $esc->id_escale; ?></td>
                                    <td>
                                        <?= htmlspecialchars($esc->nom_ligne_parent); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($esc->id_lignes); ?></small>
                                    </td>
                                    <td><?= (int) $esc->ordre_escale; ?></td>
                                    <td>
                                        <?= htmlspecialchars($L['nom_esc']); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($esc->code_gadest); ?></small>
                                    </td>
                                    <td><?= number_format($L['prix_dest'], 0, '', ' '); ?></td>
                                    <td class="actions">
                                        <a href="#escale-edit-<?= (int) $esc->id_escale; ?>"
                                           class="md-trigger" data-modal="escale-edit-<?= (int) $esc->id_escale; ?>">
                                            <span class="fas fa-edit text-warning"></span>
                                        </a>
                                        &nbsp;
                                        <a href="<?= site_url('lignes/escales/' . $this->session->company->ekey . '/toggle/' . $esc->id_escale . '/' . $esc->actif_escale); ?>?tab=escales"
                                           class="btn btn-sm btn-secondary">
                                            <?= ($esc->actif_escale == '1' || $esc->actif_escale === 1)
                                                ? '<span class="text-danger">désactiver</span>'
                                                : '<span class="text-success">activer</span>' ?>
                                        </a>
                                        &nbsp;
                                        <a href="<?= site_url('lignes/escales/' . $this->session->company->ekey . '/delete/' . (int) $esc->id_escale); ?>?tab=escales"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer cette escale tarifée (<?= htmlspecialchars($L['nom_esc'], ENT_QUOTES, 'UTF-8'); ?>) ?');"
                                           title="Supprimer">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>

                                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                             id="escale-edit-<?= (int) $esc->id_escale; ?>">
                                            <div class="modal-content">
                                                <div class="modal-header modal-header-colored">
                                                    <h3 class="modal-title">MODIFIER PRIX / ORDRE</h3>
                                                    <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                                                        <span class="mdi mdi-close text-white"></span>
                                                    </button>
                                                </div>
                                                <?= form_open(
                                                    'lignes/escales/' . $this->session->company->ekey . '/edit/' . $esc->id_escale,
                                                    array('class' => 'modal-body form')
                                                ); ?>
                                                <input type="hidden" name="tab" value="escales">
                                                <p class="text-muted">
                                                    Escale <strong><?= htmlspecialchars($L['nom_esc']); ?></strong>
                                                    <br><small>Parent : <?= htmlspecialchars($esc->nom_ligne_parent); ?></small>
                                                </p>
                                                <div class="form-group">
                                                    <label>PRIX</label>
                                                    <input class="form-control form-control-sm" type="number" min="0" step="1"
                                                           name="prix_escale" value="<?= (int) $L['prix_dest']; ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>ORDRE</label>
                                                    <input class="form-control form-control-sm" type="number" min="1" max="20"
                                                           name="ordre_escale" value="<?= (int) $esc->ordre_escale; ?>" required>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
                                                    <button class="btn btn-success" type="submit">OK</button>
                                                </div>
                                                <?= form_close(); ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p class="text-warning text-center mb-0 py-4">AUCUNE ESCALE CONFIGURÉE</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ========== ONGLET 3 : ESCALE TPE (vente escale / profil) ========== -->
            <div class="tab-pane fade <?= $tab === 'tpe' ? 'show active' : ''; ?>"
                 id="pane-tpe" role="tabpanel" aria-labelledby="tab-tpe">

                <div class="itin-tab-toolbar">
                    <p class="itin-tab-title">
                        Escale TPE
                        <small>— facturation profil vente escale (2 prix)</small>
                    </p>
                    <div class="itin-tab-actions">
                        <div class="itin-search-wrap" id="tpe-search-wrap">
                            <i class="fas fa-search itin-search-ico" aria-hidden="true"></i>
                            <input type="search" class="form-control form-control-sm" id="tpe-search"
                                   placeholder="Rechercher (OD, escale, prix…)"
                                   autocomplete="off" aria-label="Recherche instantanée escale TPE">
                            <button type="button" class="itin-search-clear" id="tpe-search-clear"
                                    title="Effacer" aria-label="Effacer la recherche">&times;</button>
                        </div>
                        <button type="button" class="btn btn-primary btn-space md-trigger"
                                data-modal="modal-add-tpe">
                            <i class="fas fa-plus"></i>&nbsp;Ajouter Escale TPE
                        </button>
                    </div>
                </div>
                <p class="itin-search-meta" id="tpe-search-meta" hidden></p>
                <p class="itin-hint mb-3">
                    Prix <strong>exclusifs</strong> au profil vente escale :
                    vers origine / destination, ou liaison <strong>escale → escale</strong> (même parent).
                    N’écrase jamais les Escales tarifées ni la vente classique / courrier / bagage.
                </p>

                <div class="card card-table mb-4">
                    <div class="card-body table-responsive">
                        <h6 class="mb-3">Prix escale ↔ origine / destination</h6>
                        <?php if (!empty($escales_tpe)): ?>
                        <table class="table table-striped table-hover" id="table-tpe">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>ITINÉRAIRE PARENT (OD)</th>
                                <th>ESCALE</th>
                                <th>ESCALE ORIGINE</th>
                                <th>PRIX ESCALE ORIGINE</th>
                                <th>ESCALE DESTINATION</th>
                                <th>PRIX ESCALE DESTINATION</th>
                                <th class="actions">ACTION</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($escales_tpe as $esc):
                                $L = $escale_labels($esc, 'tpe');
                                $search_blob = strtolower(implode(' ', array(
                                    (string) $esc->id_escale,
                                    (string) $esc->nom_ligne_parent,
                                    (string) $esc->id_lignes,
                                    $L['nom_esc'],
                                    $L['nom_orig'],
                                    $L['nom_term'],
                                    $L['label_orig'],
                                    $L['label_dest'],
                                    (string) $esc->code_gadest,
                                    $L['prix_dest'] !== null ? (string) (int) $L['prix_dest'] : '',
                                    $L['prix_orig'] !== null ? (string) (int) $L['prix_orig'] : '',
                                )));
                                ?>
                                <tr data-search="<?= htmlspecialchars($search_blob, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td><?= (int) $esc->id_escale; ?></td>
                                    <td>
                                        <?= htmlspecialchars($esc->nom_ligne_parent); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($esc->id_lignes); ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($L['nom_esc']); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($esc->code_gadest); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($L['nom_orig'] !== '' ? $L['nom_orig'] : '—'); ?></td>
                                    <td><?= $L['prix_orig'] !== null ? number_format($L['prix_orig'], 0, '', ' ') : '—'; ?></td>
                                    <td><?= htmlspecialchars($L['nom_term'] !== '' ? $L['nom_term'] : '—'); ?></td>
                                    <td><?= $L['prix_dest'] !== null ? number_format($L['prix_dest'], 0, '', ' ') : '—'; ?></td>
                                    <td class="actions">
                                        <a href="#tpe-edit-<?= (int) $esc->id_escale; ?>"
                                           class="md-trigger" data-modal="tpe-edit-<?= (int) $esc->id_escale; ?>"
                                           title="Modifier">
                                            <span class="fas fa-edit text-warning"></span>
                                        </a>
                                        &nbsp;
                                        <a href="<?= site_url('lignes/escales/' . $this->session->company->ekey . '/tpe/delete/' . (int) $esc->id_escale); ?>?tab=tpe"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer cette config Escale TPE (<?= htmlspecialchars($L['nom_esc'], ENT_QUOTES, 'UTF-8'); ?>) ?\\nLes Escales tarifées ne sont pas touchées.');"
                                           title="Supprimer">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>

                                        <div class="modal-container colored-header colored-header-primary custom-width modal-effect-7"
                                             id="tpe-edit-<?= (int) $esc->id_escale; ?>">
                                            <div class="modal-content">
                                                <div class="modal-header modal-header-colored">
                                                    <h3 class="modal-title">MODIFIER ESCALE TPE</h3>
                                                    <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                                                        <span class="mdi mdi-close text-white"></span>
                                                    </button>
                                                </div>
                                                <?= form_open(
                                                    'lignes/escales/' . $this->session->company->ekey . '/edit/' . $esc->id_escale,
                                                    array('class' => 'modal-body form')
                                                ); ?>
                                                <input type="hidden" name="tab" value="tpe">
                                                <p class="text-muted mb-2">
                                                    Parent : <strong><?= htmlspecialchars($esc->nom_ligne_parent); ?></strong>
                                                    <br>Escale : <strong><?= htmlspecialchars($L['nom_esc']); ?></strong>
                                                </p>
                                                <div class="form-group">
                                                    <label>ESCALE ORIGINE</label>
                                                    <input class="form-control form-control-sm" type="text"
                                                           value="<?= htmlspecialchars($L['nom_orig']); ?>" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label>PRIX ESCALE ORIGINE *</label>
                                                    <input class="form-control form-control-sm" type="number" min="0" step="1"
                                                           name="prix_escale_origine"
                                                           value="<?= $L['prix_orig'] !== null ? (int) $L['prix_orig'] : ''; ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>ESCALE DESTINATION</label>
                                                    <input class="form-control form-control-sm" type="text"
                                                           value="<?= htmlspecialchars($L['nom_term']); ?>" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label>PRIX ESCALE DESTINATION *</label>
                                                    <input class="form-control form-control-sm" type="number" min="0" step="1"
                                                           name="prix_escale_tpe"
                                                           value="<?= $L['prix_dest'] !== null ? (int) $L['prix_dest'] : ''; ?>" required>
                                                </div>
                                                <input type="hidden" name="ordre_escale" value="<?= (int) $esc->ordre_escale; ?>">
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
                                                    <button class="btn btn-primary" type="submit">OK</button>
                                                </div>
                                                <?= form_close(); ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0 py-3">Aucune config origine/destination TPE.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card card-table">
                    <div class="card-body table-responsive">
                        <h6 class="mb-3">Liaisons escale → escale (même parent)</h6>
                        <?php
                        $escales_tpe_liaisons = !empty($escales_tpe_liaisons) ? $escales_tpe_liaisons : array();
                        if (!empty($escales_tpe_liaisons)):
                        ?>
                        <table class="table table-striped table-hover" id="table-tpe-liaisons">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>ITINÉRAIRE PARENT (OD)</th>
                                <th>ESCALE DÉPART</th>
                                <th>ESCALE ARRIVÉE</th>
                                <th>PRIX</th>
                                <th class="actions">ACTION</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($escales_tpe_liaisons as $lia):
                                $nom_dep = trim((string) $lia->nom_escale_depart);
                                if ($nom_dep === '' && !empty($lia->arrivee_nom_depart)) {
                                    $nom_dep = trim((string) $lia->arrivee_nom_depart);
                                }
                                $nom_arr = trim((string) $lia->nom_escale_arrivee);
                                if ($nom_arr === '' && !empty($lia->arrivee_nom_arrivee)) {
                                    $nom_arr = trim((string) $lia->arrivee_nom_arrivee);
                                }
                                $search_blob = strtolower(implode(' ', array(
                                    (string) $lia->id_liaison,
                                    (string) $lia->nom_ligne_parent,
                                    (string) $lia->id_lignes,
                                    $nom_dep,
                                    $nom_arr,
                                    (string) (int) $lia->prix_liaison,
                                )));
                                ?>
                                <tr data-search="<?= htmlspecialchars($search_blob, ENT_QUOTES, 'UTF-8'); ?>">
                                    <td><?= (int) $lia->id_liaison; ?></td>
                                    <td>
                                        <?= htmlspecialchars($lia->nom_ligne_parent); ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($lia->id_lignes); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($nom_dep); ?></td>
                                    <td><?= htmlspecialchars($nom_arr); ?></td>
                                    <td><?= number_format((float) $lia->prix_liaison, 0, '', ' '); ?></td>
                                    <td class="actions">
                                        <a href="#tpe-liaison-edit-<?= (int) $lia->id_liaison; ?>"
                                           class="md-trigger" data-modal="tpe-liaison-edit-<?= (int) $lia->id_liaison; ?>">
                                            <span class="fas fa-edit text-warning"></span>
                                        </a>
                                        &nbsp;
                                        <a href="<?= site_url('lignes/escales/' . $this->session->company->ekey . '/liaison/delete/' . (int) $lia->id_liaison); ?>?tab=tpe"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer la liaison <?= htmlspecialchars($nom_dep . ' → ' . $nom_arr, ENT_QUOTES, 'UTF-8'); ?> ?');"
                                           title="Supprimer">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>

                                        <div class="modal-container colored-header colored-header-primary custom-width modal-effect-7"
                                             id="tpe-liaison-edit-<?= (int) $lia->id_liaison; ?>">
                                            <div class="modal-content">
                                                <div class="modal-header modal-header-colored">
                                                    <h3 class="modal-title">MODIFIER LIAISON ESCALE→ESCALE</h3>
                                                    <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                                                        <span class="mdi mdi-close text-white"></span>
                                                    </button>
                                                </div>
                                                <?= form_open(
                                                    'lignes/escales/' . $this->session->company->ekey . '/liaison/edit/' . (int) $lia->id_liaison,
                                                    array('class' => 'modal-body form')
                                                ); ?>
                                                <input type="hidden" name="tab" value="tpe">
                                                <p class="text-muted">
                                                    <?= htmlspecialchars($nom_dep); ?> → <?= htmlspecialchars($nom_arr); ?>
                                                    <br><small><?= htmlspecialchars($lia->nom_ligne_parent); ?></small>
                                                </p>
                                                <div class="form-group">
                                                    <label>PRIX *</label>
                                                    <input class="form-control form-control-sm" type="number" min="0" step="1"
                                                           name="prix_liaison" value="<?= (int) $lia->prix_liaison; ?>" required>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
                                                    <button class="btn btn-primary" type="submit">OK</button>
                                                </div>
                                                <?= form_close(); ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0 py-3">
                                Aucune liaison escale→escale — cochez « Prix escale → escale » à l’ajout.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========== MODALE : COMPOSER / REMPLACER TRANSIT ========== -->
<div class="modal-container colored-header colored-header-primary custom-width modal-effect-7"
     id="modal-compose-transit" style="perspective: none;">
    <div class="modal-content itin-modal">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">Composer / remplacer un transit (2 à 4 itinéraires)</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open("Lignes/additine/{$this->session->company->ekey}", array('class' => 'modal-body form', 'id' => 'form-compose-transit')); ?>
        <input type="hidden" name="tab" value="transit">

        <p class="itin-hint">
            Sélectionnez des <strong>itinéraires déjà créés</strong> (lignes), regroupés par
            <strong>compagnie d’arrivée</strong>, dans l’ordre géographique.
            Cela <strong>remplace</strong> la composition existante de la ligne conteneur.
        </p>

        <div class="form-group itin-cie-filter">
            <label for="filter-cie-transit">Filtrer par compagnie d’arrivée</label>
            <select class="form-control form-control-sm" id="filter-cie-transit">
                <option value="">Toutes les compagnies</option>
                <?php foreach ($lignes_par_cie as $cle => $groupe):
                    $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                    ?>
                    <option value="<?= htmlspecialchars((string) $cle, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>LIGNE CONTENEUR (axe commercial) *</label>
            <select class="form-control form-control-sm js-ligne-cie" name="ligne" required>
                <option value=""></option>
                <?php
                $this->load->view('beagle/pages/_ligne/_options_ligne_compagnie_arrivee', array(
                    'lignes_par_compagnie_arrivee' => $lignes_par_cie,
                    'lignes' => $lignes_list,
                ));
                ?>
            </select>
        </div>

        <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="form-group">
            <label>ITINÉRAIRE <?= $i; ?><?= $i <= 2 ? ' *' : ' (optionnel)'; ?></label>
            <select class="form-control form-control-sm js-ligne-cie" name="etape<?= $i; ?>" <?= $i <= 2 ? 'required' : ''; ?>>
                <option value=""></option>
                <?php
                $this->load->view('beagle/pages/_ligne/_options_ligne_compagnie_arrivee', array(
                    'lignes_par_compagnie_arrivee' => $lignes_par_cie,
                    'lignes' => $lignes_list,
                ));
                ?>
            </select>
        </div>
        <?php endfor; ?>

        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
            <button class="btn btn-primary" type="submit">Enregistrer la composition</button>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<!-- ========== MODALE : AJOUTER ESCALE + PRIX (vente classique) ========== -->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="modal-add-escale" style="perspective: none;">
    <div class="modal-content itin-modal">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">Ajouter une escale + prix</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open('lignes/escales/' . $this->session->company->ekey . '/add', array('class' => 'modal-body form', 'id' => 'form-add-escale')); ?>
        <input type="hidden" name="tab" value="escales">

        <p class="itin-hint">
            Choisir l’itinéraire parent, puis la destination escale, puis le prix
            (utilisé au guichet classique quand on coche « Vente escale »).
        </p>

        <div class="form-group">
            <label for="ligne_parent">ITINÉRAIRE PARENT *</label>
            <select class="form-control form-control-sm" name="ligne_parent" id="ligne_parent" required>
                <option value="">Choisir l’itinéraire parent…</option>
                <?php
                $this->load->view('beagle/pages/_ligne/_options_ligne_compagnie_arrivee', array(
                    'lignes_par_compagnie_arrivee' => $lignes_par_cie,
                    'lignes' => $lignes_list,
                ));
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="gare_escale">DESTINATION ESCALE *</label>
            <select class="form-control form-control-sm" name="gare_escale" id="gare_escale" required disabled>
                <option value="">Choisir d’abord le parent…</option>
            </select>
            <small class="text-muted">Escales liées à l’itinéraire parent sélectionné (hors terminus).</small>
        </div>

        <div class="form-group">
            <label for="prix_escale_add">PRIX *</label>
            <input class="form-control form-control-sm" type="number" min="0" step="1" name="prix_escale"
                   id="prix_escale_add" required placeholder="ex. 3500" disabled>
        </div>
        <div class="form-group">
            <label for="ordre_escale_add">ORDRE</label>
            <input class="form-control form-control-sm" type="number" min="1" max="20" name="ordre_escale"
                   id="ordre_escale_add" placeholder="auto">
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
            <button class="btn btn-success" type="submit">Enregistrer l’escale</button>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<!-- ========== MODALE : AJOUTER ESCALE TPE ========== -->
<div class="modal-container colored-header colored-header-primary custom-width modal-effect-7"
     id="modal-add-tpe" style="perspective: none;">
    <div class="modal-content itin-modal">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">Ajouter Escale TPE</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open('lignes/escales/' . $this->session->company->ekey . '/add', array('class' => 'modal-body form', 'id' => 'form-add-tpe')); ?>
        <input type="hidden" name="tab" value="tpe">

        <div class="form-group">
            <label for="tpe_ligne_parent">ITINÉRAIRE PARENT ORIGINE–DESTINATION *</label>
            <select class="form-control form-control-sm" name="ligne_parent" id="tpe_ligne_parent" required>
                <option value="">Choisir l’itinéraire parent…</option>
                <?php
                $this->load->view('beagle/pages/_ligne/_options_ligne_compagnie_arrivee', array(
                    'lignes_par_compagnie_arrivee' => $lignes_par_cie,
                    'lignes' => $lignes_list,
                ));
                ?>
            </select>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" name="liaison_escale_escale" value="1"
                   id="tpe_liaison_check">
            <label class="form-check-label" for="tpe_liaison_check">
                Prix escale → escale (même itinéraire parent)
            </label>
        </div>

        <!-- Mode standard : escale ↔ origine / destination -->
        <div id="tpe-block-od">
            <div class="form-group">
                <label for="tpe_gare_escale">ESCALE *</label>
                <select class="form-control form-control-sm" name="gare_escale" id="tpe_gare_escale" required disabled>
                    <option value="">Choisir d’abord le parent…</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tpe_escale_origine">ESCALE ORIGINE</label>
                <input class="form-control form-control-sm" type="text" id="tpe_escale_origine" readonly
                       placeholder="Rempli selon le parent…">
            </div>

            <div class="form-group">
                <label for="tpe_prix_origine">PRIX ESCALE ORIGINE *</label>
                <input class="form-control form-control-sm" type="number" min="0" step="1"
                       name="prix_escale_origine" id="tpe_prix_origine" required placeholder="ex. 2500" disabled>
            </div>

            <div class="form-group">
                <label for="tpe_escale_destination">ESCALE DESTINATION</label>
                <input class="form-control form-control-sm" type="text" id="tpe_escale_destination" readonly
                       placeholder="Rempli selon le parent…">
            </div>

            <div class="form-group">
                <label for="tpe_prix_dest">PRIX ESCALE DESTINATION *</label>
                <input class="form-control form-control-sm" type="number" min="0" step="1"
                       name="prix_escale_tpe" id="tpe_prix_dest" required placeholder="ex. 3500" disabled>
            </div>

            <div class="form-group">
                <label for="tpe_ordre">ORDRE</label>
                <input class="form-control form-control-sm" type="number" min="1" max="20" name="ordre_escale"
                       id="tpe_ordre" placeholder="auto">
            </div>
        </div>

        <!-- Mode liaison escale → escale -->
        <div id="tpe-block-liaison" style="display:none;">
            <div class="form-group">
                <label for="tpe_escale_depart">ESCALE DÉPART *</label>
                <select class="form-control form-control-sm" name="id_escale_depart" id="tpe_escale_depart" disabled>
                    <option value="">Choisir d’abord le parent…</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tpe_escale_arrivee">ESCALE ARRIVÉE *</label>
                <select class="form-control form-control-sm" name="id_escale_arrivee" id="tpe_escale_arrivee" disabled>
                    <option value="">Choisir d’abord le parent…</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tpe_prix_liaison">PRIX ESCALE → ESCALE *</label>
                <input class="form-control form-control-sm" type="number" min="0" step="1"
                       name="prix_liaison" id="tpe_prix_liaison" placeholder="ex. 1500" disabled>
            </div>
            <small class="text-muted d-block mb-2">
                Les deux escales doivent déjà exister sur cet itinéraire parent (Escales tarifées ou TPE).
            </small>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<script>
(function () {
    var ekey = <?= json_encode((string) $this->session->company->ekey); ?>;
    var parentMetaUrl = <?= json_encode(site_url('lignes/parent_meta')); ?>;

    // Mémoriser l’onglet actif dans l’URL (?tab=)
    var tabs = document.querySelectorAll('#itineraireTabs a[data-toggle="tab"]');
    tabs.forEach(function (a) {
        a.addEventListener('shown.bs.tab', function (e) {
            var href = e.target.getAttribute('href') || '';
            var tab = 'transit';
            if (href.indexOf('tpe') !== -1) tab = 'tpe';
            else if (href.indexOf('escales') !== -1) tab = 'escales';
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url.toString());
            } catch (err) { /* ignore */ }
        });
    });

    // Filtre compagnie d’arrivée dans la modale transit
    var filterCie = document.getElementById('filter-cie-transit');
    if (filterCie) {
        function applyCieFilter() {
            var cie = filterCie.value || '';
            var selects = document.querySelectorAll('#form-compose-transit select.js-ligne-cie');
            selects.forEach(function (sel) {
                var groups = sel.querySelectorAll('optgroup');
                groups.forEach(function (og) {
                    var gCie = og.getAttribute('data-compagnie') || '';
                    var show = (cie === '' || gCie === cie);
                    og.style.display = show ? '' : 'none';
                    var opts = og.querySelectorAll('option');
                    opts.forEach(function (opt) {
                        opt.disabled = !show;
                        if (!show && opt.selected) {
                            sel.value = '';
                        }
                    });
                });
            });
        }
        filterCie.addEventListener('change', applyCieFilter);
    }

    function norm(s) {
        try {
            return (s || '').toString().toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[–—−]/g, '-')
                .replace(/\s+/g, ' ')
                .trim();
        } catch (e) {
            return (s || '').toString().toLowerCase().replace(/\s+/g, ' ').trim();
        }
    }

    function bindTableSearch(inputId, wrapId, clearId, metaId, tableId) {
        var searchInput = document.getElementById(inputId);
        var searchWrap = document.getElementById(wrapId);
        var searchClear = document.getElementById(clearId);
        var searchMeta = document.getElementById(metaId);
        var tableEsc = document.getElementById(tableId);
        if (!searchInput || !tableEsc) return;

        function filterRows() {
            var q = norm(searchInput.value);
            var tokens = q === '' ? [] : q.split(' ').filter(Boolean);
            var rows = tableEsc.querySelectorAll('tbody tr[data-search]');
            var visible = 0;
            var total = rows.length;
            if (searchWrap) {
                searchWrap.classList.toggle('has-value', q !== '');
            }
            rows.forEach(function (tr) {
                var hay = norm(tr.getAttribute('data-search') || tr.textContent || '');
                var ok = tokens.length === 0 || tokens.every(function (t) {
                    return hay.indexOf(t) !== -1;
                });
                tr.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });
            if (searchMeta) {
                if (q === '') {
                    searchMeta.hidden = true;
                    searchMeta.textContent = '';
                } else {
                    searchMeta.hidden = false;
                    searchMeta.textContent = visible === 0
                        ? 'Aucun résultat pour « ' + searchInput.value.trim() + ' »'
                        : visible + ' / ' + total + ' escale(s) affichée(s)';
                }
            }
        }

        searchInput.addEventListener('input', filterRows);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                filterRows();
                searchInput.blur();
            }
        });
        if (searchClear) {
            searchClear.addEventListener('click', function () {
                searchInput.value = '';
                filterRows();
                searchInput.focus();
            });
        }
    }

    bindTableSearch('escales-search', 'escales-search-wrap', 'escales-search-clear', 'escales-search-meta', 'table-escales');
    bindTableSearch('tpe-search', 'tpe-search-wrap', 'tpe-search-clear', 'tpe-search-meta', 'table-tpe');
    bindTableSearch('tpe-search', 'tpe-search-wrap', 'tpe-search-clear', 'tpe-search-meta', 'table-tpe-liaisons');

    // Formulaire ajout escale : parent → destination escale → prix
    var selParent = document.getElementById('ligne_parent');
    var selEscale = document.getElementById('gare_escale');
    var inputPrix = document.getElementById('prix_escale_add');
    var metaCache = {};

    function resetEscaleFields() {
        if (selEscale) {
            selEscale.innerHTML = '<option value="">Choisir d’abord le parent…</option>';
            selEscale.disabled = true;
            selEscale.value = '';
        }
        if (inputPrix) {
            inputPrix.value = '';
            inputPrix.disabled = true;
        }
    }

    function fillEscaleOptions(meta) {
        if (!selEscale) return;
        var list = (meta.escales || []).slice();
        list.sort(function (a, b) {
            var na = (a.nom || a.code || '').toString();
            var nb = (b.nom || b.code || '').toString();
            return na.localeCompare(nb, 'fr', { sensitivity: 'base' });
        });
        var html = '<option value="">Choisir la destination escale…</option>';
        list.forEach(function (e) {
            html += '<option value="' + String(e.value).replace(/"/g, '&quot;') + '">'
                + String(e.nom || e.code) + '</option>';
        });
        if (list.length === 0) {
            html = '<option value="">Aucune escale disponible pour ce parent</option>';
        }
        selEscale.innerHTML = html;
        selEscale.disabled = list.length === 0;
        if (inputPrix) {
            inputPrix.disabled = true;
            inputPrix.value = '';
        }
    }

    function onEscaleChange() {
        var has = !!(selEscale && selEscale.value);
        if (inputPrix) {
            inputPrix.disabled = !has;
            if (!has) {
                inputPrix.value = '';
            }
        }
    }

    function loadParentMeta(ident) {
        if (!ident) {
            resetEscaleFields();
            return;
        }
        if (metaCache[ident]) {
            fillEscaleOptions(metaCache[ident]);
            return;
        }
        if (selEscale) {
            selEscale.innerHTML = '<option value="">Chargement…</option>';
            selEscale.disabled = true;
        }
        var url = parentMetaUrl + '/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(ident);
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    resetEscaleFields();
                    if (selEscale) {
                        selEscale.innerHTML = '<option value="">Aucune donnée pour ce parent</option>';
                    }
                    return;
                }
                metaCache[ident] = data;
                fillEscaleOptions(data);
            })
            .catch(function () {
                resetEscaleFields();
                if (selEscale) {
                    selEscale.innerHTML = '<option value="">Erreur de chargement</option>';
                }
            });
    }

    if (selParent) {
        selParent.addEventListener('change', function () {
            loadParentMeta(selParent.value || '');
        });
    }
    if (selEscale) {
        selEscale.addEventListener('change', onEscaleChange);
    }

    // ---------- Formulaire Escale TPE ----------
    var tpeParent = document.getElementById('tpe_ligne_parent');
    var tpeEscale = document.getElementById('tpe_gare_escale');
    var tpeOrigine = document.getElementById('tpe_escale_origine');
    var tpeDest = document.getElementById('tpe_escale_destination');
    var tpePrixOrig = document.getElementById('tpe_prix_origine');
    var tpePrixDest = document.getElementById('tpe_prix_dest');
    var tpeCheck = document.getElementById('tpe_liaison_check');
    var tpeBlockOd = document.getElementById('tpe-block-od');
    var tpeBlockLiaison = document.getElementById('tpe-block-liaison');
    var tpeDep = document.getElementById('tpe_escale_depart');
    var tpeArr = document.getElementById('tpe_escale_arrivee');
    var tpePrixLiaison = document.getElementById('tpe_prix_liaison');
    var tpeMetaCache = {};

    function isLiaisonMode() {
        return !!(tpeCheck && tpeCheck.checked);
    }

    function setRequired(el, on) {
        if (!el) return;
        if (on) el.setAttribute('required', 'required');
        else el.removeAttribute('required');
    }

    function fillTpeLiaisonSelects(meta) {
        var list = (meta && meta.escales_on_parent) ? meta.escales_on_parent.slice() : [];
        var html = '<option value="">Choisir l’escale…</option>';
        list.forEach(function (e) {
            html += '<option value="' + String(e.id_escale) + '">'
                + String(e.nom || e.code) + '</option>';
        });
        if (list.length === 0) {
            html = '<option value="">Aucune escale sur ce parent</option>';
        }
        if (tpeDep) {
            tpeDep.innerHTML = html;
            tpeDep.disabled = list.length === 0 || !isLiaisonMode();
        }
        if (tpeArr) {
            tpeArr.innerHTML = html;
            tpeArr.disabled = list.length === 0 || !isLiaisonMode();
        }
        if (tpePrixLiaison) {
            tpePrixLiaison.disabled = list.length === 0 || !isLiaisonMode();
            if (list.length === 0) tpePrixLiaison.value = '';
        }
    }

    function toggleTpeMode() {
        var liaison = isLiaisonMode();
        if (tpeBlockOd) tpeBlockOd.style.display = liaison ? 'none' : '';
        if (tpeBlockLiaison) tpeBlockLiaison.style.display = liaison ? '' : 'none';

        setRequired(tpeEscale, !liaison);
        setRequired(tpePrixOrig, !liaison);
        setRequired(tpePrixDest, !liaison);
        setRequired(tpeDep, liaison);
        setRequired(tpeArr, liaison);
        setRequired(tpePrixLiaison, liaison);

        if (liaison) {
            if (tpeEscale) { tpeEscale.disabled = true; tpeEscale.value = ''; }
            if (tpePrixOrig) { tpePrixOrig.disabled = true; tpePrixOrig.value = ''; }
            if (tpePrixDest) { tpePrixDest.disabled = true; tpePrixDest.value = ''; }
            fillTpeLiaisonSelects(tpeMetaCache[tpeParent ? tpeParent.value : ''] || null);
        } else {
            if (tpeDep) { tpeDep.disabled = true; tpeDep.value = ''; }
            if (tpeArr) { tpeArr.disabled = true; tpeArr.value = ''; }
            if (tpePrixLiaison) { tpePrixLiaison.disabled = true; tpePrixLiaison.value = ''; }
            if (tpeParent && tpeParent.value && tpeMetaCache[tpeParent.value]) {
                fillTpeFromMeta(tpeMetaCache[tpeParent.value]);
            }
        }
    }

    function resetTpeFields() {
        if (tpeEscale) {
            tpeEscale.innerHTML = '<option value="">Choisir d’abord le parent…</option>';
            tpeEscale.disabled = true;
            tpeEscale.value = '';
        }
        if (tpeOrigine) tpeOrigine.value = '';
        if (tpeDest) tpeDest.value = '';
        if (tpePrixOrig) { tpePrixOrig.value = ''; tpePrixOrig.disabled = true; }
        if (tpePrixDest) { tpePrixDest.value = ''; tpePrixDest.disabled = true; }
        if (tpeDep) {
            tpeDep.innerHTML = '<option value="">Choisir d’abord le parent…</option>';
            tpeDep.disabled = true;
            tpeDep.value = '';
        }
        if (tpeArr) {
            tpeArr.innerHTML = '<option value="">Choisir d’abord le parent…</option>';
            tpeArr.disabled = true;
            tpeArr.value = '';
        }
        if (tpePrixLiaison) { tpePrixLiaison.value = ''; tpePrixLiaison.disabled = true; }
    }

    function fillTpeFromMeta(meta) {
        if (tpeOrigine) {
            tpeOrigine.value = (meta.origine && meta.origine.nom) ? meta.origine.nom : '';
        }
        if (tpeDest) {
            tpeDest.value = (meta.terminus && meta.terminus.nom) ? meta.terminus.nom : '';
        }
        if (tpeEscale) {
            var list = (meta.escales || []).slice();
            list.sort(function (a, b) {
                var na = (a.nom || a.code || '').toString();
                var nb = (b.nom || b.code || '').toString();
                return na.localeCompare(nb, 'fr', { sensitivity: 'base' });
            });
            var html = '<option value="">Choisir l’escale…</option>';
            list.forEach(function (e) {
                html += '<option value="' + String(e.value).replace(/"/g, '&quot;') + '">'
                    + String(e.nom || e.code) + '</option>';
            });
            if (list.length === 0) {
                html = '<option value="">Aucune escale disponible pour ce parent</option>';
            }
            tpeEscale.innerHTML = html;
            tpeEscale.disabled = list.length === 0 || isLiaisonMode();
            if (tpePrixOrig) { tpePrixOrig.disabled = true; tpePrixOrig.value = ''; }
            if (tpePrixDest) { tpePrixDest.disabled = true; tpePrixDest.value = ''; }
        }
        fillTpeLiaisonSelects(meta);
    }

    function onTpeEscaleChange() {
        var has = !!(tpeEscale && tpeEscale.value) && !isLiaisonMode();
        if (tpePrixOrig) tpePrixOrig.disabled = !has;
        if (tpePrixDest) tpePrixDest.disabled = !has;
        if (!has) {
            if (tpePrixOrig) tpePrixOrig.value = '';
            if (tpePrixDest) tpePrixDest.value = '';
        }
    }

    function loadTpeParentMeta(ident) {
        if (!ident) {
            resetTpeFields();
            return;
        }
        if (tpeMetaCache[ident]) {
            fillTpeFromMeta(tpeMetaCache[ident]);
            toggleTpeMode();
            return;
        }
        if (tpeEscale) {
            tpeEscale.innerHTML = '<option value="">Chargement…</option>';
            tpeEscale.disabled = true;
        }
        var url = parentMetaUrl + '/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(ident) + '?tpe=1';
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) {
                    resetTpeFields();
                    if (tpeEscale) {
                        tpeEscale.innerHTML = '<option value="">Aucune donnée pour ce parent</option>';
                    }
                    return;
                }
                tpeMetaCache[ident] = data;
                fillTpeFromMeta(data);
                toggleTpeMode();
            })
            .catch(function () {
                resetTpeFields();
                if (tpeEscale) {
                    tpeEscale.innerHTML = '<option value="">Erreur de chargement</option>';
                }
            });
    }

    if (tpeCheck) {
        tpeCheck.addEventListener('change', toggleTpeMode);
    }
    if (tpeParent) {
        tpeParent.addEventListener('change', function () {
            loadTpeParentMeta(tpeParent.value || '');
        });
    }
    if (tpeEscale) {
        tpeEscale.addEventListener('change', onTpeEscaleChange);
    }
    toggleTpeMode();
})();
</script>
