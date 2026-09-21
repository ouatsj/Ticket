<?php defined('BASEPATH') OR exit('No direct script access allowed');
$ckey = $this->session->company->ekey;
$compagnies = !empty($compagnies) && is_array($compagnies) ? $compagnies : array();
$lignes = !empty($lignes) && is_array($lignes) ? $lignes : array();
$lignes_verrouillees = !empty($lignes_verrouillees) && is_array($lignes_verrouillees) ? $lignes_verrouillees : array();
$max_places = isset($max_places) ? (int) $max_places : 55;
$save_url = site_url('param_sieges_verrou/' . $ckey . '/save');
$ajax_base = site_url('param_sieges_verrou/' . $ckey . '/ajax');
$saved_lh = isset($saved_lh) ? (int) $saved_lh : 0;
?>
<style>
.sv-page { --sv-red:#b91c1c; --sv-red-bg:#fef2f2; --sv-border:#e2e8f0; }
.sv-page h4 { font-weight: 700; }
.sv-filters {
    background: #f8fafc;
    border: 1px solid var(--sv-border);
    border-radius: 10px;
    padding: 0.85rem 1rem;
    margin-bottom: 1rem;
}
.sv-cie-list {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin: 0 -0.25rem 0.65rem;
}
.sv-cie-list label {
    display: inline-block;
    margin: 0.25rem;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    -webkit-user-select: none;
    user-select: none;
}
.sv-cie-list label.is-on {
    background: #1e3a8a;
    border-color: #1e3a8a;
    color: #fff;
}
.sv-cie-list input { margin-right: 0.35rem; vertical-align: middle; }
.sv-search-wrap { position: relative; max-width: 420px; }
.sv-search-wrap .fas {
    position: absolute; left: 0.7rem; top: 50%;
    -webkit-transform: translateY(-50%); transform: translateY(-50%);
    color: #94a3b8; font-size: 0.85rem;
}
.sv-search-wrap input {
    padding-left: 2rem;
    height: 38px;
}
.sv-meta { font-size: 0.8rem; color: #64748b; margin-top: 0.4rem; }
.sv-line-item {
    display: block;
    width: 100%;
    text-align: left;
    border: 1px solid var(--sv-border);
    border-radius: 8px;
    background: #fff;
    padding: 0.55rem 0.75rem;
    margin: 0 0 0.45rem;
    cursor: pointer;
    -webkit-transition: border-color .12s, box-shadow .12s;
    transition: border-color .12s, box-shadow .12s;
}
.sv-line-item:hover, .sv-line-item:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59,130,246,.15);
    outline: none;
}
.sv-line-item.has-verrou { border-left: 4px solid var(--sv-red); }
.sv-line-item .sv-title { font-weight: 700; color: #0f172a; }
.sv-line-item .sv-sub { display: block; font-size: 0.78rem; color: #64748b; margin-top: 0.1rem; }
.sv-badge {
    display: inline-block;
    min-width: 1.4rem;
    padding: 0.1rem 0.4rem;
    border-radius: 999px;
    background: var(--sv-red);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    text-align: center;
}
.sv-locked-panel .sv-line-item { background: var(--sv-red-bg); }
.sv-empty {
    padding: 1rem;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    color: #64748b;
    font-size: 0.9rem;
}
/* Modal */
.sv-modal-backdrop {
    display: none;
    position: fixed;
    z-index: 1050;
    left: 0; top: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.55);
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    padding: 1rem;
}
.sv-modal-backdrop.is-open { display: -webkit-box; display: -ms-flexbox; display: flex; }
.sv-modal {
    background: #fff;
    border-radius: 12px;
    width: 100%;
    max-width: 720px;
    max-height: 92vh;
    overflow: auto;
    box-shadow: 0 20px 50px rgba(0,0,0,.25);
}
.sv-modal-header {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: justify;
    -ms-flex-pack: justify;
    justify-content: space-between;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--sv-border);
    background: #0f172a;
    color: #fff;
    border-radius: 12px 12px 0 0;
}
.sv-modal-header h5 { margin: 0; font-size: 1rem; font-weight: 700; }
.sv-modal-close {
    background: transparent; border: 0; color: #fff;
    font-size: 1.4rem; line-height: 1; cursor: pointer; padding: 0 0.25rem;
}
.sv-modal-body { padding: 1rem; }
.sv-modal-footer {
    padding: 0.75rem 1rem 1rem;
    border-top: 1px solid var(--sv-border);
}
.sv-seat-label {
    display: block;
    padding: 4px 6px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 400;
    border: 1px solid #dee2e6;
}
.sv-seat-label.is-verrou {
    background: #f8d7da;
    border-color: #dc3545;
}
</style>

<div class="sv-page">
    <div class="row">
        <div class="col-12 mb-2">
            <h4 class="mb-1">Sièges verrouillés (admin)</h4>
            <p class="text-muted mb-0">
                Filtrez par <strong>compagnie d’arrivée</strong>, cherchez une ligne / heure, puis cliquez
                pour ouvrir le plan et verrouiller des sièges. Les lignes désactivées n’apparaissent pas.
            </p>
        </div>
    </div>

    <?php if (!empty($saved)): ?>
        <div class="alert alert-success">
            Verrous enregistrés.
            <?php if (!empty($propagated)): ?>
                Appliqués à <strong><?= (int) $propagated; ?></strong> programme(s) futur(s).
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($msg = $this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <div class="sv-filters">
                    <div class="mb-1"><strong>Compagnies d’arrivée</strong></div>
        <?php if (empty($compagnies)): ?>
            <p class="text-muted mb-2">Aucune compagnie / ligne active.</p>
        <?php else: ?>
            <div class="sv-cie-list" id="sv-cie-list" role="group" aria-label="Filtrer par compagnie">
                <?php foreach ($compagnies as $cie): ?>
                    <label class="is-on">
                        <input type="checkbox" class="sv-cie-cb" value="<?= htmlspecialchars($cie['key'], ENT_QUOTES, 'UTF-8'); ?>" checked>
                        <?= htmlspecialchars($cie['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="sv-cie-all">Toutes</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="sv-cie-none">Aucune</button>
            </div>
        <?php endif; ?>
        <div class="sv-search-wrap">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" class="form-control" id="sv-search"
                   placeholder="Recherche instantanée : ligne, heure, arrivée…"
                   autocomplete="off" aria-label="Recherche lignes">
        </div>
        <p class="sv-meta mb-0" id="sv-list-meta"></p>
    </div>

    <div class="row">
        <div class="col-lg-7 col-12 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Lignes &amp; heures à choisir</span>
                    <small class="text-muted">Clic → plan de sièges</small>
                </div>
                <div class="card-body" style="max-height:62vh;overflow:auto;" id="sv-lines-box">
                    <div id="sv-lines-list"></div>
                    <div class="sv-empty d-none" id="sv-lines-empty">Aucune ligne pour ce filtre.</div>
                </div>
            </div>
        </div>
        <div class="col-lg-5 col-12 mb-3">
            <div class="card sv-locked-panel">
                <div class="card-header">
                    Lignes avec sièges verrouillés
                    <small class="text-muted d-block font-weight-normal">selon compagnies cochées</small>
                </div>
                <div class="card-body" style="max-height:62vh;overflow:auto;">
                    <div id="sv-locked-list"></div>
                    <div class="sv-empty d-none" id="sv-locked-empty">Aucun verrou pour les compagnies sélectionnées.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sv-modal-backdrop" id="sv-modal" aria-hidden="true">
    <div class="sv-modal" role="dialog" aria-modal="true" aria-labelledby="sv-modal-title">
        <div class="sv-modal-header">
            <h5 id="sv-modal-title">Plan de sièges</h5>
            <button type="button" class="sv-modal-close" id="sv-modal-close" aria-label="Fermer">&times;</button>
        </div>
        <form method="post" action="<?= htmlspecialchars($save_url, ENT_QUOTES, 'UTF-8'); ?>" id="sv-modal-form">
            <?php if (config_item('csrf_protection')): ?>
                <input type="hidden" name="<?= htmlspecialchars($this->security->get_csrf_token_name(), ENT_QUOTES, 'UTF-8'); ?>"
                       value="<?= htmlspecialchars($this->security->get_csrf_hash(), ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <div class="sv-modal-body">
                <input type="hidden" name="id_ligneheure" id="sv-modal-lh" value="">
                <p class="text-muted mb-2" id="sv-modal-sub"></p>
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="sv-check-all">Tout verrouiller</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="sv-uncheck-all">Tout déverrouiller</button>
                    <small class="text-muted ml-2" id="sv-count">0 verrouillé(s)</small>
                </div>
                <div class="row" id="sv-grid"></div>
                <div class="form-group mt-3 mb-0">
                    <label class="custom-control custom-checkbox mb-0">
                        <input type="checkbox" class="custom-control-input" name="appliquer_futurs" value="1" checked>
                        <span class="custom-control-label">
                            Appliquer aussi aux <strong>programmes futurs</strong> de cette heure
                        </span>
                    </label>
                </div>
            </div>
            <div class="sv-modal-footer">
                <button type="button" class="btn btn-secondary" id="sv-modal-cancel">Annuler</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-lock"></i> Enregistrer les verrous
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var LIGNES = <?= json_encode($lignes, JSON_UNESCAPED_UNICODE); ?>;
    var LOCKED = <?= json_encode($lignes_verrouillees, JSON_UNESCAPED_UNICODE); ?>;
    var MAX = <?= (int) $max_places; ?>;
    var AJAX = <?= json_encode($ajax_base, JSON_UNESCAPED_UNICODE); ?>;
    var SAVED_LH = <?= (int) $saved_lh; ?>;

    var linesBox = document.getElementById('sv-lines-list');
    var linesEmpty = document.getElementById('sv-lines-empty');
    var lockedBox = document.getElementById('sv-locked-list');
    var lockedEmpty = document.getElementById('sv-locked-empty');
    var metaEl = document.getElementById('sv-list-meta');
    var searchEl = document.getElementById('sv-search');
    var modal = document.getElementById('sv-modal');
    var grid = document.getElementById('sv-grid');
    var countEl = document.getElementById('sv-count');
    var titleEl = document.getElementById('sv-modal-title');
    var subEl = document.getElementById('sv-modal-sub');
    var lhInput = document.getElementById('sv-modal-lh');

    function selectedCies() {
        var out = {};
        document.querySelectorAll('.sv-cie-cb:checked').forEach(function (cb) {
            out[cb.value] = true;
        });
        return out;
    }

    function syncCieLabels() {
        document.querySelectorAll('#sv-cie-list label').forEach(function (lab) {
            var cb = lab.querySelector('.sv-cie-cb');
            if (!cb) return;
            if (cb.checked) lab.classList.add('is-on');
            else lab.classList.remove('is-on');
        });
    }

    function matchItem(item, cies, q) {
        if (!cies[item.cie_key]) return false;
        if (!q) return true;
        return (item.search || '').indexOf(q) !== -1;
    }

    function renderButton(item, highlight) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'sv-line-item' + (item.nb_verrou > 0 ? ' has-verrou' : '');
        if (highlight) btn.style.boxShadow = '0 0 0 2px rgba(185,28,28,.35)';
        btn.setAttribute('data-lh', String(item.id_ligneheure));
        var badge = item.nb_verrou > 0
            ? ' <span class="sv-badge">' + item.nb_verrou + '</span>'
            : '';
        btn.innerHTML =
            '<span class="sv-title">' + escapeHtml(item.nom_ligne) + ' · ' + escapeHtml(item.heure) + badge + '</span>' +
            '<span class="sv-sub">' + escapeHtml(item.cie_label) +
            (item.arrivee ? (' → ' + escapeHtml(item.arrivee)) : '') +
            ' · #' + item.id_ligneheure + '</span>';
        btn.addEventListener('click', function () { openModal(item); });
        return btn;
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function applyFilters() {
        syncCieLabels();
        var cies = selectedCies();
        var q = (searchEl.value || '').toLowerCase().trim();
        var nAll = 0, nShow = 0;

        linesBox.innerHTML = '';
        LIGNES.forEach(function (item) {
            nAll++;
            if (!matchItem(item, cies, q)) return;
            nShow++;
            linesBox.appendChild(renderButton(item, item.id_ligneheure === SAVED_LH));
        });
        linesEmpty.classList.toggle('d-none', nShow > 0);

        var nLock = 0;
        lockedBox.innerHTML = '';
        LOCKED.forEach(function (item) {
            if (!cies[item.cie_key]) return;
            if (q && (item.search || '').indexOf(q) === -1) return;
            nLock++;
            lockedBox.appendChild(renderButton(item, item.id_ligneheure === SAVED_LH));
        });
        lockedEmpty.classList.toggle('d-none', nLock > 0);

        if (metaEl) {
            metaEl.textContent = nShow + ' / ' + nAll + ' ligne(s) · ' + nLock + ' avec verrou(s)';
        }
    }

    function refreshGridCount() {
        var n = 0;
        grid.querySelectorAll('.sv-cb').forEach(function (cb) {
            var lab = cb.closest('label');
            if (cb.checked) {
                n++;
                if (lab) lab.classList.add('is-verrou');
            } else if (lab) {
                lab.classList.remove('is-verrou');
            }
        });
        if (countEl) countEl.textContent = n + ' verrouillé(s)';
    }

    function buildGrid(checkedSet) {
        grid.innerHTML = '';
        for (var n = 1; n <= MAX; n++) {
            var on = !!checkedSet[n];
            var col = document.createElement('div');
            col.className = 'col-3 col-md-2 mb-1';
            col.innerHTML =
                '<label class="sv-seat-label' + (on ? ' is-verrou' : '') + '">' +
                '<input type="checkbox" name="sieges[]" value="' + n + '" class="sv-cb"' + (on ? ' checked' : '') + '> ' +
                '<strong>' + n + '</strong>' +
                '</label>';
            grid.appendChild(col);
        }
        refreshGridCount();
    }

    function openModal(item) {
        titleEl.textContent = item.nom_ligne + ' · ' + item.heure;
        subEl.textContent = item.cie_label + (item.arrivee ? (' → ' + item.arrivee) : '') + ' · #' + item.id_ligneheure;
        lhInput.value = String(item.id_ligneheure);

        var set = {};
        (item.sieges || []).forEach(function (s) { set[parseInt(s, 10)] = true; });
        buildGrid(set);

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        // Rafraîchir depuis serveur (au cas où)
        fetch(AJAX + '/' + item.id_ligneheure, {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (!data || !data.ok || !Array.isArray(data.sieges)) return;
            if (String(lhInput.value) !== String(item.id_ligneheure)) return;
            var s2 = {};
            data.sieges.forEach(function (s) { s2[parseInt(s, 10)] = true; });
            buildGrid(s2);
        }).catch(function () {});
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    var cieList = document.getElementById('sv-cie-list');
    if (cieList) cieList.addEventListener('change', applyFilters);
    var cieAll = document.getElementById('sv-cie-all');
    var cieNone = document.getElementById('sv-cie-none');
    if (cieAll) cieAll.addEventListener('click', function () {
        document.querySelectorAll('.sv-cie-cb').forEach(function (cb) { cb.checked = true; });
        applyFilters();
    });
    if (cieNone) cieNone.addEventListener('click', function () {
        document.querySelectorAll('.sv-cie-cb').forEach(function (cb) { cb.checked = false; });
        applyFilters();
    });
    if (searchEl) searchEl.addEventListener('input', applyFilters);

    grid.addEventListener('change', refreshGridCount);
    document.getElementById('sv-check-all').addEventListener('click', function () {
        grid.querySelectorAll('.sv-cb').forEach(function (cb) { cb.checked = true; });
        refreshGridCount();
    });
    document.getElementById('sv-uncheck-all').addEventListener('click', function () {
        grid.querySelectorAll('.sv-cb').forEach(function (cb) { cb.checked = false; });
        refreshGridCount();
    });
    document.getElementById('sv-modal-close').addEventListener('click', closeModal);
    document.getElementById('sv-modal-cancel').addEventListener('click', closeModal);
    modal.addEventListener('click', function (ev) {
        if (ev.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
    });

    applyFilters();

    if (SAVED_LH > 0) {
        var found = null;
        LIGNES.forEach(function (it) {
            if (it.id_ligneheure === SAVED_LH) found = it;
        });
        if (found) {
            // laisser la liste filtrée visible ; pas d’auto-ouverture obligatoire
        }
    }
})();
</script>
