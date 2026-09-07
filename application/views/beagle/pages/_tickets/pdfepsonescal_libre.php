<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Ticket escale libre — même moteur que pdfepsonescal.php (tickets Epson qui marchent).
 * POSPrinter capture l'écran : grands caractères plein page (pas de mini-cadre 57×40).
 */
$this->load->helper('ticket_escale_libre_print');

$item = !empty($item) ? $item : null;
$accueil_url = site_url(
    'gares/' . $this->session->company->ekey
    . '/gTc/' . $bus_stop->idengare
    . '/compte/' . $conex->roleattribut
    . '/' . $bus_stop->idsousgare
    . '/' . mdate('%d/%m/%Y', now('UTC'))
);

if (!$item) {
    echo '<p style="padding:16px;font-family:Arial,sans-serif;">Ticket introuvable</p>';
    echo '<script>setTimeout(function(){location.replace(' . json_encode($accueil_url) . ');},800);</script>';
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
    $emis = (string) $item->dateheureescal;
} else {
    $emis = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
}
$logo = !empty($item->logo) ? site_url($item->logo) : '';
?>
<style>
html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
    color: #000 !important;
}
/* Masquer tout chrome pendant l'impression */
@media print {
    .no-print,
    #ticketActions {
        display: none !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }
    #ticketEpsonLibre,
    #ticketEpsonLibre table {
        width: 100% !important;
    }
}
#ticketActions {
    position: fixed;
    right: 8px;
    top: 8px;
    z-index: 30;
}
#ticketActions a {
    display: inline-block;
    padding: 8px 12px;
    background: #6c757d;
    color: #fff !important;
    text-decoration: none;
    border-radius: 6px;
    font-family: Arial, sans-serif;
    font-size: 13px;
    font-weight: 700;
}
/* Même rendu que pdfepsonescal.php — grands caractères pour POSPrinter */
#ticketEpsonLibre {
    background: #fff;
    color: #000;
    padding: 4px 8px;
}
#ticketEpsonLibre table {
    width: 100%;
    border-collapse: collapse;
}
#ticketEpsonLibre td {
    text-align: left;
    padding: 2px 0;
    color: #000;
    font-family: Arial, Helvetica, sans-serif;
    word-wrap: break-word;
}
#ticketEpsonLibre .logo-cell img {
    display: block;
    width: 850px;
    max-width: 100%;
    height: auto;
    max-height: 350px;
    object-fit: contain;
    object-position: left center;
}
</style>

<script type="text/javascript">
(function () {
    var accueil = <?= json_encode($accueil_url); ?>;
    var gone = false;
    var printed = false;

    function goHome() {
        if (gone) return;
        gone = true;
        window.location.replace(accueil);
    }

    function afterPrintGoHome() {
        if (printed) return;
        printed = true;
        /* Laisse POSPrinter démarrer le job avant de quitter la page */
        setTimeout(goHome, 1500);
    }

    function runPrint() {
        try {
            window.print();
        } catch (e) {
            goHome();
            return;
        }
        /* Secours TPE qui ne déclenchent pas afterprint */
        setTimeout(function () {
            if (!printed) afterPrintGoHome();
        }, 10000);
    }

    function whenImagesReady(cb) {
        var imgs = document.querySelectorAll('#ticketEpsonLibre img');
        if (!imgs.length) {
            setTimeout(cb, 200);
            return;
        }
        var left = imgs.length;
        var done = false;
        function one() {
            left--;
            if (left <= 0 && !done) {
                done = true;
                setTimeout(cb, 300);
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
        }, 3000);
    }

    if ('onafterprint' in window) {
        window.onafterprint = afterPrintGoHome;
    }
    if (window.matchMedia) {
        try {
            var mq = window.matchMedia('print');
            var handler = function (ev) {
                if (!ev.matches) afterPrintGoHome();
            };
            if (mq.addEventListener) mq.addEventListener('change', handler);
            else if (mq.addListener) mq.addListener(handler);
        } catch (e2) {}
    }

    window.onload = function () {
        whenImagesReady(runPrint);
    };
})();
</script>

<div id="ticketActions" class="no-print">
    <a href="<?= htmlspecialchars($accueil_url, ENT_QUOTES, 'UTF-8'); ?>">Accueil</a>
</div>

<div id="ticketEpsonLibre">
    <table>
        <?php if ($logo !== ''): ?>
        <tr>
            <td class="logo-cell" style="font-size:70px;width:40%;">
                <img src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" width="850" height="350" alt="">
            </td>
        </tr>
        <?php elseif ($compagnie !== ''): ?>
        <tr>
            <td style="font-size:55px;"><b><?= htmlspecialchars($compagnie, ENT_QUOTES, 'UTF-8'); ?></b></td>
        </tr>
        <?php endif; ?>

        <tr>
            <td style="font-size:65px;"><b>CODE:<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></b></td>
        </tr>
        <tr>
            <td style="font-size:60px;"><?= htmlspecialchars($od, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <tr>
            <td style="font-size:65px;"><?= htmlspecialchars($passager, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php if ($tel !== ''): ?>
        <tr>
            <td style="font-size:70px;">Contact:<?= htmlspecialchars($tel, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td style="font-size:70px;">Prix : <?= $prix; ?> &nbsp;FCFA</td>
        </tr>
        <?php if ($compagnie !== ''): ?>
        <tr>
            <td style="font-size:50px;"><b>BON VOYAGE AVEC <?= htmlspecialchars($compagnie, ENT_QUOTES, 'UTF-8'); ?></b></td>
        </tr>
        <?php endif; ?>
        <tr>
            <td style="font-size:35px;width:40%;">
                <?= ticket_barcode_img($code, 400, 40); ?>
            </td>
        </tr>
        <tr>
            <td style="font-size:50px;">emis : <?= htmlspecialchars($emis, ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
    </table>
</div>
