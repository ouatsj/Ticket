<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$d = isset($detail) ? $detail : null;
$fmt = function ($n) {
    if ($n === null || $n === '') {
        return '—';
    }
    return number_format((float) $n, 0, ',', ' ') . ' F';
};
$retour = site_url('rapport_autre_vente/' . $this->session->company->ekey);
if (!empty($retour_qs)) {
    $retour .= '?' . $retour_qs;
}
?>
<div class="row">
    <div class="col-12">
        <div class="mb-3">
            <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars($retour); ?>">← Retour au rapport</a>
        </div>

        <?php if (!$d): ?>
            <div class="alert alert-warning">Informations introuvables.</div>
        <?php else: ?>
            <div class="card mb-3">
                <div class="card-header">Bénéficiaire du ticket</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Nom</dt>
                                <dd class="col-sm-8"><strong><?= htmlspecialchars($d->beneficiaire ?: '—'); ?></strong></dd>
                                <dt class="col-sm-4">Type client</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->type_client_libelle ?: ($d->type_client ?: '—')); ?></dd>
                                <dt class="col-sm-4">Contact</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->contact_client ?: '—'); ?></dd>
                                <dt class="col-sm-4">N° CNIB / pièce</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->num_CNIB ?: '—'); ?></dd>
                                <dt class="col-sm-4">Délivré le</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->date_delivre ?: '—'); ?></dd>
                                <dt class="col-sm-4">Lieu de délivrance</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->lieu_delivre ?: '—'); ?></dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Document</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->id_doc ?: '—'); ?></dd>
                                <dt class="col-sm-4">Date document</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->datedoc ?: '—'); ?></dd>
                                <dt class="col-sm-4">Commentaire</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->comment_client ?: '—'); ?></dd>
                                <dt class="col-sm-4">P/O ou n° CV</dt>
                                <dd class="col-sm-8"><strong><?= htmlspecialchars($d->pourordre ?: '—'); ?></strong></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Ticket &amp; départ</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">N° ticket</dt>
                                <dd class="col-sm-8"><code><?= htmlspecialchars($d->code_ticket); ?></code></dd>
                                <dt class="col-sm-4">Code passager</dt>
                                <dd class="col-sm-8"><code><?= htmlspecialchars($d->code_passager); ?></code></dd>
                                <dt class="col-sm-4">Date vente</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->datep_create ?: $d->dateenregistrement); ?></dd>
                                <dt class="col-sm-4">Siège</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->num_siege_categorie ?: '—'); ?></dd>
                                <dt class="col-sm-4">Transit</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($d->est_transit)): ?>
                                        <span class="badge badge-info">Oui</span>
                                        <small class="text-muted"><?= (int) $d->nb_legs; ?> ticket(s) liés</small>
                                    <?php else: ?>
                                        Non
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Départ</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->depart_libelle); ?></dd>
                                <dt class="col-sm-4">Compagnie</dt>
                                <dd class="col-sm-8">
                                    <?= htmlspecialchars($d->compagnie_nom ?? '—'); ?>
                                    <?php if (!empty($d->compagnie_exp_nom) && ($d->compagnie_exp_id ?? '') !== ($d->compagnie_id ?? '')): ?>
                                        <small class="text-muted">(exp. <?= htmlspecialchars($d->compagnie_exp_nom); ?>)</small>
                                    <?php endif; ?>
                                </dd>
                                <dt class="col-sm-4">Sous-gare départ</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->nomsousgare ?: '—'); ?></dd>
                                <dt class="col-sm-4">Gare vendeur</dt>
                                <dd class="col-sm-8"><?= htmlspecialchars($d->gare_nom ?: ($d->gare_code ?: '—')); ?></dd>
                                <dt class="col-sm-4">Vendeur (chef)</dt>
                                <dd class="col-sm-8">
                                    <?= htmlspecialchars($d->utilisateur ?? ''); ?>
                                    <?php if (!empty($d->role_libelle)): ?>
                                        <?php if (!empty($d->role_ok)): ?>
                                            <span class="badge badge-success"><?= htmlspecialchars($d->role_libelle); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-danger"><?= htmlspecialchars($d->role_libelle); ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (!empty($d->role_note)): ?>
                                        <br><small class="text-warning"><?= htmlspecialchars($d->role_note); ?></small>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Tarification</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-2">Prix saisi</dt>
                        <dd class="col-sm-2"><strong><?= htmlspecialchars($fmt($d->prix_saisi)); ?></strong></dd>
                        <dt class="col-sm-2">Prix programme</dt>
                        <dd class="col-sm-2"><?= htmlspecialchars($fmt($d->prix_programme)); ?></dd>
                        <dt class="col-sm-2">Écart</dt>
                        <dd class="col-sm-2"><?= htmlspecialchars($fmt($d->ecart)); ?></dd>
                        <dt class="col-sm-2">Type</dt>
                        <dd class="col-sm-4">
                            <?php if ($d->type_anomalie === 'Gratuit'): ?>
                                <span class="badge badge-warning">Gratuit</span>
                            <?php elseif ($d->type_anomalie === 'Hors tarif'): ?>
                                <span class="badge badge-danger">Hors tarif</span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= htmlspecialchars($d->type_anomalie); ?></span>
                            <?php endif; ?>
                        </dd>
                        <dt class="col-sm-2">Arrêt compte</dt>
                        <dd class="col-sm-4">
                            <?php if (!empty($d->arrete)): ?>
                                <span class="badge badge-secondary">Arrêté</span>
                            <?php else: ?>
                                <span class="badge badge-primary">Non arrêté</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
