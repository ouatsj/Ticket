<?php defined('BASEPATH') OR exit('No direct script access allowed');
$f = isset($filters) ? $filters : array();
$stats = isset($stats) ? $stats : array();
$lignes = isset($lignes) ? $lignes : array();
$fmt = function ($n) {
    if ($n === null || $n === '') {
        return '—';
    }
    return number_format((float) $n, 0, ',', ' ') . ' F';
};
$type_labels = array(
    'all' => 'Tous',
    'anomalies' => 'Anomalies (0 F / hors tarif)',
    'gratuit' => '0 F (gratuit)',
    'hors' => 'Hors tarif',
    'conforme' => 'Conforme catalogue',
);
$arret_labels = array(
    'all' => 'Tous',
    'oui' => 'Arrêté',
    'non' => 'Non arrêté',
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Autres ventes — <?= htmlspecialchars($company->nom_entreprise ?? ''); ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #111; margin: 16px; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { margin-bottom: 12px; color: #333; }
        .stats { margin: 8px 0 14px; }
        .stats span { display: inline-block; margin-right: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 3px 5px; vertical-align: top; }
        th { background: #f0f0f0; text-align: left; white-space: nowrap; }
        .text-right { text-align: right; }
        .no-print { margin-bottom: 12px; }
        .btn {
            display: inline-block; padding: 6px 12px; margin-right: 6px;
            border: 1px solid #888; background: #f5f5f5; color: #111;
            text-decoration: none; cursor: pointer; font-size: 13px;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            th { background: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @page { size: landscape; margin: 10mm; }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn" onclick="window.print();">Imprimer</button>
        <a class="btn" href="<?= site_url('rapport_autre_vente/' . $company->ekey
            . (!empty($filters_qs) ? ('?' . $filters_qs) : '')); ?>">← Retour au rapport</a>
    </div>

    <h1>Autres ventes — <?= htmlspecialchars($company->nom_entreprise ?? ''); ?></h1>
    <div class="meta">
        Période : <strong><?= htmlspecialchars($f['date_debut'] ?? ''); ?></strong>
        → <strong><?= htmlspecialchars($f['date_fin'] ?? ''); ?></strong>
        — Prix : <?= htmlspecialchars($type_labels[$f['type'] ?? 'all'] ?? ($f['type'] ?? '')); ?>
        — Arrêt : <?= htmlspecialchars($arret_labels[$f['arret'] ?? 'all'] ?? ($f['arret'] ?? '')); ?>
        <?php if (!empty($f['compagnie'])): ?>
            — Compagnie : <?= htmlspecialchars($f['compagnie']); ?>
        <?php endif; ?>
        <?php if (!empty($f['gare'])): ?>
            — Gare : <?= htmlspecialchars($f['gare']); ?>
        <?php endif; ?>
        <br>
        Imprimé le <?= date('d/m/Y H:i'); ?>
    </div>

    <div class="stats">
        <span>Total : <strong><?= (int) ($stats['total'] ?? 0); ?></strong></span>
        <span>Gratuits : <strong><?= (int) ($stats['gratuits'] ?? 0); ?></strong></span>
        <span>Hors tarif : <strong><?= (int) ($stats['hors_tarif'] ?? 0); ?></strong></span>
        <span>Conformes : <strong><?= (int) ($stats['conformes'] ?? 0); ?></strong></span>
        <span>Non arrêtés : <strong><?= (int) ($stats['non_arretes'] ?? 0); ?></strong></span>
        <span>Arrêtés : <strong><?= (int) ($stats['arretes'] ?? 0); ?></strong></span>
    </div>

    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Compagnie</th>
            <th>Gare vendeur</th>
            <th>Vendeur</th>
            <th>Rôle</th>
            <th>Ticket</th>
            <th>Bénéficiaire</th>
            <th>Départ</th>
            <th>Transit</th>
            <th class="text-right">Prix saisi</th>
            <th class="text-right">Prix programme</th>
            <th class="text-right">Écart</th>
            <th>Type</th>
            <th>Arrêt</th>
            <th>P/O ou n° CV</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($lignes)): ?>
            <tr>
                <td colspan="15">Aucune autre vente pour cette période / ces filtres.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($lignes as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['date']); ?></td>
                    <td><?= htmlspecialchars($l['compagnie'] ?? '—'); ?></td>
                    <td><?= htmlspecialchars($l['gare']); ?></td>
                    <td><?= htmlspecialchars($l['utilisateur']); ?></td>
                    <td><?= htmlspecialchars($l['role_libelle'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($l['ticket']); ?></td>
                    <td><?= htmlspecialchars($l['beneficiaire'] ?? '—'); ?></td>
                    <td><?= htmlspecialchars($l['depart']); ?></td>
                    <td>
                        <?= htmlspecialchars($l['transit']); ?>
                        <?php if (!empty($l['transit_detail'])): ?>
                            (<?= htmlspecialchars($l['transit_detail']); ?>)
                        <?php endif; ?>
                    </td>
                    <td class="text-right"><?= htmlspecialchars($fmt($l['prix_saisi'])); ?></td>
                    <td class="text-right"><?= htmlspecialchars($fmt($l['prix_programme'])); ?></td>
                    <td class="text-right">
                        <?= $l['ecart'] === null ? '—' : htmlspecialchars($fmt($l['ecart'])); ?>
                    </td>
                    <td><?= htmlspecialchars($l['type']); ?></td>
                    <td><?= htmlspecialchars($l['arret_libelle']); ?></td>
                    <td><?= htmlspecialchars($l['pourordre']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            if (window.location.search.indexOf('autoprint=1') !== -1) {
                window.print();
            }
        });
    </script>
</body>
</html>
