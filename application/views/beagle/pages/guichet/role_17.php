<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$compte_arret_only_compte = !empty($compte_arret_only_compte) || !empty($compte_arret_blocked);
$solde = 0;
if (!empty($cptalleresc) && isset($cptalleresc->total)) {
    $solde = (float) $cptalleresc->total;
}
$gare_label = trim(
    (!empty($bus_stop->garenom) ? $bus_stop->garenom : '')
    . (!empty($bus_stop->nomsousgare) ? (' · ' . $bus_stop->nomsousgare) : '')
);
$escale_label = !empty($escale_depart_label) ? (string) $escale_depart_label : '';
$escale_fixe = !empty($escale_depart_fixe) ? (string) $escale_depart_fixe : '';
$escale_fixed_admin = !empty($escale_depart_fixed_admin);
$this->load->view('beagle/pages/guichet/_role_17_styles');
?>
<div class="r17-shell">
    <div class="r17-header">
        <?php
        // Un seul retour : gares (escale figée) ou liste escales.
        $this->load->view('_partials/btn_retour_gare');
        ?>
        <div class="r17-context">
            <?php if ($escale_label !== '' || $escale_fixe !== ''): ?>
                <p class="r17-escale mb-0">
                    <span class="r17-ctx-k">Escale vente</span>
                    <strong><?= htmlspecialchars($escale_label !== '' ? $escale_label : $escale_fixe, ENT_QUOTES, 'UTF-8'); ?></strong>
                    <?php if ($escale_fixed_admin): ?>
                        <span class="r17-badge-fixed" title="Affectée par l'administrateur">figée</span>
                    <?php endif; ?>
                </p>
            <?php elseif ($gare_label !== ''): ?>
                <p class="r17-gare mb-0">
                    <span class="r17-ctx-k">Gare</span>
                    <?= htmlspecialchars($gare_label, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($msg = $this->session->flashdata('error')): ?>
        <div class="alert alert-danger r17-alert" role="alert">
            <?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($escale_fixe === '' && !$compte_arret_only_compte): ?>
        <div class="alert alert-warning r17-alert" role="alert">
            Aucune escale de départ figée. Demandez à l’admin d’affecter une escale.
        </div>
    <?php endif; ?>

    <?php $this->load->view('beagle/pages/guichet/_compte_arret_alerts'); ?>

    <div class="r17-solde">
        <span class="label">Solde du jour</span>
        <span class="amount js-guichet-solde"
              data-solde-url="<?= site_url('gares/' . $this->session->company->ekey . '/ajax_solde/' . (int) $conex->roleattribut); ?>"
              data-solde-field="escale_formatted"
              data-solde-suffix=" FCFA"><?= number_format($solde, 0, '', ' '); ?> FCFA</span>
    </div>

    <div class="r17-grid">
        <?php if (!$compte_arret_only_compte): ?>
            <a href="#"
               class="r17-btn is-primary addventeescalelibre md-trigger"
               data-modal="ticketescal-0">
                <span class="r17-ico"><i class="fas fa-map-marker-alt"></i></span>
                <span class="r17-txt">
                    Vente mobile
                    <span class="r17-sub">Ticket · TPE 57×40</span>
                </span>
            </a>
            <a href="<?= site_url("confirmation/bagageescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-suitcase"></i></span>
                <span class="r17-txt">
                    Bagage
                    <span class="r17-sub">Facturation</span>
                </span>
            </a>
            <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-envelope"></i></span>
                <span class="r17-txt">
                    Courrier
                    <span class="r17-sub">Envoi</span>
                </span>
            </a>
            <a href="<?= site_url("ventescales/voirreimpri/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-print"></i></span>
                <span class="r17-txt">
                    Réimpression
                    <span class="r17-sub">Tickets · bagage · courrier</span>
                </span>
            </a>
        <?php endif; ?>

        <a href="<?= site_url("caisses/compteescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
           class="r17-btn">
            <span class="r17-ico"><i class="fas fa-cash-register"></i></span>
            <span class="r17-txt">
                Compte
                <span class="r17-sub">Arrêt · rapport mobile</span>
            </span>
        </a>
    </div>
</div>

<?php $this->load->view('beagle/pages/guichet/_modal_vente_escale_libre'); ?>
