<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$esc_ligne = '';
$esc_agent = '';
if (!empty($escale_ops)) {
    $esc_ligne = trim((string) $escale_ops->nom_ligne);
    if ($esc_ligne === '') {
        $esc_ligne = trim((string) $escale_ops->vente_escale_id_lignes);
    }
    $esc_agent = trim((string) $escale_ops->agent_nom);
}
?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= htmlspecialchars($retour_sousgare, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR AUX SOUS-GARES&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="card card-border card-full">
            <div class="card-header card-header-divider"><?= htmlspecialchars($escale_label, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="card-body">
                <?php if ($esc_ligne !== ''): ?>
                    <p>Ligne : <?= htmlspecialchars($esc_ligne, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if ($esc_agent !== ''): ?>
                    <p>Agent : <?= htmlspecialchars($esc_agent, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <p>Tickets ouverts : <?= number_format($ouvert_tickets, 0, '', ' '); ?> F</p>
                <p>Bagages ouverts : <?= number_format($ouvert_bagages, 0, '', ' '); ?> F</p>
                <p>Courriers ouverts : <?= number_format($ouvert_courriers, 0, '', ' '); ?> F</p>

                <?php if (!empty($compte_links)): ?>
                    <p class="mt-3 mb-1"><strong>Compte de l’agent</strong></p>
                    <?php foreach ($compte_links as $act): ?>
                        <a href="<?= htmlspecialchars($act['url'], ENT_QUOTES, 'UTF-8'); ?>"
                           class="btn btn-block btn-rounded text-dark bg-white mb-1">
                            <span class="fas fa-eye"></span>
                            <?= htmlspecialchars($act['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($exercice_links)): ?>
                    <p class="mt-3 mb-1"><strong>Exercices</strong></p>
                    <?php foreach ($exercice_links as $act): ?>
                        <a href="<?= htmlspecialchars($act['url'], ENT_QUOTES, 'UTF-8'); ?>"
                           class="btn btn-block btn-rounded text-dark bg-white mb-1">
                            <span class="fas fa-eye"></span>
                            <?= htmlspecialchars($act['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
