<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-12">
        <p class="mt-0 mb-2 ml-3">
            <a href="<?= htmlspecialchars($retour_sousgare, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR AUX SOUS-GARES&nbsp;
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
    <?php if (!empty($escale_taches)): ?>
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
    <?php if (!empty($escale_agents)): ?>
        <?php foreach ($escale_agents as $esc): ?>
            <?php
            $esc_agent = trim((string) $esc->agent_nom);
            $esc_ligne = trim((string) $esc->nom_ligne);
            if ($esc_ligne === '') {
                $esc_ligne = trim((string) $esc->vente_escale_id_lignes);
            }
            if ($esc_ligne === '' && !empty($escale_ligne)) {
                $esc_ligne = trim((string) $escale_ligne);
            }
            ?>
            <div class="col-lg-3">
                <div class="card card-border card-full">
                    <div class="card-header card-header-divider"><?= htmlspecialchars($esc_agent !== '' ? $esc_agent : ('Agent ' . (int) $esc->roleattribut), ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="card-body">
                        <?php if (!empty($escale_label)): ?>
                            <p>Escale : <?= htmlspecialchars($escale_label, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <?php if ($esc_ligne !== ''): ?>
                            <p>Ligne : <?= htmlspecialchars($esc_ligne, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($esc->voir_url)): ?>
                            <a href="<?= htmlspecialchars($esc->voir_url, ENT_QUOTES, 'UTF-8'); ?>"
                               class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                VOIR
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-lg-6 offset-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2>AUCUN AGENT</h2>
                    <p>Aucun agent vente escale n’est configuré sur cette escale.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
