<?php defined('BASEPATH') OR exit('No direct script access allowed');
$compagnies = isset($compagnies) ? $compagnies : array();
$cle = isset($cle) ? (string) $cle : '';
$familles = isset($familles) ? $familles : array();
$effectives = isset($effectives) ? $effectives : array();
$declarees = isset($declarees) ? $declarees : array();
$mois = isset($mois) ? $mois : date('Y-m');
$moisMin = isset($mois_min) ? $mois_min : date('Y-m');
$ckey = $this->session->company->ekey;
$lettresPossibles = array('A', 'B', 'C', 'D', 'E');
?>
<div class="row">
    <div class="col-lg-10 col-12">
        <div class="card">
            <div class="card-header">Lettres des états, par compagnie</div>
            <div class="card-body">
                <p class="text-muted">
                    Le réglage s'applique à la compagnie choisie, à partir du mois indiqué.
                    Les mois déjà passés et les mois déjà déclarés ne changent pas.
                    Les états globaux, les récapitulatifs globaux et les listes globales restent sur toutes les lettres.
                    Déclarer continue de lire les tampons déjà posés.
                </p>

                <?php if (!empty($saved)): ?>
                    <div class="alert alert-success">Règle enregistrée pour cette compagnie.</div>
                <?php endif; ?>
                <?php if (!empty($erreur)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($erreur); ?></div>
                <?php endif; ?>

                <?php if (!$compagnies): ?>
                    <div class="alert alert-warning">Aucune compagnie pour cette entreprise.</div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="cle_vue">Compagnie</label>
                        <select class="form-control" id="cle_vue"
                                onchange="window.location = '<?= site_url('param_lettres/' . $ckey); ?>?cle=' + encodeURIComponent(this.value);">
                            <?php foreach ($compagnies as $c): ?>
                                <option value="<?= htmlspecialchars($c->cle_compagnie); ?>"
                                    <?= ((string) $c->cle_compagnie === $cle) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c->nom_compagnie . ' — ' . $c->cle_compagnie); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?= form_open(site_url('param_lettres/' . $ckey . '/save')); ?>
                        <input type="hidden" name="cle" value="<?= htmlspecialchars($cle); ?>">

                        <div class="form-group">
                            <label for="mois">Mois de début</label>
                            <input type="month" class="form-control" id="mois" name="mois"
                                   min="<?= htmlspecialchars($moisMin); ?>"
                                   value="<?= htmlspecialchars($mois); ?>" required>
                            <small class="form-text text-muted">Mois en cours ou mois à venir, encore non déclaré.</small>
                        </div>

                        <?php foreach ($familles as $code => $libelle):
                            $regle = isset($effectives[$code]) ? $effectives[$code] : array('lettres' => array(), 'toutes' => false);
                            $actif = !empty($regle['toutes']) ? 'toutes les lettres' : implode(', ', $regle['lettres']);
                            $deja = !empty($declarees[$code]);
                        ?>
                            <fieldset class="border rounded p-3 mb-3">
                                <legend class="w-auto px-2" style="font-size:1rem;"><?= htmlspecialchars($libelle); ?></legend>
                                <p class="text-muted mb-2">
                                    En vigueur ce mois : <strong><?= htmlspecialchars($actif !== '' ? $actif : 'aucune'); ?></strong>
                                    <?php if ($deja): ?>
                                        — ce mois est déjà déclaré, une nouvelle règle ne peut pas commencer ce mois-ci.
                                    <?php endif; ?>
                                </p>
                                <div class="form-group mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                               id="toutes_<?= htmlspecialchars($code); ?>"
                                               name="famille[<?= htmlspecialchars($code); ?>][toutes]" value="1"
                                            <?= !empty($regle['toutes']) ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="toutes_<?= htmlspecialchars($code); ?>">
                                            Toutes les lettres (y compris une lettre vide)
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <?php foreach ($lettresPossibles as $lettre): ?>
                                        <div class="custom-control custom-checkbox custom-control-inline">
                                            <input type="checkbox" class="custom-control-input"
                                                   id="l_<?= htmlspecialchars($code . '_' . $lettre); ?>"
                                                   name="famille[<?= htmlspecialchars($code); ?>][lettres][]"
                                                   value="<?= $lettre; ?>"
                                                <?= in_array($lettre, $regle['lettres'], true) ? 'checked' : ''; ?>>
                                            <label class="custom-control-label"
                                                   for="l_<?= htmlspecialchars($code . '_' . $lettre); ?>"><?= $lettre; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary">Enregistrer pour cette compagnie</button>
                    <?= form_close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
