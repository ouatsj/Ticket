<?php defined('BASEPATH') OR exit('No direct script access allowed');
$active_tab = !empty($active_tab) ? $active_tab : 'generale';
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">Documentation &amp; formation Ticket Rakieta</div>
            <div class="card-body">
                <p class="mb-2">
                    Entreprise : <strong><?= htmlspecialchars($this->company->nom_entreprise); ?></strong>
                    — Agent : <?= htmlspecialchars($this->session->agent->username); ?>
                    (rôle <?= (int) $this->session->agent->userole; ?>)
                </p>
                <p class="text-muted mb-3">
                    L’onglet <strong>Documentation générale</strong> explique les procédures par cas d’utilisation
                    pour les décideurs. L’onglet <strong>Formation par rôle</strong> regroupe fiches de poste, manuels et QCM.
                </p>

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link<?= $active_tab === 'generale' ? ' active' : ''; ?>"
                           href="<?= site_url('documentation/' . $this->session->company->ekey . '/generale'); ?>">
                            Documentation générale
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?= $active_tab === 'roles' ? ' active' : ''; ?>"
                           href="<?= site_url('documentation/' . $this->session->company->ekey . '/roles'); ?>">
                            Formation par rôle
                        </a>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    <?php if ($active_tab === 'generale'): ?>
                    <div class="tab-pane fade show active"
                         id="pane-doc-generale"
                         role="tabpanel">
                        <?php
                        $this->load->view('beagle/pages/_documentation/_cas_utilisation', array(
                            'doc_generale' => !empty($doc_generale) ? $doc_generale : documentation_generale_cas_utilisation(),
                        ));
                        ?>
                    </div>
                    <?php else: ?>
                    <div class="tab-pane fade show active"
                         id="pane-doc-roles"
                         role="tabpanel">
                        <p class="mb-3">
                            Fiches de poste, permissions, manuels par rôle et
                            <strong>QCM de fin de formation</strong> imprimables.
                            Les corrigés QCM sont réservés aux administrateurs / superviseurs.
                        </p>
                        <p class="text-muted small mb-3">
                            Mise à jour récente : correspondances (compositions, chemins proposés),
                            vente à escale, liaison de programmes J/J+1, exclusion des destinations techniques
                            (ex. OUAGAESCAL), et procédures « Confirmer autre ticket ».
                        </p>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Rôle</th>
                                    <th>Description</th>
                                    <th class="text-center">Fiche de poste &amp; manuel</th>
                                    <th class="text-center">QCM (à imprimer)</th>
                                    <?php if (!empty($can_corrige)): ?>
                                        <th class="text-center">Corrigé</th>
                                    <?php endif; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($roles_doc as $code => $meta): ?>
                                    <?php if ($code === 'general') { continue; } ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($meta['titre']); ?></strong></td>
                                        <td><?= htmlspecialchars($meta['sous_titre']); ?></td>
                                        <td class="text-center">
                                            <a class="btn btn-sm btn-secondary"
                                               href="<?= site_url('documentation/' . $this->session->company->ekey . '/manuel/' . rawurlencode($code)); ?>">
                                                Consulter
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <?php if (documentation_formation_qcm($code)): ?>
                                                <a class="btn btn-sm btn-info"
                                                   href="<?= site_url('documentation/' . $this->session->company->ekey . '/qcm/' . rawurlencode($code)); ?>">
                                                    QCM
                                                </a>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <?php if (!empty($can_corrige)): ?>
                                            <td class="text-center">
                                                <?php if (documentation_formation_qcm($code)): ?>
                                                    <a class="btn btn-sm btn-warning"
                                                       href="<?= site_url('documentation/' . $this->session->company->ekey . '/qcm_corrige/' . rawurlencode($code)); ?>">
                                                        Corrigé
                                                    </a>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
