<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$__axes_count = isset($dashboard_axes_count) ? (int) $dashboard_axes_count : 0;
$__clients_count = isset($dashboard_clients_count) ? (int) $dashboard_clients_count : 0;
$__stats_days = isset($passagers_stats_days) ? (int) $passagers_stats_days : 30;
?>
<style>
.dash-kpi{border:1px solid #e8ecf0;border-radius:8px;background:#fff;padding:1rem 1.1rem;height:100%;box-shadow:0 1px 2px rgba(16,24,40,.04)}
.dash-kpi .dash-kpi-label{font-size:.78rem;font-weight:600;letter-spacing:.02em;text-transform:uppercase;color:#6c757d;margin:0 0 .35rem}
.dash-kpi .dash-kpi-value{font-size:1.75rem;font-weight:700;line-height:1.1;color:#243447;margin:0}
.dash-kpi .dash-kpi-hint{font-size:.75rem;color:#98a2b3;margin:.35rem 0 0}
.dash-lines-card{border:1px solid #e8ecf0;border-radius:8px;background:#fff;box-shadow:0 1px 2px rgba(16,24,40,.04);height:100%;display:flex;flex-direction:column}
.dash-lines-card .dash-lines-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;padding:.85rem 1rem;border-bottom:1px solid #eef1f4}
.dash-lines-card .dash-lines-title{font-size:.95rem;font-weight:700;color:#243447;margin:0}
.dash-lines-card .dash-lines-meta{font-size:.75rem;color:#6c757d;margin:.15rem 0 0}
.dash-lines-card .dash-lines-total{font-size:.85rem;font-weight:600;color:#243447;white-space:nowrap}
.dash-lines-card .dash-lines-tools{padding:.55rem 1rem;border-bottom:1px solid #eef1f4}
.dash-lines-card .dash-lines-tools input{max-width:220px}
.dash-lines-card .dash-lines-body{padding:0;max-height:340px;overflow:auto;-webkit-overflow-scrolling:touch}
.dash-lines-card table{margin:0}
.dash-lines-card th{font-size:.72rem;text-transform:uppercase;letter-spacing:.03em;color:#6c757d;border-top:0;position:sticky;top:0;background:#f8fafc;z-index:1}
.dash-lines-card td{vertical-align:middle;font-size:.875rem}
.dash-lines-card .dash-bar-wrap{height:6px;background:#eef1f4;border-radius:999px;overflow:hidden;min-width:72px}
.dash-lines-card .dash-bar{height:100%;background:#3b82f6;border-radius:999px;width:0}
.dash-lines-card .dash-cod{font-variant-numeric:tabular-nums;font-weight:600;white-space:nowrap}
.dash-lines-card .dash-lines-foot{padding:.55rem 1rem;border-top:1px solid #eef1f4;font-size:.8rem}
.dash-skel{height:12px;background:linear-gradient(90deg,#eef1f4 25%,#f6f8fa 50%,#eef1f4 75%);background-size:200% 100%;animation:dashSkel 1.1s ease-in-out infinite;border-radius:4px}
@keyframes dashSkel{0%{background-position:200% 0}100%{background-position:-200% 0}}
@media (max-width:767px){
  .dash-lines-card .dash-lines-body{max-height:280px}
  .dash-lines-card .dash-lines-tools input{max-width:100%}
}
</style>

<div class="col-12 col-sm-6 col-xl-3 mb-3">
    <div class="dash-kpi">
        <p class="dash-kpi-label">Axes</p>
        <p class="dash-kpi-value"><?= number_format($__axes_count, 0, ',', ' '); ?></p>
        <p class="dash-kpi-hint">Lignes actives</p>
    </div>
</div>

<div class="col-12 col-sm-6 col-xl-3 mb-3">
    <div class="dash-kpi">
        <p class="dash-kpi-label">Clients</p>
        <p class="dash-kpi-value"><?= number_format($__clients_count, 0, ',', ' '); ?></p>
        <p class="dash-kpi-hint">Fiches enregistrées</p>
    </div>
</div>

<div class="col-12 col-xl-6 mb-3">
    <div class="dash-lines-card" id="dash-passagers-card">
        <div class="dash-lines-head">
            <div>
                <h3 class="dash-lines-title">Passagers par ligne</h3>
                <p class="dash-lines-meta" id="dash-passagers-meta"><?= (int) $__stats_days; ?> derniers jours</p>
            </div>
            <div class="dash-lines-total" id="dash-passagers-total">—</div>
        </div>
        <div class="dash-lines-tools d-none" id="dash-passagers-tools">
            <input type="search" class="form-control form-control-sm" id="dash-passagers-filter" placeholder="Filtrer une ligne…" autocomplete="off">
        </div>
        <div class="dash-lines-body table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:48%;">Ligne</th>
                        <th style="width:32%;">Volume</th>
                        <th class="text-right" style="width:20%;">Passagers</th>
                    </tr>
                </thead>
                <tbody id="passagers-stats-body">
                    <?php if (empty($passagers_deferred)): ?>
                    <? foreach ($passagers as $pas): ?>
                    <tr>
                        <td><?= htmlspecialchars($pas->nom_ligne, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td></td>
                        <td class="text-right dash-cod"><?= (int) $pas->cod; ?></td>
                    </tr>
                    <? endforeach; ?>
                    <?php else: ?>
                    <tr class="dash-loading-row"><td colspan="3"><div class="dash-skel my-2"></div></td></tr>
                    <tr class="dash-loading-row"><td colspan="3"><div class="dash-skel my-2" style="width:85%"></div></td></tr>
                    <tr class="dash-loading-row"><td colspan="3"><div class="dash-skel my-2" style="width:70%"></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="dash-lines-foot d-none" id="dash-passagers-foot"></div>
    </div>
</div>
<?php if (!empty($passagers_deferred)): ?>
<script>
(function () {
    var url = <?= json_encode(site_url('gares/' . $this->session->company->ekey . '/ajax_passagers')); ?>;
    var INITIAL = 12;
    var state = { rows: [], max: 0, shown: INITIAL };

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function fmt(n) {
        try { return Number(n).toLocaleString('fr-FR'); } catch (e) { return String(n); }
    }
    function filtered() {
        var q = (document.getElementById('dash-passagers-filter') || {}).value || '';
        q = String(q).trim().toLowerCase();
        if (!q) return state.rows;
        return state.rows.filter(function (r) {
            return String(r.nom_ligne || '').toLowerCase().indexOf(q) !== -1;
        });
    }
    function render() {
        var tb = document.getElementById('passagers-stats-body');
        if (!tb) return;
        var list = filtered();
        var limit = state.shown;
        var slice = list.slice(0, limit);
        if (!slice.length) {
            tb.innerHTML = '<tr><td colspan="3" class="text-muted text-center py-3">Aucune ligne</td></tr>';
        } else {
            tb.innerHTML = slice.map(function (p) {
                var cod = Number(p.cod) || 0;
                var pct = state.max > 0 ? Math.max(4, Math.round((cod / state.max) * 100)) : 0;
                return '<tr data-line="' + esc(p.nom_ligne) + '">'
                    + '<td>' + esc(p.nom_ligne) + '</td>'
                    + '<td><div class="dash-bar-wrap" title="' + pct + '%"><div class="dash-bar" style="width:' + pct + '%"></div></div></td>'
                    + '<td class="text-right dash-cod">' + fmt(cod) + '</td>'
                    + '</tr>';
            }).join('');
        }
        var foot = document.getElementById('dash-passagers-foot');
        if (!foot) return;
        if (list.length > limit) {
            foot.classList.remove('d-none');
            foot.innerHTML = '<button type="button" class="btn btn-sm btn-link p-0" id="dash-passagers-more">'
                + 'Afficher les ' + (list.length - limit) + ' lignes restantes</button>';
            var btn = document.getElementById('dash-passagers-more');
            if (btn) btn.onclick = function () { state.shown = list.length; render(); };
        } else if (list.length > INITIAL && state.shown > INITIAL) {
            foot.classList.remove('d-none');
            foot.innerHTML = '<button type="button" class="btn btn-sm btn-link p-0" id="dash-passagers-less">Réduire</button>';
            var less = document.getElementById('dash-passagers-less');
            if (less) less.onclick = function () { state.shown = INITIAL; render(); };
        } else {
            foot.classList.add('d-none');
            foot.innerHTML = '';
        }
    }
    function applyPayload(data) {
        var rows = [];
        var days = <?= (int) $__stats_days; ?>;
        var total = 0;
        if (Array.isArray(data)) {
            rows = data;
        } else if (data && typeof data === 'object') {
            rows = Array.isArray(data.rows) ? data.rows : [];
            if (data.days) days = data.days;
            if (data.total != null) total = Number(data.total) || 0;
        }
        state.rows = rows;
        state.max = 0;
        state.shown = INITIAL;
        for (var i = 0; i < rows.length; i++) {
            var c = Number(rows[i].cod) || 0;
            if (c > state.max) state.max = c;
            if (!total) total += c;
        }
        var meta = document.getElementById('dash-passagers-meta');
        if (meta) meta.textContent = days + ' derniers jours · ' + rows.length + ' ligne' + (rows.length > 1 ? 's' : '');
        var tot = document.getElementById('dash-passagers-total');
        if (tot) tot.textContent = fmt(total) + ' passagers';
        var tools = document.getElementById('dash-passagers-tools');
        if (tools) tools.classList.toggle('d-none', rows.length < 6);
        render();
    }
    function run() {
        var fetchFn = (window.GuichetLoadScheduler && GuichetLoadScheduler.deferFetch)
            ? function (u, opts) { return GuichetLoadScheduler.deferFetch(u, opts, 200); }
            : function (u, opts) { return fetch(u, opts); };
        fetchFn(url, { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : { rows: [] }; })
            .then(applyPayload)
            .catch(function () {
                var tb = document.getElementById('passagers-stats-body');
                if (tb) tb.innerHTML = '<tr><td colspan="3" class="text-danger text-center py-3">Erreur de chargement</td></tr>';
            });
    }
    var filter = document.getElementById('dash-passagers-filter');
    if (filter) {
        filter.addEventListener('input', function () {
            state.shown = INITIAL;
            render();
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { setTimeout(run, 50); });
    } else {
        setTimeout(run, 50);
    }
})();
</script>
<?php endif; ?>
