<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$itineraire_nom = !empty($itineraire_nom) ? $itineraire_nom : 'Itinéraire';
$escales_points = !empty($escales_points) ? $escales_points : array();
$retour_url = !empty($retour_itineraires_url) ? $retour_itineraires_url : site_url('home/main');
$this->load->view('beagle/pages/guichet/_role_17_styles');
?>
<div class="r17-shell">
    <div class="r17-header">
        <a href="<?= htmlspecialchars($retour_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary btn-space">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ITINÉRAIRES
        </a>
        <p class="r17-gare mb-0"><?= htmlspecialchars($itineraire_nom, ENT_QUOTES, 'UTF-8'); ?> · Escales</p>
    </div>

    <div class="r17-solde" style="background:linear-gradient(135deg,#0ea5e9,#0369a1);">
        <span class="label">Point de départ de la vente</span>
        <span class="amount" style="font-size:1.05rem;font-weight:600;">
            Origine, escales et extrême (terminus)
        </span>
    </div>

    <?php if (empty($escales_points)): ?>
        <div class="alert alert-warning">
            Aucune escale sur cet itinéraire.
        </div>
    <?php else: ?>
        <div class="r17-grid">
            <?php foreach ($escales_points as $pt): ?>
                <?php
                $kind = !empty($pt->kind) ? (string) $pt->kind : 'escale';
                $is_extreme = ($kind === 'origin' || $kind === 'terminus');
                ?>
                <a href="<?= htmlspecialchars($pt->entrer_url, ENT_QUOTES, 'UTF-8'); ?>"
                   class="r17-btn <?= $is_extreme ? 'is-primary' : ''; ?>">
                    <span class="r17-ico">
                        <i class="fas <?= $kind === 'origin' ? 'fa-flag' : ($kind === 'terminus' ? 'fa-flag-checkered' : 'fa-map-marker-alt'); ?>"></i>
                    </span>
                    <span class="r17-txt">
                        <?= htmlspecialchars($pt->nom_point ?? $pt->label, ENT_QUOTES, 'UTF-8'); ?>
                        <span class="r17-sub"><?= htmlspecialchars($pt->label, ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
