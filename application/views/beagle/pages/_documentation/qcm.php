<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
    .qcm-sheet { max-width: 900px; }
    .qcm-meta { border: 1px solid #333; padding: .75rem 1rem; margin-bottom: 1rem; }
    .qcm-meta .grid { display: flex; flex-wrap: wrap; gap: .75rem 1.5rem; }
    .qcm-meta label { font-weight: 600; margin-right: .35rem; }
    .qcm-meta .line { border-bottom: 1px solid #333; min-width: 180px; display: inline-block; height: 1.1em; }
    .qcm-q { margin: 1rem 0 1.25rem; page-break-inside: avoid; }
    .qcm-q .enonce { font-weight: 600; margin-bottom: .4rem; }
    .qcm-choices label { display: block; margin: .15rem 0 .15rem 1rem; font-weight: normal; }
    .qcm-bubble {
        display: inline-block; width: 1.1rem; height: 1.1rem; border: 1.5px solid #333;
        border-radius: 50%; margin-right: .4rem; vertical-align: -2px;
    }
    .qcm-answer {
        margin-top: .35rem; padding: .35rem .55rem; background: #e8f5e9; border-left: 3px solid #2e7d32;
        font-size: .92rem;
    }
    .qcm-footer-score { margin-top: 1.5rem; border-top: 2px solid #333; padding-top: .75rem; }
    .doc-actions { margin-bottom: 1rem; }
    @media print {
        .doc-actions, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer, .no-print { display: none !important; }
        .be-content, .main-content, .container-fluid { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        .qcm-sheet { max-width: none; font-size: 11pt; }
        .card { border: none !important; box-shadow: none !important; }
        a[href]:after { content: none !important; }
    }
</style>

<div class="row">
    <div class="col-12 qcm-sheet">
        <div class="doc-actions no-print">
            <a class="btn btn-secondary"
               href="<?= site_url('documentation/' . $this->session->company->ekey); ?>">
                ← Retour documentation
            </a>
            <a class="btn btn-secondary"
               href="<?= site_url('documentation/' . $this->session->company->ekey . '/manuel/' . rawurlencode($role_code)); ?>">
                Manuel
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print();">
                Imprimer la fiche
            </button>
            <?php if (!empty($can_corrige) && empty($show_answers)): ?>
                <a class="btn btn-warning"
                   href="<?= site_url('documentation/' . $this->session->company->ekey . '/qcm_corrige/' . rawurlencode($role_code)); ?>">
                    Voir le corrigé
                </a>
            <?php endif; ?>
            <?php if (!empty($show_answers)): ?>
                <a class="btn btn-info"
                   href="<?= site_url('documentation/' . $this->session->company->ekey . '/qcm/' . rawurlencode($role_code)); ?>">
                    Version candidat (sans réponses)
                </a>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-body">
                <h3 class="mb-2">
                    <?= htmlspecialchars($qcm['titre']); ?>
                    <?php if (!empty($show_answers)): ?>
                        <span class="badge badge-warning">CORRIGÉ FORMATEUR</span>
                    <?php endif; ?>
                </h3>
                <p class="mb-2">
                    <?= htmlspecialchars($this->company->nom_entreprise); ?>
                    — <?= htmlspecialchars($role_meta['sous_titre']); ?>
                </p>
                <p class="mb-3">
                    Durée : <strong><?= htmlspecialchars($qcm['duree']); ?></strong>
                    — <?= htmlspecialchars($qcm['bareme']); ?>
                </p>

                <div class="qcm-meta">
                    <div class="grid">
                        <div><label>Nom :</label><span class="line"></span></div>
                        <div><label>Prénom :</label><span class="line"></span></div>
                        <div><label>Gare :</label><span class="line"></span></div>
                        <div><label>Date :</label><span class="line"></span></div>
                        <div><label>Formateur :</label><span class="line"></span></div>
                    </div>
                </div>

                <?php $n = 0; foreach ($qcm['questions'] as $item): $n++; ?>
                    <div class="qcm-q">
                        <div class="enonce"><?= $n; ?>. <?= htmlspecialchars($item['q']); ?></div>
                        <div class="qcm-choices">
                            <?php foreach ($item['choices'] as $letter => $text): ?>
                                <label>
                                    <span class="qcm-bubble"></span>
                                    <strong><?= htmlspecialchars($letter); ?>.</strong>
                                    <?= htmlspecialchars($text); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (!empty($show_answers)): ?>
                            <div class="qcm-answer">
                                Réponse : <strong><?= htmlspecialchars($item['answer']); ?></strong>
                                — <?= htmlspecialchars($item['tip']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="qcm-footer-score">
                    <div class="grid" style="display:flex;flex-wrap:wrap;gap:1rem 2rem;">
                        <div><label>Score :</label> ______ / <?= count($qcm['questions']); ?></div>
                        <div><label>Appréciation :</label><span class="line" style="min-width:220px;"></span></div>
                        <div><label>Signature formateur :</label><span class="line" style="min-width:160px;"></span></div>
                        <div><label>Signature candidat :</label><span class="line" style="min-width:160px;"></span></div>
                    </div>
                    <?php if (empty($show_answers)): ?>
                        <p class="mt-3 mb-0 text-muted" style="font-size:.85rem;">
                            Cochez une seule réponse par question. Ne rien écrire hors des cases prévues.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
