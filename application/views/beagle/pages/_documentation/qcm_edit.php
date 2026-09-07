<?php defined('BASEPATH') OR exit('No direct script access allowed');
$ekey = $this->session->company->ekey;
$has_override = !empty($qcm['_has_override']);
$questions = !empty($qcm['questions']) ? $qcm['questions'] : array();
// Une ligne vide pour ajouter une question
$questions[] = array(
    'q' => '',
    'choices' => array('A' => '', 'B' => '', 'C' => '', 'D' => ''),
    'answer' => 'A',
    'tip' => '',
);
?>
<style>
.qcm-edit-q {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
    background: #fafbfc;
}
.qcm-edit-q h5 { margin-bottom: .75rem; }
.qcm-edit-q .choice-row { display: flex; gap: .5rem; align-items: center; margin-bottom: .4rem; }
.qcm-edit-q .choice-row .letter {
    width: 1.6rem; font-weight: 700; text-align: center;
}
.qcm-edit-actions .btn { margin-right: .35rem; margin-bottom: .35rem; }
</style>

<div class="row">
    <div class="col-12">
        <div class="qcm-edit-actions mb-3">
            <a class="btn btn-secondary"
               href="<?= site_url('documentation/' . $ekey . '/qcm/' . rawurlencode($role_code)); ?>">
                ← Voir le QCM
            </a>
            <a class="btn btn-info"
               href="<?= site_url('documentation/' . $ekey . '/qcm_export/' . rawurlencode($role_code)); ?>">
                Exporter JSON
            </a>
            <?php if ($has_override): ?>
                <a class="btn btn-warning"
                   href="<?= site_url('documentation/' . $ekey . '/qcm_reset/' . rawurlencode($role_code)); ?>"
                   onclick="return confirm('Revenir au QCM standard (supprimer les modifications) ?');">
                    Réinitialiser (version standard)
                </a>
            <?php endif; ?>
            <a class="btn btn-secondary"
               href="<?= site_url('documentation/' . $ekey . '/roles'); ?>">
                Formation par rôle
            </a>
        </div>

        <?php if (!empty($flash_ok)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash_ok, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if (!empty($flash_err)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flash_err, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-header">
                Modifier le QCM — <?= htmlspecialchars($role_meta['titre'], ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($has_override): ?>
                    <span class="badge badge-info">Version personnalisée active</span>
                <?php else: ?>
                    <span class="badge badge-secondary">Version standard</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Modifiez le titre, la durée, le barème et chaque question.
                    Laissez l’énoncé vide pour supprimer une question.
                    La dernière ligne vide sert à en ajouter une nouvelle.
                    Vous pouvez aussi <strong>exporter</strong> le JSON, le modifier hors ligne, puis le <strong>réimporter</strong>.
                </p>

                <?= form_open_multipart(
                    'documentation/' . $ekey . '/qcm_import/' . rawurlencode($role_code),
                    array('class' => 'form-inline mb-4 p-3 border rounded bg-light')
                ); ?>
                    <label class="mr-2 mb-0" for="qcm_file">Importer un JSON</label>
                    <input class="form-control-file mr-2" type="file" name="qcm_file" id="qcm_file" accept=".json,application/json" required>
                    <button type="submit" class="btn btn-sm btn-primary">Importer</button>
                <?= form_close(); ?>

                <?= form_open(
                    'documentation/' . $ekey . '/qcm_save/' . rawurlencode($role_code),
                    array('class' => 'form', 'id' => 'qcmEditForm')
                ); ?>

                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="titre">Titre</label>
                            <input class="form-control" type="text" name="titre" id="titre" required
                                   value="<?= htmlspecialchars($qcm['titre'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="duree">Durée</label>
                            <input class="form-control" type="text" name="duree" id="duree"
                                   value="<?= htmlspecialchars($qcm['duree'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bareme">Barème</label>
                        <input class="form-control" type="text" name="bareme" id="bareme"
                               value="<?= htmlspecialchars($qcm['bareme'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <hr>
                    <h4 class="mb-3">Questions</h4>

                    <?php foreach ($questions as $i => $item): ?>
                        <?php
                        $is_blank = ($item['q'] === '');
                        $num = $is_blank ? 'Nouvelle' : ((string) ($i + 1));
                        ?>
                        <div class="qcm-edit-q">
                            <h5>
                                <?= $is_blank ? '➕ Ajouter une question' : ('Question ' . $num); ?>
                            </h5>
                            <div class="form-group">
                                <label>Énoncé</label>
                                <textarea class="form-control" name="q[<?= (int) $i; ?>]" rows="2"
                                          placeholder="<?= $is_blank ? 'Saisir un énoncé pour ajouter…' : ''; ?>"><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <?php foreach (array('A', 'B', 'C', 'D') as $letter): ?>
                                <?php $field = 'choice_' . strtolower($letter); ?>
                                <div class="choice-row">
                                    <span class="letter"><?= $letter; ?>.</span>
                                    <input class="form-control form-control-sm" type="text"
                                           name="<?= $field; ?>[<?= (int) $i; ?>]"
                                           value="<?= htmlspecialchars(
                                               isset($item['choices'][$letter]) ? $item['choices'][$letter] : '',
                                               ENT_QUOTES,
                                               'UTF-8'
                                           ); ?>"
                                           placeholder="Choix <?= $letter; ?>">
                                </div>
                            <?php endforeach; ?>
                            <div class="form-row mt-2">
                                <div class="form-group col-sm-3 mb-2">
                                    <label>Bonne réponse</label>
                                    <select class="form-control form-control-sm" name="answer[<?= (int) $i; ?>]">
                                        <?php foreach (array('A', 'B', 'C', 'D') as $letter): ?>
                                            <option value="<?= $letter; ?>"
                                                <?= (isset($item['answer']) && $item['answer'] === $letter) ? 'selected' : ''; ?>>
                                                <?= $letter; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-9 mb-2">
                                    <label>Explication (corrigé)</label>
                                    <input class="form-control form-control-sm" type="text"
                                           name="tip[<?= (int) $i; ?>]"
                                           value="<?= htmlspecialchars(isset($item['tip']) ? $item['tip'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">
                            Enregistrer les modifications
                        </button>
                        <a class="btn btn-secondary"
                           href="<?= site_url('documentation/' . $ekey . '/qcm/' . rawurlencode($role_code)); ?>">
                            Annuler
                        </a>
                    </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
