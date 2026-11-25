<?php defined('BASEPATH') OR exit('No direct script access allowed');
$ckey = isset($ckey) ? $ckey : '';
$max = isset($max_archivable) ? (int) $max_archivable : ((int) date('Y') - 2);
$hot = isset($hot_from) ? (int) $hot_from : ($max + 1);
$tabs = !empty($tabs) && is_array($tabs) ? $tabs : array();
$active = !empty($active_tab) ? $active_tab : 'ticket';
if (!isset($tabs[$active])) {
    $keys = array_keys($tabs);
    $active = $keys ? $keys[0] : 'ticket';
}
$runUrl = site_url('archive_annees/' . $ckey . '/run');
$resetUrl = site_url('archive_annees/' . $ckey . '/reset');
$baseUrl = site_url('archive_annees/' . $ckey);
$voirBase = site_url('archive_annees/' . $ckey . '/voir');
$csrfName = isset($csrf_name) ? $csrf_name : 'csrf_raketa';
$csrfHash = isset($csrf_hash) ? $csrf_hash : '';
$phaseLabels = array(
    0 => 'Préparation',
    1 => 'Copie archive',
    2 => 'Désactivation ops',
    3 => 'Retrait du live',
    4 => 'Finalisation',
);
?>
<style>
.arch-tabs { display:-webkit-box; display:-ms-flexbox; display:flex; margin:0 0 1rem; border-bottom:2px solid #e2e8f0; }
.arch-tabs a {
    -webkit-box-flex:1; -ms-flex:1; flex:1; text-align:center; padding:0.75rem 0.5rem;
    font-weight:700; color:#475569; text-decoration:none; border-bottom:3px solid transparent; margin-bottom:-2px;
}
.arch-tabs a.is-on { color:#0f172a; border-bottom-color:#0d6efd; background:#f8fafc; }
.arch-panel { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:1rem; }
.arch-badge-done { background:#166534; color:#fff; padding:0.2rem 0.55rem; border-radius:4px; font-size:0.75rem; font-weight:700; }
.arch-badge-live { background:#e2e8f0; color:#334155; padding:0.2rem 0.55rem; border-radius:4px; font-size:0.75rem; font-weight:700; }
.arch-badge-run { background:#b45309; color:#fff; padding:0.2rem 0.55rem; border-radius:4px; font-size:0.75rem; font-weight:700; }
.arch-badge-err { background:#b91c1c; color:#fff; padding:0.2rem 0.55rem; border-radius:4px; font-size:0.75rem; font-weight:700; }
.arch-actions .btn { margin:0.15rem 0.15rem 0.15rem 0; }
</style>

<div class="container-fluid py-3">
    <h1 class="h3 mb-1">Archivage</h1>
    <p class="text-muted mb-3">
        L’archivage <strong>allège</strong> le logiciel en retirant les anciennes années du live.
        Les données restent disponibles dans les <strong>états et déclarations</strong>
        (et via le bouton <strong>Voir</strong> ici).
        Choisissez Ticket / Courrier / Bagage, puis une année.
        Années encore en activité à partir de <strong><?= (int) $hot; ?></strong>.
    </p>

    <div class="arch-tabs" role="tablist">
        <?php foreach ($tabs as $key => $tab): ?>
            <a href="<?= htmlspecialchars($baseUrl . '?tab=' . rawurlencode($key), ENT_QUOTES, 'UTF-8'); ?>"
               class="<?= $key === $active ? 'is-on' : ''; ?>">
                <?= htmlspecialchars($tab['label'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div id="archive-msg" class="alert" style="display:none;"></div>
    <div id="archive-progress" class="mb-3" style="display:none;">
        <div class="progress" style="height:22px;">
            <div id="archive-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning"
                 role="progressbar" style="width:0%;">0%</div>
        </div>
        <p id="archive-step" class="small mt-1 mb-0 text-muted"></p>
    </div>

    <?php
    $cur = isset($tabs[$active]) ? $tabs[$active] : array('label' => '', 'years' => array());
    $years = !empty($cur['years']) ? $cur['years'] : array();
    ?>
    <div class="arch-panel">
        <h2 class="h5 mb-3"><?= htmlspecialchars($cur['label'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th style="width:90px;">Année</th>
                        <th style="width:130px;">État</th>
                        <th>Contenu archivé</th>
                        <th style="width:280px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$years): ?>
                    <tr><td colspan="4">Aucune année.</td></tr>
                <?php endif; ?>
                <?php foreach ($years as $y):
                    $annee = (int) $y['annee'];
                    $statut = isset($y['statut']) ? (string) $y['statut'] : 'ouverte';
                    $archived = (int) $y['archived'];
                    $live = (int) $y['live'];
                    $isArchived = ($statut === 'done' || $archived > 0);
                    if ($statut === 'done') {
                        $badge = '<span class="arch-badge-done">Archivée</span>';
                        $resume = number_format($archived, 0, ',', ' ') . ' enregistrement(s)';
                        if (!empty($y['row']->finished_at)) {
                            $resume .= ' — le ' . $y['row']->finished_at;
                        }
                    } elseif ($statut === 'running') {
                        $badge = '<span class="arch-badge-run">En cours</span>';
                        $resume = 'Archivage en cours…';
                    } elseif ($statut === 'error') {
                        $badge = '<span class="arch-badge-err">Erreur</span>';
                        $resume = !empty($y['row']->message) ? $y['row']->message : 'Échec — réessayez';
                    } elseif ($archived > 0) {
                        $badge = '<span class="arch-badge-done">Partielle</span>';
                        $resume = number_format($archived, 0, ',', ' ') . ' déjà en archive'
                            . ($live > 0 ? ' · ' . number_format($live, 0, ',', ' ') . ' encore en live' : '');
                    } else {
                        $badge = '<span class="arch-badge-live">Non archivée</span>';
                        $resume = $live > 0
                            ? number_format($live, 0, ',', ' ') . ' enregistrement(s) encore en live'
                            : 'Rien à archiver pour cette année';
                    }
                    $canReset = ($statut === 'running' || $statut === 'error');
                    $voirUrl = $voirBase . '?domaine=' . rawurlencode($active) . '&annee=' . $annee;
                ?>
                    <tr>
                        <td><strong><?= $annee; ?></strong></td>
                        <td><?= $badge; ?></td>
                        <td><?= htmlspecialchars($resume, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="arch-actions">
                            <?php if ($isArchived): ?>
                                <a class="btn btn-primary btn-sm"
                                   href="<?= htmlspecialchars($voirUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                    Voir
                                </a>
                            <?php endif; ?>
                            <button type="button"
                                class="btn btn-<?= $isArchived ? 'outline-danger' : 'danger'; ?> btn-sm js-archive-year"
                                data-annee="<?= $annee; ?>"
                                data-domaine="<?= htmlspecialchars($active, ENT_QUOTES, 'UTF-8'); ?>">
                                <?= $isArchived ? 'Archiver à nouveau' : 'Archiver'; ?>
                            </button>
                            <?php if ($canReset): ?>
                                <button type="button"
                                    class="btn btn-outline-secondary btn-sm js-archive-reset"
                                    data-annee="<?= $annee; ?>"
                                    data-domaine="<?= htmlspecialchars($active, ENT_QUOTES, 'UTF-8'); ?>">
                                    Réinit.
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    var runUrl = <?= json_encode($runUrl); ?>;
    var resetUrl = <?= json_encode($resetUrl); ?>;
    var csrfName = <?= json_encode($csrfName); ?>;
    var csrfHash = <?= json_encode($csrfHash); ?>;
    var phaseLabels = <?= json_encode($phaseLabels); ?>;
    var msg = document.getElementById('archive-msg');
    var prog = document.getElementById('archive-progress');
    var bar = document.getElementById('archive-bar');
    var stepEl = document.getElementById('archive-step');
    var busy = false;

    function show(ok, text) {
        if (!msg) return;
        msg.style.display = 'block';
        msg.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
        msg.textContent = text || '';
    }
    function setProgress(phase, detail) {
        if (!prog) return;
        prog.style.display = 'block';
        var pct = Math.min(100, Math.round(((phase + 1) / 5) * 100));
        if (bar) {
            bar.style.width = pct + '%';
            bar.textContent = pct + '%';
        }
        if (stepEl) {
            stepEl.textContent = (phaseLabels[phase] || ('Phase ' + phase)) + (detail ? (' — ' + detail) : '');
        }
    }
    function post(url, fields, cb) {
        var body = [];
        fields[csrfName] = csrfHash;
        for (var k in fields) {
            if (!fields.hasOwnProperty(k)) continue;
            body.push(encodeURIComponent(k) + '=' + encodeURIComponent(fields[k]));
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) return;
            var j = null;
            try { j = JSON.parse(xhr.responseText || '{}'); } catch (e) { j = null; }
            if (j && j.csrf_hash) csrfHash = j.csrf_hash;
            cb(xhr.status, j);
        };
        xhr.send(body.join('&'));
    }
    function runPhases(annee, domaine, phase, btn) {
        setProgress(phase, 'en cours…');
        post(runUrl, { annee: annee, domaine: domaine, phase: phase, ajax: 1 }, function (status, j) {
            if (!j || !j.ok) {
                busy = false;
                show(false, (j && j.message) || ('Erreur HTTP ' + status));
                if (btn) { btn.disabled = false; }
                return;
            }
            setProgress(j.phase != null ? j.phase : phase, j.message || '');
            if (j.done) {
                busy = false;
                show(true, j.message || 'Terminé');
                if (bar) {
                    bar.style.width = '100%';
                    bar.textContent = '100%';
                    bar.className = 'progress-bar bg-success';
                }
                setTimeout(function () { window.location.reload(); }, 900);
                return;
            }
            var next = (j.next_phase != null) ? j.next_phase : (phase + 1);
            setTimeout(function () { runPhases(annee, domaine, next, btn); }, 180);
        });
    }

    document.addEventListener('click', function (ev) {
        var reset = ev.target.closest ? ev.target.closest('.js-archive-reset') : null;
        var btn = ev.target.closest ? ev.target.closest('.js-archive-year') : null;
        if (reset) {
            var ya = reset.getAttribute('data-annee');
            var dom = reset.getAttribute('data-domaine') || 'ticket';
            if (!ya || !window.confirm('Réinitialiser ' + dom + ' ' + ya + ' ?')) return;
            post(resetUrl, { annee: ya, domaine: dom, ajax: 1 }, function (st, j) {
                show(!!(j && j.ok), (j && j.message) || 'Erreur');
                if (j && j.ok) setTimeout(function () { window.location.reload(); }, 500);
            });
            return;
        }
        if (!btn || busy) return;
        var annee = btn.getAttribute('data-annee');
        var domaine = btn.getAttribute('data-domaine') || 'ticket';
        if (!annee) return;
        if (!window.confirm('Archiver « ' + domaine + ' » pour l’année ' + annee + ' ?\nVous pourrez ensuite consulter via Voir.')) {
            return;
        }
        busy = true;
        btn.disabled = true;
        show(true, 'Archivage ' + domaine + ' ' + annee + '…');
        if (bar) bar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning';
        runPhases(annee, domaine, 0, btn);
    });
})();
</script>
