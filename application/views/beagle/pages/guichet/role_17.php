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
$escale_label = !empty($escale_depart_label) ? $escale_depart_label : '';
$this->load->view('beagle/pages/guichet/_role_17_styles');
?>
<div class="r17-shell">
    <div class="r17-header">
        <?php $this->load->view('_partials/btn_retour_gare'); ?>
        <?php if ($gare_label !== '' || $escale_label !== ''): ?>
            <p class="r17-gare mb-0">
                <?= htmlspecialchars($gare_label, ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($escale_label !== ''): ?>
                    <br><strong><?= htmlspecialchars($escale_label, ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

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
                    Vente mobile escale
                    <span class="r17-sub">Ticket rapide · impression TPE</span>
                </span>
            </a>
            <a href="<?= site_url("ventescales/voirreimpri/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-print"></i></span>
                <span class="r17-txt">
                    Voir réimpression
                    <span class="r17-sub">Réimprimer un ticket</span>
                </span>
            </a>
            <a href="<?= site_url("confirmation/bagageescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-suitcase"></i></span>
                <span class="r17-txt">
                    Bagage escal
                    <span class="r17-sub">Enregistrement bagages</span>
                </span>
            </a>
            <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-envelope"></i></span>
                <span class="r17-txt">
                    Courrier escal
                    <span class="r17-sub">Envoi / réception</span>
                </span>
            </a>
            <a href="<?= site_url("confirmation/validerarr/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-check-circle"></i></span>
                <span class="r17-txt">
                    Valider arriver
                    <span class="r17-sub">Contrôle arrivées</span>
                </span>
            </a>
        <?php endif; ?>

        <a href="<?= site_url("caisses/compteescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
           class="r17-btn">
            <span class="r17-ico"><i class="fas fa-cash-register"></i></span>
            <span class="r17-txt">
                Compte escal
                <span class="r17-sub">Arrêt / clôture de caisse</span>
            </span>
        </a>
    </div>
</div>

<?php $this->load->view('beagle/pages/guichet/_modal_vente_escale_libre'); ?>
