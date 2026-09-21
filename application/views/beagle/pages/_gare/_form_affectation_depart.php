<?php defined('BASEPATH') OR exit('No direct script access allowed');
$gares = !empty($gares) ? $gares : array();
$villes = !empty($villes) ? $villes : array();
$compagnies = !empty($compagnies) ? $compagnies : array();
$modal_suffix = !empty($modal_suffix) ? (string) $modal_suffix : 'main';
?>
<div class="form-group">
    <label>COMPAGNIE <span class="text-danger">*</span></label>
    <select name="_compgare" class="form-control form-control-sm" required>
        <option value="">— Choisir la compagnie —</option>
        <?php foreach ($compagnies as $compagnie): ?>
            <option value="<?= htmlspecialchars($compagnie->cle_compagnie, ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($compagnie->nom_compagnie, ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label class="d-block mb-2">MODE</label>
    <div class="custom-control custom-radio">
        <input type="radio" id="mode-affecter-dep-<?= htmlspecialchars($modal_suffix, ENT_QUOTES, 'UTF-8'); ?>"
               name="mode_affectation" value="affecter" class="custom-control-input" checked>
        <label class="custom-control-label" for="mode-affecter-dep-<?= htmlspecialchars($modal_suffix, ENT_QUOTES, 'UTF-8'); ?>">
            Affecter une gare physique existante
            <span class="text-muted small d-block">Nouveau code commercial pour cette compagnie, même lieu.</span>
        </label>
    </div>
    <div class="custom-control custom-radio mt-2">
        <input type="radio" id="mode-creer-dep-<?= htmlspecialchars($modal_suffix, ENT_QUOTES, 'UTF-8'); ?>"
               name="mode_affectation" value="creer" class="custom-control-input">
        <label class="custom-control-label" for="mode-creer-dep-<?= htmlspecialchars($modal_suffix, ENT_QUOTES, 'UTF-8'); ?>">
            Créer une nouvelle gare physique
        </label>
    </div>
</div>

<div class="form-group js-mode-affecter">
    <label>GARE PHYSIQUE <span class="text-danger">*</span></label>
    <select name="gareselect" class="form-control form-control-sm js-gare-physique" required>
        <option value="">— Choisir le lieu —</option>
        <?php foreach ($gares as $gnom):
            $label = $gnom->garenom
                . ' [' . $gnom->idengare . ']'
                . (!empty($gnom->nom_ville) ? (' — ' . $gnom->nom_ville) : '')
                . (!empty($gnom->nom_compagnie) ? (' · ' . $gnom->nom_compagnie) : '');
        ?>
            <option value="<?= htmlspecialchars($gnom->idengare, ENT_QUOTES, 'UTF-8'); ?>">
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group js-mode-affecter">
    <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" name="clone_sousgares" value="1"
               id="clone-sg-<?= htmlspecialchars($modal_suffix, ENT_QUOTES, 'UTF-8'); ?>">
        <label class="custom-control-label" for="clone-sg-<?= htmlspecialchars($modal_suffix, ENT_QUOTES, 'UTF-8'); ?>">
            Cloner les sous-gares d’une autre compagnie déjà sur ce lieu
        </label>
    </div>
</div>

<div class="form-group">
    <label>NOM COMMERCIAL <span class="js-mode-creer text-danger">*</span></label>
    <input class="form-control form-control-sm js-gare-nom"
           type="text"
           name="_nomgare"
           placeholder="Vide = nom de la gare physique (en affectation)"
           autocomplete="off">
</div>

<div class="row">
    <div class="form-group col-sm-6 js-mode-creer" style="display:none;">
        <label>LOCALISATION <span class="text-danger">*</span></label>
        <select name="_villegare" class="form-control form-control-sm js-gare-ville">
            <option value="">— Ville —</option>
            <?php foreach ($villes as $local): ?>
                <option value="<?= htmlspecialchars($local->id_ville, ENT_QUOTES, 'UTF-8'); ?>">
                    <?= htmlspecialchars($local->nom_ville, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group col-sm-6">
        <label>CONTACT</label>
        <input class="form-control form-control-sm" name="_contact" type="text" autocomplete="off"
               placeholder="Optionnel">
    </div>
</div>
