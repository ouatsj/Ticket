<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Reçu bagage escale — POSPrinter 57×40 mm, 2 exemplaires (CLIENT + ARCHIVE).
 */
$this->load->helper(array('ticket_escale_libre_print', 'url_safe', 'ticket_prix'));

$item = !empty($itemescbag) ? $itemescbag : null;

$accueil_url = (!empty($role17_mode) && function_exists('role17_accueil_url') && !empty($bus_stop) && !empty($conex))
    ? role17_accueil_url($bus_stop, $conex)
    : site_url(
        'confirmation/bagageescales/' . $this->session->company->ekey
        . '/' . (!empty($conex->roleattribut) ? $conex->roleattribut : '')
        . '/' . (!empty($bus_stop->idengare) ? $bus_stop->idengare : '')
        . '/' . (!empty($bus_stop->idsousgare) ? $bus_stop->idsousgare : '')
    );

if (!$item || !is_object($item)) {
    if (method_exists($this->session, 'set_flashdata')) {
        $this->session->set_flashdata(
            'error',
            'Reçu bagage introuvable — revérifiez le code ticket puis FACTURER.'
        );
    }
    echo '<p style="padding:16px;font-family:Arial,sans-serif;">Reçu bagage introuvable — retour…</p>';
    echo '<script>setTimeout(function(){location.replace(' . json_encode($accueil_url) . ');},1200);</script>';
    return;
}

$quart_raw = trim((string) (isset($item->quartier_escal) ? $item->quartier_escal : ''));
$is_libre = (stripos($quart_raw, '[LIBRE]') === 0);
$quart = $is_libre ? trim((string) preg_replace('/^\[LIBRE\]\s*/i', '', $quart_raw)) : $quart_raw;

// Uniquement « escale → destination » du ticket vérifié (jamais le nom de ligne).
$escale_ticket = '';
$dest_ticket = '';

// Ex. « BOROMO - OUAGA (origine) » / « BOROMO - BOBO (extrême) »
if ($quart !== ''
    && preg_match('/^(.+?)\s*[-–—]\s*(.+?)(?:\s*\([^)]*\))?\s*$/u', $quart, $m)
) {
    $escale_ticket = trim($m[1]);
    $dest_ticket = trim($m[2]);
}

if ($escale_ticket === '') {
    if (!empty($escale_depart_label)) {
        $escale_ticket = trim((string) preg_replace('/\s*\([^)]*\)\s*$/', '', (string) $escale_depart_label));
    } elseif (!empty($bus_stop->garenom)) {
        $escale_ticket = trim((string) $bus_stop->garenom);
    } elseif (!empty($item->nomsousgare)) {
        $escale_ticket = trim((string) $item->nomsousgare);
    }
}

if ($dest_ticket === '') {
    // Fallback : terminus ligne seulement si le ticket n'a pas d'OD parseable.
    if (!empty($item->nom_dest_ticket)) {
        $dest_ticket = trim((string) $item->nom_dest_ticket);
    } elseif (!empty($item->nom_gadest)) {
        $dest_ticket = trim((string) $item->nom_gadest);
    }
}

$escale_ticket = ticket_escale_libre_pos_text($escale_ticket, true);
$dest_ticket = ticket_escale_libre_pos_text($dest_ticket, true);
$od = trim($escale_ticket . ($escale_ticket !== '' && $dest_ticket !== '' ? ' - ' : '') . $dest_ticket);
$od = ticket_escale_libre_pos_text($od, true);

$client = ticket_escale_libre_pos_text(
    trim((string) (isset($item->nom_client) ? $item->nom_client : '') . ' ' . (isset($item->prenom_client) ? $item->prenom_client : '')),
    true
);
$tel = ticket_escale_libre_pos_text(
    !empty($item->contactexpediesc) ? (string) $item->contactexpediesc : '',
    false
);
$nb = isset($item->nombrebagageesc) ? (string) $item->nombrebagageesc : '';
$type = isset($item->typebagagesesc) ? (string) $item->typebagagesesc : '';
$contenu = ticket_escale_libre_pos_text(
    trim($nb . ($type !== '' ? (' (' . $type . ')') : '') . ' ' . (isset($item->contenubagageesc) ? $item->contenubagageesc : '')),
    true
);
if (function_exists('mb_substr')) {
    $contenu = mb_substr($contenu, 0, 30, 'UTF-8');
} else {
    $contenu = substr($contenu, 0, 30);
}

$compagnie = !empty($item->nom_compagnie)
    ? ticket_escale_libre_pos_text((string) $item->nom_compagnie, true)
    : '';
$code = !empty($item->codebagesc) ? (string) $item->codebagesc : (string) $item->id_bagageesc;
$prix = number_format((float) $item->prix_bagageesc, 0, '', ' ');
$recu_no = str_pad((string) $item->id_bagageesc, 3, '0', STR_PAD_LEFT);

$emis_raw = mdate('%Y-%m-%d %H:%i:%s', now('UTC'));
if (!empty($item->date_createesc)) {
    $emis_raw = (string) $item->date_createesc
        . (!empty($item->heure) ? (' ' . $item->heure) : '');
}
$emis = ticket_emis_texte($item, $emis_raw, isset($conex) ? $conex : null);
$logo = !empty($item->logo) ? site_url($item->logo) : '';

$copies = array(
    array('n' => '1/2', 'label' => 'CLIENT'),
    array('n' => '2/2', 'label' => 'ARCHIVE'),
);
?>
<style>
@page { size: 57mm 40mm; margin: 0; }
html, body {
    margin: 0 !important; padding: 0 !important; background: #fff !important; color: #000 !important;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
}
@media screen {
    html, body { width: 100%; height: 100%; min-height: 100vh; overflow: hidden; }
    #printStatus {
        position: fixed; inset: 0; z-index: 50; display: flex; flex-direction: column;
        align-items: center; justify-content: center; background: #fff;
        font-family: Arial, Helvetica, sans-serif; color: #222; text-align: center; padding: 24px;
    }
    #printStatus .msg { font-size: 20px; font-weight: 700; margin: 0 0 8px; }
    #printStatus .sub { font-size: 14px; color: #666; margin: 0; }
    #recuEpsonStack { position: absolute; left: 0; top: 0; z-index: 1; }
}
@media print {
    #printStatus { display: none !important; }
    /* Une seule copie active = même job que le ticket vente (1× 57×40). */
    html, body { width: 57mm !important; height: 40mm !important; margin: 0 !important; padding: 0 !important; overflow: hidden !important; }
    #recuEpsonStack { position: static !important; width: 57mm !important; height: 40mm !important; }
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
    box-sizing: border-box; width: 57mm; height: 40mm; margin: 0; padding: 0.5mm 1.6mm 0.4mm;
    background: #fff; color: #000; text-align: center;
    font-family: Arial, Helvetica, DejaVu Sans, sans-serif; overflow: hidden;
    display: flex; flex-direction: column; align-items: center; justify-content: space-between; line-height: 1.05;
}
.recu-copy .t-logo { display: block; max-width: 24mm; max-height: 5.5mm; width: auto; height: auto; margin: 0 auto; object-fit: contain; }
.recu-copy .t-company { font-size: 7.5pt; font-weight: 700; max-width: 53mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.recu-copy .t-title { font-size: 7pt; font-weight: 700; letter-spacing: 0.04em; }
.recu-copy .t-od { font-size: 8.5pt; font-weight: 700; max-width: 53mm; overflow: hidden; }
.recu-copy .t-line { font-size: 7.5pt; font-weight: 700; max-width: 53mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.recu-copy .t-prix { font-size: 11pt; font-weight: 800; }
.recu-copy .t-code { font-size: 7pt; font-weight: 700; }
.recu-copy img.ticket-barcode { display: block; width: 48mm; height: 6mm; margin: 0 auto; object-fit: fill; }
.recu-copy .t-emis { font-size: 5.5pt; max-width: 53mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
<script>
(function () {
    var homeUrl = <?= json_encode($accueil_url); ?>;
    var copies = [];
    var idx = 0;
    var gone = false;
    var waitingAfterPrint = false;
    var fallbackTimer = null;

    function goHome() {
        if (gone) return;
        gone = true;
        try { location.replace(homeUrl); } catch (e) { location.href = homeUrl; }
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
        /* Pause courte entre jobs pour laisser POSPrinter éjecter. */
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
    <p class="sub">2 exemplaires · CLIENT + ARCHIVE · POSPrinter 57×40</p>
</div>

<div id="recuEpsonStack">
<?php foreach ($copies as $copy): ?>
    <div class="recu-copy">
        <?php if ($logo !== ''): ?>
            <img class="t-logo" src="<?= htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>" alt="">
        <?php elseif ($compagnie !== ''): ?>
            <div class="t-company"><?= htmlspecialchars($compagnie, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <div class="t-title">RECU BAGAGE <?= htmlspecialchars($copy['n'] . ' ' . $copy['label'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="t-od"><?= htmlspecialchars($od, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="t-line">N° <?= htmlspecialchars($recu_no, ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($client, ENT_QUOTES, 'UTF-8'); ?><?= $tel !== '' ? (' ' . htmlspecialchars($tel, ENT_QUOTES, 'UTF-8')) : ''; ?></div>
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
