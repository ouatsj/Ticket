<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">

    <div class="col-lg-8">
        
        <? if (!empty($itineraires)): ?>

            <div class="card card-table">

                <div class="card-header">
                    <strong>Composition transit</strong>
                    <span class="text-muted"> — chaque étape est un itinéraire (ligne) existant</span>
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>
                        <tr>
                            <th>#</th>
                            <th>LIGNE CONTENEUR</th>
                            <th>ORDRE</th>
                            <th>ITINÉRAIRE (JAMBE)</th>
                            <th>DÉPART</th>
                            <th>ARRIVÉE</th>
                            <th class="actions">ACTION</th>
                        </tr>
                        </thead>

                        <tbody>
                        <? foreach ($itineraires as $item): ?>
                            <tr>
                                <td><?= (int) $item->id_tabitinligne; ?></td>
                                <td><?= htmlspecialchars($item->nom_ligne); ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($item->id_lignes); ?></small>
                                </td>
                                <td><?= (int) $item->ordre_etape; ?></td>
                                <td><?= htmlspecialchars($item->nom_itineraires); ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($item->code_itineraires); ?></small>
                                </td>
                                <td><?= htmlspecialchars($item->depart_itine); ?></td>
                                <td><?= htmlspecialchars($item->arrive_itine); ?></td>
                                <td class="actions">
                                    <a href="<?= site_url('Lignes/activeit/' . $this->session->company->ekey . '/' . $item->id_itineraire . '/' . $item->id_tabitinligne . '/0/' . $item->actifint); ?>"
                                       class="btn btn-sm btn-secondary">
                                        <?= ($item->actifint == '1' || $item->actifint === 1)
                                            ? '<span class="text-danger">désactiver</span>'
                                            : '<span class="text-success">activer</span>' ?>
                                    </a>
                                </td>
                            </tr>
                        <? endforeach; ?>
                        </tbody>

                    </table>

                </div>

            </div>
        
        <? else: ?>

            <div class="card">
                <div class="card-header card-header-divider">
                    <h1 class="text-info text-center"><?= $this->session->company->nom_entreprise; ?></h1>
                </div>
                <div class="card-body">
                    <p class="text-warning text-center">AUCUNE COMPOSITION TRANSIT</p>
                </div>
            </div>
        
        <? endif; ?>

        <div class="card card-table mt-3">
            <div class="card-header">
                <strong>Escales tarifées</strong>
                <span class="text-muted"> — définies sur un itinéraire, sans programme dédié</span>
            </div>
            <div class="card-body table-responsive">
                <? if (!empty($escales)): ?>
                <table class="table table-striped table-hover" id="table-escales">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>ITINÉRAIRE PARENT</th>
                        <th>ORDRE</th>
                        <th>ESCALE (DESTINATION)</th>
                        <th>PRIX</th>
                        <th class="actions">ACTION</th>
                    </tr>
                    </thead>
                    <tbody>
                    <? foreach ($escales as $esc): ?>
                        <tr>
                            <td><?= (int) $esc->id_escale; ?></td>
                            <td>
                                <?= htmlspecialchars($esc->nom_ligne_parent); ?><br>
                                <small class="text-muted"><?= htmlspecialchars($esc->id_lignes); ?></small>
                            </td>
                            <td><?= (int) $esc->ordre_escale; ?></td>
                            <td>
                                <?= htmlspecialchars($esc->nom_escale); ?><br>
                                <small class="text-muted"><?= htmlspecialchars($esc->code_gadest); ?></small>
                            </td>
                            <td><?= number_format((float) $esc->prix_escale, 0, '', ' '); ?></td>
                            <td class="actions">
                                <a href="#escale-edit-<?= (int) $esc->id_escale; ?>"
                                   class="md-trigger" data-modal="escale-edit-<?= (int) $esc->id_escale; ?>">
                                    <span class="fas fa-edit text-warning"></span>
                                </a>
                                &nbsp;
                                <a href="<?= site_url('lignes/escales/' . $this->session->company->ekey . '/toggle/' . $esc->id_escale . '/' . $esc->actif_escale); ?>"
                                   class="btn btn-sm btn-secondary">
                                    <?= ($esc->actif_escale == '1' || $esc->actif_escale === 1)
                                        ? '<span class="text-danger">désactiver</span>'
                                        : '<span class="text-success">activer</span>' ?>
                                </a>

                                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                     id="escale-edit-<?= (int) $esc->id_escale; ?>">
                                    <div class="modal-content">
                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">MODIFIER PRIX / ORDRE</h3>
                                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                                                <span class="mdi mdi-close text-white"></span>
                                            </button>
                                        </div>
                                        <?= form_open('lignes/escales/' . $this->session->company->ekey . '/edit/' . $esc->id_escale, array('class' => 'modal-body form')); ?>
                                        <p class="text-muted">
                                            <?= htmlspecialchars($esc->nom_ligne_parent); ?>
                                            → <?= htmlspecialchars($esc->nom_escale); ?>
                                        </p>
                                        <div class="form-group">
                                            <label>PRIX</label>
                                            <input class="form-control form-control-sm" type="number" min="0" step="1"
                                                   name="prix_escale" value="<?= (int) $esc->prix_escale; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>ORDRE</label>
                                            <input class="form-control form-control-sm" type="number" min="1" max="20"
                                                   name="ordre_escale" value="<?= (int) $esc->ordre_escale; ?>" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
                                            <button class="btn btn-success" type="submit">OK</button>
                                        </div>
                                        <?= form_close(); ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <? endforeach; ?>
                    </tbody>
                </table>
                <? else: ?>
                    <p class="text-warning text-center mb-0">AUCUNE ESCALE CONFIGURÉE</p>
                <? endif; ?>
            </div>
        </div>

    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header text-center">Composer / remplacer un transit (2 à 4 itinéraires)</div>
            <?= form_open("Lignes/additine/{$this->session->company->ekey}"); ?>

            <div class="card-body">
                <div class="form-group">
                    <label>LIGNE CONTENEUR (axe commercial)</label>
                    <select class="form-control form-control-sm" name="ligne" required>
                        <option value=""></option>
                        <? foreach ($lignes as $items): ?>
                            <option value="<?= htmlspecialchars($items->ident_ligne); ?>">
                                <?= htmlspecialchars($items->nom_ligne); ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                <p class="text-muted small">Sélectionnez des <strong>itinéraires déjà créés</strong> (lignes), dans l’ordre géographique. Cela <strong>remplace</strong> la composition existante de cette ligne.</p>
                <? for ($i = 1; $i <= 4; $i++): ?>
                <div class="form-group">
                    <label>ITINÉRAIRE <?= $i; ?><?= $i <= 2 ? ' *' : ' (optionnel)'; ?></label>
                    <select class="form-control form-control-sm" name="etape<?= $i; ?>" <?= $i <= 2 ? 'required' : ''; ?>>
                        <option value=""></option>
                        <? foreach ($lignes as $items): ?>
                            <option value="<?= htmlspecialchars($items->ident_ligne); ?>">
                                <?= htmlspecialchars($items->nom_ligne); ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                <? endfor; ?>
                <div class="card-footer text-center">
                    <button class="btn btn-primary" type="submit">Enregistrer la composition</button>
                </div>
                <?= form_close(); ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header text-center">Ajouter une escale + prix</div>
            <?= form_open('lignes/escales/' . $this->session->company->ekey . '/add'); ?>
            <div class="card-body">
                <p class="text-muted small">
                    Ex. parent <strong>BANFORA-BOBO</strong>, escale une destination intermédiaire, avec son prix.
                    Pas besoin de créer un programme pour l’escale.
                </p>
                <div class="form-group">
                    <label>ITINÉRAIRE PARENT *</label>
                    <select class="form-control form-control-sm" name="ligne_parent" required>
                        <option value=""></option>
                        <? foreach ($lignes as $items): ?>
                            <option value="<?= htmlspecialchars($items->ident_ligne); ?>">
                                <?= htmlspecialchars($items->nom_ligne); ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>DESTINATION ESCALE *</label>
                    <select class="form-control form-control-sm" name="gare_escale" required>
                        <option value=""></option>
                        <? if (!empty($garearrivees)): ?>
                            <? foreach ($garearrivees as $garearrive): ?>
                                <option value="<?= htmlspecialchars($garearrive->code_gadest . '.' . $garearrive->nom_gadest); ?>">
                                    <?= htmlspecialchars($garearrive->nom_gadest); ?>
                                    (<?= htmlspecialchars($garearrive->code_gadest); ?>)
                                </option>
                            <? endforeach; ?>
                        <? endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>PRIX *</label>
                    <input class="form-control form-control-sm" type="number" min="0" step="1" name="prix_escale" required placeholder="ex. 3500">
                </div>
                <div class="form-group">
                    <label>ORDRE</label>
                    <input class="form-control form-control-sm" type="number" min="1" max="20" name="ordre_escale" placeholder="auto">
                </div>
                <div class="card-footer text-center">
                    <button class="btn btn-success" type="submit">Enregistrer l’escale</button>
                </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
