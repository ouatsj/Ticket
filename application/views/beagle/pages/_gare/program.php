<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


    <?php if ($msg = $this->session->flashdata('prog_portee_error')): ?>
    <div class="row mb-2 ml-2 mr-2">
        <div class="col-12 col-md-10">
            <div class="alert alert-danger mb-2 py-2"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
<?php endif; ?>
<div class="row mb-3 ml-2 mr-2" id="mode_depart_toggle">
    <div class="col-12 col-md-10">
        <div class="alert alert-info mb-2 py-2">
            <strong>Départs</strong> —
            par défaut <strong>toute portée</strong> (toutes les sous-gares). Cochez <strong>Sous-gares</strong> pour activer les cases et restreindre le départ.
        </div>
    </div>
</div>


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
        $__prog_edit_modal_rendered = false;
    ?>
    <div class="row">
        <div class="col-sm-12">

            <div class="card card-table">

                <div class="card-header">
                    <div class="row align-items-center mb-2">
                        <div class="col-md-6">
                            <strong>Programmes par compagnie d'arrivée</strong>
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
                                    $__corr = (!empty($corr_index) && isset($corr_index[$item->code_progr]))
                                        ? $corr_index[$item->code_progr] : null;
                                    // Compteur = passagers de CE départ uniquement (pas suite∪dérivé).
                                    $__code_esc = $this->db->escape_str($item->code_progr);
                                    $nb = $this->db->query(
                                    "SELECT COUNT(code_passager) AS nbr FROM passager p
                                    WHERE p.code_pro = '{$__code_esc}'
                                    AND p.actif_pas = 0
                                    AND p.num_siege_categorie IS NOT NULL")->row();?>
                                <tr>
                                    <td><?= $item->code_progr; ?>/
                                        <span><?= $item->depart_code; ?></span>
                                        <?php
                                            $__psg2 = $this->db->query(
                                                "SELECT ps.idsousgare, sg.nomsousgare FROM programme_sousgare ps
                                                 LEFT JOIN sousgare sg ON sg.idsousgare = ps.idsousgare
                                                 WHERE ps.code_progr = ?",
                                                array($item->code_progr)
                                            )->result();
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
                                            <?php
                                                $__psg = $this->db->query(
                                                    "SELECT idsousgare FROM programme_sousgare WHERE code_progr = ?",
                                                    array($item->code_progr)
                                                )->result();
                                                $__ids = array();
                                                foreach ($__psg as $__r) { $__ids[] = (int) $__r->idsousgare; }
                                                if (empty($__ids) && !empty($item->idsousgare_prog)) {
                                                    $__ids[] = (int) $item->idsousgare_prog;
                                                }
                                                $__ventes = array();
                                                if (!isset($this->m_programme)) {
                                                    $this->load->model('Programme_model', 'm_programme');
                                                }
                                                if (isset($this->m_programme)) {
                                                    $__ventes = $this->m_programme->comptes_ventes_par_sousgare($item->code_progr);
                                                }
                                                $__ventes_attr = array();
                                                foreach ($__ventes as $__sg => $__nb) {
                                                    $__ventes_attr[] = ((int) $__sg) . ':' . ((int) $__nb);
                                                }
                                            ?>
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
                                           data-heure="<?= htmlspecialchars($item->heure, ENT_QUOTES, 'UTF-8'); ?>">
                                            <span class="fas fa-exchange-alt <?= !empty($__corr) ? 'text-success' : 'text-info'; ?>"></span>
                                        </a>&nbsp;
                                        <a href="<?= site_url('Gares/activer/' . $this->session->company->ekey . '/' . $item->code_progr. '/' . $item->gareidentif. '/' . $item->statut_prog.'/'.$conex->roleattribut.'/'.$gare_stop->idsousgare);?> "class="btn btn-space btn-secondary">
                                            <?= ($item->statut_prog === 'actif') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                            class="icon mdi text-success">activer</span>' ?>
                                        </a>&nbsp;
                                        &nbsp;
                                    <?endif;?>
                                        
                                        <?php if (!$__prog_edit_modal_rendered): ?>
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
                                                                Toute port&eacute;e
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
                                                    
                                                    <div class="form-group col-sm-3">
                                                        <label>QUOTA DEBUT</label>
                                                        <input class="form-control form-control-sm" name="debut" id="ouotadebut"
                                                        value="" type="text" autocomplete="off">
                                                    </div>
                                                    <div class="form-group col-sm-3">
                                                        <label>QUOTA FIN</label>
                                                        <input class="form-control form-control-sm" name="fin" id="ouotafin" value="" type="text" autocomplete="off">
                                                    </div>
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
                                        <?php $__prog_edit_modal_rendered = true; endif; ?>
                                        <?
                                            $cid = $this->session->company->ekey;
                                                $ligneh = $this->db->query(
                                                "SELECT * FROM ligne_heure lh
                                                JOIN lignes l ON lh.ligne_id = l.ident_ligne
                                                JOIN heures h ON lh.heure_identif = h.id_heure
                                                JOIN gare_exp ge ON l.gaexp_lg = ge.code_gaexp
                                                JOIN compagnies c ON ge.id_compagd = c.cle_compagnie
                                                JOIN entreprise e ON c.id_entrep = e.id_entreprise
                                                WHERE e.ekey = '$cid'
                                                AND ge.code_gaexp = '$item->code_gaexp'
                                                AND lh.heure_identif = '$item->id_heure'
                                                AND l.nom_ligne != '$item->nom_ligne'
                                                AND lh.actif_lh = 1
                                                ORDER BY l.nom_ligne")->result(); 
                                        ?>
                                        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                            id="prog-ajout-<?= $item->code_progr; ?>">
                                            <div class="modal-content">
                                                <div class="modal-header modal-header-colored">
                                                    <h3 class="modal-title">AJOUTER SOUS AXE AU PROGRAMME</h3>
                                                    <button class="close modal-close" type="button"
                                                    data-dismiss="modal" aria-hidden="true"><span
                                                    class="mdi mdi-close text-white"></span>
                                                    </button>
                                                </div>
                                                <?= form_open("Programmes/gajout_/{$this->session->company->ekey}/{$item->depart_code}/{$item->categorie}/{$item->typetarif}/{$item->date_progr}/{$item->gareidentif}/{$item->dateheure_prog}",
                                                    array('class' => 'modal-body form')); ?>

                                                <div class="row">
                                                    
                                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$gare_stop->idengare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$gare_stop->idsousgare;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                                    <div class="form-group col-sm-4">

                                                    <label>DEPART</label>
                                                    <select class="form-control form-control-sm" name="heureprog">
                                                    <option value=""></option>
                                                    <? foreach ($ligneh as $ligne): ?>
                                                    <option value="<?= $ligne->id_ligneheure. '.' .$ligne->ligne_id. '.' .$ligne->heure; ?>"><?= $ligne->nom_ligne.'/'.$ligne->heure; ?>
                                                    </option>
                                                    <? endforeach; ?>
                                                    </select>
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>DEBUT PLACE</label>
                                                        <input class="form-control form-control-sm" name="debut"
                                                        type="text" autocomplete="off"
                                                    placeholder="1">
                                                    </div>
                                                    <div class="form-group col-sm-4">
                                                        <label>FIN PLACE</label>
                                                        <input class="form-control form-control-sm" name="fin"
                                                        type="text" autocomplete="off"
                                                        placeholder="65">
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

        </div>
    </div>
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
                                            Toute port&eacute;e
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
                                    <select class="form-control form-control-sm" name="categorie">
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
                                <div class="form-group col-sm-3">
                                    <label>QUOTA DEBUT</label>
                                    <input class="form-control form-control-sm" name="debut"
                                            type="text" autocomplete="off"
                                            placeholder="1">
                                </div>
                                <div class="form-group col-sm-3">
                                    <label>QUOTA FIN</label>
                                    <input class="form-control form-control-sm" name="fin"
                                            type="text" autocomplete="off"
                                            placeholder="65">
                                </div>
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
                                            Toute port&eacute;e
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
                <select class="form-control form-control-sm" name="categorie">
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
            <div class="form-group col-sm-4">
                <label>QUOTA DEBUT</label>
                <input class="form-control form-control-sm" name="debut"
                        type="text" autocomplete="off"
                        placeholder="1">
            </div>
            <div class="form-group col-sm-4">
                <label>QUOTA FIN</label>
                <input class="form-control form-control-sm" name="fin"
                        type="text" autocomplete="off"
                        placeholder="65">
            </div>
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
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="ligne" id="idligne" required>
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
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
                <button class="btn btn-success md-trigger" type="submit"
                        data-dismiss="modal">
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
            <div class="form-group col-sm-4">
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="ligneliste" id="idligneliste">
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
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
                <label>LIGNE</label>
                <select class="form-control form-control-sm" name="ligne" id="idlign">
                    <option value="">Choisissez la ligne</option>
                    <? foreach ($alllignes as $ligneitem): ?>
                        <option value="<?= $ligneitem->ident_ligne; ?>">
                            <?= $ligneitem->nom_ligne; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>PROGRAMME</label>
                <select class="form-control form-control-sm" name="prog" id="idprogr">
                    <option value="">Choisissez la ligne</option>
                    
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
                <p class="text-muted small">Choisir un départ déjà créé à la gare de correspondance. Les sièges du départ hub dérivé seront le miroir des sièges occupés sur ce départ.</p>
                <div id="corr-suggest-list"></div>
                <div id="corr-portee-box" class="mt-3" style="display:none;">
                    <hr>
                    <h6>Portée Banfora (dérivé ± principal)</h6>
                    <label class="mb-1" style="font-weight:400;cursor:pointer;">
                        <input type="radio" name="corr_scope_ban_mode" value="gare" checked class="js-corr-ban-mode"> Toute portée Banfora
                    </label>
                    <label class="mb-1 ml-3" style="font-weight:400;cursor:pointer;">
                        <input type="radio" name="corr_scope_ban_mode" value="sousgare" class="js-corr-ban-mode"> Sous-gares Banfora
                    </label>
                    <div id="corr-sg-banfora" class="row mt-1" style="opacity:0.55;pointer-events:none;"></div>
                    <div class="mt-2">
                        <label style="font-weight:400;cursor:pointer;">
                            <input type="checkbox" id="corr-apply-derive" checked> Appliquer au dérivé Banfora→hub (A)
                        </label><br>
                        <label style="font-weight:400;cursor:pointer;">
                            <input type="checkbox" id="corr-apply-principal" checked> Appliquer aussi au principal Banfora→Ouaga (B)
                        </label>
                    </div>
                    <hr>
                    <h6>Portée Bobo (suite correspondance)</h6>
                    <label class="mb-1" style="font-weight:400;cursor:pointer;">
                        <input type="radio" name="corr_scope_bob_mode" value="gare" checked class="js-corr-bob-mode"> Toute portée Bobo
                    </label>
                    <label class="mb-1 ml-3" style="font-weight:400;cursor:pointer;">
                        <input type="radio" name="corr_scope_bob_mode" value="sousgare" class="js-corr-bob-mode"> Sous-gares Bobo
                    </label>
                    <div id="corr-sg-bobo" class="row mt-1" style="opacity:0.55;pointer-events:none;"></div>
                    <div class="mt-2">
                        <label style="font-weight:400;cursor:pointer;">
                            <input type="checkbox" id="corr-apply-suite" checked> Appliquer à la suite Bobo→Ouaga
                        </label>
                    </div>
                </div>
            </div>
            <div id="corr-msg" class="mt-2"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary js-corr-close" type="button">Fermer</button>
            <button class="btn btn-danger js-corr-unlink" type="button" style="display:none;">Supprimer le lien</button>
            <button class="btn btn-primary js-corr-save" type="button" disabled>Lier + créer départ hub</button>
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
        suite: null,
        lien: null,
        verrouille: false,
        sousgaresBanfora: [],
        sousgaresBobo: []
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
            suite: null,
            lien: null,
            verrouille: false,
            sousgaresBanfora: [],
            sousgaresBobo: []
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
        document.getElementById('corr-msg').innerHTML = '';
        resetPorteeUi();
    }

    function resetPorteeUi() {
        var portee = document.getElementById('corr-portee-box');
        if (portee) portee.style.display = 'none';
        var ban = document.getElementById('corr-sg-banfora');
        var bob = document.getElementById('corr-sg-bobo');
        if (ban) ban.innerHTML = '';
        if (bob) bob.innerHTML = '';
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
        setSgBoxEnabled('corr-sg-banfora', false);
        setSgBoxEnabled('corr-sg-bobo', false);
    }

    function setSgBoxEnabled(id, enabled) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.opacity = enabled ? '1' : '0.55';
        el.style.pointerEvents = enabled ? 'auto' : 'none';
        var checks = el.querySelectorAll('input[type="checkbox"]');
        Array.prototype.forEach.call(checks, function (c) {
            c.disabled = !enabled;
            if (!enabled) c.checked = true;
        });
    }

    function renderSgChecks(containerId, list, nameAttr) {
        var box = document.getElementById(containerId);
        if (!box) return;
        if (!list || !list.length) {
            box.innerHTML = '<div class="col-12"><small class="text-muted">Aucune sous-gare.</small></div>';
            return;
        }
        var html = '';
        list.forEach(function (sg) {
            html += '<div class="form-group col-sm-4 col-md-3 mb-1">'
                + '<label class="custom-control custom-checkbox mb-0">'
                + '<input class="custom-control-input" type="checkbox" name="' + nameAttr + '" value="'
                + sg.idsousgare + '" checked disabled>'
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

    function loadSousgaresBobo(gareCode) {
        state.sousgaresBobo = [];
        renderSgChecks('corr-sg-bobo', [], 'corr_scope_bobo');
        if (!gareCode) return Promise.resolve();
        return fetch(base + '/sousgares_correspondance/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(gareCode), {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(parseJsonResponse).then(function (data) {
            state.sousgaresBobo = (data && data.sousgares) ? data.sousgares : [];
            renderSgChecks('corr-sg-bobo', state.sousgaresBobo, 'corr_scope_bobo');
            var mode = document.querySelector('input[name="corr_scope_bob_mode"]:checked');
            setSgBoxEnabled('corr-sg-bobo', mode && mode.value === 'sousgare');
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

    function renderSuggestions(list) {
        var box = document.getElementById('corr-suggest-list');
        if (!list || !list.length) {
            box.innerHTML = '<p class="text-muted">Aucun départ de correspondance compatible trouvé pour cette date.</p>';
            return;
        }
        var html = '<div class="list-group">';
        list.forEach(function (s, i) {
            html += '<label class="list-group-item" style="cursor:pointer;">'
                + '<input type="radio" name="corr_suite" value="' + s.code_progr + '" data-idx="' + i + '" style="margin-right:8px;">'
                + '<strong>' + (s.label || s.nom_ligne) + '</strong>'
                + ' <small class="text-muted">(' + s.code_progr + ' · gare ' + s.gareidentif + ' · sièges '
                + s.intervalle1 + '-' + s.intervalle2 + ')</small></label>';
        });
        html += '</div>';
        box.innerHTML = html;
        var radios = box.querySelectorAll('input[name="corr_suite"]');
        Array.prototype.forEach.call(radios, function (inp) {
            inp.addEventListener('change', function () {
                var idx = parseInt(inp.getAttribute('data-idx'), 10);
                state.suite = list[idx];
                var saveBtn = document.querySelector('#modal-correspondance .js-corr-save');
                if (saveBtn) saveBtn.disabled = !state.suite;
                loadSousgaresBobo(state.suite && state.suite.gareidentif);
            });
        });
    }

    function showPorteeBox(sousgaresBanfora) {
        state.sousgaresBanfora = sousgaresBanfora || [];
        renderSgChecks('corr-sg-banfora', state.sousgaresBanfora, 'corr_scope_banfora');
        var portee = document.getElementById('corr-portee-box');
        if (portee) portee.style.display = 'block';
        var banMode = document.querySelector('input[name="corr_scope_ban_mode"]:checked');
        setSgBoxEnabled('corr-sg-banfora', banMode && banMode.value === 'sousgare');
        var bobMode = document.querySelector('input[name="corr_scope_bob_mode"]:checked');
        setSgBoxEnabled('corr-sg-bobo', bobMode && bobMode.value === 'sousgare');
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
                + (suite.heure || '') + ' <code>' + lien.code_progr_suite + '</code></div>';
        }
        if (derive) {
            html += '<div>Dérivé (miroir sièges) : ' + (derive.nom_ligne || '') + ' '
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

    function openFor(code, nom, heure) {
        state.principal = code;
        state.suite = null;
        state.lien = null;
        state.sousgaresBanfora = [];
        state.sousgaresBobo = [];
        resetPorteeUi();
        document.getElementById('corr-principal-label').textContent =
            'Départ principal : ' + (nom || '') + ' ' + (heure || '') + ' (' + code + ')';
        document.getElementById('corr-linked-box').style.display = 'none';
        document.getElementById('corr-suggest-box').style.display = 'block';
        document.getElementById('corr-suggest-list').innerHTML = '';
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
            return fetch(base + '/suggest_correspondances/' + encodeURIComponent(ekey) + '/' + encodeURIComponent(code), {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(parseJsonResponse);
        }).then(function (sug) {
            if (!sug) return;
            setMsg('', false);
            if (!sug.ok) {
                setMsg(sug.error || 'Erreur suggestions', true);
                return;
            }
            renderSuggestions(sug.suggestions || []);
            showPorteeBox(sug.sousgares_banfora || []);
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
            openFor(btn.getAttribute('data-code'), btn.getAttribute('data-nom'), btn.getAttribute('data-heure'));
            return;
        }
        if (closer) {
            e.preventDefault();
            hideModal();
        }
    });

    Array.prototype.forEach.call(document.querySelectorAll('.js-corr-ban-mode'), function (inp) {
        inp.addEventListener('change', function () {
            setSgBoxEnabled('corr-sg-banfora', inp.value === 'sousgare');
        });
    });
    Array.prototype.forEach.call(document.querySelectorAll('.js-corr-bob-mode'), function (inp) {
        inp.addEventListener('change', function () {
            setSgBoxEnabled('corr-sg-bobo', inp.value === 'sousgare');
        });
    });

    var saveEl = document.querySelector('#modal-correspondance .js-corr-save');
    if (saveEl) {
        saveEl.addEventListener('click', function () {
            if (!state.principal || !state.suite) return;
            var btn = this;
            btn.disabled = true;
            setMsg('Création du lien…', false);
            var body = new URLSearchParams();
            body.set('code_progr_principal', state.principal);
            body.set('code_progr_suite', state.suite.code_progr);
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
                    setMsg((data && data.error) || 'Échec du lien', true);
                    btn.disabled = false;
                    return;
                }
                var warn = (data.portee_warnings && data.portee_warnings.length)
                    ? ' (portée partielle : ' + data.portee_warnings.join(', ') + ')'
                    : '';
                setMsg('Lien créé. Départ dérivé : ' + (data.lien && data.lien.code_progr_derive ? data.lien.code_progr_derive : '') + warn, false);
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

<!--End of file: program.php-->
<!--File location: application/views/beagle/pages/_gares/program.php-->