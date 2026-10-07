<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Ticket escale libre — rouleau POS 80 mm, hauteur libre, contenu collé en haut.
 */
$this->load->helper(array('ticket_escale_libre_print', 'url_safe', 'ticket_prix'));

$item = !empty($item) ? $item : null;
$accueil_url = (!empty($role17_mode) && function_exists('role17_accueil_url'))
    ? role17_accueil_url($bus_stop, $conex)
    : site_url(
        'gares/' . $this->session->company->ekey
        . '/gTc/' . $bus_stop->idengare
        . '/compte/' . $conex->roleattribut
        . '/' . $bus_stop->idsousgare
        . '/' . mdate('%d/%m/%Y', now('UTC'))
    );

if (!$item) {
    if (method_exists($this->session, 'set_flashdata')) {
        $this->session->set_flashdata('error', 'Ticket introuvable pour impression.');
    }
    echo '<p style="padding:16px;font-family:Arial,sans-serif;">Ticket introuvable — retour à l’accueil…</p>';
    echo '<script>setTimeout(function(){location.replace(' . json_encode($accueil_url) . ');},1200);</script>';
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
?>
<?php $this->load->view('beagle/pages/_tickets/_pos_escale_plein'); ?>
<script type="text/javascript">
(function () {
    var accueil = <?= json_encode($accueil_url); ?>;
    var gone = false;
    var printDone = false;

    function goHome() {
        if (gone) return;
        gone = true;
        window.location.replace(accueil);
    }

    function afterPrintGoHome() {
        if (printDone) return;
        printDone = true;
        /* Laisse POSPrinter démarrer le job avant de quitter */
        setTimeout(goHome, 2200);
    }

    function runPrint() {
        posEscaleFitPage(document.getElementById('ticketEpsonLibre'));
        try {
            window.print();
        } catch (e) {
            goHome();
            return;
        }
        /* Secours si afterprint n'existe pas / ne se déclenche pas */
        setTimeout(function () {
            if (!printDone) afterPrintGoHome();
        }, 12000);
    }

    function whenImagesReady(cb) {
        var imgs = document.querySelectorAll('#ticketEpsonLibre img');
        if (!imgs.length) {
            setTimeout(cb, 150);
            return;
        }
        var left = imgs.length;
        var done = false;
        function one() {
            left--;
            if (left <= 0 && !done) {
                done = true;
                setTimeout(cb, 200);
            }
        }
        for (var i = 0; i < imgs.length; i++) {
            if (imgs[i].complete) one();
            else {
                imgs[i].addEventListener('load', one);
                imgs[i].addEventListener('error', one);
            }
        }
        setTimeout(function () {
            if (!done) {
                done = true;
                cb();
            }
        }, 2500);
    }

    if ('onafterprint' in window) {
        window.onafterprint = afterPrintGoHome;
    }
    /* matchMedia('print') retiré : sur TPE il bascule trop tôt → page blanche sans papier */

    window.onload = function () {
        whenImagesReady(runPrint);
    };
})();
</script>

<div id="printStatus">
    <p class="msg">Impression en cours…</p>
    <p class="sub">POSPrinter · retour automatique</p>
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
