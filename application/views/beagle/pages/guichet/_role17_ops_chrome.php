<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Chrome commun pages secondaires Venteescal (bagage, courrier, compte, réimp).
 * Affiche l'escale liée + styles boutons tactiles.
 */
if (empty($role17_mode) || !role17_is_agent()) {
    return;
}
$escale_label = !empty($escale_depart_label) ? (string) $escale_depart_label : '';
$escale_fixed_admin = !empty($escale_depart_fixed_admin);
$ligne_hint = !empty($escale_id_lignes) ? (string) $escale_id_lignes : '';
$this->load->view('beagle/pages/guichet/_role_17_styles');
?>
<style>
/* Pages ops r17 : boutons legacy → taille tactile */
.r17-ops .btn,
.r17-ops a.btn,
.r17-ops button.btn,
.r17-ops input.btn,
.r17-ops .btn-space {
    min-height: 48px !important;
    padding: 0.55rem 0.9rem !important;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    line-height: 1.2 !important;
}
.r17-ops .modal-footer .btn,
.r17-ops .modal-footer input.btn {
    min-height: 52px !important;
    font-size: 1rem !important;
}
.r17-ops .form-control,
.r17-ops select.form-control,
.r17-ops input.form-control {
    min-height: 42px !important;
    font-size: 16px !important;
}
.r17-ops-banner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin: 0 0 0.65rem;
    padding: 0.55rem 0.75rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    color: #1e3a8a;
    font-size: 0.9rem;
}
.r17-ops-banner strong { font-weight: 700; }
.r17-ops-banner .r17-badge-fixed {
    display: inline-block;
    margin-left: 0.35rem;
    padding: 0.05rem 0.35rem;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    background: #dbeafe;
    color: #1d4ed8;
}
.r17-ops-banner .r17-ligne {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.15rem;
}
.r17-ops .r17-depart-chip {
    font-size: 0.85rem;
    line-height: 1.3;
    padding: 0.4rem 0.55rem;
    margin-bottom: 0.5rem;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    color: #1e3a8a;
}
</style>
<div class="r17-ops-banner">
    <div>
        <span class="r17-ctx-k" style="font-size:0.65rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-right:.35rem;">Escale liée</span>
        <strong><?= htmlspecialchars($escale_label !== '' ? $escale_label : '—', ENT_QUOTES, 'UTF-8'); ?></strong>
        <?php if ($escale_fixed_admin): ?>
            <span class="r17-badge-fixed">figée</span>
        <?php endif; ?>
    </div>
    <?php
    $accueil_r17 = (!empty($bus_stop) && !empty($conex) && function_exists('role17_accueil_url'))
        ? role17_accueil_url($bus_stop, $conex)
        : '#';
    ?>
    <a href="<?= htmlspecialchars($accueil_r17, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary btn-sm" style="min-height:36px!important;padding:0.35rem 0.7rem!important;font-size:0.8rem!important;">
        <i class="fas fa-home"></i>&nbsp;Accueil
    </a>
</div>
<?php if ($msg = $this->session->flashdata('error')): ?>
    <div class="alert alert-danger" role="alert" style="margin:0 0 0.75rem;border-radius:8px;">
        <?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>
<?php if ($msg = $this->session->flashdata('success')): ?>
    <div class="alert alert-success" role="alert" style="margin:0 0 0.75rem;border-radius:8px;">
        <?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>
<?php if ($msg = $this->session->flashdata('sale_error')): ?>
    <div class="alert alert-danger" role="alert" style="margin:0 0 0.75rem;border-radius:8px;">
        <?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>
