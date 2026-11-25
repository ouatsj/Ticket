<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Réimpression venteescale — page légère (TPE) : pas de FA / pas de _role_17_styles.
 */
$tickets = !empty($reponseallereimp) && is_array($reponseallereimp) ? $reponseallereimp : array();
$bags = !empty($reimpri_bagages) && is_array($reimpri_bagages) ? $reimpri_bagages : array();
$cours = !empty($reimpri_courriers) && is_array($reimpri_courriers) ? $reimpri_courriers : array();

$tab = isset($_GET['tab']) ? (string) $_GET['tab'] : 'ticket';
if (!in_array($tab, array('ticket', 'bagage', 'courrier'), true)) {
    $tab = 'ticket';
}

$ekey = !empty($this->session->company->ekey) ? $this->session->company->ekey : '';
$ra = (!empty($conex) && !empty($conex->roleattribut)) ? (int) $conex->roleattribut : 0;
$gare = (!empty($bus_stop) && !empty($bus_stop->idengare)) ? (string) $bus_stop->idengare : '';
$sg = (!empty($bus_stop) && isset($bus_stop->idsousgare)) ? (int) $bus_stop->idsousgare : 0;

$accueil = !empty($reimpri_accueil_url) ? (string) $reimpri_accueil_url : '#';
if ($accueil === '#' && $ekey !== '' && $ra > 0 && $gare !== '') {
    if (!function_exists('retour_guichet_url')) {
        $this->load->helper('retour');
    }
    $accueil = function_exists('retour_guichet_url')
        ? retour_guichet_url($ekey, $gare, $ra, $sg)
        : site_url('gares/' . $ekey . '/gTc/' . $gare . '/compte/' . $ra . '/' . $sg);
}

$base_reimpri = site_url(
    'ventescales/voirreimpri/' . $ekey . '/' . $ra . '/' . rawurlencode($gare) . '/' . $sg
);

$escale_label = !empty($escale_depart_label) ? (string) $escale_depart_label : '';
$n_tickets = isset($reimpri_count_tickets) ? (int) $reimpri_count_tickets : count($tickets);
$n_bags = isset($reimpri_count_bagages) ? (int) $reimpri_count_bagages : count($bags);
$n_cours = isset($reimpri_count_courriers) ? (int) $reimpri_count_courriers : count($cours);
?>
<style>
.rp-wrap { color:#0f172a; font-family:sans-serif; }
.rp-top {
    display:-webkit-box; display:-ms-flexbox; display:flex;
    -webkit-box-pack:justify; -ms-flex-pack:justify; justify-content:space-between;
    -webkit-box-align:center; -ms-flex-align:center; align-items:center;
    -ms-flex-wrap:wrap; flex-wrap:wrap;
    gap:0.5rem; margin:0 0 0.75rem; padding:0.55rem 0.7rem;
    background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; color:#1e3a8a;
}
.rp-top strong { font-weight:700; }
.rp-home {
    display:inline-block; padding:0.65rem 1rem; min-height:48px; line-height:1.2;
    background:#475569; color:#fff !important; text-decoration:none; border-radius:8px;
    font-weight:700; font-size:1.05rem;
}
.rp-title { margin:0 0 0.5rem; font-size:1.2rem; font-weight:800; color:#0f172a; }
.rp-sum {
    margin:0 0 0.75rem; padding:0.5rem 0.7rem; border-radius:8px;
    background:#ecfdf5; border:1px solid #6ee7b7; color:#065f46; font-weight:700; font-size:0.9rem;
}
.rp-tabs { display:-webkit-box; display:-ms-flexbox; display:flex; margin:0 0 0.75rem; }
.rp-tabs a {
    -webkit-box-flex:1; -ms-flex:1 1 33%; flex:1 1 33%;
    min-height:52px; margin:0 0.2rem; padding:0.55rem 0.35rem;
    text-align:center; text-decoration:none; font-weight:700; font-size:0.95rem;
    color:#334155; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:8px;
}
.rp-tabs a.is-on { background:#0d6efd; color:#fff; border-color:#0d6efd; }
.rp-hint {
    margin:0 0 0.75rem; padding:0.55rem 0.7rem; border-radius:8px;
    background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; font-size:0.85rem;
}
.rp-empty {
    margin:0.5rem 0; padding:0.85rem; border-radius:8px;
    background:#f8fafc; border:1px dashed #cbd5e1; color:#475569;
}
.rp-row {
    display:-webkit-box; display:-ms-flexbox; display:flex;
    margin:0 0 0.55rem; min-height:64px;
}
.rp-btn {
    -webkit-box-flex:1; -ms-flex:1 1 auto; flex:1 1 auto;
    display:block; padding:0.75rem 0.85rem; text-decoration:none;
    background:#fff; color:#0f172a !important; border:1px solid #cbd5e1;
    border-right:0; border-radius:8px 0 0 8px; font-weight:700;
}
.rp-btn span { display:block; font-size:0.82rem; font-weight:600; color:#64748b; margin-top:0.15rem; }
.rp-del {
    -webkit-box-flex:0; -ms-flex:0 0 52px; flex:0 0 52px;
    display:-webkit-box; display:-ms-flexbox; display:flex;
    -webkit-box-align:center; -ms-flex-align:center; align-items:center;
    -webkit-box-pack:center; -ms-flex-pack:center; justify-content:center;
    background:#b91c1c; color:#fff !important; text-decoration:none;
    border-radius:0 8px 8px 0; font-weight:800; font-size:1.1rem;
}
.rp-alert { padding:0.55rem 0.75rem; border-radius:8px; margin:0 0 0.75rem; }
.rp-alert-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.rp-alert-ok { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
</style>

<div class="rp-wrap">
    <div class="rp-top">
        <div>
            <span style="font-size:0.65rem;text-transform:uppercase;letter-spacing:.04em;color:#64748b;">Escale</span>
            <strong><?= htmlspecialchars($escale_label !== '' ? $escale_label : ($gare !== '' ? $gare : '—'), ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>
        <a class="rp-home" href="<?= htmlspecialchars($accueil, ENT_QUOTES, 'UTF-8'); ?>">Accueil</a>
    </div>

    <?php if ($msg = $this->session->flashdata('error')): ?>
        <div class="rp-alert rp-alert-err"><?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($msg = $this->session->flashdata('success')): ?>
        <div class="rp-alert rp-alert-ok"><?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($msg = $this->session->flashdata('sale_error')): ?>
        <div class="rp-alert rp-alert-err"><?= htmlspecialchars((string) $msg, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <h1 class="rp-title">Réimpression</h1>
    <p class="rp-sum"><?= (int) $n_tickets; ?> ticket(s) · <?= (int) $n_bags; ?> bagage(s) · <?= (int) $n_cours; ?> courrier(s)</p>

    <div class="rp-tabs" role="tablist">
        <a href="<?= htmlspecialchars($base_reimpri . '?tab=ticket', ENT_QUOTES, 'UTF-8'); ?>"
           class="<?= $tab === 'ticket' ? 'is-on' : ''; ?>">Tickets (<?= (int) $n_tickets; ?>)</a>
        <a href="<?= htmlspecialchars($base_reimpri . '?tab=bagage', ENT_QUOTES, 'UTF-8'); ?>"
           class="<?= $tab === 'bagage' ? 'is-on' : ''; ?>">Bagage (<?= (int) $n_bags; ?>)</a>
        <a href="<?= htmlspecialchars($base_reimpri . '?tab=courrier', ENT_QUOTES, 'UTF-8'); ?>"
           class="<?= $tab === 'courrier' ? 'is-on' : ''; ?>">Courrier (<?= (int) $n_cours; ?>)</a>
    </div>

    <?php if ($tab === 'ticket'): ?>
        <p class="rp-hint">Tickets repositionnés par le chef guichet. Après impression, ils quittent la liste.</p>
        <?php if (!$tickets): ?>
            <p class="rp-empty">Aucun ticket autorisé à réimprimer pour le moment.</p>
        <?php else: ?>
            <?php foreach ($tickets as $item):
                $tf = !empty($item->typtarifesc) ? $item->typtarifesc : '0';
                $lh = !empty($item->id_lgeheur) ? $item->id_lgeheur : (!empty($item->id_ligneheure) ? $item->id_ligneheure : '0');
                $od = trim(preg_replace('/^\[LIBRE\]\s*/i', '', (string) (isset($item->quartier_escal) ? $item->quartier_escal : '')));
                if ($od === '' && !empty($item->nom_ligne)) {
                    $od = (string) $item->nom_ligne;
                }
                $print = site_url(
                    'ventescales/pdfepsonescalrp/' . $ekey . '/'
                    . rawurlencode($item->idclescal) . '/' . rawurlencode($tf) . '/' . rawurlencode($lh) . '/'
                    . rawurlencode($gare) . '/' . $ra . '/' . $sg
                );
                $del = site_url(
                    'ventescales/supprimescal/' . $ekey . '/'
                    . rawurlencode($item->idclescal) . '/' . $ra . '/'
                    . rawurlencode($gare) . '/' . $sg
                );
                $who = trim(
                    (isset($item->nom_client) ? $item->nom_client : '') . ' '
                    . (isset($item->prenom_client) ? $item->prenom_client : '')
                );
            ?>
                <div class="rp-row">
                    <a class="rp-btn" href="<?= htmlspecialchars($print, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars($item->idclescal, ENT_QUOTES, 'UTF-8'); ?>
                        <span><?= htmlspecialchars(trim($who . ($od !== '' ? (' · ' . $od) : '')), ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                    <a class="rp-del" href="<?= htmlspecialchars($del, ENT_QUOTES, 'UTF-8'); ?>"
                       title="Supprimer"
                       onclick="return confirm('Supprimer définitivement ce ticket escale ?');">X</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php elseif ($tab === 'bagage'): ?>
        <p class="rp-hint">Reçus bagage du jour · 57×40.</p>
        <?php if (!$bags): ?>
            <p class="rp-empty">Aucun bagage facturé aujourd’hui — rien à réimprimer.</p>
        <?php else: ?>
            <?php foreach ($bags as $b):
                $print = site_url(
                    'historique_passagers/pdfepsonbagesc/' . $ekey . '/'
                    . $b->id_bagageesc . '/' . rawurlencode($gare) . '/' . $ra . '/' . $sg
                );
                $del = site_url(
                    'ventescales/supprimebagesc/' . $ekey . '/'
                    . $b->id_bagageesc . '/' . $ra . '/' . rawurlencode($gare) . '/' . $sg
                );
            ?>
                <div class="rp-row">
                    <a class="rp-btn" href="<?= htmlspecialchars($print, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars(isset($b->codebagesc) ? $b->codebagesc : ('#' . $b->id_bagageesc), ENT_QUOTES, 'UTF-8'); ?>
                        <span><?= htmlspecialchars(
                            trim((isset($b->nom_client) ? $b->nom_client : '') . ' ' . (isset($b->prenom_client) ? $b->prenom_client : ''))
                            . ' · ' . number_format((float) $b->prix_bagageesc, 0, '', ' ') . ' F',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?></span>
                    </a>
                    <a class="rp-del" href="<?= htmlspecialchars($del, ENT_QUOTES, 'UTF-8'); ?>"
                       onclick="return confirm('Supprimer ce bagage ?');">X</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php else: ?>
        <p class="rp-hint">Reçus courrier du jour · 57×40.</p>
        <?php if (!$cours): ?>
            <p class="rp-empty">Aucun courrier envoyé aujourd’hui — rien à réimprimer.</p>
        <?php else: ?>
            <?php foreach ($cours as $c):
                $ex = !empty($c->expditid) ? $c->expditid : '0';
                $re = !empty($c->receptid) ? $c->receptid : '0';
                $tc = !empty($c->type_client) ? $c->type_client : 'Adulte';
                $dp = !empty($c->departcolisesc) ? $c->departcolisesc : '0';
                $print = site_url(
                    'historiquesescal/reditpdfesc/' . $ekey . '/'
                    . $c->courrierexpidesc . '/' . rawurlencode($dp) . '/' . rawurlencode($ex) . '/'
                    . rawurlencode($re) . '/' . rawurlencode($tc) . '/'
                    . rawurlencode($gare) . '/' . $ra . '/' . $sg
                );
                $del = site_url(
                    'ventescales/supprimecouresc/' . $ekey . '/'
                    . $c->courrierexpidesc . '/' . $ra . '/'
                    . rawurlencode($gare) . '/' . $sg
                );
            ?>
                <div class="rp-row">
                    <a class="rp-btn" href="<?= htmlspecialchars($print, ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars($c->num_couresc, ENT_QUOTES, 'UTF-8'); ?>
                        <span><?= htmlspecialchars(
                            trim((isset($c->nom_client) ? $c->nom_client : '') . ' ' . (isset($c->prenom_client) ? $c->prenom_client : ''))
                            . ' · ' . number_format((float) $c->prixcolisesc, 0, '', ' ') . ' F',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?></span>
                    </a>
                    <a class="rp-del" href="<?= htmlspecialchars($del, ENT_QUOTES, 'UTF-8'); ?>"
                       onclick="return confirm('Annuler ce courrier escale ?');">X</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
