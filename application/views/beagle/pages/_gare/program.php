<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
    if (!isset($reconductions_offres) || !is_array($reconductions_offres)) {
        $reconductions_offres = array();
    }
    if (!isset($reconduction_index) || !is_array($reconduction_index)) {
        $reconduction_index = array();
    }
    $__peut_prog = isset($this->session->agent->userole)
        && in_array((string) $this->session->agent->userole, array('1', '2', '5', '8', '15'), true);
?>


    <?php if ($msg = $this->session->flashdata('prog_portee_error')): ?>
    <div class="row mb-2 ml-2 mr-2">
        <div class="col-12 col-md-10">
            <div class="alert alert-danger mb-2 py-2"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
<?php endif; ?>
    <?php if ($msg = $this->session->flashdata('prog_quota_error')): ?>
    <div class="row mb-2 ml-2 mr-2">
        <div class="col-12 col-md-10">
            <div class="alert alert-danger mb-2 py-2"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
<?php endif; ?>
    <?php if ($msg = $this->session->flashdata('prog_edit_error')): ?>
    <div class="row mb-2 ml-2 mr-2">
        <div class="col-12 col-md-10">
            <div class="alert alert-danger mb-2 py-2"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
<?php endif; ?>
<script type="text/javascript">window.__SITE_BASE = <?= json_encode(rtrim(site_url(''), '/')); ?>;</script>
<div class="row mb-3 ml-2 mr-2" id="mode_depart_toggle">
    <div class="col-12 col-md-10">
        <div class="alert alert-info mb-2 py-2">
            <strong>Départs</strong> —
            par défaut <strong>toute gare</strong> (toutes les sous-gares). Cochez <strong>Sous-gares</strong> pour activer les cases et restreindre le départ.
        </div>
    </div>
</div>

<?php if ($__peut_prog && !empty($reconductions_offres)): ?>
<div class="row mb-2 ml-2 mr-2">
    <div class="col-12 col-md-10">
        <div class="alert alert-warning mb-2 py-2">
            <strong>Sièges restants</strong> —
            <?= count($reconductions_offres); ?> départ<?= count($reconductions_offres) > 1 ? 's' : ''; ?>
            en amont avec places libres.
            <a href="#" class="alert-link js-reco-open">Créer un départ avec ces sièges</a>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
  function applyScopeMode(box) {
    if (!box) return;
    var mode = box.querySelector('.js-scope-mode[value="sousgare"]');
    var isSg = mode && mode.checked;
    var wrap = box.querySelector('.js-sg-checks-wrap');
    if (wrap) {
      wrap.style.opacity = isSg ? '1' : '0.55';
      wrap.style.pointerEvents = isSg ? '' : 'none';
    }
    box.querySelectorAll('.js-sg-check').forEach(function (c) {
      var locked = c.getAttribute('data-locked') === '1';
      if (isSg) {
        c.disabled = locked;
        if (locked) c.checked = true;
      } else {
        c.disabled = true;
        c.checked = true;
      }
    });
    box.querySelectorAll('.js-sg-check-all, .js-sg-uncheck-all').forEach(function (b) {
      b.disabled = !isSg;
    });
  }
  function bindPortee(root) {
    root = root || document;
    root.querySelectorAll('[id^="portee_sousgares"]').forEach(function (box) {
      box.querySelectorAll('.js-scope-mode').forEach(function (radio) {
        radio.addEventListener('change', function () { applyScopeMode(box); });
      });
      applyScopeMode(box);
    });
    root.querySelectorAll('.js-sg-check-all').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var box = btn.closest('[id^="portee_sousgares"]') || btn.closest('.form-group');
        if (!box) return;
        box.querySelectorAll('.js-sg-check').forEach(function (c) { if (!c.disabled) c.checked = true; });
      };
    });
    root.querySelectorAll('.js-sg-uncheck-all').forEach(function (btn) {
      btn.onclick = function (e) {
        e.preventDefault();
        var box = btn.closest('[id^="portee_sousgares"]') || btn.closest('.form-group');
        if (!box) return;
        box.querySelectorAll('.js-sg-check').forEach(function (c) {
          if (!c.disabled && c.getAttribute('data-locked') !== '1') c.checked = false;
        });
      };
    });
  }
  window.__applyPorteeScopeMode = applyScopeMode;
  document.addEventListener('DOMContentLoaded', function () {
    bindPortee(document);
    // Les cases désactivées ne partent pas en POST : les réactiver juste avant envoi si cochées
    document.querySelectorAll('form').forEach(function (form) {
      form.addEventListener('submit', function () {
        form.querySelectorAll('.js-sg-check:checked[disabled]').forEach(function (c) {
          c.disabled = false;
        });
      });
    });
  });
})();
</script>

<? if (!empty($progs)): ?>
    <div class="row">
        <p class="mt-0 mb-2 ml-4">
            <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $gare_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $gare_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
            </a>
             <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
                       
            <button class="btn btn-space btn-secondary md-trigger"
                    data-modal="new-prog">
                <i class="fas fa-plus text-success"></i>&nbsp;AJOUTER PROGRAMME&nbsp;
            </button>
            <button class="btn btn-space btn-secondary md-trigger js-reco-open"
                    data-modal="modal-reconduction">
                <i class="fas fa-share-square text-warning"></i>&nbsp;SIÈGES RESTANTS&nbsp;
                <?php if (!empty($reconductions_offres)): ?>
                    <span class="badge badge-warning"><?= count($reconductions_offres); ?></span>
                <?php endif; ?>
            </button>
            <?endif;?>
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '3' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
                       
            <button class="btn btn-space btn-secondary addtirage md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                    data-modal="form-add-liste">
                <i class="fas fa-list-alt text-info"></i>&nbsp;TIRAGE LISTE&nbsp;
            </button>

            <?endif;?>
            <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
                       
                <button class="btn btn-space btn-secondary listetirage md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        data-modal="add-liste">
                    <i class="fas fa-list-alt text-info"></i>&nbsp;LISTE&nbsp;
                </button>
                <button class="btn btn-space btn-secondary addvoir md-trigger" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                        data-modal="form-liste">
                    <i class="fas fa-list-alt text-info"></i>&nbsp;VOIR LISTE&nbsp;
                </button>


                <button class="btn btn-space btn-secondary md-trigger" title="Ajouter sous gare"
                            data-modal="new-sousgare">
                        <i class="fas fa-plus text-info"></i>&nbsp;AJOUTER SOUS-GARE&nbsp;
                </button>
                <a href="<?= site_url("gares/sousgares/{$this->session->company->ekey}/{$bus_stop->code_gaexp}/{$conex->roleattribut}/{$gare_stop->idsousgare}"); ?>"
                       class="btn btn-space btn-secondary">
                        <i class="fas fa-book text-info"></i>&nbsp;&nbsp;AFFICHER SOUS GARES
                </a>&nbsp;

                <button class="btn btn-space btn-secondary md-trigger" title="Ajouter"
                            data-modal="new-sous">
                        <i class="fas fa-plus text-success"></i>&nbsp;AJOUTER TEMPS&nbsp;
                </button>
                <a href="<?= site_url("gares/souslignegares/{$this->session->company->ekey}/{$bus_stop->code_gaexp}/{$conex->roleattribut}/{$gare_stop->idsousgare}"); ?>"
                       class="btn btn-space btn-secondary">
                        <i class="fas fa-book text-info"></i>&nbsp;&nbsp;AFFICHER LES TEMPS
                </a>&nbsp;
            
            <?endif;?>
        </p>
    </div>
 <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '3' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
    <?php
        $progs_par_compagnie = !empty($progs_par_compagnie) ? $progs_par_compagnie : array();
        if (empty($progs_par_compagnie) && !empty($progs)) {
            $progs_par_compagnie = array('_tous' => array(
                'cle_compagnie' => null,
                'nom_compagnie' => 'Tous',
                'programmes' => $progs,
            ));
        }
        $group_keys = array_keys($progs_par_compagnie);
        $first_key = !empty($group_keys) ? reset($group_keys) : null;
        if (!isset($corr_index) || !is_array($corr_index)) { $corr_index = array(); }
        if (!isset($reconduction_index) || !is_array($reconduction_index)) { $reconduction_index = array(); }
        if (!isset($reconductions_offres) || !is_array($reconductions_offres)) { $reconductions_offres = array(); }
    ?>
    <div class="row">
        <div class="col-sm-12">

            <div class="card card-table">

                <div class="card-header">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <strong>Programmes par compagnie</strong>
                        </div>
                        <div class="col-md-6">
                            <input type="search" id="filtre-prog" class="form-control form-control-sm"
                                   placeholder="Rechercher code, ligne, date, heure…" autocomplete="off">
                        </div>
                    </div>
                    <ul class="nav nav-tabs nav-tabs-primary nav-tabs-classic flex-wrap" role="tablist" id="tabs-prog-compagnie">
                        <? foreach ($progs_par_compagnie as $cle => $groupe):
                            $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                            $nb = !empty($groupe['programmes']) ? count($groupe['programmes']) : 0;
                            $pane_id = 'prog-comp-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'prog-comp-') { $pane_id = 'prog-comp-sans'; }
                            $is_active = ($cle === $first_key);
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
                        <? foreach ($progs_par_compagnie as $cle => $groupe):
                            $pane_id = 'prog-comp-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $cle);
                            if ($pane_id === 'prog-comp-') { $pane_id = 'prog-comp-sans'; }
                            $is_active = ($cle === $first_key);
                            $table_id = 'table-' . $pane_id;
                        ?>
                            <div class="tab-pane fade<?= $is_active ? ' active show' : ''; ?>"
                                 id="<?= htmlspecialchars($pane_id, ENT_QUOTES, 'UTF-8'); ?>"
                                 role="tabpanel">
                                <div class="table-responsive noSwipe">
                                    <table class="table table-striped table-hover table-progs"
                                           id="<?= htmlspecialchars($table_id, ENT_QUOTES, 'UTF-8'); ?>">
                                        <thead>
                                        <tr>
                                            <th>CODE</th>
                                            <th>CATEGORIE <br>PASSAGER</th>
                                            <th>TARIF</th>
                                            <th>LIGNE</th>
                                            <th>DATE</th>
                                            <th>HEURE</th>
                                            <th>DEBUT</th>
                                            <th>FIN</th>
                                            <th>ACTION</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                            <? foreach ($groupe['programmes'] as $item): ?>
                                <? 
                                    $cid=$this->session->company->ekey;
                                    $__prog_stats = !empty($prog_page_stats) ? $prog_page_stats : array(
                                        'passager_nbr' => array(),
                                        'sousgares' => array(),
                                        'ventes_sg' => array(),
                                    );
                                    $__code_prog = $item->code_progr;
                                    $__corr = (!empty($corr_index) && isset($corr_index[$__code_prog]))
                                        ? $corr_index[$__code_prog] : null;
                                    $__reco = (!empty($reconduction_index) && isset($reconduction_index[$__code_prog]))
                                        ? $reconduction_index[$__code_prog] : null;
                                    $nb = (object) array(
                                        'nbr' => isset($__prog_stats['passager_nbr'][$__code_prog])
                                            ? (int) $__prog_stats['passager_nbr'][$__code_prog]
                                            : 0,
                                    );
                                    $__psg2 = isset($__prog_stats['sousgares'][$__code_prog])
                                        ? $__prog_stats['sousgares'][$__code_prog]
                                        : array();
                                ?>
                                <tr>
                                    <td><?= $item->code_progr; ?>/
                                        <span><?= $item->depart_code; ?></span>
                                        <?php
                                            $__ids = array();
                                            foreach ($__psg2 as $__r) {
                                                if (!empty($__r->idsousgare)) {
                                                    $__ids[] = (int) $__r->idsousgare;
                                                }
                                            }
                                            if (empty($__ids) && !empty($item->idsousgare_prog)) {
                                                $__ids[] = (int) $item->idsousgare_prog;
                                            }
                                            $__ventes = isset($__prog_stats['ventes_sg'][$__code_prog])
                                                ? $__prog_stats['ventes_sg'][$__code_prog]
                                                : array();
                                            $__sieges_occ = array();
                                            if (!isset($this->m_programme)) {
                                                $this->load->model('Programme_model', 'm_programme');
                                            }
                                            if (isset($this->m_programme)) {
                                                $__sieges_occ = $this->m_programme->sieges_occupes_programme($__code_prog);
                                            }
                                            $__ventes_attr = array();
                                            foreach ($__ventes as $__sg => $__nb) {
                                                $__ventes_attr[] = ((int) $__sg) . ':' . ((int) $__nb);
                                            }
                                            $__ventes_total = 0;
                                            foreach ($__ventes as $__nb_v) {
                                                $__ventes_total += (int) $__nb_v;
                                            }
                                            $__peut_supprimer_prog = ($nb->nbr === 0 && $__ventes_total === 0 && empty($__corr));
                                        ?>
                                        <? if (!empty($__psg2)): ?>
                                            <br><small class="text-warning"><?php
                                                $__names = array();
                                                foreach ($__psg2 as $__r) {
                                                    $__names[] = !empty($__r->nomsousgare) ? $__r->nomsousgare : ('#'.$__r->idsousgare);
                                                }
                                                echo htmlspecialchars(implode(', ', $__names), ENT_QUOTES, 'UTF-8');
                                            ?></small>
                                        <? elseif (empty($item->idsousgare_prog)): ?>
                                            <br><small class="text-info">Gare (toutes SG)</small>
                                        <? else: ?>
                                            <br><small class="text-warning">Sous-gare #<?= (int) $item->idsousgare_prog; ?></small>
                                        <? endif; ?>
                                        <?php
                                            if ($__corr) {
                                                if (!isset($this->m_programme_correspondance)) {
                                                    $this->load->model('Programme_correspondance_model', 'm_programme_correspondance');
                                                }
                                                echo $this->m_programme_correspondance->badge_html($__corr);
                                            }
                                            if ($__reco) {
                                                if (!isset($this->m_programme_reconduction)) {
                                                    $this->load->model('Programme_reconduction_model', 'm_programme_reconduction');
                                                }
                                                echo $this->m_programme_reconduction->badge_html($__reco);
                                            }
                                        ?>
                                    </td>
                                    <td><?= $item->categori; ?><br><?= $nb->nbr; ?></td>
                                    <td><?= $item->type_tarifs; ?></td>
                                    <td><?= $item->nom_ligne; ?></td>
                                    <td><?= $item->dateheure_prog; ?></td>
                                    <td><?= $item->heure; ?></td>
                                    <td><?= $item->intervalle1; ?></td>
                                    <td><?= $item->intervalle2;?></td>
                                    <td class="actions">
                                    <? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
   
                                        <a href="<?= "#?{$item->code_progr}&"; ?>" title="Ajouter Sous axe au programme"
                                            class="md-trigger" data-modal="prog-ajout-<?= $item->code_progr; ?>"
                                            style="display:none;">
                                            <span class="fas fa-edit text-success"></span>
                                        </a>
                                        
                                        <a href="<?= "#?{$item->code_progr}&&{$item->depart_code}"; ?>"
                                            data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                            data-code="<?= $item->code_progr; ?>"
                                            data-departcd="<?= $item->depart_code; ?>"
                                            data-categorie="<?= $item->categori; ?>"
                                            data-categnbplace="<?= $item->nbr_place; ?>"
                                            data-typtarif="<?= $item->typetarif; ?>"
                                            data-eure="<?= $item->id_heur. '.' .$item->ligne_id. '.' .$item->heure; ?>"
                                            data-inter1="<?= $item->intervalle1; ?>"
                                            data-inter2="<?= $item->intervalle2; ?>"
                                            data-pdate="<?= $item->date_progr; ?>"
                                            data-idsousgare_prog="<?= !empty($item->idsousgare_prog) ? (int) $item->idsousgare_prog : ''; ?>"
                                            <?php /* stats sous-gares / ventes préchargées ($prog_page_stats) */ ?>
                                            data-sieges-occupes="<?= htmlspecialchars(implode(',', $__sieges_occ), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-portee-sgs="<?= htmlspecialchars(implode(',', $__ids), ENT_QUOTES, 'UTF-8'); ?>"
                                            data-ventes-sgs="<?= htmlspecialchars(implode(',', $__ventes_attr), ENT_QUOTES, 'UTF-8'); ?>"
                                            class="addgprogramme md-trigger"
                                            data-modal="prog-edit-0">&nbsp;
                                                <span class="fas fa-edit text-warning"></span>
                                        </a>&nbsp;

                                        <a href="#"
                                           class="js-corr-link"
                                           title="<?= !empty($__corr) ? 'Voir / gérer le lien de correspondance' : 'Lier une correspondance'; ?>"
                                           data-code="<?= htmlspecialchars($item->code_progr, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-ligne="<?= htmlspecialchars(isset($item->ligne_id) ? $item->ligne_id : (isset($item->ident_ligne) ? $item->ident_ligne : ''), ENT_QUOTES, 'UTF-8'); ?>"
                                           data-nom="<?= htmlspecialchars($item->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-heure="<?= htmlspecialchars($item->heure, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-pdate="<?= htmlspecialchars($item->date_progr, ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="fas fa-exchange-alt <?= !empty($__corr) ? 'text-success' : 'text-info'; ?>"></span>
                                        </a>&nbsp;
                                        <?php
                                            $__reco_role = $__reco && isset($__reco['role']) ? $__reco['role'] : '';
                                            $__deja_sorti = $__reco && !empty($__reco['sorti']);
                                            if ($__reco_role !== 'cible' && !$__deja_sorti):
                                        ?>
                                        <a href="#"
                                           class="js-sortie-decl"
                                           title="Déclarer la sortie — choisir les sièges à publier aux gares aval"
                                           data-code="<?= htmlspecialchars($item->code_progr, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-nom="<?= htmlspecialchars($item->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-heure="<?= htmlspecialchars($item->heure, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-pdate="<?= htmlspecialchars($item->date_progr, ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="fas fa-sign-out-alt text-warning"></span>
                                        </a>&nbsp;
                                        <?php endif; ?>
                                        <?php if ($__peut_supprimer_prog): ?>
                                        <a href="#"
                                           class="js-prog-delete"
                                           title="Supprimer ce programme (aucun passager)"
                                           data-code="<?= htmlspecialchars($item->code_progr, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-nom="<?= htmlspecialchars($item->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>"
                                           data-heure="<?= htmlspecialchars($item->heure, ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="fas fa-trash text-danger"></span>
                                        </a>&nbsp;
                                        <?php endif; ?>
                                        <a href="<?= site_url('Gares/activer/' . $this->session->company->ekey . '/' . $item->code_progr. '/' . $item->gareidentif. '/' . $item->statut_prog.'/'.$conex->roleattribut.'/'.$gare_stop->idsousgare);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->statut_prog === 'actif') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                            class="icon mdi text-success">activer</span>' ?>
                                        </a>&nbsp;
                                        &nbsp;
                                    <? endif; ?>
                                    </td>
                                </tr>
                            
                            <? endforeach; ?>

                                        </tbody>
                                    </table>
                                    <p class="text-muted filtre-prog-vide d-none mb-0">Aucun résultat pour cette recherche.</p>
                                </div>
                            </div>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
            (function () {
                var input = document.getElementById('filtre-prog');
                if (!input) { return; }
                function filterActivePane() {
                    var q = (input.value || '').toLowerCase().trim();
                    var pane = document.querySelector('.tab-content > .tab-pane.active');
                    if (!pane) { return; }
                    var rows = pane.querySelectorAll('tbody tr');
                    var visible = 0;
                    for (var i = 0; i < rows.length; i++) {
                        var show = !q || (rows[i].textContent || '').toLowerCase().indexOf(q) !== -1;
                        rows[i].style.display = show ? '' : 'none';
                        if (show) { visible++; }
                    }
                    var emptyMsg = pane.querySelector('.filtre-prog-vide');
                    if (emptyMsg) {
                        emptyMsg.classList.toggle('d-none', !(q && visible === 0));
                    }
                }
                input.addEventListener('input', filterActivePane);
                var tabLinks = document.querySelectorAll('#tabs-prog-compagnie a[data-toggle="tab"]');
                for (var t = 0; t < tabLinks.length; t++) {
                    tabLinks[t].addEventListener('shown.bs.tab', filterActivePane);
                    if (window.jQuery) {
                        window.jQuery(tabLinks[t]).on('shown.bs.tab', filterActivePane);
                    }
                }
            })();
            </script>

            <script>
            (function () {
                var ekey = <?= json_encode($this->session->company->ekey); ?>;
                function progDeleteCsrf(body) {
                    var metaToken = document.querySelector('meta[name="csrf-token"]');
                    var metaParam = document.querySelector('meta[name="csrf-param"]');
                    var name = (metaParam && metaParam.getAttribute('content')) || 'csrf_raketa';
                    var hash = metaToken ? metaToken.getAttribute('content') : '';
                    if (hash) body.set(name, hash);
                    return body;
                }
                document.addEventListener('click', function (e) {
                    var btn = e.target.closest ? e.target.closest('.js-prog-delete') : null;
                    if (!btn) return;
                    e.preventDefault();
                    var code = btn.getAttribute('data-code') || '';
                    var nom = btn.getAttribute('data-nom') || '';
                    var heure = btn.getAttribute('data-heure') || '';
                    if (!code) return;
                    var msg = 'Supprimer le programme ' + code;
                    if (nom || heure) {
                        msg += ' (' + [nom, heure].filter(Boolean).join(' / ') + ')';
                    }
                    msg += ' ? Cette action est définitive.';
                    if (!window.confirm(msg)) return;
                    var body = progDeleteCsrf(new URLSearchParams());
                    body.set('code_progr', code);
                    var base = (window.__SITE_BASE || '').replace(/\/$/, '');
                    fetch(base + '/Programmes/delete_programme/' + encodeURIComponent(ekey), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                        body: body.toString()
                    }).then(function (r) {
                        return r.text().then(function (t) {
                            try { return JSON.parse(t); }
                            catch (err) { throw new Error('Réponse non JSON'); }
                        });
                    }).then(function (data) {
                        if (data && data.ok) {
                            window.location.reload();
                            return;
                        }
                        var errMsg = (data && (data.message || data.error)) ? (data.message || data.error) : 'Suppression impossible.';
                        window.alert(errMsg);
                    }).catch(function () {
                        window.alert('Erreur réseau lors de la suppression.');
                    });
                });
            })();
            </script>

        </div>
    </div>

    <?php if ($__peut_prog): ?>
    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="prog-edit-0">
        <div class="modal-content">
            <div class="modal-header modal-header-colored">
                <h3 class="modal-title" id="Titleprog"></h3>
                <button class="close modal-close" type="button"
                data-dismiss="modal" aria-hidden="true"><span
                class="mdi mdi-close text-white"></span>
                </button>
            </div>
            <?= form_open('', array('class' => 'modal-body form', 'id' => 'formprog')); ?>

            <div class="row">
                <input class="form-control form-control-sm" name="ouotancien" id="ouotafinancien"
                value="" type="hidden" autocomplete="off">
                <input class="form-control form-control-sm" name="ouotnouveau" id="ouotafinnouveau"
                value="" type="hidden" autocomplete="off">
               <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">

                <div class="form-group col-sm-12" id="portee_sousgares_box_edit">
                    <label>PORTÉE DU DÉPART</label>
                    <div class="mb-2" style="display:flex;flex-wrap:wrap;align-items:center;gap:1.25rem;">
                        <label class="mb-0" style="font-weight:400;color:#404040;cursor:pointer;white-space:nowrap;">
                            <input type="radio" class="js-scope-mode" name="scope_depart" value="gare" checked style="margin-right:0.4rem;vertical-align:middle;">
                            Toute gare
                        </label>
                        <label class="mb-0" style="font-weight:400;color:#404040;cursor:pointer;white-space:nowrap;">
                            <input type="radio" class="js-scope-mode" name="scope_depart" value="sousgare" style="margin-right:0.4rem;vertical-align:middle;">
                            Sous-gares
                        </label>
                    </div>
                    <div class="js-sg-checks-wrap" style="opacity:0.55">
                        <div class="mb-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary js-sg-check-all" disabled>Tout cocher</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary js-sg-uncheck-all" disabled>Tout décocher</button>
                            <small class="text-muted ml-2">Une sous-gare ayant déjà vendu ne peut pas être décochée.</small>
                        </div>
                        <div class="row" id="scope_sousgares_edit_list">
                            <?php if (!empty($sousgares)): ?>
                                <?php foreach ($sousgares as $sous): ?>
                                    <div class="form-group col-sm-4 col-md-3 mb-1">
                                        <label class="custom-control custom-checkbox mb-0">
                                            <input class="custom-control-input js-sg-check" type="checkbox" name="scope_sousgares[]"
                                                   value="<?= (int) $sous->idsousgare; ?>" checked disabled
                                                   data-locked="0"
                                                   data-sg-name="<?= htmlspecialchars($sous->nomsousgare, ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="custom-control-label">
                                                <?= htmlspecialchars($sous->nomsousgare, ENT_QUOTES, 'UTF-8'); ?>
                                                <small class="js-sg-ventes text-danger font-weight-bold" style="display:none;"></small>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label>CATEGORIE</label>
                    <select class="form-control form-control-sm" id="idcateg" name="categorie">
                    <option value=""></option>
                        <? foreach ($categories as $categbus): ?>
                            <option value="<?= $categbus->categorie; ?>">
                            <?= $categbus->categorie; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-sm-4">
                    <label>TYPE TARIF</label>
                        <select class="form-control form-control-sm" id="typetaf" name="tariftype">
                        <option value=""></option>
                        <? foreach ($bases as $typetarif): ?>
                        <option value="<?= $typetarif->id_tarifs; ?>">
                    <?= "{$typetarif->type_tarifs}"; ?>
                        </option>
                    <? endforeach; ?>
                    </select>
                </div>
                <?php
                    $this->load->view('beagle/pages/_gare/_partial_depart_compagnie', array(
                        'lignesheure' => !empty($lignesheure) ? $lignesheure : array(),
                        'lignesheure_par_compagnie' => !empty($lignesheure_par_compagnie) ? $lignesheure_par_compagnie : array(),
                        'depart_name' => 'heureprog',
                        'depart_id' => 'progh',
                        'compagnie_id' => 'compagnie-arrivee-edit',
                        'depart_label' => 'DEPART',
                        'col_comp' => 'col-sm-4',
                        'col_dep' => 'col-sm-4',
                    ));
                ?>
                <?php $this->load->view('beagle/pages/_gare/_partial_quota_sieges', array('categ_select_id' => 'idcateg', 'quota_mode' => 'edit')); ?>
                <div class="form-group col-sm-4">
                    <label>DATE</label>
                        <input class="form-control form-control-sm" type="date" id ="progdate" name="dateprogramme" value="">
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
    <?php endif; ?>
<?endif;?>
<? else: ?>
<? if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2' OR $this->session->agent->userole === '5' OR $this->session->agent->userole === '8' OR $this->session->agent->userole === '15'): ?>
    <div class="row">
        <div class="col-md-4 offset-4">
            <div class="card card-divider">

                <div class="card-header card-header-divider">
                    <?= "<strong>{$this->session->company->nom_entreprise}</strong>"; ?>
                    <span class="card-subtitle">AUCUN PRGOGRAMME ENREGISTRÉ</span>
                </div>

                <div class="card-body text-center">
                    POUR AJOUTER UN PROGRAMME IL VOUS SUFFIT DE CLIQUER SUR LE BOUTON EN BAS DE PAGE
                </div>

                <div class="card-footer text-center">

                    <div class="tools">
                        <button class="btn btn-rounded btn-space btn-success md-trigger"
                                data-modal="new-prog">
                            <i class="fas fa-edit text-success"></i>
                            CRÉER UN NOUVEAU PROGRAMME
                        </button>
                        <button class="btn btn-rounded btn-space btn-warning md-trigger js-reco-open"
                                data-modal="modal-reconduction">
                            <i class="fas fa-share-square"></i>
                            SIÈGES RESTANTS
                        </button>
                    </div>
                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                         id="new-prog" style="perspective: none;">

                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title">CRÉER UN NOUVEAU PROGRAMME</h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true"><span
                                            class="mdi mdi-close text-white"></span></button>
                            </div>
                            
                            <?= form_open("Programmes/addg/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                            <div class="row no-margin-y">

                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">

                                <div class="form-group col-sm-12" id="portee_sousgares_box">
                                    <label>PORTÉE DU DÉPART</label>
                                    <div class="mb-2" style="display:flex;flex-wrap:wrap;align-items:center;gap:1.25rem;">
                                        <label class="mb-0" style="font-weight:400;color:#404040;cursor:pointer;white-space:nowrap;">
                                            <input type="radio" class="js-scope-mode" name="scope_depart" value="gare" checked style="margin-right:0.4rem;vertical-align:middle;">
                                            Toute gare
                                        </label>
                                        <label class="mb-0" style="font-weight:400;color:#404040;cursor:pointer;white-space:nowrap;">
                                            <input type="radio" class="js-scope-mode" name="scope_depart" value="sousgare" style="margin-right:0.4rem;vertical-align:middle;">
                                            Sous-gares
                                        </label>
                                    </div>
                                    <div class="js-sg-checks-wrap" style="opacity:0.55">
                                        <div class="mb-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-sg-check-all" disabled>Tout cocher</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-sg-uncheck-all" disabled>Tout décocher</button>
                                            <small class="text-muted ml-2">Cochez « Sous-gares » pour activer la sélection</small>
                                        </div>
                                        <div class="row">
                                            <?php if (!empty($sousgares)): ?>
                                                <?php foreach ($sousgares as $sous): ?>
                                                    <div class="form-group col-sm-4 col-md-3 mb-1">
                                                        <label class="custom-control custom-checkbox mb-0">
                                                            <input class="custom-control-input js-sg-check" type="checkbox" name="scope_sousgares[]"
                                                                   value="<?= (int) $sous->idsousgare; ?>" checked disabled>
                                                            <span class="custom-control-label"><?= htmlspecialchars($sous->nomsousgare, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="col-12"><small class="text-muted">Aucune sous-gare trouvée pour cette gare.</small></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-sm-3">
                                    <label>CATEGORIE</label>
                                    <select class="form-control form-control-sm" name="categorie" id="prog-categ-new-empty">
                                    <option value=""></option>
                                        <? foreach ($categories as $categbus): ?>
                                            <option value="<?= $categbus->categorie; ?>">
                                                <?= $categbus->categorie; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-3">
                                    <label>TYPE TARIF</label>
                                    <select class="form-control form-control-sm" name="tariftype">
                                        <option value=""></option>
                                        <? foreach ($bases as $typetarif): ?>
                                            <option value="<?= $typetarif->id_tarifs; ?>">
                                                <?= "{$typetarif->type_tarifs}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <?php
                                    $this->load->view('beagle/pages/_gare/_partial_depart_compagnie', array(
                                        'lignesheure' => !empty($lignesheure) ? $lignesheure : array(),
                                        'lignesheure_par_compagnie' => !empty($lignesheure_par_compagnie) ? $lignesheure_par_compagnie : array(),
                                        'depart_name' => 'itineraireheure',
                                        'depart_id' => 'itineraireheure-empty',
                                        'compagnie_id' => 'compagnie-arrivee-empty',
                                        'col_comp' => 'col-sm-3',
                                        'col_dep' => 'col-sm-3',
                                    ));
                                ?>
                                <?php $this->load->view('beagle/pages/_gare/_partial_quota_sieges', array('categ_select_id' => 'prog-categ-new-empty')); ?>
                                <div class="form-group col-sm-3">
                                    <label>DATE DEPART</label>
                                        <input class="form-control form-control-sm" type="date" name="datedp">

                                </div>

                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="button"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>

                                    <button class="btn btn-success modal-close" type="submit"
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
    <?endif;?>
<? endif; ?>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
        id="new-prog" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">CRÉER UN NOUVEAU PROGRAMME</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span></button>
        </div>
        
        <?= form_open("Programmes/addg/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

        <div class="row">

            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">

                                <div class="form-group col-sm-12" id="portee_sousgares_box">
                                    <label>PORTÉE DU DÉPART</label>
                                    <div class="mb-2" style="display:flex;flex-wrap:wrap;align-items:center;gap:1.25rem;">
                                        <label class="mb-0" style="font-weight:400;color:#404040;cursor:pointer;white-space:nowrap;">
                                            <input type="radio" class="js-scope-mode" name="scope_depart" value="gare" checked style="margin-right:0.4rem;vertical-align:middle;">
                                            Toute gare
                                        </label>
                                        <label class="mb-0" style="font-weight:400;color:#404040;cursor:pointer;white-space:nowrap;">
                                            <input type="radio" class="js-scope-mode" name="scope_depart" value="sousgare" style="margin-right:0.4rem;vertical-align:middle;">
                                            Sous-gares
                                        </label>
                                    </div>
                                    <div class="js-sg-checks-wrap" style="opacity:0.55">
                                        <div class="mb-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-sg-check-all" disabled>Tout cocher</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-sg-uncheck-all" disabled>Tout décocher</button>
                                            <small class="text-muted ml-2">Cochez « Sous-gares » pour activer la sélection</small>
                                        </div>
                                        <div class="row">
                                            <?php if (!empty($sousgares)): ?>
                                                <?php foreach ($sousgares as $sous): ?>
                                                    <div class="form-group col-sm-4 col-md-3 mb-1">
                                                        <label class="custom-control custom-checkbox mb-0">
                                                            <input class="custom-control-input js-sg-check" type="checkbox" name="scope_sousgares[]"
                                                                   value="<?= (int) $sous->idsousgare; ?>" checked disabled>
                                                            <span class="custom-control-label"><?= htmlspecialchars($sous->nomsousgare, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="col-12"><small class="text-muted">Aucune sous-gare trouvée pour cette gare.</small></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

            <div class="form-group col-sm-4">
                <label>CATEGORIE</label>
                <select class="form-control form-control-sm" name="categorie" id="prog-categ-new-main">
                <option value=""></option>
                    <? foreach ($categories as $categbus): ?>
                        <option value="<?= $categbus->categorie; ?>">
                            <?= $categbus->categorie; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE TARIF</label>
                <select class="form-control form-control-sm" name="tariftype">
                    <option value=""></option>
                    <? foreach ($bases as $typetarif): ?>
                        <option value="<?= $typetarif->id_tarifs; ?>">
                            <?= "{$typetarif->type_tarifs}"; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <?php
                $this->load->view('beagle/pages/_gare/_partial_depart_compagnie', array(
                    'lignesheure' => !empty($lignesheure) ? $lignesheure : array(),
                    'lignesheure_par_compagnie' => !empty($lignesheure_par_compagnie) ? $lignesheure_par_compagnie : array(),
                    'depart_name' => 'itineraireheure',
                    'depart_id' => 'itineraireheure-new',
                    'compagnie_id' => 'compagnie-arrivee-new',
                    'col_comp' => 'col-sm-4',
                    'col_dep' => 'col-sm-4',
                ));
            ?>
            <?php $this->load->view('beagle/pages/_gare/_partial_quota_sieges', array('categ_select_id' => 'prog-categ-new-main')); ?>
            <div class="form-group col-sm-4">
                <label>DATE DEPART</label>
                    <input class="form-control form-control-sm" type="date" name="datedp">

            </div>

        </div>
        <div class="form-group row">
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>

                    <button class="btn btn-success modal-close" type="submit"
                            data-dismiss="modal">
                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                    </button>
                
            </div>
        </div>
        <?= form_close(); ?>

    </div>

</div>
<!--tirage de liste agent d'appel-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-add-liste" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="ltTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'listeForm')); ?>
        <div class="row">
        
            <input type="hidden" name="categor" id="catego">
            <input type="hidden" name="garedp" value="<?=$bus_stop->idengare;?>">
            <input type="hidden" name="code_programe" id="code_program">
            <div class="form-group col-sm-4">
                    <label>BUS</label>
                    <input type="text" name="bus" id="busid" class="form-control form-control-sm" autocomplete="off" requered>
                
            </div>
            <?php
                $lignes_par_compagnie_arrivee = !empty($lignes_par_compagnie_arrivee)
                    ? $lignes_par_compagnie_arrivee
                    : array();
                if (empty($lignes_par_compagnie_arrivee) && !empty($alllignes) && isset($this->m_lignes)) {
                    $lignes_par_compagnie_arrivee = $this->m_lignes->group_by_compagnie_arrivee($alllignes);
                }
            ?>
            <div class="form-group col-sm-4">
                <label>COMPAGNIE D&apos;ARRIV&Eacute;E</label>
                <select class="form-control form-control-sm js-filtre-compagnie-arrivee"
                        id="compagnie_tirage"
                        name="compagnie_tirage"
                        data-target-depart="idligne"
                        required>
                    <option value="">— Choisir une compagnie —</option>
                    <? foreach ($lignes_par_compagnie_arrivee as $cle => $groupe):
                        $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                        $nb = !empty($groupe['lignes']) ? count($groupe['lignes']) : 0;
                    ?>
                        <option value="<?= htmlspecialchars((string) $cle, ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?> (<?= (int) $nb; ?>)
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm js-depart-filtre" name="ligne" id="idligne" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem):
                        $cle_ca = isset($ligneitem->cle_compagnie_arrivee) ? (string) $ligneitem->cle_compagnie_arrivee : '';
                        if ($cle_ca === '' && isset($ligneitem->id_compaga)) {
                            $cle_ca = (string) $ligneitem->id_compaga;
                        }
                        if ($cle_ca === '') {
                            $cle_ca = '_sans';
                        }
                    ?>
                        <option value="<?= htmlspecialchars($ligneitem->ident_ligne, ENT_QUOTES, 'UTF-8'); ?>"
                                data-compagnie="<?= htmlspecialchars($cle_ca, ENT_QUOTES, 'UTF-8'); ?>"
                                hidden disabled>
                            <?= htmlspecialchars($ligneitem->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <? endforeach; ?>
                </select>
                <small class="form-text text-muted">Sélectionnez d&apos;abord la compagnie d&apos;arrivée.</small>
            </div>
            <div class="form-group col-sm-4">
                <label>OUARTIER</label>
                <select class="form-control form-control-sm" name="touteligne">
                    <option value="">Toutes gares</option>
                    <option value="larle">Centrale</option>
                        <option value="escale">Escale</option>
                    
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="dateencour" id="choisirdate"
                        type="date" required>
            </div>
            <div class="form-group col-sm-4">
                <label>CATEGORIE</label>
                <select name="categoriebus" id="idcategoriebus" class="form-control form-control-sm" requered>
                    <option value="">Choisissez la categorie de bus</option>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>HEURE</label>
            
                <select class="form-control form-control-sm" name="heurex" id="choisirheure">
                    <option value="">Choisissez l'heure</option>
                    
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>CODE PROGRAMME</label>
                <select class="form-control form-control-sm" name="progra" id="idprog" required>
                    <option value="">Choisissez code programme</option>
                    
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="typeperso" id="typpersoid">
                    <option value=""></option>
                    <option value="chauffeur">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                </select>
            </div>
            
            <div class="form-group col-sm-4">
                <label>CHAUFFEUR</label>
                <select name="chauffeur" id="idchauf" class="form-control form-control-sm">
                    <option value="">Choisissez le chauffeur</option>
                    
                </select>
            </div>

            <div class="form-group col-sm-4">
                <label>TYPE_PERSONNE</label>
                <select class="form-control form-control-sm" name="typeperso1" id="typpersoid1">
                    <option value=""></option>
                    <option value="convoyeur">Personnel</option>
                    <option value="autrepersonnel">Autrepersonnel</option>
                </select>
            </div>

            
            <div class="form-group col-sm-4">
                <label>CONVOYEUR</label>
                <select name="convoi" id="idconvoi" class="form-control form-control-sm">
                    <option value="">Choisissez le convoyeur</option>

                </select>
            </div>
                        
            <div class="col-sm-6 text-center text-danger" style="display:none"
                    id="infosms" style="display:none">
                <p id="erreurinfo"></p>
            </div>
            <input type="hidden" name='idaxes' id="identcodepart">
            
        </div>

        <div class="form-group row">
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="reset"
                        data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success" type="submit" id="listeFormSubmit">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                </button>
            </div>
        </div>
        <?= form_close(); ?>
    </div>
</div>

<!--tirage de liste chef guichet-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="add-liste" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="listeTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'Formliste')); ?>
        <div class="row">
            <input type="hidden" name="depgares" value="<?=$bus_stop->idengare;?>">
            <?php
                $lignes_par_compagnie_arrivee = !empty($lignes_par_compagnie_arrivee)
                    ? $lignes_par_compagnie_arrivee
                    : array();
                if (empty($lignes_par_compagnie_arrivee) && !empty($alllignes) && isset($this->m_lignes)) {
                    $lignes_par_compagnie_arrivee = $this->m_lignes->group_by_compagnie_arrivee($alllignes);
                }
            ?>
            <div class="form-group col-sm-4">
                <label>COMPAGNIE D&apos;ARRIV&Eacute;E</label>
                <select class="form-control form-control-sm js-filtre-compagnie-arrivee"
                        id="compagnie_liste"
                        name="compagnie_liste"
                        data-target-depart="idligneliste"
                        required>
                    <option value="">— Choisir une compagnie —</option>
                    <? foreach ($lignes_par_compagnie_arrivee as $cle => $groupe):
                        $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                        $nb = !empty($groupe['lignes']) ? count($groupe['lignes']) : 0;
                    ?>
                        <option value="<?= htmlspecialchars((string) $cle, ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?> (<?= (int) $nb; ?>)
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm js-depart-filtre" name="ligneliste" id="idligneliste" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem):
                        $cle_ca = isset($ligneitem->cle_compagnie_arrivee) ? (string) $ligneitem->cle_compagnie_arrivee : '';
                        if ($cle_ca === '' && isset($ligneitem->id_compaga)) {
                            $cle_ca = (string) $ligneitem->id_compaga;
                        }
                        if ($cle_ca === '') {
                            $cle_ca = '_sans';
                        }
                    ?>
                        <option value="<?= htmlspecialchars($ligneitem->ident_ligne, ENT_QUOTES, 'UTF-8'); ?>"
                                data-compagnie="<?= htmlspecialchars($cle_ca, ENT_QUOTES, 'UTF-8'); ?>"
                                hidden disabled>
                            <?= htmlspecialchars($ligneitem->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <? endforeach; ?>
                </select>
                <small class="form-text text-muted">Sélectionnez d&apos;abord la compagnie d&apos;arrivée.</small>
            </div>
            <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="dateencourliste" id="choisirdateliste"
                        type="date" required>
            </div>
            <div class="form-group col-sm-4">
                <label>HEURE</label>
            
                <select class="form-control form-control-sm" name="heurexliste" id="choisirheureliste">
                    <option value="">Choisissez l'heure</option>
                    
                </select>
            </div>
            
        <div class="form-group row">
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
        </div>
        <?= form_close(); ?>
    </div>
</div>
<!-- voir liste passager chef de guichet-->
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-liste" style="perspective: none;">

    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="lisTitle"></h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                        class="mdi mdi-close text-white"></span>
            </button>
        </div>
        
        <?= form_open("", array('class' =>'modal-body form', 'id' => 'listForm')); ?>
        <div class="row">
        <input type="hidden" name="gared" value="<?=$bus_stop->idengare;?>">
        <div class="form-group col-sm-4">
                <label>DATE</label>
                <input class="form-control form-control-sm" name="datechoix" id="choixdate"
                        type="date" required>
            </div>
            <div class="form-group col-sm-4">
                <label>COMPAGNIE D&apos;ARRIV&Eacute;E</label>
                <select class="form-control form-control-sm js-filtre-compagnie-arrivee"
                        id="compagnie_voir"
                        name="compagnie_voir"
                        data-target-depart="idlign"
                        required>
                    <option value="">— Choisir une compagnie —</option>
                    <? foreach ($lignes_par_compagnie_arrivee as $cle => $groupe):
                        $comp_label = !empty($groupe['nom_compagnie']) ? $groupe['nom_compagnie'] : 'Sans compagnie';
                        $nb = !empty($groupe['lignes']) ? count($groupe['lignes']) : 0;
                    ?>
                        <option value="<?= htmlspecialchars((string) $cle, ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($comp_label, ENT_QUOTES, 'UTF-8'); ?> (<?= (int) $nb; ?>)
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm js-depart-filtre" name="ligne" id="idlign" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem):
                        $cle_ca = isset($ligneitem->cle_compagnie_arrivee) ? (string) $ligneitem->cle_compagnie_arrivee : '';
                        if ($cle_ca === '' && isset($ligneitem->id_compaga)) {
                            $cle_ca = (string) $ligneitem->id_compaga;
                        }
                        if ($cle_ca === '') {
                            $cle_ca = '_sans';
                        }
                    ?>
                        <option value="<?= htmlspecialchars($ligneitem->ident_ligne, ENT_QUOTES, 'UTF-8'); ?>"
                                data-compagnie="<?= htmlspecialchars($cle_ca, ENT_QUOTES, 'UTF-8'); ?>"
                                hidden disabled>
                            <?= htmlspecialchars($ligneitem->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <? endforeach; ?>
                </select>
                <small class="form-text text-muted">Sélectionnez d&apos;abord la compagnie d&apos;arrivée.</small>
            </div>
            <div class="form-group col-sm-4">
                <label>PROGRAMME</label>
                <select class="form-control form-control-sm" name="prog" id="idprogr">
                    <option value="">Choisissez le programme</option>
                    
                </select>
            </div>
            
        </div>

        <div class="form-group row">
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
        </div>
        <?= form_close(); ?>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="new-sousgare" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">UNE SOUS GARE DE DEPART</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span></button>
        </div>
        <?= form_open('Programmes/addsousgare/' . $this->session->company->ekey.'/'.$bus_stop->code_gaexp, array('class' => 'modal-body form')) ?>
        <div class="row">
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group col-sm-4">
                <label>NOM SOUS GARE</label>
                <input class="form-control form-control-sm"
                    type="text"
                    name="_nomsousgare"
                    placeholder="nom sous gare" autocomplete="off" required>
            </div>

            <!-- CONTACT -->
            <div class="form-group col-sm-4">
                <label>CONTACT</label>
                <input class="form-control form-control-sm" name="contact" type="text" autocomplete="off">
            </div>  
              
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER
            </button>
            <button class="btn btn-success md_trigger" type="submit" data-dismiss="modal">OK
            </button>
        </div>
        
        <?= form_close(); ?>

    </div>

</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="new-sous" style="perspective: 1300px;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">UN TEMPS</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span></button>
        </div>
        <?= form_open('Programmes/addligne/' . $this->session->company->ekey.'/'.$bus_stop->code_gaexp, array('class' => 'modal-body form')) ?>
        <div class="row">
            
            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
            <div class="form-group col-sm-4">
                <label>NOM SOUS GARE</label>
                <select class="form-control form-control-sm" name="_nomsousgare">
                    <option value=""></option>
                    <? foreach ($sousgares as $sous): ?>
                        <option value="<?= $sous->idsousgare; ?>">
                            <?= $sous->nomsousgare; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="_nomligne">
                    <option value=""></option>
                    <? foreach ($lignes as $depart): ?>
                        <option value="<?= $depart->ident_ligne; ?>">
                            <?= $depart->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>POSITION</label>
                <select class="form-control form-control-sm" name="position">
                    <option value=""></option>
                    <? foreach ($positions as $posit): ?>
                        <option value="<?= $posit->idinter; ?>">
                            <?= $posit->possitiongare; ?> / <?= $posit->minutetemps; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>HEURE</label>
                    <select class="form-control form-control-sm" name="heureidprog">
                    <option value=""></option>
                    <? foreach ($lignesheure as $ligne): ?>
                    <option value="<?= $ligne->id_ligneheure; ?>">
                <?= $ligne->nom_ligne.'/'.$ligne->heure; ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER
            </button>
            <button class="btn btn-success md_trigger" type="submit" data-dismiss="modal">OK
            </button>
        </div>
        
        <?= form_close(); ?>

    </div>

</div>


<div class="modal-container colored-header colored-header-primary custom-width modal-effect-7"
     id="modal-correspondance" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">Correspondance</h3>
            <button class="close modal-close js-corr-close" type="button" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body">
            <p class="mb-2" id="corr-principal-label"></p>
            <div id="corr-linked-box" class="mb-3" style="display:none;"></div>
            <div id="corr-suggest-box">
                <p class="text-muted small">Choisir la date et l’heure de départ à la gare de correspondance (même jour ou lendemain, min. 30 min après le principal). Le départ sera créé avec le même bus, le même <code>depart_code</code> et les mêmes sièges que le principal. Les sièges du départ hub dérivé restent le miroir des sièges occupés sur la suite.</p>
                <div id="corr-heures-form" style="display:none;">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Date</label>
                        <div class="col-sm-9">
                            <select id="corr-date-suite" class="form-control"></select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Heure</label>
                        <div class="col-sm-9">
                            <select id="corr-heure-suite" class="form-control" disabled>
                                <option value="">— Choisir une date —</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="corr-suggest-list"></div>
                <div id="corr-portee-box" class="mt-3" style="display:none;">
                    <hr>
                    <h6 id="corr-portee-principal-title">Gare départ (dérivé ± principal)</h6>
                    <label class="mb-1" style="font-weight:400;cursor:pointer;">
                        <input type="radio" name="corr_scope_ban_mode" value="gare" checked class="js-corr-ban-mode">
                        <span id="corr-ban-mode-gare-label">Toute gare</span>
                    </label>
                    <label class="mb-1 ml-3" style="font-weight:400;cursor:pointer;">
                        <input type="radio" name="corr_scope_ban_mode" value="sousgare" class="js-corr-ban-mode">
                        <span id="corr-ban-mode-sg-label">Sous-gares</span>
                    </label>
                    <div id="corr-sg-banfora-hint" class="mt-1"><small class="text-muted">Choisissez « Sous-gares » pour sélectionner des sous-gares.</small></div>
                    <div id="corr-sg-banfora" class="row mt-1" style="display:none;"></div>
                    <div class="mt-2">
                        <label style="font-weight:400;cursor:pointer;">
                            <input type="checkbox" id="corr-apply-derive" checked>
                            <span id="corr-apply-derive-label">Appliquer au dérivé (hub)</span>
                        </label><br>
                        <label style="font-weight:400;cursor:pointer;">
                            <input type="checkbox" id="corr-apply-principal" checked>
                            <span id="corr-apply-principal-label">Appliquer aussi au principal</span>
                        </label>
                    </div>
                    <hr>
                    <h6 id="corr-portee-suite-title">Gare correspondance (suite)</h6>
                    <label class="mb-1" style="font-weight:400;cursor:pointer;">
                        <input type="radio" name="corr_scope_bob_mode" value="gare" checked class="js-corr-bob-mode">
                        <span id="corr-bob-mode-gare-label">Toute gare</span>
                    </label>
                    <label class="mb-1 ml-3" style="font-weight:400;cursor:pointer;">
                        <input type="radio" name="corr_scope_bob_mode" value="sousgare" class="js-corr-bob-mode">
                        <span id="corr-bob-mode-sg-label">Sous-gares</span>
                    </label>
                    <div id="corr-sg-bobo-hint" class="mt-1"><small class="text-muted">Choisissez « Sous-gares » pour sélectionner des sous-gares.</small></div>
                    <div id="corr-sg-bobo" class="row mt-1" style="display:none;"></div>
                    <div class="mt-2">
                        <label style="font-weight:400;cursor:pointer;">
                            <input type="checkbox" id="corr-apply-suite" checked>
                            <span id="corr-apply-suite-label">Appliquer à la suite</span>
                        </label>
                    </div>
                </div>
            </div>
            <div id="corr-msg" class="mt-2"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary js-corr-close" type="button">Fermer</button>
            <button class="btn btn-danger js-corr-unlink" type="button" style="display:none;">Supprimer le lien</button>
            <button class="btn btn-primary js-corr-save" type="button" disabled>Lier + créer départs</button>
        </div>
    </div>
</div>

<script>
(function () {
    var ekey = <?= json_encode($this->session->company->ekey); ?>;
    var base = <?= json_encode(rtrim(site_url('Programmes'), '/')); ?>;
    var modalEl = document.getElementById('modal-correspondance');
    if (!modalEl) return;

    var state = {
        principal: null,
        principalMeta: null,
        suite: null,
        lien: null,
        verrouille: false,
        sousgaresPrincipal: [],
        sousgaresSuite: [],
        porteePrincipale: [],
        porteeSuite: [],
        heuresParDate: {},
        datesAutorisees: [],
        hubGare: ''
    };

    function $corrModal() {
        return (window.jQuery && typeof jQuery.fn.niftyModal === 'function')
            ? jQuery('#modal-correspondance')
            : null;
    }

    function showModal() {
        var $m = $corrModal();
        if ($m) {
            $m.niftyModal('show');
            return;
        }
        modalEl.classList.add('modal-show');
        document.body.classList.add('modal-open');
    }
    function hideModal() {
        var $m = $corrModal();
        if ($m) {
            $m.niftyModal('hide');
        } else {
            modalEl.classList.remove('modal-show');
            document.body.classList.remove('modal-open');
        }
        state = {
            principal: null,
            principalMeta: null,
            suite: null,
            lien: null,
            verrouille: false,
            sousgaresPrincipal: [],
            sousgaresSuite: [],
            porteePrincipale: [],
            porteeSuite: [],
            heuresParDate: {},
            datesAutorisees: [],
            hubGare: ''
        };
        var saveBtn = document.querySelector('#modal-correspondance .js-corr-save');
        var unlinkBtn = document.querySelector('#modal-correspondance .js-corr-unlink');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.style.display = 'inline-block';
        }
        if (unlinkBtn) unlinkBtn.style.display = 'none';
        document.getElementById('corr-linked-box').style.display = 'none';
        document.getElementById('corr-suggest-box').style.display = 'block';
        document.getElementById('corr-suggest-list').innerHTML = '';
        var heuresFormHide = document.getElementById('corr-heures-form');
        if (heuresFormHide) heuresFormHide.style.display = 'none';
        var dateSelHide = document.getElementById('corr-date-suite');
        var heureSelHide = document.getElementById('corr-heure-suite');
        if (dateSelHide) dateSelHide.innerHTML = '';
        if (heureSelHide) {
            heureSelHide.innerHTML = '<option value="">— Choisir une date —</option>';
            heureSelHide.disabled = true;
        }
        document.getElementById('corr-msg').innerHTML = '';
        resetPorteeUi();
    }

    function setText(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text || '';
    }

    function resetPorteeUi() {
        var portee = document.getElementById('corr-portee-box');
        if (portee) portee.style.display = 'none';
        var ban = document.getElementById('corr-sg-banfora');
        var bob = document.getElementById('corr-sg-bobo');
        if (ban) {
            ban.innerHTML = '';
            ban.style.display = 'none';
        }
        if (bob) {
            bob.innerHTML = '';
            bob.style.display = 'none';
        }
        var banHint = document.getElementById('corr-sg-banfora-hint');
        var bobHint = document.getElementById('corr-sg-bobo-hint');
        if (banHint) banHint.style.display = 'block';
        if (bobHint) bobHint.style.display = 'block';
        var banGare = document.querySelector('input[name="corr_scope_ban_mode"][value="gare"]');
        var bobGare = document.querySelector('input[name="corr_scope_bob_mode"][value="gare"]');
        if (banGare) banGare.checked = true;
        if (bobGare) bobGare.checked = true;
        var ad = document.getElementById('corr-apply-derive');
        var ap = document.getElementById('corr-apply-principal');
        var asu = document.getElementById('corr-apply-suite');
        if (ad) ad.checked = true;
        if (ap) ap.checked = true;
        if (asu) asu.checked = true;
        setText('corr-portee-principal-title', 'Gare départ (dérivé ± principal)');
        setText('corr-ban-mode-gare-label', 'Toute gare');
        setText('corr-ban-mode-sg-label', 'Sous-gares');
        setText('corr-apply-derive-label', 'Appliquer au dérivé (hub)');
        setText('corr-apply-principal-label', 'Appliquer aussi au principal');
        setText('corr-portee-suite-title', 'Gare correspondance (suite)');
        setText('corr-bob-mode-gare-label', 'Toute gare');
        setText('corr-bob-mode-sg-label', 'Sous-gares');
        setText('corr-apply-suite-label', 'Appliquer à la suite');
    }

    function idsToMap(ids) {
        var map = {};
        (ids || []).forEach(function (id) {
            map[String(id)] = true;
        });
        return map;
    }

    /**
     * Affiche les cases SG seulement en mode sous-gares.
     * selectedIds : portée actuelle (vide = cocher toutes au passage en sous-gares).
     */
    function renderSgChecks(containerId, hintId, list, nameAttr, selectedIds, enabled) {
        var box = document.getElementById(containerId);
        var hint = document.getElementById(hintId);
        if (!box) return;
        if (!enabled) {
            box.innerHTML = '';
            box.style.display = 'none';
            if (hint) hint.style.display = 'block';
            return;
        }
        if (hint) hint.style.display = 'none';
        box.style.display = 'flex';
        if (!list || !list.length) {
            box.innerHTML = '<div class="col-12"><small class="text-muted">Aucune sous-gare.</small></div>';
            return;
        }
        var selected = idsToMap(selectedIds);
        var hasSelection = selectedIds && selectedIds.length > 0;
        var html = '';
        list.forEach(function (sg) {
            var idStr = String(sg.idsousgare);
            var checked = hasSelection ? !!selected[idStr] : true;
            html += '<div class="form-group col-sm-4 col-md-3 mb-1">'
                + '<label class="custom-control custom-checkbox mb-0">'
                + '<input class="custom-control-input" type="checkbox" name="' + nameAttr + '" value="'
                + sg.idsousgare + '"' + (checked ? ' checked' : '') + '>'
                + '<span class="custom-control-label">' + (sg.nomsousgare || ('#' + sg.idsousgare)) + '</span>'
                + '</label></div>';
        });
        box.innerHTML = html;
    }

    function collectChecked(nameAttr) {
        var nodes = document.querySelectorAll('input[name="' + nameAttr + '"]:checked');
        var out = [];
        Array.prototype.forEach.call(nodes, function (n) {
            out.push(n.value);
        });
        return out;
    }

    function updatePorteeLabels() {
        var p = state.principalMeta || {};
        var s = state.suite || {};
        var gareP = p.gareidentif || 'départ';
        var nomP = p.nom_ligne || 'principal';
        var gareS = s.gareidentif || 'correspondance';
        var nomS = s.nom_ligne || 'suite';
        setText('corr-portee-principal-title', 'Gare ' + gareP + ' (dérivé ± principal)');
        setText('corr-ban-mode-gare-label', 'Toute gare ' + gareP);
        setText('corr-ban-mode-sg-label', 'Sous-gares ' + gareP);
        setText('corr-apply-derive-label', 'Appliquer au dérivé ' + gareP + '→hub');
        setText('corr-apply-principal-label', 'Appliquer aussi au principal (' + nomP + ')');
        setText('corr-portee-suite-title', 'Gare ' + gareS + ' (suite)');
        setText('corr-bob-mode-gare-label', 'Toute gare ' + gareS);
        setText('corr-bob-mode-sg-label', 'Sous-gares ' + gareS);
        setText('corr-apply-suite-label', 'Appliquer à la suite (' + nomS + ')');
    }

    function applyPrincipalModeUi() {
        var mode = document.querySelector('input[name="corr_scope_ban_mode"]:checked');
        var sousgare = mode && mode.value === 'sousgare';
        renderSgChecks(
            'corr-sg-banfora',
            'corr-sg-banfora-hint',
            state.sousgaresPrincipal,
            'corr_scope_banfora',
            state.porteePrincipale,
            sousgare
        );
    }

    function applySuiteModeUi() {
        var mode = document.querySelector('input[name="corr_scope_bob_mode"]:checked');
        var sousgare = mode && mode.value === 'sousgare';
        renderSgChecks(
            'corr-sg-bobo',
            'corr-sg-bobo-hint',
            state.sousgaresSuite,
            'corr_scope_bobo',
            state.porteeSuite,
            sousgare
        );
    }

    function initPrincipalScopeMode() {
        var hasPartial = state.porteePrincipale && state.porteePrincipale.length > 0;
        var banSg = document.querySelector('input[name="corr_scope_ban_mode"][value="sousgare"]');
        var banGare = document.querySelector('input[name="corr_scope_ban_mode"][value="gare"]');
        if (hasPartial && banSg) {
            banSg.checked = true;
        } else if (banGare) {
            banGare.checked = true;
        }
        applyPrincipalModeUi();
    }

    function initSuiteScopeMode() {
        var hasPartial = state.porteeSuite && state.porteeSuite.length > 0;
        var bobSg = document.querySelector('input[name="corr_scope_bob_mode"][value="sousgare"]');
        var bobGare = document.querySelector('input[name="corr_scope_bob_mode"][value="gare"]');
        if (hasPartial && bobSg) {
            bobSg.checked = true;
        } else if (bobGare) {
            bobGare.checked = true;
        }
        applySuiteModeUi();
    }

    function loadSousgaresSuite(gareCode) {
        if (state.sousgaresSuite && state.sousgaresSuite.length) {
            applySuiteModeUi();
            return Promise.resolve();
        }
        state.sousgaresSuite = [];
        applySuiteModeUi();
        if (!gareCode) return Promise.resolve();
        return fetch(base + '/sousgares_correspondance/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(gareCode), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(parseJsonResponse).then(function (data) {
            state.sousgaresSuite = (data && data.sousgares) ? data.sousgares : [];
            applySuiteModeUi();
        });
    }

    function showPorteeAfterSuite() {
        updatePorteeLabels();
        var portee = document.getElementById('corr-portee-box');
        if (portee) portee.style.display = 'block';
        initPrincipalScopeMode();
        state.porteeSuite = [];
        return loadSousgaresSuite(state.suite && state.suite.gareidentif).then(function () {
            initSuiteScopeMode();
        });
    }

    function setMsg(html, isErr) {
        var el = document.getElementById('corr-msg');
        el.innerHTML = html || '';
        el.className = 'mt-2 ' + (isErr ? 'text-danger' : 'text-success');
    }

    function parseJsonResponse(r) {
        return r.text().then(function (t) {
            try {
                return JSON.parse(t);
            } catch (e) {
                throw new Error('Réponse non JSON (HTTP ' + r.status + ')');
            }
        });
    }

    function appendCsrf(body) {
        var metaToken = document.querySelector('meta[name="csrf-token"]');
        var metaParam = document.querySelector('meta[name="csrf-param"]');
        var name = (metaParam && metaParam.getAttribute('content')) || 'csrf_raketa';
        var hash = metaToken ? metaToken.getAttribute('content') : '';
        if (hash) {
            body.set(name, hash);
        }
        return body;
    }

    function corrErrorLabel(code) {
        var map = {
            dates_hors_plage: 'La suite doit être le même jour ou le lendemain du principal.',
            dates_differentes: 'La suite doit être le même jour ou le lendemain du principal.',
            marge_horaire: 'La suite doit partir au moins 30 min après le principal.',
            dates_invalides: 'Date de programme invalide.',
            deja_lie: 'Ce principal a déjà un lien de correspondance.',
            programme_introuvable: 'Programme introuvable.',
            params_manquants: 'Date et heure de correspondance requises.',
            heure_incompatible: 'Horaire incompatible avec cette liaison.',
            depart_hub_existe: 'Un départ existe déjà à ce créneau à la gare de correspondance.',
            echec_creation_suite: 'Échec création du départ à la gare de correspondance.',
            echec_creation_programme: 'Échec création du programme.',
            aucune_ligne_suite: 'Aucune ligne de correspondance trouvée pour ce départ.',
            ligne_derive_introuvable: 'Aucune ligne hub de la même compagnie (VIP reste VIP, CMT reste CMT).',
            heure_derive_introuvable: 'Aucun horaire VIP/CMT trouvé pour le départ hub (vérifiez les heures de la ligne hub).',
            echec_creation_derive: 'Échec création du départ dérivé.'
        };
        return map[code] || code || 'Erreur';
    }

    function formatDateLabel(dateStr, principalDate) {
        if (!dateStr) return '';
        if (dateStr === principalDate) return dateStr + ' (même jour)';
        return dateStr + ' (lendemain)';
    }

    function fillHeureSelect(dateStr) {
        var heureSel = document.getElementById('corr-heure-suite');
        if (!heureSel) return;
        var list = (state.heuresParDate && state.heuresParDate[dateStr]) ? state.heuresParDate[dateStr] : [];
        var html = '<option value="">— Choisir une heure —</option>';
        list.forEach(function (h, i) {
            html += '<option value="' + h.id_ligneheure + '" data-idx="' + i + '">'
                + (h.label || (h.nom_ligne + ' ' + (h.heure || ''))) + '</option>';
        });
        heureSel.innerHTML = html;
        heureSel.disabled = !list.length;
        state.suite = null;
        var saveBtn = document.querySelector('#modal-correspondance .js-corr-save');
        if (saveBtn) saveBtn.disabled = true;
        var portee = document.getElementById('corr-portee-box');
        if (portee) portee.style.display = 'none';
    }

    function onHeureSelected() {
        var dateSel = document.getElementById('corr-date-suite');
        var heureSel = document.getElementById('corr-heure-suite');
        if (!dateSel || !heureSel || !heureSel.value) {
            state.suite = null;
            return;
        }
        var dateStr = dateSel.value;
        var list = (state.heuresParDate && state.heuresParDate[dateStr]) ? state.heuresParDate[dateStr] : [];
        var idx = heureSel.selectedIndex - 1;
        if (idx < 0 || !list[idx]) {
            state.suite = null;
            return;
        }
        var h = list[idx];
        state.suite = {
            id_ligneheure: h.id_ligneheure,
            date_progr: dateStr,
            gareidentif: h.gareidentif || state.hubGare,
            nom_ligne: h.nom_ligne,
            heure: h.heure,
            label: h.label
        };
        var saveBtn = document.querySelector('#modal-correspondance .js-corr-save');
        if (saveBtn) saveBtn.disabled = !state.suite;
        showPorteeAfterSuite();
    }

    function renderHeuresForm(data) {
        var box = document.getElementById('corr-suggest-list');
        var form = document.getElementById('corr-heures-form');
        var dateSel = document.getElementById('corr-date-suite');
        var heureSel = document.getElementById('corr-heure-suite');
        if (!box || !form || !dateSel || !heureSel) return;

        state.heuresParDate = data.heures_par_date || {};
        state.datesAutorisees = data.dates_autorisees || [];
        state.hubGare = data.hub_gare || '';
        state.sousgaresSuite = data.sousgares_suite || [];

        var principalDate = (data.principal && data.principal.date_progr) ? data.principal.date_progr : '';
        var totalHeures = 0;
        state.datesAutorisees.forEach(function (d) {
            totalHeures += ((state.heuresParDate[d] || []).length);
        });

        if (!state.datesAutorisees.length || totalHeures === 0) {
            form.style.display = 'none';
            var msg = data.message === 'aucune_ligne_suite'
                ? 'Aucune ligne de correspondance configurée pour ce départ.'
                : 'Aucun horaire compatible (même jour ou lendemain, min. 30 min après le principal).';
            box.innerHTML = '<p class="text-muted">' + msg + '</p>';
            return;
        }

        box.innerHTML = '';
        form.style.display = 'block';
        var dateHtml = '';
        state.datesAutorisees.forEach(function (d) {
            if (!(state.heuresParDate[d] || []).length) return;
            dateHtml += '<option value="' + d + '">' + formatDateLabel(d, principalDate) + '</option>';
        });
        dateSel.innerHTML = dateHtml;
        if (dateSel.options.length) {
            dateSel.selectedIndex = 0;
            fillHeureSelect(dateSel.value);
        }
    }

    function appendScopeToBody(body) {
        var banMode = document.querySelector('input[name="corr_scope_ban_mode"]:checked');
        var bobMode = document.querySelector('input[name="corr_scope_bob_mode"]:checked');
        var applyDerive = document.getElementById('corr-apply-derive');
        var applyPrincipal = document.getElementById('corr-apply-principal');
        var applySuite = document.getElementById('corr-apply-suite');

        body.set('has_scope_banfora', '1');
        body.set('has_scope_bobo', '1');
        body.set('apply_derive', (applyDerive && applyDerive.checked) ? '1' : '0');
        body.set('apply_principal', (applyPrincipal && applyPrincipal.checked) ? '1' : '0');
        body.set('apply_suite', (applySuite && applySuite.checked) ? '1' : '0');

        if (banMode && banMode.value === 'sousgare') {
            collectChecked('corr_scope_banfora').forEach(function (id) {
                body.append('scope_banfora[]', id);
            });
        }
        if (bobMode && bobMode.value === 'sousgare') {
            collectChecked('corr_scope_bobo').forEach(function (id) {
                body.append('scope_bobo[]', id);
            });
        }
    }

    function renderLinked(data) {
        var box = document.getElementById('corr-linked-box');
        var lien = data.lien;
        var suite = data.suite;
        var derive = data.derive;
        var verrouille = !!(data.verrouille);
        var nbVentes = data.nb_ventes || 0;
        var html = '<div class="alert alert-info mb-0">';
        html += '<div><strong>Lien actif</strong></div>';
        if (suite) {
            html += '<div>Correspondance : ' + (suite.nom_ligne || '') + ' '
                + (suite.date_progr ? (suite.date_progr + ' ') : '')
                + (suite.heure || '') + ' <code>' + lien.code_progr_suite + '</code></div>';
        }
        if (derive) {
            html += '<div>Dérivé (miroir sièges) : ' + (derive.nom_ligne || '') + ' '
                + (derive.date_progr ? (derive.date_progr + ' ') : '')
                + (derive.heure || '') + ' <code>' + lien.code_progr_derive + '</code></div>';
        } else if (lien.code_progr_derive) {
            html += '<div>Dérivé : <code>' + lien.code_progr_derive + '</code></div>';
        }
        if (verrouille) {
            html += '<div class="mt-2 text-danger"><strong>Lien verrouillé</strong> : '
                + nbVentes + ' vente(s) active(s) — suppression impossible.</div>';
        }
        html += '</div>';
        box.innerHTML = html;
        box.style.display = 'block';
        document.getElementById('corr-suggest-box').style.display = 'none';
        var saveBtn = document.querySelector('#modal-correspondance .js-corr-save');
        var unlinkBtn = document.querySelector('#modal-correspondance .js-corr-unlink');
        if (saveBtn) saveBtn.style.display = 'none';
        if (unlinkBtn) {
            if (verrouille) {
                unlinkBtn.style.display = 'none';
            } else {
                unlinkBtn.style.display = 'inline-block';
                unlinkBtn.disabled = false;
            }
        }
        state.lien = lien;
        state.principal = lien.code_progr_principal;
        state.verrouille = verrouille;
    }

    function openFor(code, nom, heure, pdate) {
        state.principal = code;
        state.principalMeta = { nom_ligne: nom || '', gareidentif: '' };
        state.suite = null;
        state.lien = null;
        state.sousgaresPrincipal = [];
        state.sousgaresSuite = [];
        state.porteePrincipale = [];
        state.porteeSuite = [];
        state.heuresParDate = {};
        state.datesAutorisees = [];
        state.hubGare = '';
        resetPorteeUi();
        var datePart = pdate ? (' ' + pdate) : '';
        document.getElementById('corr-principal-label').textContent =
            'Départ principal : ' + (nom || '') + datePart + ' ' + (heure || '') + ' (' + code + ')';
        document.getElementById('corr-linked-box').style.display = 'none';
        document.getElementById('corr-suggest-box').style.display = 'block';
        document.getElementById('corr-suggest-list').innerHTML = '';
        var heuresFormOpen = document.getElementById('corr-heures-form');
        if (heuresFormOpen) heuresFormOpen.style.display = 'none';
        var dateSelOpen = document.getElementById('corr-date-suite');
        var heureSelOpen = document.getElementById('corr-heure-suite');
        if (dateSelOpen) dateSelOpen.innerHTML = '';
        if (heureSelOpen) {
            heureSelOpen.innerHTML = '<option value="">— Choisir une date —</option>';
            heureSelOpen.disabled = true;
        }
        var saveBtn = document.querySelector('#modal-correspondance .js-corr-save');
        var unlinkBtn = document.querySelector('#modal-correspondance .js-corr-unlink');
        if (saveBtn) {
            saveBtn.style.display = 'inline-block';
            saveBtn.disabled = true;
        }
        if (unlinkBtn) unlinkBtn.style.display = 'none';
        setMsg('Chargement…', false);
        showModal();

        fetch(base + '/get_correspondance/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(code), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(parseJsonResponse).then(function (data) {
            if (data && data.lien) {
                setMsg('', false);
                renderLinked(data);
                return null;
            }
            return fetch(base + '/heures_correspondance/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(code), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(parseJsonResponse);
        }).then(function (sug) {
            if (!sug) return;
            setMsg('', false);
            if (!sug.ok) {
                setMsg(sug.error || 'Erreur horaires', true);
                return;
            }
            if (sug.principal) {
                state.principalMeta = sug.principal;
                var dateP = sug.principal.date_progr ? (' ' + sug.principal.date_progr) : '';
                document.getElementById('corr-principal-label').textContent =
                    'Départ principal : ' + (sug.principal.nom_ligne || nom || '')
                    + dateP + ' ' + (sug.principal.heure || heure || '')
                    + ' · gare ' + (sug.principal.gareidentif || '')
                    + ' · bus ' + (sug.principal.intervalle1 || '') + '-' + (sug.principal.intervalle2 || '')
                    + ' · ' + (sug.principal.depart_code || '')
                    + ' (' + code + ')';
            }
            state.sousgaresPrincipal = sug.sousgares_principal || sug.sousgares_banfora || [];
            state.porteePrincipale = sug.portee_principale || [];
            renderHeuresForm(sug);
        }).catch(function (err) {
            setMsg((err && err.message) ? err.message : 'Erreur réseau', true);
        });
    }

    document.addEventListener('click', function (e) {
        var t = e.target;
        var btn = null;
        var closer = null;
        while (t && t !== document) {
            if (t.classList) {
                if (t.classList.contains('js-corr-link')) {
                    btn = t;
                    break;
                }
                if (t.classList.contains('js-corr-close')) {
                    closer = t;
                    break;
                }
            }
            t = t.parentNode;
        }
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            openFor(
                btn.getAttribute('data-code'),
                btn.getAttribute('data-nom'),
                btn.getAttribute('data-heure'),
                btn.getAttribute('data-pdate')
            );
            return;
        }
        if (closer) {
            e.preventDefault();
            hideModal();
        }
    });

    Array.prototype.forEach.call(document.querySelectorAll('.js-corr-ban-mode'), function (inp) {
        inp.addEventListener('change', function () {
            applyPrincipalModeUi();
        });
    });
    Array.prototype.forEach.call(document.querySelectorAll('.js-corr-bob-mode'), function (inp) {
        inp.addEventListener('change', function () {
            applySuiteModeUi();
        });
    });

    var dateSuiteEl = document.getElementById('corr-date-suite');
    if (dateSuiteEl) {
        dateSuiteEl.addEventListener('change', function () {
            fillHeureSelect(dateSuiteEl.value);
        });
    }
    var heureSuiteEl = document.getElementById('corr-heure-suite');
    if (heureSuiteEl) {
        heureSuiteEl.addEventListener('change', onHeureSelected);
    }

    var saveEl = document.querySelector('#modal-correspondance .js-corr-save');
    if (saveEl) {
        saveEl.addEventListener('click', function () {
            if (!state.principal || !state.suite || !state.suite.id_ligneheure) return;
            var btn = this;
            btn.disabled = true;
            setMsg('Création du lien…', false);
            var body = new URLSearchParams();
            body.set('code_progr_principal', state.principal);
            body.set('id_ligneheure', String(state.suite.id_ligneheure));
            body.set('date_progr_suite', state.suite.date_progr);
            appendScopeToBody(body);
            appendCsrf(body);
            fetch(base + '/link_correspondance/' + encodeURIComponent(ekey), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: body.toString()
            }).then(parseJsonResponse).then(function (data) {
                if (!data || !data.ok) {
                    setMsg(corrErrorLabel(data && data.error) || 'Échec du lien', true);
                    btn.disabled = false;
                    return;
                }
                var warn = (data.portee_warnings && data.portee_warnings.length)
                    ? ' (sous-gares partielles : ' + data.portee_warnings.join(', ') + ')'
                    : '';
                var suiteCode = data.suite && data.suite.code_progr ? data.suite.code_progr : '';
                var deriveCode = data.lien && data.lien.code_progr_derive ? data.lien.code_progr_derive : '';
                setMsg('Lien créé. Suite : ' + suiteCode + ' · Dérivé : ' + deriveCode + warn, false);
                setTimeout(function () { window.location.reload(); }, 800);
            }).catch(function (err) {
                setMsg((err && err.message) ? err.message : 'Erreur réseau', true);
                btn.disabled = false;
            });
        });
    }

    var unlinkEl = document.querySelector('#modal-correspondance .js-corr-unlink');
    if (unlinkEl) {
        unlinkEl.addEventListener('click', function () {
            var principal = state.principal || (state.lien && state.lien.code_progr_principal);
            if (!principal) return;
            if (state.verrouille) {
                setMsg('Lien verrouillé : des ventes existent déjà.', true);
                return;
            }
            if (!window.confirm('Supprimer le lien de correspondance ? (les programmes restent)')) return;
            var body = new URLSearchParams();
            body.set('code_progr_principal', principal);
            appendCsrf(body);
            fetch(base + '/unlink_correspondance/' + encodeURIComponent(ekey), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: body.toString()
            }).then(parseJsonResponse).then(function (data) {
                if (!data || !data.ok) {
                    var msg = (data && data.message) ? data.message : ((data && data.error) || 'Échec suppression');
                    setMsg(msg, true);
                    return;
                }
                setMsg('Lien supprimé.', false);
                setTimeout(function () { window.location.reload(); }, 600);
            }).catch(function (err) {
                setMsg((err && err.message) ? err.message : 'Erreur réseau', true);
            });
        });
    }
})();
</script>

<div class="modal-container colored-header colored-header-warning custom-width modal-effect-7"
     id="modal-reconduction" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">Créer un départ avec sièges restants</h3>
            <button class="close modal-close js-reco-close" type="button" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body">
            <p class="text-muted small mb-2">
                Un bus en amont a déclaré sa sortie. Le nouveau départ reprend le même code de départ et la même catégorie de bus,
                à l’heure de départ de la gare de correspondance (pas l’heure du départ principal).
            </p>
            <div id="reco-offres-list"></div>
            <div id="reco-detail" class="mt-3" style="display:none;">
                <hr>
                <p class="mb-1"><strong id="reco-detail-label"></strong></p>
                <div class="form-group">
                    <label>Départ correspondance (horaire local)</label>
                    <select class="form-control form-control-sm" id="reco-heure"></select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control form-control-sm" id="reco-date">
                </div>
                <div class="mb-2">
                    <label>Sièges (lecture seule en gare aval)</label>
                    <p class="text-muted small mb-1">Les sièges restants sont reconduits automatiquement ; ils ne sont ni cochables ni décochables. Les sièges déjà vendus restent grisés.</p>
                    <div id="reco-sieges" class="row mt-2"></div>
                </div>
            </div>
            <div id="reco-msg" class="mt-2"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary js-reco-close" type="button">Fermer</button>
            <button class="btn btn-warning js-reco-save" type="button" disabled>Créer le départ</button>
        </div>
    </div>
</div>

<script>
(function () {
    var ekey = <?= json_encode($this->session->company->ekey); ?>;
    var base = <?= json_encode(rtrim(site_url('Programmes'), '/')); ?>;
    var gareExp = <?= json_encode(isset($bus_stop->code_gaexp) ? $bus_stop->code_gaexp : (isset($gare_stop->idengare) ? $gare_stop->idengare : '')); ?>;
    var modalEl = document.getElementById('modal-reconduction');
    if (!modalEl) return;

    var state = { offre: null, sieges: [], heures: [] };

    function $m() {
        return (window.jQuery && typeof jQuery.fn.niftyModal === 'function')
            ? jQuery('#modal-reconduction') : null;
    }
    function showModal() {
        var jq = $m();
        if (jq) { jq.niftyModal('show'); return; }
        modalEl.classList.add('modal-show');
        document.body.classList.add('modal-open');
    }
    function hideModal() {
        var jq = $m();
        if (jq) { jq.niftyModal('hide'); }
        else {
            modalEl.classList.remove('modal-show');
            document.body.classList.remove('modal-open');
        }
        state = { offre: null, sieges: [], heures: [] };
        document.getElementById('reco-detail').style.display = 'none';
        document.getElementById('reco-msg').innerHTML = '';
        var save = document.querySelector('.js-reco-save');
        if (save) save.disabled = true;
    }
    function setMsg(text, isErr) {
        var el = document.getElementById('reco-msg');
        if (!el) return;
        el.innerHTML = text
            ? ('<div class="alert alert-' + (isErr ? 'danger' : 'success') + ' py-1 mb-0">' + text + '</div>')
            : '';
    }
    function parseJsonResponse(r) {
        return r.text().then(function (t) {
            try { return JSON.parse(t); }
            catch (e) { throw new Error('Réponse non JSON (HTTP ' + r.status + ')'); }
        });
    }
    function appendCsrf(body) {
        var metaToken = document.querySelector('meta[name="csrf-token"]');
        var metaParam = document.querySelector('meta[name="csrf-param"]');
        var name = (metaParam && metaParam.getAttribute('content')) || 'csrf_raketa';
        var hash = metaToken ? metaToken.getAttribute('content') : '';
        if (hash) body.set(name, hash);
        return body;
    }
    function recoError(code) {
        var map = {
            droit_insuffisant: 'Droits insuffisants.',
            programme_introuvable: 'Programme introuvable.',
            deja_sorti: 'La sortie de ce départ est déjà déclarée.',
            sortie_non_declaree: 'La sortie du départ amont n’est pas déclarée.',
            gare_cible_invalide: 'Gare aval invalide.',
            aucun_siege: 'Choisissez au moins un siège.',
            siege_indisponible: 'Un siège choisi n’est plus libre.',
            heure_correspondance_introuvable: 'Impossible de trouver l’heure de départ de la gare de correspondance.',
            depart_code_manquant: 'Code de départ du principal introuvable.',
            date_invalide: 'Date invalide.',
            echec_creation_depart: 'Échec de création du départ.',
            siege_pris: 'Un siège vient d’être pris par une autre gare.',
            echec_sortie: 'Impossible de déclarer la sortie.'
        };
        return map[code] || code || 'Erreur';
    }

    function renderOffres(list) {
        var box = document.getElementById('reco-offres-list');
        if (!list || !list.length) {
            box.innerHTML = '<p class="text-muted mb-0">Aucune offre de sièges restants pour cette gare (même destination, départ amont sorti).</p>';
            return;
        }
        var html = '<div class="list-group">';
        list.forEach(function (o) {
            var heureCorr = (o.heure_correspondance || '').toString().substr(0, 5);
            var heurePrin = (o.heure_principale || o.heure || '').toString().substr(0, 5);
            var heureAff = heureCorr || heurePrin;
            var codeDep = o.depart_code_principal || o.depart_code || '';
            var label = (o.nom_gaep || o.gareidentif || '') + ' · ' + (o.nom_ligne || '')
                + (codeDep ? (' · ' + codeDep) : '')
                + ' · corr. ' + heureAff
                + (heureCorr && heurePrin && heureCorr !== heurePrin ? (' (départ ' + heurePrin + ')') : '')
                + ' · ' + (o.nb_restants || 0) + ' place(s)';
            html += '<label class="list-group-item list-group-item-action mb-0" style="cursor:pointer;">'
                + '<input type="radio" name="reco_offre" class="js-reco-pick mr-2" value="'
                + String(o.code_progr_source).replace(/"/g, '&quot;') + '"> '
                + label + '</label>';
        });
        html += '</div>';
        box.innerHTML = html;
        box.querySelectorAll('.js-reco-pick').forEach(function (inp) {
            inp.addEventListener('change', function () {
                var code = inp.value;
                var offre = null;
                for (var i = 0; i < list.length; i++) {
                    if (String(list[i].code_progr_source) === code) { offre = list[i]; break; }
                }
                if (offre) selectOffre(offre);
            });
        });
    }

    function selectOffre(offre) {
        state.offre = offre;
        document.getElementById('reco-detail').style.display = 'block';
        document.getElementById('reco-detail-label').textContent =
            (offre.nom_gaep || '') + ' → ' + (offre.nom_gadest || '')
            + (offre.depart_code_principal ? (' · code ' + offre.depart_code_principal) : '')
            + ' · sièges restants';
        document.getElementById('reco-date').value = offre.date_correspondance || offre.date_progr || '';
        var sieges = offre.sieges_restants || [];
        var occupes = {};
        (offre.sieges_occupes || []).forEach(function (n) { occupes[String(n)] = true; });
        var libres = {};
        sieges.forEach(function (n) { libres[String(n)] = true; });
        var wrap = document.getElementById('reco-sieges');
        var d = parseInt(offre.intervalle1, 10) || 0;
        var f = parseInt(offre.intervalle2, 10) || 0;
        var html = '';
        function siegeGrise(n, hint) {
            return '<div class="col-3 col-md-2 mb-1"><label class="text-muted" style="font-weight:400;cursor:not-allowed;">'
                + '<input type="checkbox" disabled checked> ' + n
                + (hint ? ' <small>' + hint + '</small>' : '')
                + '</label></div>';
        }
        if (f >= d && d > 0) {
            for (var n = d; n <= f; n++) {
                if (occupes[String(n)]) {
                    html += siegeGrise(n, 'vendu');
                } else if (libres[String(n)]) {
                    html += siegeGrise(n, 'reconduit');
                }
            }
        } else {
            sieges.forEach(function (n) {
                html += siegeGrise(n, 'reconduit');
            });
        }
        wrap.innerHTML = html || '<p class="text-muted mb-0">Aucun siège restant.</p>';
        var sel = document.getElementById('reco-heure');
        sel.innerHTML = '<option value="">Chargement…</option>';
        fetch(base + '/heures_reconduction/' + encodeURIComponent(ekey) + '/'
            + encodeURIComponent(gareExp) + '/' + encodeURIComponent(offre.gadest_lg || ''), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(parseJsonResponse).then(function (data) {
            var heures = (data && data.heures) ? data.heures : [];
            state.heures = heures;
            if (!heures.length) {
                sel.innerHTML = '<option value="">Aucun horaire vers cette destination</option>';
                document.querySelector('.js-reco-save').disabled = true;
                return;
            }
            var pref = offre.id_ligneheure_correspondance ? String(offre.id_ligneheure_correspondance) : '';
            sel.innerHTML = heures.map(function (h) {
                var hh = (h.heure || '').toString().substr(0, 5);
                var cie = h.nom_compagnie_arrivee ? (' · ' + h.nom_compagnie_arrivee) : '';
                return '<option value="' + h.id_ligneheure + '">' + (h.nom_ligne || '') + ' / ' + hh + cie + '</option>';
            }).join('');
            if (pref) {
                var found = false;
                for (var i = 0; i < sel.options.length; i++) {
                    if (sel.options[i].value === pref) { found = true; break; }
                }
                if (!found) {
                    var opt = document.createElement('option');
                    opt.value = pref;
                    var hc = (offre.heure_correspondance || '').toString().substr(0, 5);
                    opt.textContent = 'Correspondance / ' + hc;
                    sel.appendChild(opt);
                }
                sel.value = pref;
            }
            document.querySelector('.js-reco-save').disabled = false;
        }).catch(function () {
            sel.innerHTML = '<option value="">Erreur chargement horaires</option>';
        });
    }

    function loadOffres() {
        document.getElementById('reco-offres-list').innerHTML = '<p class="text-muted">Chargement…</p>';
        fetch(base + '/offres_reconduction/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(gareExp), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(parseJsonResponse).then(function (data) {
            renderOffres(data && data.offres ? data.offres : []);
        }).catch(function (err) {
            document.getElementById('reco-offres-list').innerHTML =
                '<p class="text-danger">' + ((err && err.message) ? err.message : 'Erreur') + '</p>';
        });
    }

    function openReco() {
        showModal();
        loadOffres();
    }

    document.addEventListener('click', function (e) {
        var t = e.target;
        var openBtn = null, closeBtn = null;
        while (t && t !== document) {
            if (t.classList) {
                if (t.classList.contains('js-reco-open')) openBtn = t;
                if (t.classList.contains('js-reco-close')) closeBtn = t;
            }
            t = t.parentNode;
        }
        if (openBtn) {
            e.preventDefault();
            openReco();
            return;
        }
        if (closeBtn) {
            e.preventDefault();
            hideModal();
        }
    });

    var saveEl = document.querySelector('.js-reco-save');
    if (saveEl) {
        saveEl.addEventListener('click', function () {
            if (!state.offre) return;
            var sieges = (state.offre.sieges_restants || []).slice();
            if (!sieges.length) {
                setMsg('Aucun siège restant à reconduire.', true);
                return;
            }
            var idH = document.getElementById('reco-heure').value;
            if (!idH) {
                setMsg('Choisissez un horaire local.', true);
                return;
            }
            saveEl.disabled = true;
            setMsg('Création du départ…', false);
            var body = new URLSearchParams();
            body.set('code_progr_source', state.offre.code_progr_source);
            body.set('gare_cible', gareExp);
            body.set('id_ligneheure', idH);
            body.set('date_progr', document.getElementById('reco-date').value || '');
            body.set('sieges_csv', sieges.join(','));
            sieges.forEach(function (n) { body.append('sieges[]', n); });
            appendCsrf(body);
            fetch(base + '/creer_reconduction/' + encodeURIComponent(ekey), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: body.toString()
            }).then(parseJsonResponse).then(function (data) {
                if (!data || !data.ok) {
                    setMsg(recoError(data && data.error), true);
                    saveEl.disabled = false;
                    return;
                }
                setMsg('Départ créé : ' + (data.code_progr || '') + (data.depart_code ? (' · code ' + data.depart_code) : ''), false);
                setTimeout(function () { window.location.reload(); }, 700);
            }).catch(function (err) {
                setMsg((err && err.message) ? err.message : 'Erreur réseau', true);
                saveEl.disabled = false;
            });
        });
    }
})();
</script>

<div class="modal-container colored-header colored-header-warning custom-width modal-effect-7"
     id="modal-sortie" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">Déclarer la sortie</h3>
            <button class="close modal-close js-sortie-close" type="button" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body">
            <p class="mb-1"><strong id="sortie-label"></strong></p>
            <p class="text-muted small mb-2">
                Cochez les sièges libres à publier aux gares aval. Décocher un siège libre le garde ici.
                Un siège vendu coché = client qui n’a pas voyagé : le n° est détaché (reste NULL sur le passager),
                la vente n’est pas supprimée, le siège est publié aux gares aval.
            </p>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="sortie-check-all">Tout cocher</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="sortie-uncheck-all">Tout décocher</button>
                <span class="small text-muted ml-2" id="sortie-count"></span>
            </div>
            <div id="sortie-sieges" class="row mt-2"></div>
            <div id="sortie-msg" class="mt-2"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary js-sortie-close" type="button">Annuler</button>
            <button class="btn btn-warning js-sortie-save" type="button" disabled>Publier et déclarer la sortie</button>
        </div>
    </div>
</div>

<script>
(function () {
    var ekey = <?= json_encode($this->session->company->ekey); ?>;
    var base = <?= json_encode(rtrim(site_url('Programmes'), '/')); ?>;
    var modalEl = document.getElementById('modal-sortie');
    if (!modalEl) return;

    var state = { code: null };

    function $m() {
        return (window.jQuery && typeof jQuery.fn.niftyModal === 'function')
            ? jQuery('#modal-sortie') : null;
    }
    function showModal() {
        var jq = $m();
        if (jq) { jq.niftyModal('show'); return; }
        modalEl.classList.add('modal-show');
        document.body.classList.add('modal-open');
    }
    function hideModal() {
        var jq = $m();
        if (jq) { jq.niftyModal('hide'); }
        else {
            modalEl.classList.remove('modal-show');
            document.body.classList.remove('modal-open');
        }
        state = { code: null };
        var msg = document.getElementById('sortie-msg');
        if (msg) msg.innerHTML = '';
        var save = document.querySelector('.js-sortie-save');
        if (save) save.disabled = true;
    }
    function setMsg(text, isErr) {
        var el = document.getElementById('sortie-msg');
        if (!el) return;
        el.innerHTML = text
            ? ('<div class="alert alert-' + (isErr ? 'danger' : 'success') + ' py-1 mb-0">' + text + '</div>')
            : '';
    }
    function parseJsonResponse(r) {
        return r.text().then(function (t) {
            try { return JSON.parse(t); }
            catch (e) { throw new Error('Réponse non JSON (HTTP ' + r.status + ')'); }
        });
    }
    function appendCsrf(body) {
        var metaToken = document.querySelector('meta[name="csrf-token"]');
        var metaParam = document.querySelector('meta[name="csrf-param"]');
        var name = (metaParam && metaParam.getAttribute('content')) || 'csrf_raketa';
        var hash = metaToken ? metaToken.getAttribute('content') : '';
        if (hash) body.set(name, hash);
        return body;
    }
    function sortieError(code) {
        var map = {
            droit_insuffisant: 'Droits insuffisants.',
            programme_introuvable: 'Programme introuvable.',
            deja_sorti: 'La sortie de ce départ est déjà déclarée.',
            aucun_siege: 'Cochez au moins un siège à publier.',
            siege_indisponible: 'Un siège choisi n’est plus libre.',
            echec_sortie: 'Impossible de déclarer la sortie.'
        };
        return map[code] || code || 'Erreur';
    }
    function updateCount() {
        var n = document.querySelectorAll('#sortie-sieges .js-sortie-siege:checked').length;
        var el = document.getElementById('sortie-count');
        if (el) el.textContent = n + ' siège(s) à publier';
        var save = document.querySelector('.js-sortie-save');
        if (save) save.disabled = n < 1;
    }
    function renderSieges(data) {
        var wrap = document.getElementById('sortie-sieges');
        var label = document.getElementById('sortie-label');
        var heure = (data.heure || '').toString().substr(0, 5);
        label.textContent = (data.nom_ligne || '') + ' · ' + (data.date_progr || '') + ' ' + heure
            + (data.nom_gadest ? ' → ' + data.nom_gadest : '');
        var d = parseInt(data.intervalle1, 10) || 0;
        var f = parseInt(data.intervalle2, 10) || 0;
        var occupes = {};
        (data.sieges_occupes || []).forEach(function (n) { occupes[String(n)] = true; });
        var libres = {};
        (data.sieges_restants || []).forEach(function (n) { libres[String(n)] = true; });
        if (f < d) {
            wrap.innerHTML = '<div class="col-12"><p class="text-muted mb-0">Aucun plan de sièges.</p></div>';
            updateCount();
            return;
        }
        var html = '';
        for (var n = d; n <= f; n++) {
            var isOcc = !!occupes[String(n)];
            var isLibre = !!libres[String(n)];
            if (isOcc) {
                html += '<div class="col-3 col-md-2 mb-1"><label style="font-weight:400;cursor:pointer;">'
                    + '<input type="checkbox" class="js-sortie-siege js-sortie-noshow" value="' + n + '"> '
                    + n + ' <small class="text-muted">vendu · libérer</small></label></div>';
            } else if (isLibre) {
                html += '<div class="col-3 col-md-2 mb-1"><label style="font-weight:400;cursor:pointer;">'
                    + '<input type="checkbox" class="js-sortie-siege js-sortie-libre" value="' + n + '" checked> ' + n
                    + '</label></div>';
            }
        }
        if (!html) {
            html = '<div class="col-12"><p class="text-muted mb-0">Aucun siège restant à publier.</p></div>';
        }
        wrap.innerHTML = html;
        wrap.querySelectorAll('.js-sortie-siege').forEach(function (c) {
            c.addEventListener('change', updateCount);
        });
        updateCount();
        if (!(data.sieges_restants || []).length) {
            setMsg('Aucun siège libre : la sortie n’a rien à publier aux gares aval.', true);
        }
    }
    function openSortie(code) {
        state.code = code;
        document.getElementById('sortie-sieges').innerHTML = '<div class="col-12"><p class="text-muted">Chargement…</p></div>';
        document.getElementById('sortie-label').textContent = '';
        setMsg('', false);
        showModal();
        fetch(base + '/apercu_sortie/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(code), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(parseJsonResponse).then(function (data) {
            if (!data || !data.ok) {
                setMsg(sortieError(data && data.error), true);
                document.querySelector('.js-sortie-save').disabled = true;
                return;
            }
            renderSieges(data);
        }).catch(function (err) {
            setMsg((err && err.message) ? err.message : 'Erreur réseau', true);
        });
    }

    document.addEventListener('click', function (e) {
        var t = e.target;
        var openBtn = null, closeBtn = null;
        while (t && t !== document) {
            if (t.classList) {
                if (t.classList.contains('js-sortie-decl')) openBtn = t;
                if (t.classList.contains('js-sortie-close')) closeBtn = t;
            }
            t = t.parentNode;
        }
        if (openBtn) {
            e.preventDefault();
            var code = openBtn.getAttribute('data-code');
            if (code) openSortie(code);
            return;
        }
        if (closeBtn) {
            e.preventDefault();
            hideModal();
        }
    });

    var chkAll = document.getElementById('sortie-check-all');
    var unchk = document.getElementById('sortie-uncheck-all');
    if (chkAll) {
        chkAll.addEventListener('click', function () {
            document.querySelectorAll('#sortie-sieges .js-sortie-libre').forEach(function (c) { c.checked = true; });
            updateCount();
        });
    }
    if (unchk) {
        unchk.addEventListener('click', function () {
            document.querySelectorAll('#sortie-sieges .js-sortie-libre').forEach(function (c) { c.checked = false; });
            updateCount();
        });
    }

    var saveEl = document.querySelector('.js-sortie-save');
    if (saveEl) {
        saveEl.addEventListener('click', function () {
            if (!state.code) return;
            var sieges = [];
            document.querySelectorAll('#sortie-sieges .js-sortie-siege:checked').forEach(function (c) {
                sieges.push(c.value);
            });
            if (!sieges.length) {
                setMsg('Cochez au moins un siège à publier.', true);
                return;
            }
            saveEl.disabled = true;
            setMsg('Déclaration en cours…', false);
            var body = new URLSearchParams();
            body.set('code_progr', state.code);
            body.set('sieges_csv', sieges.join(','));
            sieges.forEach(function (n) { body.append('sieges[]', n); });
            appendCsrf(body);
            fetch(base + '/declare_sortie/' + encodeURIComponent(ekey), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: body.toString()
            }).then(parseJsonResponse).then(function (data) {
                if (!data || !data.ok) {
                    setMsg(sortieError(data && data.error), true);
                    saveEl.disabled = false;
                    return;
                }
                var n = data.nb_restants != null ? data.nb_restants : sieges.length;
                setMsg('Sortie déclarée. ' + n + ' place(s) publiée(s) aux gares aval.', false);
                setTimeout(function () { window.location.reload(); }, 700);
            }).catch(function (err) {
                setMsg((err && err.message) ? err.message : 'Erreur réseau', true);
                saveEl.disabled = false;
            });
        });
    }
})();
</script>

<!--End of file: program.php-->
<!--File location: application/views/beagle/pages/_gares/program.php-->
