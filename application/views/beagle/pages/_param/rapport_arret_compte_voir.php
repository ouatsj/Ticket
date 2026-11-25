<?php defined('BASEPATH') OR exit('No direct script access allowed');
$nom = trim((string) $arret->first_name . ' ' . (string) $arret->last_name);
if ($nom === '') {
    $nom = !empty($arret->username) ? $arret->username : ('Opérateur #' . $arret->roleattribut);
}
$sgLabel = !empty($arret->nomsousgare)
    ? ($arret->gare_code . ' — ' . $arret->nomsousgare)
    : $arret->gare_code;
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
    line-height: 1.35;
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
}
.epson-head {
    text-align: center;
    border-bottom: 1px dashed #000;
    padding-bottom: 5px;
    margin-bottom: 6px;
}
.epson-head .title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
}
.epson-meta {
    margin-bottom: 6px;
    font-size: 11px;
}
.epson-meta div {
    margin-bottom: 2px;
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
    font-size: 11px;
}
.epson-table th,
.epson-table td {
    border: 1px solid #000;
    padding: 3px 4px;
}
.epson-table th {
    background: #f0f0f0;
    text-align: left;
}
.epson-table td.num,
.epson-table th.num {
    text-align: right;
    font-weight: 700;
}
.epson-total {
    margin-top: 8px;
    padding-top: 5px;
    border-top: 1px dashed #000;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    justify-content: space-between;
}
@media print {
    .epson-toolbar { display: none !important; }
    .epson-ticket { width: 74mm; margin: 0; padding: 0; }
}
</style>

<div class="epson-toolbar">
    <a href="<?= htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8'); ?>">&larr; Retour</a>
    <button type="button" onclick="window.print();">Imprimer</button>
</div>

<div class="epson-ticket">
    <div class="epson-head">
        <div class="title">Rapport arrêt de compte</div>
        <div>N° #<?= (int) $arret->id; ?> — <?= htmlspecialchars($arret->created_at, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>

    <div class="epson-meta">
        <div><strong>Guichetier :</strong> <?= htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?><?= !empty($arret->username) ? ' (' . htmlspecialchars($arret->username, ENT_QUOTES, 'UTF-8') . ')' : ''; ?></div>
        <div><strong>Gare / Sous-gare :</strong> <?= htmlspecialchars($sgLabel, ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Tickets :</strong> <?= (int) $arret->pass_count; ?> aller(s) / <?= (int) $arret->np_count; ?> retour(s)</div>
    </div>

    <?php if (!empty($tickets_by_comp)): ?>
        <div class="epson-section">Tickets par compagnie</div>
        <table class="epson-table">
            <thead>
                <tr>
                    <th>Compagnie</th>
                    <th class="num">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets_by_comp as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['compagnie'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="num"><?= number_format((float) $t['montant'], 0, ',', ' '); ?> F</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (!empty($bagages_by_comp)): ?>
        <div class="epson-section">Bagages par compagnie</div>
        <table class="epson-table">
            <thead>
                <tr>
                    <th>Compagnie</th>
                    <th class="num">Montant</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bagages_by_comp as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['compagnie'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="num"><?= number_format((float) $b['montant'], 0, ',', ' '); ?> F</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="epson-total">
        <span>TOTAL ARRÊTÉ :</span>
        <span><?= number_format((float) $arret->total_amount, 0, ',', ' '); ?> F</span>
    </div>
</div>
