<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Ticket escale libre — POSPrinter 57×40 mm.
 * Écran : message seul (pas d'aperçu). Impression : ticket plein format.
 */
$this->load->helper('ticket_escale_libre_print');

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
    $emis_raw = (string) $item->dateheureescal;
} else {
    $emis_raw = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
}
$emis = ticket_emis_texte($item, $emis_raw, isset($conex) ? $conex : null);
$logo = !empty($item->logo) ? site_url($item->logo) : '';
?>
<style>
@page {
    size: 57mm 40mm;
    margin: 0;
}
html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
    color: #000 !important;
    -webkit-text-size-adjust: 100%;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* ——— ÉCRAN : pas d'aperçu ticket (POSPrinter) ——— */
@media screen {
    html, body {
        width: 100%;
        height: 100%;
        min-height: 100vh;
        overflow: hidden;
    }
    #printStatus {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #fff;
        font-family: Arial, Helvetica, sans-serif;
        color: #222;
        text-align: center;
        padding: 24px;
    }
    #printStatus .msg {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 8px;
    }
    #printStatus .sub {
        font-size: 14px;
        color: #666;
        margin: 0;
    }
    /* Ticket sous le masque blanc — prêt pour le job d'impression */
    #ticketEpsonLibre {
        position: absolute;
        left: 0;
        top: 0;
        width: 57mm;
        height: 40mm;
        z-index: 1;
    }
}

/* ——— IMPRESSION : ticket plein 57×40 ——— */
@media print {
    #printStatus {
        display: none !important;
    }
    html, body {
        width: 57mm !important;
        height: 40mm !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    #ticketEpsonLibre {
        position: static !important;
        width: 57mm !important;
        height: 40mm !important;
        margin: 0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

#ticketEpsonLibre {
    box-sizing: border-box;
    width: 57mm;
    height: 40mm;
    margin: 0;
    padding: 0.6mm 1.8mm 0.5mm;
    background: #fff;
    color: #000;
    text-align: center;
    font-family: Arial, Helvetica, DejaVu Sans, sans-serif;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    line-height: 1.08;
}
#ticketEpsonLibre .t-logo {
    display: block;
    max-width: 28mm;
    max-height: 6.5mm;
    width: auto;
    height: auto;
    margin: 0 auto;
    object-fit: contain;
}
#ticketEpsonLibre .t-company {
    font-size: 8.5pt;
    font-weight: 700;
    max-width: 53mm;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#ticketEpsonLibre .t-od {
    font-size: 10.5pt;
    font-weight: 700;
    max-width: 53mm;
    overflow: hidden;
}
#ticketEpsonLibre .t-passager {
    font-size: 9pt;
    font-weight: 700;
    max-width: 53mm;
    overflow: hidden;
}
#ticketEpsonLibre .t-tel {
    font-size: 8pt;
    max-width: 53mm;
    white-space: nowrap;
    overflow: hidden;
}
#ticketEpsonLibre .t-prix {
    font-size: 12pt;
    font-weight: 700;
}
#ticketEpsonLibre .t-code {
    font-size: 8pt;
    font-weight: 700;
    letter-spacing: 0.03em;
}
#ticketEpsonLibre img.ticket-barcode {
    display: block !important;
    width: 50mm !important;
    max-width: 50mm !important;
    height: 6.5mm !important;
    margin: 0 auto !important;
    object-fit: fill !important;
}
#ticketEpsonLibre .t-emis {
    font-size: 6.5pt;
    max-width: 53mm;
    white-space: nowrap;
    overflow: hidden;
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
        /* Laisse POSPrinter démarrer le job avant de quitter */
        setTimeout(goHome, 1800);
    }

    function runPrint() {
        try {
            window.print();
        } catch (e) {
            goHome();
            return;
        }
        setTimeout(function () {
            if (!printed) afterPrintGoHome();
        }, 10000);
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

<div id="printStatus">
    <p class="msg">Impression en cours…</p>
    <p class="sub">POSPrinter · retour automatique</p>
</div>

<div id="ticketEpsonLibre">
    <?php if ($logo !== ''): ?>
        <img class="t-logo" src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="">
    <?php elseif ($compagnie !== ''): ?>
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
