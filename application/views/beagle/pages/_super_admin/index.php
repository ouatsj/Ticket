<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php if (!empty($notice)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<?php if (!empty($target_account_id) && !empty($users)): ?>
    <div class="mb-3">
        <a class="btn btn-secondary" href="<?= site_url(
            'utilisateurs/' . $this->session->company->ekey . '/gTv/'
            . $users[0]->uid . '/compte/' . mdate("%d/%m/%Y", now('UTC'))
        ); ?>">
            <span class="fas fa-arrow-circle-left"></span> Retour au compte
        </a>
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <strong>État du contrôle SuperAdmin</strong>
    </div>
    <div class="card-body">
        <p>
            Contrôle des permissions :
            <span class="badge badge-<?= $enforcement_enabled ? 'success' : 'secondary'; ?>">
                <?= $enforcement_enabled ? 'ACTIVÉ' : 'DÉSACTIVÉ'; ?>
            </span>
        </p>
        <p class="text-muted">
            Quand il est désactivé, les droits historiques des rôles restent appliqués.
            Le compte SuperAdmin conserve l’accès à cette page pour pouvoir le réactiver.
        </p>
        <?= form_open('super-administration/' . $this->session->company->ekey . '/controle'); ?>
            <input type="hidden" name="enabled" value="<?= $enforcement_enabled ? '0' : '1'; ?>">
            <button class="btn btn-<?= $enforcement_enabled ? 'warning' : 'success'; ?>" type="submit">
                <?= $enforcement_enabled ? 'Désactiver le contrôle' : 'Activer le contrôle'; ?>
            </button>
        <?= form_close(); ?>
    </div>
</div>

<?php if (!empty($sales_price_controls_enabled) && empty($target_account_id)): ?>
    <?php
    $sales_settings = isset($sales_settings) && is_array($sales_settings) ? $sales_settings : array();
    $setting_checked = function ($key, $default = '0') use ($sales_settings) {
        $value = array_key_exists($key, $sales_settings) ? $sales_settings[$key] : $default;
        return (string) $value === '1' ? 'checked' : '';
    };
    ?>
    <div class="card mb-4">
        <div class="card-header"><strong>Réglages des ventes à prix libre</strong></div>
        <div class="card-body">
            <?= form_open('super-administration/' . $this->session->company->ekey . '/reglages-ventes'); ?>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="free_price_enabled" value="1"
                               <?= $setting_checked('sales.free_price_enabled', '1'); ?>>
                        Autoriser la fonctionnalité de prix libre
                    </label>
                </div>
                <div class="form-group" style="max-width: 320px;">
                    <label for="discount-threshold">Seuil de réduction nécessitant une validation (%)</label>
                    <input id="discount-threshold" class="form-control" type="number"
                           name="discount_threshold" min="0" max="100" step="0.01"
                           value="<?= htmlspecialchars(
                               isset($sales_settings['sales.discount_threshold_percent'])
                                   ? $sales_settings['sales.discount_threshold_percent']
                                   : '20',
                               ENT_QUOTES,
                               'UTF-8'
                           ); ?>" required>
                </div>
                <div class="form-group">
                    <label class="d-block">
                        <input type="checkbox" name="discount_requires_approval" value="1"
                               <?= $setting_checked('sales.discount_requires_approval', '1'); ?>>
                        Exiger une validation au-delà du seuil
                    </label>
                    <label class="d-block">
                        <input type="checkbox" name="misc_requires_approval" value="1"
                               <?= $setting_checked('sales.misc_requires_approval', '1'); ?>>
                        Exiger une validation pour « Divers »
                    </label>
                    <label class="d-block">
                        <input type="checkbox" name="valid_card_zero_fare_enabled" value="1"
                               <?= $setting_checked('sales.valid_card_zero_fare_enabled', '1'); ?>>
                        Autoriser 0 F avec une carte de voyage valide
                    </label>
                    <label class="d-block">
                        <input type="checkbox" name="card_expiry_required" value="1"
                               <?= $setting_checked('sales.card_expiry_required', '1'); ?>>
                        Rendre obligatoire la date de péremption des cartes
                    </label>
                    <label class="d-block">
                        <input type="checkbox" name="post_print_edit_enabled" value="1"
                               <?= $setting_checked('sales.post_print_edit_enabled'); ?>>
                        Permettre les modifications après impression aux comptes autorisés
                    </label>
                </div>
                <button class="btn btn-primary" type="submit">Enregistrer les réglages</button>
            <?= form_close(); ?>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <strong>
            <?= !empty($target_account_id) && !empty($users)
                ? 'Permissions de ' . htmlspecialchars(
                    trim($users[0]->first_name . ' ' . $users[0]->last_name)
                    . ' (' . $users[0]->username . ')',
                    ENT_QUOTES,
                    'UTF-8'
                )
                : 'Permissions Audit et Documentation'; ?>
        </strong>
    </div>
    <div class="card-body">
        <p class="text-muted">
            Cochez les droits à accorder, puis enregistrez la ligne de l’utilisateur.
            Une case décochée retire explicitement le droit.
        </p>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Utilisateur</th>
                    <?php foreach ($permissions as $permission): ?>
                        <th><?= htmlspecialchars($permission->permission_label, ENT_QUOTES, 'UTF-8'); ?></th>
                    <?php endforeach; ?>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $accountId = (int) $user->cpuser_id;
                    $formId = 'permissions-user-' . $accountId;
                    $roles = array_filter(explode(',', (string) $user->role_codes));
                    $legacyAdmin = in_array('1', $roles, TRUE) || in_array('2', $roles, TRUE);
                    ?>
                    <tr>
                        <td>
                            <?= form_open(
                                'super-administration/' . $this->session->company->ekey . '/permissions/' . $accountId,
                                array('id' => $formId)
                            ); ?>
                            <?php if (!empty($target_account_id)): ?>
                                <input type="hidden" name="return_cpuser_id" value="<?= $accountId; ?>">
                            <?php endif; ?>
                            <?= form_close(); ?>
                            <strong>
                                <?= htmlspecialchars(trim($user->first_name . ' ' . $user->last_name), ENT_QUOTES, 'UTF-8'); ?>
                            </strong><br>
                            <small><?= htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?></small>
                        </td>
                        <?php foreach ($permissions as $permission): ?>
                            <?php
                            $code = (string) $permission->permission_code;
                            $explicit = isset($assignments[$accountId])
                                && array_key_exists($code, $assignments[$accountId]);
                            if ($explicit) {
                                $checked = (bool) $assignments[$accountId][$code];
                            } else {
                                $legacyAdminPermissions = array(
                                    'audit.view',
                                    'audit.generate',
                                    'documentation.answers',
                                );
                                $checked = $code === 'documentation.view'
                                    || ($legacyAdmin && in_array($code, $legacyAdminPermissions, TRUE));
                            }
                            ?>
                            <td class="text-center">
                                <input type="checkbox"
                                       form="<?= $formId; ?>"
                                       name="permissions[]"
                                       value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>"
                                       <?= $checked ? 'checked' : ''; ?>>
                                <?php if (!$explicit): ?>
                                    <br><small class="text-muted">droit historique</small>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <button class="btn btn-sm btn-primary" form="<?= $formId; ?>" type="submit">
                                Enregistrer
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

