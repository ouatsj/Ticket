<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$ekey = (int) $this->session->company->ekey;
$filter = isset($closure_filter) ? (string) $closure_filter : '';
$rows = isset($closure_reviews) && is_array($closure_reviews) ? $closure_reviews : array();
$statusLabels = array(
    'requires_review' => 'À revoir',
    'clear' => 'Conforme',
    'reviewed' => 'Validé',
    'rejected' => 'Rejeté',
);
$statusBadges = array(
    'requires_review' => 'warning',
    'clear' => 'success',
    'reviewed' => 'info',
    'rejected' => 'danger',
);
?>

<div class="row">
    <div class="col-12">
        <p class="mt-0 mb-3 ml-2">
            <a href="<?= site_url('gares/' . $ekey); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;Retour&nbsp;
            </a>
            <?php if (function_exists('super_admin_is_current') && super_admin_is_current()): ?>
                <a href="<?= site_url('super-administration/' . $ekey); ?>" class="btn btn-space btn-outline-secondary">
                    Super administration
                </a>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if (!empty($closure_notice)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($closure_notice, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php if (!empty($closure_error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($closure_error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header">
        <strong>Contrôle des arrêts de compte</strong>
        <span class="badge badge-secondary ml-2">
            mode <?= htmlspecialchars(isset($fraud_mode) ? $fraud_mode : 'observe', ENT_QUOTES, 'UTF-8'); ?>
        </span>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            Les écarts sont journalisés à l’arrêt. Cette page permet de les examiner sans bloquer la vente ni l’arrêt classique.
        </p>
        <form method="get" action="<?= site_url('Caisses/closure_reviews/' . $ekey); ?>" class="form-inline">
            <label class="mr-2" for="closure-status-filter">Statut</label>
            <select id="closure-status-filter" name="status" class="form-control form-control-sm mr-2">
                <option value="" <?= $filter === '' ? 'selected' : ''; ?>>Tous</option>
                <?php foreach ($statusLabels as $code => $label): ?>
                    <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                        <?= $filter === $code ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
        </form>
    </div>
</div>

<div class="card card-table">
    <div class="card-header">
        <div class="title">Arrêts récents (200 max)</div>
    </div>
    <div class="card-body">
        <div class="table-responsive noSwipe">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Opérateur</th>
                    <th>Compagnie</th>
                    <th>Déclaré</th>
                    <th>Attendu</th>
                    <th>Écart</th>
                    <th>Motif saisi</th>
                    <th>Statut</th>
                    <th class="actions">Décision</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="9" class="text-muted text-center">
                            Aucun arrêt à afficher pour ce filtre.
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($rows as $item): ?>
                    <?php
                    $status = (string) $item->review_status;
                    $badge = isset($statusBadges[$status]) ? $statusBadges[$status] : 'secondary';
                    $label = isset($statusLabels[$status]) ? $statusLabels[$status] : $status;
                    $operator = trim($item->first_name . ' ' . $item->last_name);
                    if ($operator === '') {
                        $operator = $item->username !== '' ? $item->username : ('#' . $item->roleattribut);
                    }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $item->created_at, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?= htmlspecialchars($operator, ENT_QUOTES, 'UTF-8'); ?>
                            <?php if ($item->username !== ''): ?>
                                <div class="small text-muted"><?= htmlspecialchars($item->username, ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars((string) $item->company_code, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= number_format((float) $item->declared_amount, 0, ',', ' '); ?></td>
                        <td><?= number_format((float) $item->expected_amount, 0, ',', ' '); ?></td>
                        <td class="<?= abs((float) $item->difference_amount) > 0.009 ? 'text-danger font-weight-bold' : ''; ?>">
                            <?= number_format((float) $item->difference_amount, 0, ',', ' '); ?>
                        </td>
                        <td><?= htmlspecialchars((string) $item->reason, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <span class="badge badge-<?= $badge; ?>">
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <?php if (!empty($item->review_reason)): ?>
                                <div class="small text-muted mt-1">
                                    <?= htmlspecialchars((string) $item->review_reason, ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($status === 'requires_review'): ?>
                                <?= form_open('Caisses/review_closure/' . $ekey . '/' . (int) $item->id, array('class' => 'mb-0')); ?>
                                    <div class="form-group mb-1">
                                        <select name="review_status" class="form-control form-control-sm" required>
                                            <option value="reviewed">Valider</option>
                                            <option value="rejected">Rejeter</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-1">
                                        <input type="text" name="review_reason" class="form-control form-control-sm"
                                               placeholder="Motif de décision" required maxlength="500">
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success">Enregistrer</button>
                                <?= form_close(); ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
