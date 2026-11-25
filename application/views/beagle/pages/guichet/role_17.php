<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
// Aperçu admin : bandeau contexte (le bouton retour est dans btn_retour_gare).
$__apercu_guichet = !empty($vue_escale_seule)
    || trim((string) $this->input->get('escale_profil')) === '17';
if ($__apercu_guichet) {
    $this->load->view('beagle/pages/guichet/_masquer_etats_escale');
}
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

    <?php
    // Liens ops : hub de l’agent (guser), pas le lieu remappé (KOM24) issu d’un
    // sget(idsousgare lieu) — sinon réimp/bagage → page blanche ou compte// 404.
    $__r17_gare = !empty($conex->guser)
        ? (string) $conex->guser
        : (!empty($conex->idengare) ? (string) $conex->idengare : (string) $bus_stop->idengare);
    $__r17_sg = (int) $bus_stop->idsousgare;
    if (!empty($bus_stop->gareprinceid) && (string) $bus_stop->gareprinceid !== $__r17_gare
        && !empty($bus_stop->idengare) && (string) $bus_stop->idengare !== $__r17_gare
    ) {
        // bus_stop = sous-gare du lieu : reprendre une sg du hub si possible.
        $__hub_sg = $this->db->query(
            "SELECT idsousgare FROM sousgare WHERE gareprinceid = ? ORDER BY idsousgare ASC LIMIT 1",
            array($__r17_gare)
        )->row();
        if ($__hub_sg) {
            $__r17_sg = (int) $__hub_sg->idsousgare;
        }
    } elseif (!empty($conex->guser) && isset($this->session->agent->idsousgare)
        && !empty($this->session->agent->guser)
        && (string) $this->session->agent->guser === $__r17_gare
        && (string) $this->session->agent->userole === '17'
    ) {
        $__r17_sg = (int) $this->session->agent->idsousgare;
    }
    // Aperçu admin : sous-gare typique du vendeur (login hub), pas celle du chef.
    if (!empty($__apercu_guichet) && !empty($conex->roleattribut) && $__r17_gare !== '') {
        $__vendeur_sg = $this->db->query(
            "SELECT es.departsgescal AS sg
             FROM escalclients es
             WHERE es.iduseescal = ?
             ORDER BY es.idclescal DESC
             LIMIT 1",
            array((int) $conex->roleattribut)
        )->row();
        if ($__vendeur_sg && (int) $__vendeur_sg->sg > 0) {
            $__r17_sg = (int) $__vendeur_sg->sg;
        }
    }
    $__r17_ra = (int) $conex->roleattribut;
    // Réimpression dans le Guichet (même page) — plus de navigation vers page blanche.
    $__reimpri_q = $this->input->get();
    if (!is_array($__reimpri_q)) {
        $__reimpri_q = array();
    }
    $__reimpri_q['reimpri'] = '1';
    unset($__reimpri_q['reimpri_tab']);
    $__uri = uri_string();
    $__reimpri_href = site_url($__uri) . '?' . http_build_query($__reimpri_q);
    ?>

    <?php if (!empty($show_reimpri_panel)): ?>
        <?php $this->load->view('beagle/pages/guichet/_reimpri_panel'); ?>
    <?php else: ?>
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
            <a href="<?= site_url("confirmation/bagageescales/{$this->session->company->ekey}/{$__r17_ra}/{$__r17_gare}/{$__r17_sg}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-suitcase"></i></span>
                <span class="r17-txt">
                    Bagage
                    <span class="r17-sub">Facturation</span>
                </span>
            </a>
            <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$__r17_ra}/{$__r17_gare}/{$__r17_sg}"); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-envelope"></i></span>
                <span class="r17-txt">
                    Courrier
                    <span class="r17-sub">Envoi · Bordereau</span>
                </span>
            </a>
            <a href="<?= htmlspecialchars($__reimpri_href, ENT_QUOTES, 'UTF-8'); ?>"
               class="r17-btn">
                <span class="r17-ico"><i class="fas fa-print"></i></span>
                <span class="r17-txt">
                    Réimpression
                    <span class="r17-sub">Tickets · bagage · courrier</span>
                </span>
            </a>
        <?php else: ?>
            <div class="alert alert-warning" role="alert" style="grid-column:1/-1;margin:0;border-radius:8px;">
                Vente, bagage, courrier et réimpression sont temporairement masqués.
                Effectuez d’abord votre <strong>arrêt de compte</strong> (bouton Compte).
            </div>
        <?php endif; ?>

        <a href="<?= site_url("caisses/compteescal/{$this->session->company->ekey}/{$__r17_ra}/{$__r17_gare}/{$__r17_sg}"); ?>"
           class="r17-btn">
            <span class="r17-ico"><i class="fas fa-cash-register"></i></span>
            <span class="r17-txt">
                Compte
                <span class="r17-sub">Arrêt · rapport mobile</span>
            </span>
        </a>
    </div>
    <?php endif; ?>
</div>

<?php $this->load->view('beagle/pages/guichet/_modal_vente_escale_libre'); ?>
