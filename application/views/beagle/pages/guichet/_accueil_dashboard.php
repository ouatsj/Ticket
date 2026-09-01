<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$__axes_count = isset($dashboard_axes_count) ? (int) $dashboard_axes_count : 0;
$__clients_count = isset($dashboard_clients_count) ? (int) $dashboard_clients_count : 0;
?>
<div class="col-12 col-lg-6 col-xl-3">
    <div class="widget widget-tile">
        <div class="chart sparkline" id="spark1">
            <canvas width="85" height="35"
                style="display: inline-block; width: 85px; height: 35px; vertical-align: top;"></canvas>
        </div>
        <div class="data-info">
            <div class="desc">Axes</div>
            <div class="value">
                <span class="indicator indicator-equal mdi mdi-chevron-right"></span>
                <span class="number" data-toggle="counter"
                    data-end="<?= $__axes_count; ?>">
                    <?= $__axes_count; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-lg-6 col-xl-3">
    <div class="widget widget-tile">
        <div class="chart sparkline" id="spark3">
            <canvas width="85" height="35"
                style="display: inline-block; width: 85px; height: 35px; vertical-align: top;"></canvas>
        </div>
        <div class="data-info">
            <div class="desc">Clients</div>
            <div class="value">
                <span class="indicator indicator-positive mdi mdi-chevron-up"></span>
                <span class="number" data-toggle="counter"
                    data-end="<?= $__clients_count; ?>">
                    <?= $__clients_count; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="col-12 col-lg-6">
    <div class="card card-table">
        <div class="card-header">
            <div class="tools dropdown">
                <span class="icon mdi mdi-download"></span>
                <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                    <span class="icon mdi mdi-more-vert"></span>
                </a>
            </div>
        </div>
        <div class="title"></div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width:37%;">Lignes</th>
                        <th style="width:36%;">nombre passager</th>
                    </tr>
                </thead>
                <tbody id="passagers-stats-body">
                    <?php if (empty($passagers_deferred)): ?>
                    <? foreach ($passagers as $pas): ?>
                    <tr>
                        <td><?= $pas->nom_ligne; ?></td>
                        <td><?= $pas->cod; ?></td>
                    </tr>
                    <? endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="2" class="text-muted">Chargement…</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if (!empty($passagers_deferred)): ?>
<script>
(function () {
    var url = <?= json_encode(site_url('gares/' . $this->session->company->ekey . '/ajax_passagers')); ?>;
    var run = function () {
        var fetchFn = (window.GuichetLoadScheduler && GuichetLoadScheduler.deferFetch)
            ? GuichetLoadScheduler.deferFetch.bind(GuichetLoadScheduler)
            : function (u, opts) { return fetch(u, opts); };
        fetchFn(url, { credentials: 'same-origin' }, 800)
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (rows) {
                var tb = document.getElementById('passagers-stats-body');
                if (!tb) return;
                if (!rows || !rows.length) {
                    tb.innerHTML = '<tr><td colspan="2">Aucune donnée</td></tr>';
                    return;
                }
                tb.innerHTML = rows.map(function (p) {
                    return '<tr><td>' + String(p.nom_ligne).replace(/</g, '&lt;') + '</td><td>' + p.cod + '</td></tr>';
                }).join('');
            })
            .catch(function () {
                var tb = document.getElementById('passagers-stats-body');
                if (tb) tb.innerHTML = '<tr><td colspan="2">Erreur de chargement</td></tr>';
            });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(run, 400);
        });
    } else {
        setTimeout(run, 400);
    }
})();
</script>
<?php endif; ?>
