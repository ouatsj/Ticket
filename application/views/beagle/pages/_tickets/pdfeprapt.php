<?php defined('BASEPATH') OR exit('No direct script access allowed');
$retour_url = site_url("caisses/compte/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");
$reponsealler = !empty($reponsealler) && is_array($reponsealler) ? $reponsealler : array();
$reponseretour = !empty($reponseretour) && is_array($reponseretour) ? $reponseretour : array();
$reponsealler_rattrapage = !empty($reponsealler_rattrapage) && is_array($reponsealler_rattrapage) ? $reponsealler_rattrapage : array();
$reponseretour_anterieur = !empty($reponseretour_anterieur) && is_array($reponseretour_anterieur) ? $reponseretour_anterieur : array();
$reponserepro = !empty($reponserepro) && is_array($reponserepro) ? $reponserepro : array();
$reponseconf = !empty($reponseconf) && is_array($reponseconf) ? $reponseconf : array();

$montantglobal = 0;
$montantglobalr = 0;
$montantglobal_rat = 0;
$has_anterieur = !empty($reponsealler_rattrapage) || !empty($reponseretour_anterieur);

$nb_repro = 0;
foreach ($reponserepro as $_rp) {
    $nb_repro += (int) $_rp->cdrep;
}
$nb_conf = 0;
foreach ($reponseconf as $_cf) {
    $nb_conf += (int) $_cf->cdconf;
}
?>
<style>
@page { size: 80mm auto; margin: 2mm; }
html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
    color: #000 !important;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    line-height: 1.3;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.epson-toolbar {
    max-width: 76mm;
    margin: 8px auto;
    display: flex;
    justify-content: space-between;
    gap: 8px;
}
.epson-toolbar a,
.epson-toolbar button {
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid #333;
    border-radius: 4px;
    background: #f5f5f5;
    color: #111;
    text-decoration: none;
    cursor: pointer;
}
.epson-toolbar button {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}
.epson-ticket {
    width: 74mm;
    margin: 0 auto;
    padding: 2mm 1mm;
    color: #000;
}
.epson-head {
    text-align: center;
    border-bottom: 1px dashed #000;
    padding-bottom: 4px;
    margin-bottom: 6px;
}
.epson-head .title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
}
.epson-head .meta {
    font-size: 11px;
    font-weight: 700;
    margin-top: 2px;
}
.epson-section {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin: 8px 0 4px;
    border-bottom: 1px solid #000;
    padding-bottom: 2px;
}
.epson-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 10px;
}
.epson-table th,
.epson-table td {
    border: 1px solid #000;
    padding: 2px 3px;
    vertical-align: top;
    word-wrap: break-word;
    overflow-wrap: break-word;
}
.epson-table th {
    font-weight: 700;
    background: #f0f0f0;
}
.epson-table .col-ligne { width: 42%; text-align: left; }
.epson-table .col-nbr { width: 13%; text-align: right; }
.epson-table .col-pu { width: 20%; text-align: right; }
.epson-table .col-tot { width: 25%; text-align: right; font-weight: 700; }
.epson-table .col-ligne2 { width: 78%; text-align: left; }
.epson-table .col-nbr2 { width: 22%; text-align: right; font-weight: 700; }
.epson-total {
    margin: 5px 0 2px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    justify-content: space-between;
}
.epson-total.grand {
    font-size: 12px;
    border-top: 1px dashed #000;
    padding-top: 4px;
    margin-top: 6px;
}
@media print {
    .epson-toolbar { display: none !important; }
    .epson-ticket { width: 74mm; margin: 0; padding: 0; }
}
</style>

<div class="epson-toolbar">
    <a href="<?= htmlspecialchars($retour_url, ENT_QUOTES, 'UTF-8'); ?>">&larr; Retour</a>
    <button type="button" onclick="window.print();">Imprimer</button>
</div>

<div class="epson-ticket">
    <div class="epson-head">
        <div class="title">Rapport : <?= htmlspecialchars(!empty($ncomp->nom_compagnie) ? $ncomp->nom_compagnie : '', ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="meta"><?= mdate('%d/%m/%Y', now('UTC')); ?> — <?= htmlspecialchars(!empty($conex->username) ? $conex->username : '', ENT_QUOTES, 'UTF-8'); ?></div>
    </div>

    <div class="epson-section">Arrêt du jour</div>
    <table class="epson-table">
        <thead>
            <tr>
                <th class="col-ligne">LIGNES</th>
                <th class="col-nbr">NBR</th>
                <th class="col-pu">PU</th>
                <th class="col-tot">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reponsealler as $repba): ?>
                <?php
                $cd = (int) $repba->cd;
                $pu = $cd > 0 ? ($repba->total / $cd) : 0;
                $montantglobal += $repba->total;
                ?>
                <tr>
                    <td class="col-ligne"><?= htmlspecialchars($repba->nom_ligne, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="col-nbr"><?= $cd; ?></td>
                    <td class="col-pu"><?= number_format($pu, 0, '', ' '); ?></td>
                    <td class="col-tot"><?= number_format($repba->total, 0, '', ' '); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($reponseretour as $repou): ?>
                <?php
                $aler = explode('-', $repou->nom_ligne);
                $allerretour = (isset($aler[1]) ? $aler[1] : '') . '-' . (isset($aler[0]) ? $aler[0] : '');
                $cod = (int) $repou->cod;
                $pu = $cod > 0 ? ($repou->totalr / $cod) : 0;
                $montantglobalr += $repou->totalr;
                ?>
                <tr>
                    <td class="col-ligne"><?= htmlspecialchars($allerretour, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="col-nbr"><?= $cod; ?></td>
                    <td class="col-pu"><?= number_format($pu, 0, '', ' '); ?></td>
                    <td class="col-tot"><?= number_format($repou->totalr, 0, '', ' '); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="epson-total">
        <span>Recette du jour :</span>
        <span><?= number_format($montantglobal + $montantglobalr, 0, '', ' '); ?> F</span>
    </div>

    <?php if ($has_anterieur): ?>
        <div class="epson-section">Antérieur oublié (jours précédents)</div>
        <table class="epson-table">
            <thead>
                <tr>
                    <th class="col-ligne">LIGNES</th>
                    <th class="col-nbr">NBR</th>
                    <th class="col-pu">PU</th>
                    <th class="col-tot">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reponsealler_rattrapage as $reprat): ?>
                    <?php
                    $cd = (int) $reprat->cd;
                    $pu = $cd > 0 ? ($reprat->total / $cd) : 0;
                    $montantglobal_rat += $reprat->total;
                    ?>
                    <tr>
                        <td class="col-ligne"><?= htmlspecialchars($reprat->nom_ligne, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-nbr"><?= $cd; ?></td>
                        <td class="col-pu"><?= number_format($pu, 0, '', ' '); ?></td>
                        <td class="col-tot"><?= number_format($reprat->total, 0, '', ' '); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($reponseretour_anterieur as $repret): ?>
                    <?php
                    $alerAnt = explode('-', $repret->nom_ligne);
                    $allerretourAnt = (isset($alerAnt[1]) ? $alerAnt[1] : '') . '-' . (isset($alerAnt[0]) ? $alerAnt[0] : '');
                    $cod = (int) $repret->cod;
                    $pu = $cod > 0 ? ($repret->totalr / $cod) : 0;
                    $montantglobal_rat += $repret->totalr;
                    ?>
                    <tr>
                        <td class="col-ligne"><?= htmlspecialchars($allerretourAnt, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-nbr"><?= $cod; ?></td>
                        <td class="col-pu"><?= number_format($pu, 0, '', ' '); ?></td>
                        <td class="col-tot"><?= number_format($repret->totalr, 0, '', ' '); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="epson-total">
            <span>Total antérieur oublié :</span>
            <span><?= number_format($montantglobal_rat, 0, '', ' '); ?> F</span>
        </div>
        <div class="epson-total grand">
            <span>Total général (jour + antérieur) :</span>
            <span><?= number_format($montantglobal + $montantglobalr + $montantglobal_rat, 0, '', ' '); ?> F</span>
        </div>
    <?php endif; ?>

    <?php if ($nb_repro > 0): ?>
        <div class="epson-section">Reprogrammation (non facturable) — <?= $nb_repro; ?></div>
        <table class="epson-table">
            <thead>
                <tr>
                    <th class="col-ligne2">LIGNES</th>
                    <th class="col-nbr2">NBR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reponserepro as $reponserep): ?>
                    <tr>
                        <td class="col-ligne2"><?= htmlspecialchars($reponserep->nom_ligne, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-nbr2"><?= (int) $reponserep->cdrep; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($nb_conf > 0): ?>
        <div class="epson-section">Confirmation (non facturable) — <?= $nb_conf; ?></div>
        <table class="epson-table">
            <thead>
                <tr>
                    <th class="col-ligne2">LIGNES</th>
                    <th class="col-nbr2">NBR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reponseconf as $reponsecf): ?>
                    <tr>
                        <td class="col-ligne2"><?= htmlspecialchars($reponsecf->nom_ligne, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-nbr2"><?= (int) $reponsecf->cdconf; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script type="text/javascript">
window.onload = function () {
    window.print();
};
</script>
