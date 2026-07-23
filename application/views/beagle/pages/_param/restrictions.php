<?php defined('BASEPATH') OR exit('No direct script access allowed');
$params = isset($params) ? $params : array();
$labels = isset($labels) ? $labels : array();
$types = isset($types) ? $types : array();
$db_overrides = isset($db_overrides) ? $db_overrides : array();
$gares_choices = isset($gares_choices) ? $gares_choices : array();
$ckey = $this->session->company->ekey;

$caissier_gares = isset($params['restriction_caissier_gares']) && is_array($params['restriction_caissier_gares'])
    ? $params['restriction_caissier_gares'] : array();
$sup_gares = isset($params['restriction_sup_agence_gares']) && is_array($params['restriction_sup_agence_gares'])
    ? $params['restriction_sup_agence_gares'] : array();
$vendeur_gares = isset($params['restriction_vendeur_gares']) && is_array($params['restriction_vendeur_gares'])
    ? $params['restriction_vendeur_gares'] : array();

$cais_delai_map = isset($params['restriction_caissier_delai_par_gare']) && is_array($params['restriction_caissier_delai_par_gare'])
    ? $params['restriction_caissier_delai_par_gare'] : array();
$sup_delai_map = isset($params['restriction_sup_agence_delai_par_gare']) && is_array($params['restriction_sup_agence_delai_par_gare'])
    ? $params['restriction_sup_agence_delai_par_gare'] : array();
$vend_delai_map = isset($params['restriction_vendeur_delai_par_gare']) && is_array($params['restriction_vendeur_delai_par_gare'])
    ? $params['restriction_vendeur_delai_par_gare'] : array();

$cais_def = (int) (isset($params['restriction_caissier_delai_jour']) ? $params['restriction_caissier_delai_jour'] : 10);
$sup_def = (int) (isset($params['restriction_sup_agence_delai_jour']) ? $params['restriction_sup_agence_delai_jour'] : 20);
$vend_def = (int) (isset($params['restriction_vendeur_delai_heures']) ? $params['restriction_vendeur_delai_heures'] : 48);
?>
<div class="row">
    <div class="col-lg-8 col-12">
        <div class="card">
            <div class="card-header">Restrictions comptes utilisateurs</div>
            <div class="card-body">
                <p class="text-muted">
                    Ces valeurs sont enregistrées en base et prises en compte immédiatement
                    (y compris par le cron). Sans valeur en base, le fichier de config reste utilisé.
                </p>

                <?php if (!empty($saved)): ?>
                    <div class="alert alert-success">Paramètres enregistrés.</div>
                <?php endif; ?>

                <?= form_open(site_url('param_restrictions/' . $ckey . '/save')); ?>

                    <h6 class="mt-2 mb-3">Inactivité / désactivation</h6>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="cron_inact"
                                   name="compte_arret_inactivite_cron" value="1"
                                <?= !empty($params['compte_arret_inactivite_cron']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="cron_inact">
                                <?= htmlspecialchars($labels['compte_arret_inactivite_cron']); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="jours"><?= htmlspecialchars($labels['compte_desactivation_jours']); ?></label>
                        <input type="number" min="1" max="90" class="form-control" id="jours"
                               name="compte_desactivation_jours"
                               value="<?= (int) $params['compte_desactivation_jours']; ?>">
                        <small class="form-text text-muted">Ex. 5 = désactivation après 5 jours sans activité.</small>
                    </div>

                    <h6 class="mt-4 mb-3">Sessions</h6>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="sess_auto"
                                   name="session_deconnexion_auto" value="1"
                                <?= !empty($params['session_deconnexion_auto']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="sess_auto">
                                <?= htmlspecialchars($labels['session_deconnexion_auto']); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="minutes"><?= htmlspecialchars($labels['session_inactivite_minutes']); ?></label>
                        <input type="number" min="5" max="1440" class="form-control" id="minutes"
                               name="session_inactivite_minutes"
                               value="<?= (int) $params['session_inactivite_minutes']; ?>">
                    </div>

                    <h6 class="mt-4 mb-3">Arrêt chef de guichet</h6>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="chef_obl"
                                   name="chef_arret_obligatoire" value="1"
                                <?= !empty($params['chef_arret_obligatoire']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="chef_obl">
                                <?= htmlspecialchars($labels['chef_arret_obligatoire']); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="heures"><?= htmlspecialchars($labels['chef_arret_delai_heures']); ?></label>
                        <input type="number" min="1" max="168" class="form-control" id="heures"
                               name="chef_arret_delai_heures"
                               value="<?= (int) $params['chef_arret_delai_heures']; ?>">
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-2">Caissiers (rôle 4) — blocage par gare</h6>
                    <p class="text-muted small mb-3">
                        Si activé : désactivation d’inactivité ciblée + <strong>blocage</strong> si l’arrêt
                        de caisse du mois M n’est pas fait avant le jour configuré du mois M+1.
                        Cochez une gare et indiquez son délai (jour). Aucune gare cochée = toutes les gares
                        (délai par défaut ci-dessous).
                    </p>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="cais_en"
                                   name="restriction_caissier_enabled" value="1"
                                <?= !empty($params['restriction_caissier_enabled']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="cais_en">
                                <?= htmlspecialchars($labels['restriction_caissier_enabled']); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cais_jour"><?= htmlspecialchars($labels['restriction_caissier_delai_jour']); ?></label>
                        <input type="number" min="1" max="28" class="form-control" id="cais_jour"
                               name="restriction_caissier_delai_jour"
                               value="<?= $cais_def; ?>">
                        <small class="form-text text-muted">
                            Utilisé si aucune valeur spécifique n’est saisie pour une gare cochée.
                        </small>
                    </div>

                    <div class="form-group border rounded p-3" style="max-height: 280px; overflow: auto;">
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle-gares="cais">Tout cocher</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-gares="cais">Tout décocher</button>
                        </div>
                        <?php foreach ($gares_choices as $gid => $info): ?>
                            <?php
                            $checked = in_array($gid, $caissier_gares, true);
                            $day_g = isset($cais_delai_map[$gid]) ? (int) $cais_delai_map[$gid] : $cais_def;
                            ?>
                            <div class="d-flex align-items-center flex-wrap mb-2 gare-row-cais">
                                <div class="custom-control custom-checkbox mr-3 mb-0">
                                    <input type="checkbox" class="custom-control-input gare-cais"
                                           id="cais_<?= htmlspecialchars($gid); ?>"
                                           name="restriction_caissier_gares[]"
                                           value="<?= htmlspecialchars($gid); ?>"
                                        <?= $checked ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="cais_<?= htmlspecialchars($gid); ?>">
                                        <?= htmlspecialchars($info['label']); ?>
                                    </label>
                                </div>
                                <div class="input-group input-group-sm" style="width: 110px;">
                                    <input type="number" min="1" max="28" class="form-control"
                                           name="restriction_caissier_delai_par_gare[<?= htmlspecialchars($gid); ?>]"
                                           value="<?= $day_g; ?>"
                                           title="Jour du mois suivant">
                                    <div class="input-group-append">
                                        <span class="input-group-text">j</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($gares_choices)): ?>
                            <p class="text-muted mb-0 small">Aucune gare trouvée.</p>
                        <?php endif; ?>
                    </div>

                    <h6 class="mt-4 mb-2">Superviseurs d’agence (rôle 13) — blocage par gare</h6>
                    <p class="text-muted small mb-3">
                        Si activé : <strong>blocage</strong> si les éléments arrêtés par le caissier (mois M)
                        ne sont pas validés avant le jour configuré du mois M+1.
                        Délai configurable par gare cochée.
                    </p>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="sup_en"
                                   name="restriction_sup_agence_enabled" value="1"
                                <?= !empty($params['restriction_sup_agence_enabled']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="sup_en">
                                <?= htmlspecialchars($labels['restriction_sup_agence_enabled']); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="sup_jour"><?= htmlspecialchars($labels['restriction_sup_agence_delai_jour']); ?></label>
                        <input type="number" min="1" max="28" class="form-control" id="sup_jour"
                               name="restriction_sup_agence_delai_jour"
                               value="<?= $sup_def; ?>">
                    </div>

                    <div class="form-group border rounded p-3" style="max-height: 280px; overflow: auto;">
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle-gares="sup">Tout cocher</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-gares="sup">Tout décocher</button>
                        </div>
                        <?php foreach ($gares_choices as $gid => $info): ?>
                            <?php
                            $checked = in_array($gid, $sup_gares, true);
                            $day_g = isset($sup_delai_map[$gid]) ? (int) $sup_delai_map[$gid] : $sup_def;
                            ?>
                            <div class="d-flex align-items-center flex-wrap mb-2">
                                <div class="custom-control custom-checkbox mr-3 mb-0">
                                    <input type="checkbox" class="custom-control-input gare-sup"
                                           id="sup_<?= htmlspecialchars($gid); ?>"
                                           name="restriction_sup_agence_gares[]"
                                           value="<?= htmlspecialchars($gid); ?>"
                                        <?= $checked ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="sup_<?= htmlspecialchars($gid); ?>">
                                        <?= htmlspecialchars($info['label']); ?>
                                    </label>
                                </div>
                                <div class="input-group input-group-sm" style="width: 110px;">
                                    <input type="number" min="1" max="28" class="form-control"
                                           name="restriction_sup_agence_delai_par_gare[<?= htmlspecialchars($gid); ?>]"
                                           value="<?= $day_g; ?>"
                                           title="Jour du mois suivant">
                                    <div class="input-group-append">
                                        <span class="input-group-text">j</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <h6 class="mt-4 mb-2">Vendeurs (rôles 6 / 10 / 12 / 15 / 17) — blocage par gare</h6>
                    <p class="text-muted small mb-3">
                        Si activé : <strong>blocage</strong> si l’arrêt de compte n’est pas fait, ou si l’arrêt
                        soumis dépasse le délai (heures) sans validation chef. Délai configurable par gare.
                    </p>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="vend_en"
                                   name="restriction_vendeur_enabled" value="1"
                                <?= !empty($params['restriction_vendeur_enabled']) ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="vend_en">
                                <?= htmlspecialchars($labels['restriction_vendeur_enabled']); ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="vend_heures"><?= htmlspecialchars($labels['restriction_vendeur_delai_heures']); ?></label>
                        <input type="number" min="1" max="168" class="form-control" id="vend_heures"
                               name="restriction_vendeur_delai_heures"
                               value="<?= $vend_def; ?>">
                        <small class="form-text text-muted">
                            Ex. <strong>48</strong> = après arrêt, le vendeur est bloqué si non validé sous 48 h.
                        </small>
                    </div>

                    <div class="form-group border rounded p-3" style="max-height: 280px; overflow: auto;">
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle-gares="vend">Tout cocher</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-gares="vend">Tout décocher</button>
                        </div>
                        <?php foreach ($gares_choices as $gid => $info): ?>
                            <?php
                            $checked = in_array($gid, $vendeur_gares, true);
                            $h_g = isset($vend_delai_map[$gid]) ? (int) $vend_delai_map[$gid] : $vend_def;
                            ?>
                            <div class="d-flex align-items-center flex-wrap mb-2">
                                <div class="custom-control custom-checkbox mr-3 mb-0">
                                    <input type="checkbox" class="custom-control-input gare-vend"
                                           id="vend_<?= htmlspecialchars($gid); ?>"
                                           name="restriction_vendeur_gares[]"
                                           value="<?= htmlspecialchars($gid); ?>"
                                        <?= $checked ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="vend_<?= htmlspecialchars($gid); ?>">
                                        <?= htmlspecialchars($info['label']); ?>
                                    </label>
                                </div>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <input type="number" min="1" max="168" class="form-control"
                                           name="restriction_vendeur_delai_par_gare[<?= htmlspecialchars($gid); ?>]"
                                           value="<?= $h_g; ?>"
                                           title="Heures après arrêt">
                                    <div class="input-group-append">
                                        <span class="input-group-text">h</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="card">
            <div class="card-header">Valeurs actuelles</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <?php foreach ($params as $k => $v): ?>
                        <li class="mb-2">
                            <strong><?= htmlspecialchars(isset($labels[$k]) ? $labels[$k] : $k); ?></strong><br>
                            <?php if (!empty($types[$k]) && $types[$k] === 'bool'): ?>
                                <?= $v ? 'Oui' : 'Non'; ?>
                            <?php elseif (!empty($types[$k]) && $types[$k] === 'list'): ?>
                                <?php
                                $list = is_array($v) ? $v : array();
                                echo empty($list)
                                    ? '<em>Toutes les gares (si restriction activée)</em>'
                                    : htmlspecialchars(implode(', ', $list));
                                ?>
                            <?php elseif (!empty($types[$k]) && $types[$k] === 'map'): ?>
                                <?php
                                $mapv = is_array($v) ? $v : array();
                                if (empty($mapv)) {
                                    echo '<em>Aucun override</em>';
                                } else {
                                    $parts = array();
                                    foreach ($mapv as $gk => $gv) {
                                        $parts[] = $gk . '=' . (int) $gv;
                                    }
                                    echo htmlspecialchars(implode(', ', $parts));
                                }
                                ?>
                            <?php else: ?>
                                <?= htmlspecialchars((string) $v); ?>
                            <?php endif; ?>
                            <?php if (array_key_exists($k, $db_overrides)): ?>
                                <span class="badge badge-info">Paramètres</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Fichier config</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    function setAll(cls, checked) {
        document.querySelectorAll('.' + cls).forEach(function (el) { el.checked = checked; });
    }
    document.querySelectorAll('[data-toggle-gares]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setAll('gare-' + btn.getAttribute('data-toggle-gares'), true);
        });
    });
    document.querySelectorAll('[data-clear-gares]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setAll('gare-' + btn.getAttribute('data-clear-gares'), false);
        });
    });
})();
</script>
