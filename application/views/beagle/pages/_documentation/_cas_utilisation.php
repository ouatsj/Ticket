<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Contenu Documentation générale (cas d'utilisation décideurs).
 * Attend : $doc_generale (retour de documentation_generale_cas_utilisation())
 */
$doc = !empty($doc_generale) ? $doc_generale : documentation_generale_cas_utilisation();
?>
<style>
    .doc-generale .cas-card {
        border: 1px solid #e3e6ea;
        border-radius: 4px;
        margin-bottom: 1.25rem;
        background: #fff;
    }
    .doc-generale .cas-card h4 {
        margin: 0;
        padding: .75rem 1rem;
        background: #f5f7fa;
        border-bottom: 1px solid #e3e6ea;
        font-size: 1.05rem;
    }
    .doc-generale .cas-body { padding: 1rem; }
    .doc-generale .cas-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #6c757d;
        margin-bottom: .25rem;
        font-weight: 600;
    }
    .doc-generale .cas-block { margin-bottom: .85rem; }
    .doc-generale .principes {
        background: #f8f9fb;
        border-left: 4px solid #4285f4;
        padding: .85rem 1rem;
        margin-bottom: 1.5rem;
    }
    .doc-generale .sommaire a { display: block; padding: .2rem 0; }
    @media print {
        .doc-actions, .nav-tabs, .navbar, .be-left-sidebar, .be-top-header, .page-head, footer { display: none !important; }
        .tab-pane { display: block !important; opacity: 1 !important; }
        .cas-card { break-inside: avoid; }
    }
</style>

<div class="doc-generale">
    <div class="doc-actions no-print mb-3">
        <button type="button" class="btn btn-primary" onclick="window.print();">
            Imprimer la documentation générale
        </button>
        <a class="btn btn-secondary"
           href="<?= site_url('documentation/' . $this->session->company->ekey . '/manuel/general'); ?>">
            Repères techniques (manuel)
        </a>
    </div>

    <h3 class="mb-3"><?= htmlspecialchars($doc['titre']); ?></h3>

    <?php foreach ($doc['intro'] as $p): ?>
        <p><?= htmlspecialchars($p); ?></p>
    <?php endforeach; ?>

    <div class="principes">
        <div class="cas-label">Principes directeurs</div>
        <ul class="mb-0">
            <?php foreach ($doc['principes'] as $pr): ?>
                <li><?= htmlspecialchars($pr); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="sommaire mb-4 no-print">
        <div class="cas-label">Sommaire des cas d'utilisation</div>
        <?php foreach ($doc['cas'] as $cas): ?>
            <a href="#cas-<?= htmlspecialchars($cas['id']); ?>"><?= htmlspecialchars($cas['titre']); ?></a>
        <?php endforeach; ?>
    </div>

    <?php foreach ($doc['cas'] as $cas): ?>
        <article class="cas-card" id="cas-<?= htmlspecialchars($cas['id']); ?>">
            <h4><?= htmlspecialchars($cas['titre']); ?></h4>
            <div class="cas-body">
                <div class="cas-block">
                    <div class="cas-label">Objectif</div>
                    <p class="mb-0"><?= htmlspecialchars($cas['objectif']); ?></p>
                </div>
                <div class="cas-block">
                    <div class="cas-label">Acteurs</div>
                    <ul class="mb-0">
                        <?php foreach ($cas['acteurs'] as $a): ?>
                            <li><?= htmlspecialchars($a); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="cas-block">
                    <div class="cas-label">Procédure d'utilisation</div>
                    <ol class="mb-0">
                        <?php foreach ($cas['etapes'] as $e): ?>
                            <li><?= htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <div class="cas-block">
                    <div class="cas-label">Résultat attendu</div>
                    <p class="mb-0"><?= htmlspecialchars($cas['resultat']); ?></p>
                </div>
                <div class="cas-block mb-0">
                    <div class="cas-label">Points de contrôle (décideur)</div>
                    <ul class="mb-0">
                        <?php foreach ($cas['controles'] as $c): ?>
                            <li><?= htmlspecialchars($c); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</div>
