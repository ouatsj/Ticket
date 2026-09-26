<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-12">
        <p class="mt-0 mb-2 ml-3">
            <a href="<?= htmlspecialchars($retour_sousgare, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR AUX GARES&nbsp;
            </a>
        </p>
    </div>
    <?php if (!empty($escale_label)): ?>
        <div class="col-12">
            <h3 class="ml-3"><?= htmlspecialchars($escale_label, ENT_QUOTES, 'UTF-8'); ?>
                <?php if (!empty($escale_ligne)): ?>
                    <small class="text-muted">— <?= htmlspecialchars($escale_ligne, ENT_QUOTES, 'UTF-8'); ?></small>
                <?php endif; ?>
            </h3>
        </div>
    <?php endif; ?>
    <?php if (!empty($escale_admin)): ?>
        <?php
        $profils_admin = array(
            array('id' => 'chef', 'label' => 'Chef de guichet', 'note' => 'Mêmes boutons que Voir caisse : recettes, dépôts, versement, dépenses, arrêt. L’aide chef a les mêmes boutons. La recette vente escale est la liste ci-dessous.', 'recette' => true, 'badge' => ''),
            array('id' => 'adjoint', 'label' => 'Adjoint caisse', 'note' => 'Mêmes boutons que Voir caisse : recettes, dépôts, versement, dépenses, validation.', 'recette' => true, 'badge' => ''),
            array('id' => 'caissier', 'label' => 'Caissier', 'note' => 'Mêmes boutons que Voir caisse : recettes, dépôts, versement, dépenses, arrêt de caisse, validation.', 'recette' => true, 'badge' => ''),
            array('id' => 'comptable', 'label' => 'Comptable', 'note' => 'Exercices escale, comme sur la page de la sous-gare.', 'recette' => false, 'badge' => 'Comptable'),
            array('id' => 'superviseur', 'label' => 'Superviseur', 'note' => 'Cartes caissier et adjoint, plus les exercices escale.', 'recette' => true, 'badge' => 'Comptable'),
            array('id' => 'agence', 'label' => 'Superviseur d\'agence', 'note' => 'Consultation de la recette escale et exercices.', 'recette' => true, 'badge' => 'Superviseur d\'agence'),
            array('id' => 'site', 'label' => 'Superviseur de site', 'note' => 'Consultation de la recette escale et exercices.', 'recette' => true, 'badge' => 'Superviseur de site'),
        );
        ?>
        <div class="col-12 mb-3">
            <div class="sg-lieu-tabs escale-profils" role="tablist">
                <?php foreach ($profils_admin as $i => $profil): ?>
                    <button type="button" class="sg-lieu-tab<?= $i === 0 ? ' is-active' : ''; ?>"
                            data-esc-profil="<?= htmlspecialchars($profil['id'], ENT_QUOTES, 'UTF-8'); ?>"
                            role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false'; ?>">
                        <?= htmlspecialchars($profil['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php foreach ($profils_admin as $i => $profil): ?>
            <div class="col-12 escale-profil-panel" data-esc-panel="<?= htmlspecialchars($profil['id'], ENT_QUOTES, 'UTF-8'); ?>"<?= $i === 0 ? '' : ' style="display:none"'; ?>>
                <p class="ml-3 text-muted"><?= htmlspecialchars($profil['note'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="row">
                    <?php if (!empty($profil['recette'])): ?>
                        <div class="col-12">
                            <h4 class="ml-3">RECETTE GUICHET ESCALE</h4>
                        </div>
                        <?php $this->load->view('beagle/pages/_recette/_vendeuses_onglets', array(
                            'rg_agents' => isset($escale_agents) ? $escale_agents : array(),
                            'rg_profil' => 'profilsesc',
                            'rg_qui' => 'vendeur escale',
                            'rg_scope' => 'rg-' . $profil['id'],
                        )); ?>
                    <?php endif; ?>
                    <?php
                    $pack = (!empty($escale_admin_packs) && isset($escale_admin_packs[$profil['id']]))
                        ? $escale_admin_packs[$profil['id']]
                        : array();
                    ?>
                    <?php if (!empty($pack)): ?>
                        <?php foreach ($pack as $tache): ?>
                            <div class="col-lg-3">
                                <div class="card card-border card-full">
                                    <div class="card-header card-header-divider">
                                        <span class="badge <?= htmlspecialchars($tache['badge_class'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($tache['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (!empty($tache['intro'])): ?>
                                            <?= htmlspecialchars($tache['intro'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($tache['links'] as $act): ?>
                                            <a href="<?= htmlspecialchars($act['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                               class="btn btn-block btn-rounded text-dark bg-white mb-1">
                                                <span class="fas fa-eye"></span>
                                                <?= htmlspecialchars($act['label'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <style>
        .escale-profils { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .escale-profils .sg-lieu-tab {
            flex: 1 1 160px;
            min-height: 48px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #1f2937;
            font-weight: 700;
            font-size: .95rem;
        }
        .escale-profils .sg-lieu-tab.is-active { background: #0d6efd; border-color: #0d6efd; color: #fff; }
        </style>
        <script>
        (function () {
            var tabs = document.querySelectorAll('[data-esc-profil]');
            var panels = document.querySelectorAll('[data-esc-panel]');
            if (!tabs.length) return;
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var id = tab.getAttribute('data-esc-profil');
                    tabs.forEach(function (t) {
                        var on = t === tab;
                        t.classList.toggle('is-active', on);
                        t.setAttribute('aria-selected', on ? 'true' : 'false');
                    });
                    panels.forEach(function (panel) {
                        panel.style.display = panel.getAttribute('data-esc-panel') === id ? '' : 'none';
                    });
                });
            });
        })();
        </script>
    <?php elseif (!empty($voir_recette_escale)): ?>
        <div class="col-12">
            <h4 class="ml-3">RECETTE GUICHET ESCALE</h4>
        </div>
        <?php $this->load->view('beagle/pages/_recette/_vendeuses_onglets', array(
            'rg_agents' => isset($escale_agents) ? $escale_agents : array(),
            'rg_profil' => 'profilsesc',
            'rg_qui' => 'vendeur escale',
        )); ?>
    <?php endif; ?>
    <?php if (empty($escale_admin) && !empty($escale_taches)): ?>
        <?php foreach ($escale_taches as $tache): ?>
            <div class="col-lg-3">
                <div class="card card-border card-full">
                    <div class="card-header card-header-divider">
                        <span class="badge <?= htmlspecialchars($tache['badge_class'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($tache['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($tache['intro'])): ?>
                            <?= htmlspecialchars($tache['intro'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php foreach ($tache['links'] as $act): ?>
                            <a href="<?= htmlspecialchars($act['url'], ENT_QUOTES, 'UTF-8'); ?>"
                               class="btn btn-block btn-rounded text-dark bg-white mb-1">
                                <span class="fas fa-eye"></span>
                                <?= htmlspecialchars($act['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
