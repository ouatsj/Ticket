<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$liste = !empty($vendeuses) && is_array($vendeuses) ? $vendeuses : array();
$groupes = array(
    'connecte' => array(),
    'attente' => array(),
    'deconnecte' => array(),
);
foreach ($liste as $item) {
    $g = !empty($item->guichet_groupe) ? (string) $item->guichet_groupe : 'deconnecte';
    if (!isset($groupes[$g])) {
        $g = 'deconnecte';
    }
    $groupes[$g][] = $item;
}
$onglets = array(
    'connecte' => 'Connectés',
    'attente' => 'Arrêt en attente',
    'deconnecte' => 'Déconnectés',
);
$id_caiss = (!empty($caisseident) && !empty($caisseident->id_caiss)) ? $caisseident->id_caiss : 0;
$idsg = (!empty($bus_stop) && !empty($bus_stop->idsousgare)) ? $bus_stop->idsousgare : 0;
$date_op = mdate('%d/%m/%Y', now('UTC'));
?>
<div class="col-12 mb-3">
    <input type="search" id="rg-filtre" class="form-control"
           placeholder="Rechercher un guichetier (nom, téléphone, profil)" autocomplete="off">
</div>
<div class="col-12 mb-3">
    <div class="rg-tabs" role="tablist">
        <?php $first = true; foreach ($onglets as $cle => $lib): ?>
            <button type="button" class="rg-tab<?= $first ? ' is-active' : ''; ?>"
                    data-rg-tab="<?= $cle; ?>" role="tab" aria-selected="<?= $first ? 'true' : 'false'; ?>">
                <?= htmlspecialchars($lib, ENT_QUOTES, 'UTF-8'); ?>
                <span class="rg-count" data-rg-count="<?= $cle; ?>"><?= count($groupes[$cle]); ?></span>
            </button>
        <?php $first = false; endforeach; ?>
    </div>
</div>
<?php foreach ($onglets as $cle => $lib): ?>
    <div class="rg-panel row col-12" data-rg-panel="<?= $cle; ?>"<?= $cle === 'connecte' ? '' : ' style="display:none"'; ?>>
        <?php if (empty($groupes[$cle])): ?>
            <div class="col-12 rg-vide" data-rg-vide="1">
                <p class="text-center text-muted mb-0">Aucun guichetier dans cet onglet.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($groupes[$cle] as $item):
            $hay = trim(
                (isset($item->first_name) ? $item->first_name : '') . ' '
                . (isset($item->last_name) ? $item->last_name : '') . ' '
                . (isset($item->phone) ? $item->phone : '') . ' '
                . (isset($item->type_rols) ? $item->type_rols : '')
            );
            $hay = function_exists('mb_strtolower') ? mb_strtolower($hay, 'UTF-8') : strtolower($hay);
        ?>
            <div class="col-lg-3 rg-card" data-rg-search="<?= htmlspecialchars($hay, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="card card-border card-contrast">
                    <div class="card-header card-header-contrast"><?= htmlspecialchars((string) $item->first_name, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="card-body">
                        <p class="text-danger"><?= htmlspecialchars((string) $item->type_rols, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>Nom:<?= htmlspecialchars((string) $item->first_name, ENT_QUOTES, 'UTF-8'); ?>&nbsp;<?= htmlspecialchars((string) $item->last_name, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>Contact: <?= htmlspecialchars((string) $item->phone, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p>
                            <?php if ($cle === 'attente'): ?>
                                <span class="icon mdi text-warning">Arrêt en attente de validation</span>
                            <?php elseif ($cle === 'connecte'): ?>
                                <span class="icon mdi text-success">En ligne</span>
                            <?php else: ?>
                                <span class="icon mdi text-danger">Déconnecté</span>
                            <?php endif; ?>
                        </p>
                        <a href="<?= site_url('utilisateurs/'
                            . $this->session->company->ekey . '/profils/'
                            . $item->guser . '/' . $idsg . '/' . $item->roleattribut . '/' . $id_caiss . '/' . $conex->roleattribut . '/' . $date_op); ?>"
                           class="btn btn-block btn-rounded text-dark bg-info">
                            <span class="icon mdi mdi-eye"></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <div class="col-12 rg-vide-filtre" style="display:none">
            <p class="text-center text-muted mb-0">Aucun guichetier ne correspond à la recherche.</p>
        </div>
    </div>
<?php endforeach; ?>
<style>
.rg-tabs { display:flex; flex-wrap:wrap; gap:8px; }
.rg-tab {
    min-height:48px; padding:8px 14px; border:1px solid #ced4da; border-radius:8px;
    background:#fff; font-weight:600;
}
.rg-tab.is-active { background:#0d6efd; color:#fff; border-color:#0d6efd; }
.rg-count {
    display:inline-block; min-width:1.6em; margin-left:6px; padding:0 6px;
    border-radius:999px; background:rgba(0,0,0,.08); font-size:.9em;
}
.rg-tab.is-active .rg-count { background:rgba(255,255,255,.25); }
</style>
<script>
(function () {
    var input = document.getElementById('rg-filtre');
    var tabs = document.querySelectorAll('.rg-tab');
    var panels = document.querySelectorAll('.rg-panel');
    function norm(s) {
        return (s || '').toLowerCase();
    }
    function apply() {
        var q = norm(input ? input.value : '');
        panels.forEach(function (panel) {
            var cards = panel.querySelectorAll('.rg-card');
            var shown = 0;
            cards.forEach(function (card) {
                var ok = q === '' || norm(card.getAttribute('data-rg-search')).indexOf(q) !== -1;
                card.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            var vide = panel.querySelector('[data-rg-vide]');
            if (vide) vide.style.display = (q === '' && cards.length === 0) ? '' : 'none';
            var filtre = panel.querySelector('.rg-vide-filtre');
            if (filtre) filtre.style.display = (q !== '' && shown === 0) ? '' : 'none';
            var cle = panel.getAttribute('data-rg-panel');
            var badge = document.querySelector('[data-rg-count="' + cle + '"]');
            if (badge) badge.textContent = String(q === '' ? cards.length : shown);
        });
    }
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var cle = tab.getAttribute('data-rg-tab');
            tabs.forEach(function (t) {
                var on = t === tab;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (panel) {
                panel.style.display = panel.getAttribute('data-rg-panel') === cle ? '' : 'none';
            });
        });
    });
    if (input) input.addEventListener('input', apply);
})();
</script>
