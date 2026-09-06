<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$item = !empty($item) ? $item : null;
$emis = '';
if ($item) {
    if (!empty($item->dateheureescal) && $item->dateheureescal !== '0000-00-00 00:00:00') {
        $emis = $item->dateheureescal;
    } else {
        $emis = mdate("%Y-%m-%d %H:%i:%s", now('UTC'));
    }
}
$od = $item ? trim(preg_replace('/^\[LIBRE\]\s*/', '', (string) $item->quartier_escal)) : '';
$prix = $item ? number_format((float) $item->prixescal, 0, '', ' ') : '';
$passager = $item ? trim($item->nom_client . ' ' . $item->prenom_client) : '';
$compagnie = $item && !empty($item->nom_compagnie) ? (string) $item->nom_compagnie : '';
$accueil_url = site_url(
    'gares/' . $this->session->company->ekey
    . '/gTc/' . $bus_stop->idengare
    . '/compte/' . $conex->roleattribut
    . '/' . $bus_stop->idsousgare
    . '/' . mdate('%d/%m/%Y', now('UTC'))
);
?>
<style>
html, body {
    margin: 0;
    padding: 0;
    background: #f5f5f5;
    font-family: Arial, Helvetica, sans-serif;
}
.pos-screen {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    text-align: center;
    padding: 16px;
}
.pos-screen p {
    margin: 0;
    font-size: 16px;
    color: #333;
}
.ticket-escale-libre {
    display: none;
}

@page {
    size: 57mm auto;
    margin: 2mm;
}

@media print {
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 57mm !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .pos-screen,
    .no-print,
    .auth-guichet-banner,
    .be-top-header,
    .be-left-sidebar,
    .be-right-sidebar,
    .be-footer,
    .be-navbar-header,
    .navbar,
    nav,
    header,
    footer,
    .alert {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    .be-wrapper,
    .be-content,
    .main-content,
    .page,
    .container-fluid,
    .row,
    .col-12,
    .tab-container,
    .tab-content,
    .tab-pane {
        margin: 0 !important;
        padding: 0 !important;
        width: 57mm !important;
        max-width: 57mm !important;
        float: none !important;
        position: static !important;
        background: transparent !important;
        box-shadow: none !important;
        border: 0 !important;
        overflow: visible !important;
    }
    .ticket-escale-libre {
        display: block !important;
        width: 53mm;
        max-width: 53mm;
        margin: 0 auto;
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        text-align: center;
    }
    .ticket-escale-libre .logo {
        margin: 0 0 1.5mm;
    }
    .ticket-escale-libre .logo img {
        max-width: 36mm;
        max-height: 12mm;
        width: auto;
        height: auto;
    }
    .ticket-escale-libre .compagnie {
        font-size: 13px;
        font-weight: 800;
        line-height: 1.2;
        text-transform: uppercase;
        margin: 0 0 1.5mm;
        letter-spacing: 0.3px;
    }
    .ticket-escale-libre .sep {
        border: 0;
        border-top: 1.5px solid #000;
        margin: 1.5mm 0;
    }
    .ticket-escale-libre .sep-dash {
        border: 0;
        border-top: 1px dashed #000;
        margin: 1.5mm 0;
    }
    .ticket-escale-libre .od {
        font-size: 15px;
        font-weight: 800;
        line-height: 1.25;
        margin: 1mm 0 2mm;
        word-wrap: break-word;
        text-transform: uppercase;
    }
    .ticket-escale-libre .passager {
        font-size: 14px;
        font-weight: 700;
        line-height: 1.25;
        margin: 1mm 0;
        word-wrap: break-word;
    }
    .ticket-escale-libre .tel {
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        margin: 0.5mm 0 1.5mm;
    }
    .ticket-escale-libre .prix {
        font-size: 16px;
        font-weight: 800;
        line-height: 1.2;
        margin: 1.5mm 0;
    }
    .ticket-escale-libre .code {
        font-size: 13px;
        font-weight: 800;
        line-height: 1.2;
        margin: 1mm 0;
        letter-spacing: 0.5px;
    }
    .ticket-escale-libre .emis {
        font-size: 10px;
        font-weight: 600;
        line-height: 1.2;
        margin: 1mm 0 0.5mm;
    }
    .ticket-escale-libre .barcode {
        margin-top: 1.5mm;
    }
    .ticket-escale-libre .barcode img {
        max-width: 50mm;
        height: 10mm;
    }
}
</style>

<div class="pos-screen no-print">
    <?php if (!$item): ?>
        <p class="text-danger">Ticket introuvable. Redirection…</p>
    <?php else: ?>
        <p>Impression en cours…</p>
    <?php endif; ?>
</div>

<?php if ($item): ?>
<div class="ticket-escale-libre" aria-hidden="true">
    <?php if (!empty($item->logo)): ?>
    <div class="logo">
        <img src="<?= site_url($item->logo); ?>" alt="">
    </div>
    <?php endif; ?>

    <?php if ($compagnie !== ''): ?>
    <div class="compagnie"><?= htmlspecialchars($compagnie, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <hr class="sep">

    <div class="od"><?= htmlspecialchars($od, ENT_QUOTES, 'UTF-8'); ?></div>

    <div class="passager"><?= htmlspecialchars($passager, ENT_QUOTES, 'UTF-8'); ?></div>
    <div class="tel"><?= htmlspecialchars($item->contact_client, ENT_QUOTES, 'UTF-8'); ?></div>

    <hr class="sep-dash">

    <div class="prix"><?= $prix; ?> FCFA</div>
    <div class="code"><?= htmlspecialchars($item->idclescal, ENT_QUOTES, 'UTF-8'); ?></div>

    <div class="barcode">
        <?= ticket_barcode_img($item->idclescal, 260, 40); ?>
    </div>

    <div class="emis"><?= htmlspecialchars($emis, ENT_QUOTES, 'UTF-8'); ?></div>
</div>
<?php endif; ?>

<script type="text/javascript">
(function () {
    var accueil = <?= json_encode($accueil_url); ?>;
    var done = false;

    function goHome() {
        if (done) return;
        done = true;
        window.location.replace(accueil);
    }

    window.onload = function () {
        <?php if (!$item): ?>
        setTimeout(goHome, 800);
        return;
        <?php endif; ?>

        setTimeout(function () {
            try {
                window.print();
            } catch (e) {
                goHome();
            }
        }, 150);

        if ('onafterprint' in window) {
            window.onafterprint = goHome;
        }

        setTimeout(goHome, 2500);

        if (window.matchMedia) {
            var mq = window.matchMedia('print');
            var handler = function (mql) {
                if (!mql.matches) {
                    setTimeout(goHome, 300);
                }
            };
            if (mq.addEventListener) {
                mq.addEventListener('change', handler);
            } else if (mq.addListener) {
                mq.addListener(handler);
            }
        }
    };
})();
</script>
