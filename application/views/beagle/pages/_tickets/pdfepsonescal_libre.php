<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Ticket escale libre — aperçu + impression POS.
 * Page autonome (pas d’overlay fullscreen) : évite la page blanche sur TPE Chrome 64.
 */
$this->load->helper(array('ticket_escale_libre_print', 'url_safe', 'ticket_prix'));

$item = !empty($item) ? $item : null;
if (!empty($print_retour_url)) {
    $accueil_url = (string) $print_retour_url;
} elseif (!empty($role17_mode) && function_exists('role17_accueil_url')) {
    $accueil_url = role17_accueil_url($bus_stop, $conex);
} else {
    $accueil_url = site_url(
        'gares/' . $this->session->company->ekey
        . '/gTc/' . $bus_stop->idengare
        . '/compte/' . $conex->roleattribut
        . '/' . $bus_stop->idsousgare
        . '/' . mdate('%d/%m/%Y', now('UTC'))
        . '?reimpri=1'
    );
}

if (!$item) {
    if (method_exists($this->session, 'set_flashdata')) {
        $this->session->set_flashdata('error', 'Ticket introuvable pour impression.');
    }
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Impression</title></head><body>';
    echo '<p style="padding:16px;font-family:Arial,sans-serif;">Ticket introuvable — retour…</p>';
    echo '<script>setTimeout(function(){location.replace(' . json_encode($accueil_url) . ');},1200);</script>';
    echo '</body></html>';
    return;
}

$od = trim(preg_replace('/^\[LIBRE\]\s*/', '', (string) $item->quartier_escal));
$od = ticket_escale_libre_pos_text($od, true);
$passager = ticket_escale_libre_pos_text(
    trim((string) $item->nom_client . ' ' . (string) $item->prenom_client),
    true
);
$compagnie = !empty($item->nom_compagnie)
    ? ticket_escale_libre_pos_text((string) $item->nom_compagnie, true)
    : '';
$tel = !empty($item->contact_client)
    ? ticket_escale_libre_pos_text((string) $item->contact_client, false)
    : '';
$code = (string) $item->idclescal;
$prix_val = isset($item->prixescal) ? $item->prixescal : (isset($item->prix) ? $item->prix : 0);
$prix = number_format((float) $prix_val, 0, '', ' ');
if (!empty($item->dateheureescal) && $item->dateheureescal !== '0000-00-00 00:00:00') {
    $emis_raw = (string) $item->dateheureescal;
} else {
    $emis_raw = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
}
$emis = ticket_emis_texte($item, $emis_raw, isset($conex) ? $conex : null);
$logo = !empty($item->logo) ? site_url($item->logo) : '';
$manual = !empty($print_manual);
?>
<style type="text/css">
@page { margin: 0; }
html, body {
    margin: 0;
    padding: 0;
    background: #e2e8f0;
    color: #000;
    font-family: Arial, Helvetica, sans-serif;
}
#tb {
    padding: 14px 12px;
    background: #0f172a;
    color: #fff;
    text-align: center;
}
#tb b { display: block; font-size: 20px; margin: 0 0 6px; }
#tb span { display: block; font-size: 13px; color: #cbd5e1; margin: 0 0 12px; }
#tb button, #tb a {
    display: inline-block;
    min-height: 48px;
    margin: 4px;
    padding: 12px 20px;
    font-size: 18px;
    font-weight: 800;
    border: 0;
    border-radius: 8px;
    text-decoration: none;
    color: #fff;
    vertical-align: middle;
}
#tb button { background: #2563eb; cursor: pointer; }
#tb a { background: #475569; line-height: 48px; }
#ticketEpsonLibre {
    box-sizing: border-box;
    width: 80mm;
    max-width: 100%;
    margin: 14px auto;
    padding: 0 1mm 2mm;
    background: #fff;
    color: #000;
    text-align: center;
    border: 1px solid #94a3b8;
    line-height: 1.25;
}
#ticketEpsonLibre .t-logo {
    display: block;
    width: 78mm;
    max-width: 100%;
    height: auto;
    max-height: 22mm;
    margin: 0 auto 1mm;
}
#ticketEpsonLibre .t-company { font-size: 11pt; font-weight: 700; margin: 0 0 0.6mm; }
#ticketEpsonLibre .t-od { font-size: 14pt; font-weight: 700; margin: 0 0 0.6mm; }
#ticketEpsonLibre .t-passager,
#ticketEpsonLibre .t-tel { font-size: 12pt; font-weight: 700; margin: 0 0 0.4mm; }
#ticketEpsonLibre .t-prix { font-size: 14pt; font-weight: 800; margin: 1mm 0; }
#ticketEpsonLibre .t-code { font-size: 11pt; font-weight: 700; margin: 0 0 0.6mm; }
#ticketEpsonLibre .t-emis { font-size: 8pt; margin: 1mm 0 0; }
#ticketEpsonLibre img.ticket-barcode {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 0 auto;
}
@media print {
    html, body { background: #fff !important; width: 80mm !important; }
    #tb { display: none !important; }
    #ticketEpsonLibre {
        margin: 0 !important;
        border: 0 !important;
        width: 80mm !important;
    }
}
</style>
<script type="text/javascript">
(function () {
    var accueil = <?= json_encode($accueil_url); ?>;
    var manual = <?= $manual ? 'true' : 'false'; ?>;
    var gone = false;
    function goHome() {
        if (gone) return;
        gone = true;
        try { location.replace(accueil); } catch (e) { location.href = accueil; }
    }
    function doPrint() {
        try { window.print(); } catch (e) {}
        setTimeout(goHome, 2800);
    }
    if ('onafterprint' in window) {
        window.onafterprint = function () { setTimeout(goHome, 1800); };
    }
    window.rakietaPrintTicket = doPrint;
    if (!manual) {
        /* Vente : auto-print POSPrinter, sans overlay blanc. */
        if (window.addEventListener) {
            window.addEventListener('load', function () { setTimeout(doPrint, 350); });
        } else {
            window.onload = function () { setTimeout(doPrint, 350); };
        }
    }
})();
</script>

<div id="tb">
    <b>Ticket prêt à imprimer</b>
    <span><?= $manual ? 'Vérifiez puis appuyez sur IMPRIMER' : 'Impression automatique…'; ?></span>
    <?php if ($manual): ?>
    <button type="button" onclick="window.rakietaPrintTicket(); return false;">IMPRIMER</button>
    <a href="<?= htmlspecialchars($accueil_url, ENT_QUOTES, 'UTF-8'); ?>">Retour</a>
    <?php endif; ?>
</div>

<div id="ticketEpsonLibre">
    <?php if ($logo !== ''): ?>
        <img class="t-logo" src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="">
    <?php endif; ?>
    <?php if ($compagnie !== ''): ?>
        <div class="t-company"><?= htmlspecialchars($compagnie, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="t-od"><?= htmlspecialchars($od, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="t-passager"><?= htmlspecialchars($passager, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php if ($tel !== ''): ?>
        <div class="t-tel"><?= htmlspecialchars($tel, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="t-prix"><?= $prix; ?> FCFA</div>
    <div class="t-code"><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></div>
    <?= ticket_barcode_img($code, 280, 40); ?>
    <div class="t-emis"><?= htmlspecialchars($emis, ENT_QUOTES, 'UTF-8'); ?></div>
</div>
