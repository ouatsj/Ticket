<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?
$arrivees_par_compagnie = !empty($arrivees_par_compagnie) ? $arrivees_par_compagnie : array();
$gares = !empty($gares) ? $gares : array();
$villes = !empty($villes) ? $villes : array();
$compagnies = !empty($compagnies) ? $compagnies : array();
?>

<div class="row">
    <div class="col-12 d-flex flex-wrap align-items-center mb-2 ml-4 pr-4">
        <button type="button" class="btn btn-space btn-info md-trigger" data-modal="add-new-gare">
            <span class="icon mdi mdi-plus-1 text-white"></span>
            AJOUTER UNE GARE D'ARRIVEE
        </button>
    </div>
</div>

<? if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger mx-4"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8'); ?></div>
<? endif; ?>
<? if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success mx-4"><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8'); ?></div>
<? endif; ?>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="add-new-gare" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">NOUVELLE GARE D'ARRIVEE</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open('Gares/add/' . $this->session->company->ekey, array('class' => 'modal-body form')); ?>
        <div class="form-group col-sm-4">
            <label>GARE</label>
            <select name="gareselected" class="form-control form-control-sm">
                <option value=""></option>
                <? foreach ($gares as $gnom): ?>
                    <option value="<?= $gnom->idengare; ?>">
                        <?= $gnom->garenom; ?>
                    </option>
                <? endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>NOM GARE</label>
            <input class="form-control form-control-sm"
                   type="text" name="nomgare"
                   placeholder="La designation de la gare" autocomplete="off" required>
        </div>
        <div class="row">
            <div class="form-group col-sm-4">
                <label>LOCALISATION DE LA GARE</label>
                <select name="villegare" class="form-control form-control-sm">
                    <option value=""></option>
                    <? foreach ($villes as $local): ?>
                        <option value="<?= $local->id_ville; ?>">
                            <?= $local->nom_ville; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>COMPAGNIE</label>
                <select name="compgare" class="form-control form-control-sm">
                    <option value=""></option>
                    <? foreach ($compagnies as $compagnie): ?>
                        <option value="<?= $compagnie->cle_compagnie; ?>">
                            <?= $compagnie->nom_compagnie; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>CONTACT</label>
                <input class="form-control form-control-sm" name="contact" type="text"
                       placeholder="" autocomplete="off">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
            <button class="btn btn-success" type="submit">OK</button>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">

        <? if (!empty($arrivees_par_compagnie)):
            $group_keys = array_keys($arrivees_par_compagnie);
            $first_key = reset($group_keys);
        ?>

            <div class="card card-table">
                <div class="card-header">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <strong>Gares d'arrivée par compagnie</strong>
                        </div>
                        <div class="col-md-6">
                            <input type="search"
                                   id="filtre-gare-arrivee"
                                   class="form-control form-control-sm"
                                   placeholder="Rechercher gare, code, ville…"
                                   autocomplete="off">
                        </div>
                    </div>
                    <ul class="nav nav-tabs nav-tabs-primary nav-tabs-classic flex-wrap" role="tablist" id="tabs-compagnie-arrivee-gares">
                        <? foreach ($arrivees_par_compagnie as $cle => $groupe):
                            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                            $nb = !empty($groupe['gares']) ? count($groupe['gares']) : 0;
                            $pane_id = 'gare-arr-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'gare-arr-') {
                                $pane_id = 'gare-arr-sans';
                            }
                            $is_active = ((string) $cle === (string) $first_key);
                        ?>
                            <li class="nav-item">
                                <a class="nav-link<?= $is_active ? ' active show' : ''; ?>"
                                   href="#<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                   data-toggle="tab"
                                   role="tab"
                                   aria-selected="<?= $is_active ? 'true' : 'false'; ?>">
                                    <?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?>
                                    <span class="badge badge-pill badge-primary"><?= (int) $nb; ?></span>
                                </a>
                            </li>
                        <? endforeach; ?>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">
                        <? foreach ($arrivees_par_compagnie as $cle => $groupe):
                            $pane_id = 'gare-arr-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'gare-arr-') {
                                $pane_id = 'gare-arr-sans';
                            }
                            $is_active = ((string) $cle === (string) $first_key);
                        ?>
                            <div class="tab-pane fade<?= $is_active ? ' active show' : ''; ?>"
                                 id="<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                 role="tabpanel">
                                <div class="row">
                                    <? foreach ($groupe['gares'] as $item):
                                        $actif_ga = (!isset($item->actif_ga) || (string) $item->actif_ga === '1' || (int) $item->actif_ga === 1) ? 1 : 0;
                                        $can_delete = !empty($item->can_delete);
                                    ?>
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-3 js-gare-arrivee-card<?= $actif_ga ? '' : ' opacity-75'; ?>"
                                             data-search="<?= htmlspecialchars(
                                                 strtolower($item->nom_gadest . ' ' . $item->code_gadest . ' ' . $item->nom_ville . ' ' . (isset($item->contactgare) ? $item->contactgare : '')),
                                                 ENT_QUOTES,
                                                 'UTF-8'
                                             ); ?>">
                                            <div class="card card-border card-full<?= $actif_ga ? '' : ' table-secondary'; ?>">
                                                <div class="card-header card-header-divider">
                                                    <?= htmlspecialchars($item->nom_gadest, ENT_QUOTES, 'UTF-8'); ?>
                                                    &nbsp;
                                                    <a href="<?= "#?{$item->code_gadest}&name={$item->nom_gadest}"; ?>"
                                                       class="md-trigger" data-modal="edit-gare-<?= $item->code_gadest; ?>"
                                                       title="Modifier">
                                                        <span class="fas fa-edit text-white"></span>
                                                    </a>
                                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                                         id="edit-gare-<?= $item->code_gadest; ?>">
                                                        <div class="modal-content">
                                                            <div class="modal-header modal-header-colored">
                                                                <h3 class="modal-title">MODIFICATION SUR <?= htmlspecialchars($item->nom_gadest, ENT_QUOTES, 'UTF-8'); ?></h3>
                                                                <button class="close modal-close" type="button"
                                                                        data-dismiss="modal" aria-hidden="true">
                                                                    <span class="mdi mdi-close text-white"></span>
                                                                </button>
                                                            </div>
                                                            <?= form_open('Gares/edit_/' . $this->session->company->ekey
                                                                . '/' . $item->code_gadest, array('class' => 'modal-body form')); ?>
                                                            <div class="form-group col-sm-4">
                                                                <label>GARE</label>
                                                                <select name="gareselected" class="form-control form-control-sm">
                                                                    <option value="<?= $item->idgaresdest; ?>"><?= $item->nom_gadest; ?></option>
                                                                    <? foreach ($gares as $gnom): ?>
                                                                        <option value="<?= $gnom->idengare; ?>">
                                                                            <?= $gnom->garenom; ?>
                                                                        </option>
                                                                    <? endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="row no-margin-y">
                                                                <div class="form-group">
                                                                    <label>NOM GARE </label>
                                                                    <input class="form-control form-control-sm" name="_garenom"
                                                                           value="<?= htmlspecialchars($item->nom_gadest, ENT_QUOTES, 'UTF-8'); ?>"
                                                                           type="text" autocomplete="off">
                                                                </div>
                                                                <div class="form-group col-sm-4">
                                                                    <label>LOCALISATION DE LA GARE</label>
                                                                    <select class="form-control form-control-sm" name="_glocalise">
                                                                        <option value="<?= $item->id_villega; ?>"><?= $item->nom_ville; ?></option>
                                                                        <? foreach ($villes as $local): ?>
                                                                            <option value="<?= $local->id_ville; ?>">
                                                                                <?= $local->nom_ville; ?>
                                                                            </option>
                                                                        <? endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-sm-4">
                                                                    <label>COMPAGNIE</label>
                                                                    <select name="_compagare" class="form-control form-control-sm">
                                                                        <option value="<?= $item->id_compaga; ?>"><?= $item->nom_compagnie; ?></option>
                                                                        <? foreach ($compagnies as $compagnie): ?>
                                                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                                                <?= $compagnie->nom_compagnie; ?>
                                                                            </option>
                                                                        <? endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group col-sm-4">
                                                                    <label>CONTACT</label>
                                                                    <input class="form-control form-control-sm" name="_contact" type="text"
                                                                           value="<?= htmlspecialchars(isset($item->contactgare) ? $item->contactgare : '', ENT_QUOTES, 'UTF-8'); ?>"
                                                                           autocomplete="off">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                                                                    <i class="icon icon-left mdi text-dark mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                                </button>
                                                                <button class="btn btn-success" type="submit">
                                                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                                                </button>
                                                            </div>
                                                            <?= form_close(); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p>code:<?= htmlspecialchars($item->code_gadest, ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p>ville:<?= htmlspecialchars($item->nom_ville, ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p>contact:<?= htmlspecialchars(isset($item->contactgare) ? $item->contactgare : '', ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p>
                                                        <? if ($actif_ga): ?>
                                                            <span class="badge badge-success">Active</span>
                                                        <? else: ?>
                                                            <span class="badge badge-secondary">Désactivée</span>
                                                        <? endif; ?>
                                                    </p>
                                                    <div class="d-flex flex-wrap" style="gap:0.35rem;">
                                                        <a href="<?= site_url('Gares/active_arrivee/' . $this->session->company->ekey . '/' . rawurlencode($item->code_gadest) . '/' . $actif_ga); ?>"
                                                           class="btn btn-sm btn-secondary"
                                                           title="<?= $actif_ga ? 'Masquer cette gare du guichet' : 'Réafficher cette gare au guichet'; ?>">
                                                            <?= $actif_ga
                                                                ? '<span class="text-danger">désactiver</span>'
                                                                : '<span class="text-success">activer</span>'; ?>
                                                        </a>
                                                        <? if ($can_delete): ?>
                                                            <a href="<?= site_url('Gares/delete_arrivee/' . $this->session->company->ekey . '/' . rawurlencode($item->code_gadest)); ?>"
                                                               class="btn btn-sm btn-danger"
                                                               onclick="return confirm('Supprimer définitivement cette gare d\'arrivée ?');"
                                                               title="Supprimer (jamais utilisée)">
                                                                supprimer
                                                            </a>
                                                        <? endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <? endforeach; ?>
                                </div>
                                <p class="text-muted filtre-gare-arrivee-vide d-none mb-0">Aucun résultat pour cette recherche.</p>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                var input = document.getElementById('filtre-gare-arrivee');
                if (!input) { return; }

                function filterActivePane() {
                    var q = (input.value || '').toLowerCase().trim();
                    var pane = document.querySelector('#tabs-compagnie-arrivee-gares ~ .card-body .tab-pane.active')
                        || document.querySelector('.tab-content > .tab-pane.active');
                    if (!pane) { return; }
                    var cards = pane.querySelectorAll('.js-gare-arrivee-card');
                    var visible = 0;
                    for (var i = 0; i < cards.length; i++) {
                        var hay = cards[i].getAttribute('data-search') || '';
                        var show = !q || hay.indexOf(q) !== -1;
                        cards[i].style.display = show ? '' : 'none';
                        if (show) { visible++; }
                    }
                    var empty = pane.querySelector('.filtre-gare-arrivee-vide');
                    if (empty) {
                        if (q && visible === 0) {
                            empty.classList.remove('d-none');
                        } else {
                            empty.classList.add('d-none');
                        }
                    }
                }

                input.addEventListener('input', filterActivePane);
                var tabLinks = document.querySelectorAll('#tabs-compagnie-arrivee-gares a[data-toggle="tab"]');
                for (var t = 0; t < tabLinks.length; t++) {
                    tabLinks[t].addEventListener('shown.bs.tab', filterActivePane);
                    if (window.jQuery) {
                        window.jQuery(tabLinks[t]).on('shown.bs.tab', filterActivePane);
                    }
                }
            })();
            </script>

        <? else: ?>

            <div class="col-lg-4 offset-lg-4">
                <div class="card">
                    <div class="card-header card-header-divider"><?= htmlspecialchars($this->session->company->nom_entreprise, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="card-body text-center text-capitalize">
                        <h2>AUCUNE GARE TROUVEE</h2>
                        <p>Vous pouvez en ajouter avec le bouton ci-dessus.</p>
                    </div>
                </div>
            </div>

        <? endif; ?>

    </div>
</div>
<!--End of file: view.php-->
<!--File location: application/views/beagle/pages/_gare/view.php-->
