<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php if (!empty($show_onglet_escale) && !empty($escales_lieu)): ?>
<div class="row">
    <div class="col-12 px-4 mb-3">
        <div class="sg-lieu-tabs" role="tablist">
            <button type="button" class="sg-lieu-tab is-active" data-acc-tab="gares" role="tab" aria-selected="true">Gares</button>
            <button type="button" class="sg-lieu-tab" data-acc-tab="escale" role="tab" aria-selected="false">Escale<?php if (!empty($escales_lieu)): ?> (<?= count($escales_lieu); ?>)<?php endif; ?></button>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="row" id="accueil-panel-gares">        
        
    <? foreach($gares as $item): ?>
        <? if (in_array((string) $item->idengare, array('BOR26', 'KOM24', 'DIS10'), true)) { continue; } ?>

        <div class="col-lg-4">

            <div class="card card-border card-full">
                <div class="card-header card-header-divider">AGENCE: <?= $item->garenom; ?>
                    <div class="card-header card-header-divider">
                            
                    </div>

                    <div class="card-body">
                        <p>Code:<?= $item->idengare; ?></p>
                        <p>Ville:<?= $item->nom_ville; ?></p>
                        <p>Contact:<?= $item->contactgares;?></p>
                    <? if (
                        (in_array($agent_userole, array('1', '2', '4'), TRUE))
                        AND isset($soldes[$item->idengare])
                    ) : ?>
                        <? $s = $soldes[$item->idengare]; ?>
                            <p>Recette totale:<strong><?= number_format($s['r'], 0, '', ' '); ?></strong></p>
                            <p>Depot total:<strong><?= number_format($s['dp'], 0, '', ' ');?></strong></p>
                            <p>Versement total:<strong><?= number_format($s['v'], 0, '', ' '); ?></strong></p>
                            <p>Depense totale:<strong><?= number_format($s['d'], 0, '', ' ');?></strong></p>
                            <p class="form-group text-center">Solde total:<strong><?=number_format($s['solde'], 0, '', ' ');?>F</strong></p>
                        <?endif;?>

                        <? if ($agent_userole === '18' AND isset($soldes[$item->idengare])) : ?>
                        <? $s = $soldes[$item->idengare]; ?>
                            <p>Recette totale:<strong><?= number_format($s['r'], 0, '', ' '); ?></strong></p>
                            <p>Depot total:<strong><?= number_format($s['dp'], 0, '', ' ');?></strong></p>
                            <p>Versement total:<strong><?= number_format($s['v'], 0, '', ' '); ?></strong></p>
                            <p>Depense totale:<strong><?= number_format($s['d'], 0, '', ' ');?></strong></p>
                            <p class="form-group text-center">Solde total:<strong><?=number_format($s['solde'], 0, '', ' ');?>F</strong></p>
                        <?endif;?>
                            <a href="<?= site_url('gares/'.$company_ekey.'/gTs/'.$item->idengare.'/sousgare/'.$item->roleattribut.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>"
                               class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                <?= ((string) $agent_userole === '17') ? 'VOIR ITINÉRAIRES' : 'VOIR GARES'; ?>
                            </a>
                    </div>
                </div>
            </div>
        </div>
    <? endforeach;?>
</div>
<?php if (!empty($show_onglet_escale) && !empty($escales_lieu)): ?>
<div class="row" id="accueil-panel-escale" style="display:none">
    <?php if (!empty($escales_lieu)): ?>
        <?php foreach ($escales_lieu as $esc): ?>
            <div class="col-lg-4">
                <div class="card card-border card-full">
                    <div class="card-header card-header-divider"><?= htmlspecialchars($esc->label, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="card-body">
                        <?php if (!empty($esc->gare)): ?>
                            <p>Gare : <?= htmlspecialchars($esc->gare, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($esc->ligne)): ?>
                            <p>Ligne : <?= htmlspecialchars($esc->ligne, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <p>Agents : <?= (int) $esc->nb_agents; ?></p>
                        <a href="<?= htmlspecialchars($esc->voir_url, ENT_QUOTES, 'UTF-8'); ?>"
                           class="btn btn-block btn-rounded text-dark bg-white">
                            <span class="fas fa-eye"></span>
                            VOIR
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-lg-6 offset-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2>AUCUNE ESCALE CONFIGURÉE</h2>
                    <p>Aucun agent vente escale n’a d’escale affectée sur vos gares.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<style>
.sg-lieu-tabs { display: flex; gap: 0.5rem; }
.sg-lieu-tab {
    flex: 1 1 50%;
    min-height: 52px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #1f2937;
    font-weight: 700;
    font-size: 1.05rem;
}
.sg-lieu-tab.is-active { background: #0d6efd; border-color: #0d6efd; color: #fff; }
</style>
<script>
(function () {
    var tabs = document.querySelectorAll('.sg-lieu-tab');
    var gares = document.getElementById('accueil-panel-gares');
    var escale = document.getElementById('accueil-panel-escale');
    if (!tabs.length || !gares || !escale) return;
    function show(name) {
        gares.style.display = name === 'gares' ? '' : 'none';
        escale.style.display = name === 'escale' ? '' : 'none';
        for (var i = 0; i < tabs.length; i++) {
            var on = tabs[i].getAttribute('data-acc-tab') === name;
            tabs[i].classList.toggle('is-active', on);
            tabs[i].setAttribute('aria-selected', on ? 'true' : 'false');
        }
    }
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener('click', function () {
            show(this.getAttribute('data-acc-tab'));
        });
    }
    var onglet = '';
    if (window.location.search) {
        var parts = window.location.search.replace(/^\?/, '').split('&');
        for (var j = 0; j < parts.length; j++) {
            var pair = parts[j].split('=');
            if (decodeURIComponent(pair[0] || '') === 'onglet') {
                onglet = decodeURIComponent(pair[1] || '');
            }
        }
    }
    if (onglet === 'escale' || !gares.querySelector('.card')) show('escale');
})();
</script>
<?php endif; ?>
