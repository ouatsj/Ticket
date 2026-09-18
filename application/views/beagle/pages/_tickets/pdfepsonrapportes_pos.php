<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Rapport mobile escale — POSPrinter 57×40 mm
 * Tickets + bagage + courrier (après arrêt global).
 */
$this->load->helper(array('ticket_escale_libre_print', 'url_safe'));

$accueil_url = (!empty($role17_mode) && function_exists('role17_accueil_url') && !empty($bus_stop) && !empty($conex))
    ? role17_accueil_url($bus_stop, $conex)
    : site_url(
        'caisses/compteescal/' . $this->session->company->ekey
        . '/' . (!empty($conex->roleattribut) ? $conex->roleattribut : '')
        . '/' . (!empty($bus_stop->idengare) ? $bus_stop->idengare : '')
        . '/' . (!empty($bus_stop->idsousgare) ? $bus_stop->idsousgare : '')
    );

$compagnie = '';
if (!empty($ncomp) && !empty($ncomp->nom_compagnie)) {
    $compagnie = ticket_escale_libre_pos_text((string) $ncomp->nom_compagnie, true);
}
$agent = !empty($conex->username)
    ? ticket_escale_libre_pos_text((string) $conex->username, true)
    : '';
$jour = mdate('%d/%m/%Y', now('UTC'));

$tickets = !empty($reponsealler) && is_array($reponsealler) ? $reponsealler : array();
$bagages = !empty($reponsebagageesc) && is_array($reponsebagageesc) ? $reponsebagageesc : array();
$courriers = !empty($reponsecourrieresc) && is_array($reponsecourrieresc) ? $reponsecourrieresc : array();

$sum_tickets = 0.0;
$nb_tickets = 0;
foreach ($tickets as $row) {
    $sum_tickets += isset($row->total) ? (float) $row->total : 0.0;
    $nb_tickets += isset($row->cd) ? (int) $row->cd : 0;
}
$sum_bag = 0.0;
$nb_bag = 0;
foreach ($bagages as $row) {
    $sum_bag += isset($row->bagtotal) ? (float) $row->bagtotal : 0.0;
    $nb_bag += isset($row->cbg) ? (int) $row->cbg : 0;
}
$sum_cour = 0.0;
$nb_cour = 0;
foreach ($courriers as $row) {
    $sum_cour += isset($row->montant) ? (float) $row->montant : 0.0;
    $nb_cour += isset($row->nombres) ? (int) $row->nombres : 0;
}
$grand = $sum_tickets + $sum_bag + $sum_cour;

$fmt = function ($n) {
    return number_format((float) $n, 0, '', ' ');
};

// Pages détail : max ~4 lignes utiles / page 40 mm
$detail_pages = array();
$chunk = function ($section, $rows, $label_fn, $nbr_fn, $pu_fn, $tot_fn) use (&$detail_pages) {
    if (!$rows) {
        return;
    }
    $chunks = array_chunk(array_values($rows), 4);
    foreach ($chunks as $i => $part) {
        $detail_pages[] = array(
            'section' => $section,
            'part' => $i + 1,
            'parts' => count($chunks),
            'rows' => $part,
            'label_fn' => $label_fn,
            'nbr_fn' => $nbr_fn,
            'pu_fn' => $pu_fn,
            'tot_fn' => $tot_fn,
        );
    }
};

$chunk(
    'TICKETS',
    $tickets,
    function ($r) { return isset($r->nom_ligne) ? (string) $r->nom_ligne : 'TICKET'; },
    function ($r) { return isset($r->cd) ? (int) $r->cd : 0; },
    function ($r) { return isset($r->prixescal) ? (float) $r->prixescal : 0; },
    function ($r) { return isset($r->total) ? (float) $r->total : 0; }
);
$chunk(
    'BAGAGE',
    $bagages,
    function ($r) { return isset($r->nom_ligne) ? (string) $r->nom_ligne : 'BAGAGE'; },
    function ($r) { return isset($r->cbg) ? (int) $r->cbg : 0; },
    function ($r) { return isset($r->prix_bagageesc) ? (float) $r->prix_bagageesc : 0; },
    function ($r) { return isset($r->bagtotal) ? (float) $r->bagtotal : 0; }
);
$chunk(
    'COURRIER',
    $courriers,
    function ($r) { return isset($r->nom_ligne) ? (string) $r->nom_ligne : 'COURRIER'; },
    function ($r) { return isset($r->nombres) ? (int) $r->nombres : 0; },
    function ($r) { return isset($r->prixcolisesc) ? (float) $r->prixcolisesc : 0; },
    function ($r) { return isset($r->montant) ? (float) $r->montant : 0; }
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
    #recuEpsonStack { position: fixed; left: -9999px; top: 0; }
}
@media print {
    #printStatus { display: none !important; }
    html, body { width: 57mm !important; height: auto !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; }
    #recuEpsonStack { position: static !important; }
    .recu-copy { page-break-after: always; break-after: page; }
    .recu-copy:last-child { page-break-after: auto; break-after: auto; }
}
.recu-copy {
    box-sizing: border-box; width: 57mm; height: 40mm; margin: 0; padding: 0.6mm 1.5mm 0.5mm;
    background: #fff; color: #000; text-align: center;
    font-family: Arial, Helvetica, DejaVu Sans, sans-serif; overflow: hidden;
    display: flex; flex-direction: column; align-items: stretch; justify-content: flex-start; line-height: 1.08;
}
.recu-copy .t-title { font-size: 7.5pt; font-weight: 800; letter-spacing: 0.03em; margin: 0 0 0.3mm; }
.recu-copy .t-meta { font-size: 6pt; font-weight: 700; margin: 0 0 0.4mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.recu-copy .t-company { font-size: 6.5pt; font-weight: 700; margin: 0 0 0.5mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.recu-copy .t-row {
    display: flex; justify-content: space-between; gap: 1mm;
    font-size: 6.5pt; font-weight: 700; text-align: left; margin: 0 0 0.25mm;
}
.recu-copy .t-row span:last-child { white-space: nowrap; }
.recu-copy .t-sep { border: 0; border-top: 0.3mm solid #000; margin: 0.4mm 0; }
.recu-copy .t-total { font-size: 8.5pt; font-weight: 800; margin-top: 0.4mm; }
.recu-copy .t-sec { font-size: 6.5pt; font-weight: 800; text-align: left; margin: 0 0 0.35mm; }
.recu-copy table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.recu-copy th, .recu-copy td {
    font-size: 5.5pt; font-weight: 700; text-align: left; padding: 0; line-height: 1.1;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.recu-copy th:nth-child(2), .recu-copy td:nth-child(2),
.recu-copy th:nth-child(3), .recu-copy td:nth-child(3),
.recu-copy th:nth-child(4), .recu-copy td:nth-child(4) { text-align: right; width: 9mm; }
.recu-copy th:nth-child(1), .recu-copy td:nth-child(1) { width: auto; }
</style>
<script>
(function () {
    var homeUrl = <?= json_encode($accueil_url); ?>;
    var printed = false;
    function goHome() {
        try { location.replace(homeUrl); } catch (e) { location.href = homeUrl; }
    }
    function runPrint() {
        if (printed) return;
        printed = true;
        try { window.print(); } catch (e) {}
        setTimeout(goHome, 1200);
        window.onafterprint = goHome;
    }
    window.onload = function () { setTimeout(runPrint, 200); };
})();
</script>

<div id="printStatus">
    <p class="msg">Impression rapport…</p>
    <p class="sub">POSPrinter 57×40 · tickets + bagage + courrier</p>
</div>

<div id="recuEpsonStack">
    <div class="recu-copy">
        <div class="t-title">RAPPORT ESCALE</div>
        <div class="t-meta"><?= htmlspecialchars($jour . ($agent !== '' ? (' · ' . $agent) : ''), ENT_QUOTES, 'UTF-8'); ?></div>
        <?php if ($compagnie !== ''): ?>
            <div class="t-company"><?= htmlspecialchars($compagnie, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <hr class="t-sep">
        <div class="t-row"><span>TICKETS (<?= (int) $nb_tickets; ?>)</span><span><?= $fmt($sum_tickets); ?></span></div>
        <div class="t-row"><span>BAGAGE (<?= (int) $nb_bag; ?>)</span><span><?= $fmt($sum_bag); ?></span></div>
        <div class="t-row"><span>COURRIER (<?= (int) $nb_cour; ?>)</span><span><?= $fmt($sum_cour); ?></span></div>
        <hr class="t-sep">
        <div class="t-total">TOTAL <?= $fmt($grand); ?> FCFA</div>
    </div>

    <?php foreach ($detail_pages as $page): ?>
        <?php
        $label_fn = $page['label_fn'];
        $nbr_fn = $page['nbr_fn'];
        $pu_fn = $page['pu_fn'];
        $tot_fn = $page['tot_fn'];
        $sec_title = $page['section']
            . ($page['parts'] > 1 ? (' ' . $page['part'] . '/' . $page['parts']) : '');
        ?>
        <div class="recu-copy">
            <div class="t-title">RAPPORT ESCALE</div>
            <div class="t-sec"><?= htmlspecialchars($sec_title, ENT_QUOTES, 'UTF-8'); ?></div>
            <table>
                <thead>
                    <tr>
                        <th>LIBELLE</th>
                        <th>NBR</th>
                        <th>PU</th>
                        <th>TOT</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($page['rows'] as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(ticket_escale_libre_pos_text($label_fn($r), true), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= (int) $nbr_fn($r); ?></td>
                        <td><?= $fmt($pu_fn($r)); ?></td>
                        <td><?= $fmt($tot_fn($r)); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>
