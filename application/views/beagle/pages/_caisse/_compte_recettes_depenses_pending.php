<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$recettes = !empty($compte_recettes_pending) ? $compte_recettes_pending : [];
$depenses = !empty($compte_depenses_pending) ? $compte_depenses_pending : [];
$detail_limit = !empty($compte_pending_detail_limit) ? (int) $compte_pending_detail_limit : 0;

if (!empty($compte_pending_recettes_total)) {
    $nb_recettes = (int) $compte_pending_recettes_total->nb;
    $total_recettes = (float) $compte_pending_recettes_total->total;
} else {
    $nb_recettes = count($recettes);
    $total_recettes = 0;
    foreach ($recettes as $row) {
        $total_recettes += (float) $row->montant_recet;
    }
}

if (!empty($compte_pending_depenses_total)) {
    $nb_depenses = (int) $compte_pending_depenses_total->nb;
    $total_depenses = (float) $compte_pending_depenses_total->total;
} else {
    $nb_depenses = count($depenses);
    $total_depenses = 0;
    foreach ($depenses as $row) {
        $total_depenses += (float) $row->montant_depens;
    }
}

$has_rd_links = !empty($compte_rd_arret_url);
$recettes_truncated = ($detail_limit > 0 && $nb_recettes > count($recettes));
$depenses_truncated = ($detail_limit > 0 && $nb_depenses > count($depenses));
?>
<div class="col-lg-12 mt-3">
    <div class="alert alert-info mb-2">
        <strong>Recettes et dépenses en attente d'arrêt de compte</strong>
        <?php if (!empty($compte_operateur_label)): ?>
        — chef de guichet <strong><?= htmlspecialchars($compte_operateur_label, ENT_QUOTES, 'UTF-8'); ?></strong>
        <?php endif; ?>
        <?php if (!empty($compte_last_arret_recettes) || !empty($compte_last_arret_depenses)): ?>
        <br>Dernier arrêt recettes :
        <strong><?= !empty($compte_last_arret_recettes) ? htmlspecialchars(date('d/m/Y', strtotime($compte_last_arret_recettes)), ENT_QUOTES, 'UTF-8') : '—'; ?></strong>
        — dernier arrêt dépenses :
        <strong><?= !empty($compte_last_arret_depenses) ? htmlspecialchars(date('d/m/Y', strtotime($compte_last_arret_depenses)), ENT_QUOTES, 'UTF-8') : '—'; ?></strong>.
        <?php elseif (!empty($compte_last_arret)): ?>
        <br>Depuis le dernier arrêt <strong>recettes / dépenses</strong> du
        <strong><?= htmlspecialchars(date('d/m/Y', strtotime($compte_last_arret)), ENT_QUOTES, 'UTF-8'); ?></strong>.
        <?php else: ?>
        <br>Aucun arrêt recettes/dépenses précédent — toutes les saisies non clôturées sont affichées.
        <?php endif; ?>
        <br>
        Solde net : <strong><?= number_format($total_recettes - $total_depenses, 0, '', ' '); ?> FCFA</strong>
        (recettes <?= number_format($total_recettes, 0, '', ' '); ?> − dépenses <?= number_format($total_depenses, 0, '', ' '); ?>).
        <?php if ($has_rd_links && ($nb_recettes > 0 || $nb_depenses > 0)): ?>
        <br><span class="text-muted">L'arrêt tickets ci-dessous ne clôture pas les recettes/dépenses internes.</span>
        <?php endif; ?>
    </div>
    <?php if ($has_rd_links && ($nb_recettes > 0 || $nb_depenses > 0)): ?>
    <p class="mb-2">
        <a href="<?= htmlspecialchars($compte_rd_arret_url, ENT_QUOTES, 'UTF-8'); ?>"
           class="btn btn-space btn-warning">
            <i class="fas fa-puzzle-piece"></i>&nbsp;ARRÊTER RECETTES / DÉPENSES
            <?php if (!empty($compte_rd_caisse_label)): ?>
            (<?= htmlspecialchars($compte_rd_caisse_label, ENT_QUOTES, 'UTF-8'); ?>)
            <?php endif; ?>
        </a>
        <a href="<?= htmlspecialchars($compte_rd_recettes_url, ENT_QUOTES, 'UTF-8'); ?>"
           class="btn btn-space btn-secondary">
            <i class="fas fa-eye"></i>&nbsp;VOIR RECETTES
        </a>
        <a href="<?= htmlspecialchars($compte_rd_depenses_url, ENT_QUOTES, 'UTF-8'); ?>"
           class="btn btn-space btn-secondary">
            <i class="fas fa-eye"></i>&nbsp;VOIR DÉPENSES
        </a>
        <?php if (empty($compte_rd_caisse_label)): ?>
        <a href="<?= htmlspecialchars($compte_rd_caisse_url, ENT_QUOTES, 'UTF-8'); ?>"
           class="btn btn-space btn-outline-secondary">
            <i class="fas fa-cash-register"></i>&nbsp;CHOISIR LA CAISSE
        </a>
        <?php endif; ?>
    </p>
    <?php endif; ?>
</div>
<div class="col-lg-6">
    <div class="card card-table">
        <div class="card-header">
            <div class="title">
                Recettes saisies (<?= $nb_recettes; ?>) — <?= number_format($total_recettes, 0, '', ' '); ?> FCFA
                <?php if ($recettes_truncated): ?>
                <small class="text-muted">— aperçu <?= count($recettes); ?> / <?= $nb_recettes; ?></small>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body" style="max-height: 420px; overflow-y: auto;">
            <div class="table-responsive noSwipe">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Nom / libellé</th>
                            <th class="text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recettes)): ?>
                        <tr><td colspan="4" class="text-muted text-center">Aucune recette en attente.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recettes as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item->date_recet, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item->type_recet, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item->nom, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-right"><?= number_format((float) $item->montant_recet, 0, '', ' '); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($recettes_truncated && $has_rd_links): ?>
            <p class="text-center mb-0">
                <a href="<?= htmlspecialchars($compte_rd_recettes_url, ENT_QUOTES, 'UTF-8'); ?>">Voir toutes les recettes en attente</a>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="col-lg-6">
    <div class="card card-table">
        <div class="card-header">
            <div class="title">
                Dépenses saisies (<?= $nb_depenses; ?>) — <?= number_format($total_depenses, 0, '', ' '); ?> FCFA
                <?php if ($depenses_truncated): ?>
                <small class="text-muted">— aperçu <?= count($depenses); ?> / <?= $nb_depenses; ?></small>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body" style="max-height: 420px; overflow-y: auto;">
            <div class="table-responsive noSwipe">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Nom / libellé</th>
                            <th class="text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($depenses)): ?>
                        <tr><td colspan="4" class="text-muted text-center">Aucune dépense en attente.</td></tr>
                    <?php else: ?>
                        <?php foreach ($depenses as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item->date_depens, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item->type_depense, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($item->nom_perso, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-right"><?= number_format((float) $item->montant_depens, 0, '', ' '); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($depenses_truncated && $has_rd_links): ?>
            <p class="text-center mb-0">
                <a href="<?= htmlspecialchars($compte_rd_depenses_url, ENT_QUOTES, 'UTF-8'); ?>">Voir toutes les dépenses en attente</a>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>
