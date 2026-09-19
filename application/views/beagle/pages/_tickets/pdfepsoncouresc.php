<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Reçu courrier escale — POSPrinter 57×40 mm, 3 exemplaires.
 */
$this->load->helper(array('ticket_escale_libre_print', 'url_safe', 'ticket_prix'));

$single = !empty($single) ? $single : null;
$exped = !empty($exped) ? $exped : null;
$destin = !empty($destin) ? $destin : null;

$accueil_url = (!empty($role17_mode) && function_exists('role17_accueil_url'))
    ? role17_accueil_url($bus_stop, $conex)
    : site_url(
        'confirmation/courrierescales/' . $this->session->company->ekey
        . '/' . $conex->roleattribut
        . '/' . $bus_stop->idengare
        . '/' . $bus_stop->idsousgare
    );

if (!$single) {
    if (method_exists($this->session, 'set_flashdata')) {
        $this->session->set_flashdata(
            'error',
            'Reçu courrier introuvable — réessayez l’envoi ou la réimpression depuis la liste du jour.'
        );
    }
    echo '<p style="padding:16px;font-family:Arial,sans-serif;">Reçu introuvable — retour…</p>';
    echo '<script>setTimeout(function(){location.replace(' . json_encode($accueil_url) . ');},1200);</script>';
    return;
}

// Enrichir OD si JOINs partiels (escale) : labels session / bus_stop.
$dep = trim((string) (isset($single->nomsousgare) ? $single->nomsousgare : (isset($single->nom_gaep) ? $single->nom_gaep : '')));
if ($dep === '' && !empty($single->nom_gaep)) {
    $dep = (string) $single->nom_gaep;
}
if ($dep === '' && !empty($bus_stop)) {
    if (!empty($bus_stop->nomsousgare)) {
        $dep = (string) $bus_stop->nomsousgare;
    } elseif (!empty($bus_stop->garenom)) {
        $dep = (string) $bus_stop->garenom;
    } elseif (!empty($bus_stop->nom_gaep)) {
        $dep = (string) $bus_stop->nom_gaep;
    }
}
if ($dep === '' && !empty($escale_depart_label)) {
    $dep = preg_replace('/\s*\/\s*.*$/', '', (string) $escale_depart_label);
}
$arr = trim((string) (isset($single->nom_gadest) ? $single->nom_gadest : ''));
$od = ticket_escale_libre_pos_text($dep . ' - ' . $arr, true);

$exp_name = '';
$exp_tel = '';
if ($exped) {
    if (!empty($exped->nom_client) || !empty($exped->prenom_client)) {
        $exp_name = trim((string) $exped->nom_client . ' ' . (string) $exped->prenom_client);
        $exp_tel = !empty($exped->contact_client) ? (string) $exped->contact_client : '';
    } elseif (!empty($exped->nomprenom_perso)) {
        $exp_name = (string) $exped->nomprenom_perso;
        $exp_tel = !empty($exped->contact_perso) ? (string) $exped->contact_perso : '';
    }
}
$dest_name = '';
$dest_tel = '';
if ($destin) {
    if (!empty($destin->nom_client) || !empty($destin->prenom_client)) {
        $dest_name = trim((string) $destin->nom_client . ' ' . (string) $destin->prenom_client);
        $dest_tel = !empty($destin->contact_client) ? (string) $destin->contact_client : '';
    } elseif (!empty($destin->nomprenom_perso)) {
        $dest_name = (string) $destin->nomprenom_perso;
        $dest_tel = !empty($destin->contact_perso) ? (string) $destin->contact_perso : '';
    }
}
$exp_name = ticket_escale_libre_pos_text($exp_name, true);
$dest_name = ticket_escale_libre_pos_text($dest_name, true);
$exp_tel = ticket_escale_libre_pos_text($exp_tel, false);
$dest_tel = ticket_escale_libre_pos_text($dest_tel, false);

$compagnie = !empty($single->nom_compagnie)
    ? ticket_escale_libre_pos_text((string) $single->nom_compagnie, true)
    : '';
$code = (string) $single->num_couresc;
$prix = number_format((float) $single->prixcolisesc, 0, '', ' ');
$contenu = trim(
    (isset($single->nombrecolis) ? (string) $single->nombrecolis . ' ' : '')
    . (isset($single->naturecourrieresc) ? (string) $single->naturecourrieresc : (isset($single->naturecoli) ? (string) $single->naturecoli : ''))
);
$contenu = ticket_escale_libre_pos_text($contenu, true);
if (function_exists('mb_substr')) {
    $contenu = mb_substr($contenu, 0, 28, 'UTF-8');
} else {
    $contenu = substr($contenu, 0, 28);
}

$emis_raw = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
if (!empty($single->dateenvoiesc)) {
    $emis_raw = (string) $single->dateenvoiesc
        . (!empty($single->heure) ? (' ' . $single->heure) : '');
}
$emis = ticket_emis_texte($single, $emis_raw, isset($conex) ? $conex : null);
$logo = !empty($single->logo) ? site_url($single->logo) : '';

$copies = array(
    array('n' => '1/3', 'label' => 'CLIENT'),
    array('n' => '2/3', 'label' => 'COURRIER'),
    array('n' => '3/3', 'label' => 'ARCHIVE'),
);
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
    #printStatus .msg { font-size: 20px; font-weight: 700; margin: 0 0 8px; }
    #printStatus .sub { font-size: 14px; color: #666; margin: 0; }
    #recuEpsonStack {
        position: absolute;
        left: 0;
        top: 0;
        z-index: 1;
    }
}

@media print {
    #printStatus { display: none !important; }
    /* Une seule copie active = même job que le ticket vente (1× 57×40). */
    html, body {
        width: 57mm !important;
        height: 40mm !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    #recuEpsonStack {
        position: static !important;
        width: 57mm !important;
        height: 40mm !important;
    }
    .recu-copy.is-print-skip { display: none !important; }
    .recu-copy:not(.is-print-skip) {
        position: static !important;
        width: 57mm !important;
        height: 40mm !important;
        page-break-after: auto;
        break-after: auto;
    }
}

.recu-copy {
    box-sizing: border-box;
    width: 57mm;
    height: 40mm;
    margin: 0;
    padding: 0.5mm 1.6mm 0.4mm;
    background: #fff;
    color: #000;
    text-align: center;
    font-family: Arial, Helvetica, DejaVu Sans, sans-serif;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    line-height: 1.05;
}
.recu-copy .t-logo {
    display: block;
    max-width: 24mm;
    max-height: 5.5mm;
    width: auto;
    height: auto;
    margin: 0 auto;
    object-fit: contain;
}
.recu-copy .t-company {
    font-size: 7.5pt;
    font-weight: 700;
    max-width: 53mm;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.recu-copy .t-title {
    font-size: 7pt;
    font-weight: 700;
    letter-spacing: 0.04em;
}
.recu-copy .t-od {
    font-size: 9pt;
    font-weight: 700;
    max-width: 53mm;
    overflow: hidden;
}
.recu-copy .t-line {
    font-size: 7.5pt;
    font-weight: 700;
    max-width: 53mm;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.recu-copy .t-prix {
    font-size: 11pt;
    font-weight: 700;
}
.recu-copy .t-code {
    font-size: 7.5pt;
    font-weight: 700;
    letter-spacing: 0.03em;
}
.recu-copy img.ticket-barcode {
    display: block !important;
    width: 48mm !important;
    max-width: 48mm !important;
    height: 5.5mm !important;
    margin: 0 auto !important;
    object-fit: fill !important;
}
.recu-copy .t-emis {
    font-size: 5.5pt;
    max-width: 53mm;
    white-space: nowrap;
    overflow: hidden;
}
</style>

<script type="text/javascript">
(function () {
    var accueil = <?= json_encode($accueil_url); ?>;
    var copies = [];
    var idx = 0;
    var gone = false;
    var waitingAfterPrint = false;
    var fallbackTimer = null;

    function goHome() {
        if (gone) return;
        gone = true;
        window.location.replace(accueil);
    }

    function showOnly(i) {
        for (var n = 0; n < copies.length; n++) {
            if (n === i) copies[n].classList.remove('is-print-skip');
            else copies[n].classList.add('is-print-skip');
        }
    }

    function whenImagesReady(cb) {
        var imgs = document.querySelectorAll('#recuEpsonStack img');
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

    function afterOneCopy() {
        if (!waitingAfterPrint) return;
        waitingAfterPrint = false;
        if (fallbackTimer) {
            clearTimeout(fallbackTimer);
            fallbackTimer = null;
        }
        idx++;
        if (idx >= copies.length) {
            setTimeout(goHome, 1800);
            return;
        }
        setTimeout(printNext, 500);
    }

    function printNext() {
        if (gone) return;
        showOnly(idx);
        waitingAfterPrint = true;
        try {
            window.print();
        } catch (e) {
            goHome();
            return;
        }
        fallbackTimer = setTimeout(function () {
            if (waitingAfterPrint) afterOneCopy();
        }, 10000);
    }

    if ('onafterprint' in window) {
        window.onafterprint = function () {
            if (waitingAfterPrint) afterOneCopy();
        };
    }
    if (window.matchMedia) {
        try {
            var mq = window.matchMedia('print');
            var handler = function (ev) {
                if (!ev.matches && waitingAfterPrint) afterOneCopy();
            };
            if (mq.addEventListener) mq.addEventListener('change', handler);
            else if (mq.addListener) mq.addListener(handler);
        } catch (e2) {}
    }

    window.onload = function () {
        copies = Array.prototype.slice.call(document.querySelectorAll('#recuEpsonStack .recu-copy'));
        whenImagesReady(function () {
            if (!copies.length) {
                goHome();
                return;
            }
            printNext();
        });
    };
})();
</script>

<div id="printStatus">
    <p class="msg">Impression en cours…</p>
    <p class="sub">3 exemplaires · POSPrinter 57×40 · retour automatique</p>
</div>

<div id="recuEpsonStack">
<?php foreach ($copies as $copy): ?>
    <div class="recu-copy">
        <?php if ($logo !== ''): ?>
            <img class="t-logo" src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="">
        <?php elseif ($compagnie !== ''): ?>
            <div class="t-company"><?= htmlspecialchars($compagnie, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="t-title">RECU COURRIER <?= htmlspecialchars($copy['n'] . ' ' . $copy['label'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="t-od"><?= htmlspecialchars($od, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="t-line">EXP <?= htmlspecialchars($exp_name, ENT_QUOTES, 'UTF-8'); ?><?= $exp_tel !== '' ? (' ' . htmlspecialchars($exp_tel, ENT_QUOTES, 'UTF-8')) : ''; ?></div>
        <div class="t-line">DEST <?= htmlspecialchars($dest_name, ENT_QUOTES, 'UTF-8'); ?><?= $dest_tel !== '' ? (' ' . htmlspecialchars($dest_tel, ENT_QUOTES, 'UTF-8')) : ''; ?></div>
        <?php if ($contenu !== ''): ?>
            <div class="t-line"><?= htmlspecialchars($contenu, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <div class="t-prix"><?= $prix; ?> FCFA</div>
        <div class="t-code"><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?></div>
        <?= ticket_barcode_img($code, 260, 36); ?>
        <div class="t-emis"><?= htmlspecialchars($emis, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
<?php endforeach; ?>
</div>
