<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$gare_nom = '';
if (!empty($bus_stop->garenom)) {
    $gare_nom = $bus_stop->garenom;
} elseif (!empty($bus_stop->nom_gaep)) {
    $gare_nom = $bus_stop->nom_gaep;
}
$itineraires = !empty($itineraires) ? $itineraires : array();
$this->load->view('beagle/pages/guichet/_role_17_styles');
?>
<div class="r17-shell">
    <div class="r17-header">
        <a href="<?= site_url('home/main'); ?>" class="btn btn-secondary btn-space">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR AUX GARES
        </a>
        <?php if ($gare_nom !== ''): ?>
            <p class="r17-gare mb-0"><?= htmlspecialchars($gare_nom, ENT_QUOTES, 'UTF-8'); ?> · Itinéraires</p>
        <?php endif; ?>
    </div>

    <div class="r17-solde" style="background:linear-gradient(135deg,#334155,#0f172a);">
        <span class="label">Choisir l'itinéraire</span>
        <span class="amount" style="font-size:1.15rem;font-weight:600;">
            Lignes liées à cette gare
        </span>
    </div>

    <?php if (!empty($manquante_sousgare)): ?>
        <div class="alert alert-danger">
            Aucune sous-gare configurée sur cette agence — impossible d'ouvrir la vente escale.
        </div>
    <?php endif; ?>

    <?php if (empty($itineraires)): ?>
        <div class="alert alert-warning">
            Aucun itinéraire trouvé pour cette gare.
            Vérifiez les lignes et les <strong>Escales tarifées</strong>.
        </div>
    <?php else: ?>
        <div class="r17-grid">
            <?php foreach ($itineraires as $it): ?>
                <?php if (empty($it->entrer_url)): ?>
                    <div class="r17-btn" style="opacity:.55;cursor:not-allowed;">
                        <span class="r17-ico"><i class="fas fa-route"></i></span>
                        <span class="r17-txt">
                            <?= htmlspecialchars($it->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>
                            <span class="r17-sub"><?= htmlspecialchars($it->label ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                    </div>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($it->entrer_url, ENT_QUOTES, 'UTF-8'); ?>" class="r17-btn is-primary">
                        <span class="r17-ico"><i class="fas fa-route"></i></span>
                        <span class="r17-txt">
                            <?= htmlspecialchars($it->nom_ligne, ENT_QUOTES, 'UTF-8'); ?>
                            <span class="r17-sub"><?= htmlspecialchars($it->label ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
