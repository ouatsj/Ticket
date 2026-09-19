<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Réimpression venteescale — onglets Tickets / Bagage / Courrier (rôle 17).
 */
$conex_role = (!empty($conex) && isset($conex->userole)) ? (string) $conex->userole : '';
// Faire confiance au flag contrôleur ; fallback session / atrib. gare.
$role17_mode = !empty($role17_mode)
    || (function_exists('role17_is_agent') && role17_is_agent())
    || $conex_role === '17';

$tickets = !empty($reponseallereimp) ? $reponseallereimp : array();
$bags = !empty($reimpri_bagages) ? $reimpri_bagages : array();
$cours = !empty($reimpri_courriers) ? $reimpri_courriers : array();
if (!is_array($tickets)) {
    $tickets = array();
}
if (!is_array($bags)) {
    $bags = array();
}
if (!is_array($cours)) {
    $cours = array();
}

$tab = isset($_GET['tab']) ? (string) $_GET['tab'] : 'ticket';
if (!in_array($tab, array('ticket', 'bagage', 'courrier'), true)) {
    $tab = 'ticket';
}

$accueil = '#';
if (!empty($bus_stop) && !empty($conex)) {
    if ($role17_mode && function_exists('role17_accueil_url')) {
        $accueil = role17_accueil_url($bus_stop, $conex);
    } else {
        $accueil = site_url(
            'historique_passagers/' . $this->session->company->ekey
            . '/' . $conex->roleattribut
            . '/' . $bus_stop->idengare
            . '/' . $bus_stop->idsousgare
        );
    }
}
$base_reimpri = site_url(
    'ventescales/voirreimpri/' . $this->session->company->ekey
    . '/' . (!empty($conex->roleattribut) ? $conex->roleattribut : '')
    . '/' . (!empty($bus_stop->idengare) ? $bus_stop->idengare : '')
    . '/' . (!empty($bus_stop->idsousgare) ? $bus_stop->idsousgare : '')
);
?>
<div class="<?= $role17_mode ? 'r17-ops r17-shell' : ''; ?>">
<?php if ($role17_mode): ?>
    <?php $this->load->view('beagle/pages/guichet/_role_17_styles'); ?>
    <?php $this->load->view('beagle/pages/guichet/_role17_ops_chrome'); ?>
<style>
.r17-reimpri-title {
    margin: 0 0 0.65rem;
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
}
.r17-tabs {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin: 0.5rem 0 0.85rem;
}
.r17-tabs a {
    -webkit-box-flex: 1;
    -ms-flex: 1 1 30%;
    flex: 1 1 30%;
    min-height: 44px;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    margin: 0.2rem;
    padding: 0.45rem 0.6rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    color: #1e3a8a;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
}
.r17-tabs a.is-active { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
.r17-tabs .r17-tab-n {
    display: inline-block; min-width: 1.25rem; padding: 0 0.3rem; border-radius: 999px;
    font-size: 0.75rem; background: rgba(0,0,0,.12); margin-left: 0.35rem;
}
.r17-tabs a.is-active .r17-tab-n { background: rgba(255,255,255,.25); }
.r17-tab-panel { display: block; min-height: 120px; }
.r17-tab-panel[hidden] { display: none !important; }
.r17-hint {
    margin: 0 0 0.75rem; padding: 0.55rem 0.7rem; border-radius: 8px;
    background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; font-size: 0.85rem;
}
.r17-empty {
    margin: 0.5rem 0 0; padding: 0.85rem 0.9rem; border-radius: 8px;
    background: #f8fafc; border: 1px dashed #cbd5e1; color: #475569; font-size: 0.9rem;
}
.r17-row-actions {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: stretch;
    -ms-flex-align: stretch;
    align-items: stretch;
    margin: 0 0 0.55rem 0;
}
.r17-row-actions .r17-btn {
    -webkit-box-flex: 1;
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    margin: 0 !important;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}
.r17-row-actions .r17-del {
    -webkit-box-flex: 0;
    -ms-flex: 0 0 52px;
    flex: 0 0 52px;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    margin: 0;
    text-decoration: none;
    color: #fff;
    background: #b91c1c;
    border: 1px solid #991b1b;
    border-radius: 0 8px 8px 0;
    font-size: 1.05rem;
}
.r17-row-actions .r17-del:active { background: #7f1d1d; }
/* Chrome 64 / TPE : pas de flex gap — marges manuelles */
.r17-shell .r17-grid {
    display: block !important;
}
.r17-shell .r17-grid .r17-row-actions {
    width: 100%;
    box-sizing: border-box;
}
@media (min-width: 480px) {
    .r17-shell .r17-grid {
        display: -webkit-box !important;
        display: -ms-flexbox !important;
        display: flex !important;
        -ms-flex-wrap: wrap !important;
        flex-wrap: wrap !important;
        margin-left: -0.25rem;
        margin-right: -0.25rem;
    }
    .r17-shell .r17-grid .r17-row-actions {
        width: calc(50% - 0.5rem) !important;
        margin: 0.25rem !important;
    }
}
</style>

    <h1 class="r17-reimpri-title">Réimpression</h1>

    <div class="r17-tabs" role="tablist">
        <a href="<?= htmlspecialchars($base_reimpri . '?tab=ticket', ENT_QUOTES, 'UTF-8'); ?>"
           class="<?= $tab === 'ticket' ? 'is-active' : ''; ?>" role="tab">
            Tickets <span class="r17-tab-n"><?= (int) count($tickets); ?></span>
        </a>
        <a href="<?= htmlspecialchars($base_reimpri . '?tab=bagage', ENT_QUOTES, 'UTF-8'); ?>"
           class="<?= $tab === 'bagage' ? 'is-active' : ''; ?>" role="tab">
            Bagage <span class="r17-tab-n"><?= (int) count($bags); ?></span>
        </a>
        <a href="<?= htmlspecialchars($base_reimpri . '?tab=courrier', ENT_QUOTES, 'UTF-8'); ?>"
           class="<?= $tab === 'courrier' ? 'is-active' : ''; ?>" role="tab">
            Courrier <span class="r17-tab-n"><?= (int) count($cours); ?></span>
        </a>
    </div>

    <div class="r17-tab-panel" <?= $tab === 'ticket' ? '' : 'hidden'; ?>>
        <p class="r17-hint">
            Les tickets n’apparaissent ici qu’après validation du <strong>chef guichet</strong>
            (repositionnement réimpression). Une fois imprimés, ils quittent cette liste.
        </p>
        <?php if (empty($tickets)): ?>
            <p class="r17-empty">Aucun ticket autorisé à réimprimer pour le moment.</p>
        <?php else: ?>
            <div class="r17-grid">
                <?php foreach ($tickets as $item):
                    $tf = !empty($item->typtarifesc) ? $item->typtarifesc : '0';
                    $lh = !empty($item->id_lgeheur) ? $item->id_lgeheur : (!empty($item->id_ligneheure) ? $item->id_ligneheure : '0');
                    $od = trim(preg_replace('/^\[LIBRE\]\s*/i', '', (string) (isset($item->quartier_escal) ? $item->quartier_escal : '')));
                    if ($od === '' && !empty($item->nom_ligne)) {
                        $od = (string) $item->nom_ligne;
                    }
                    $print = site_url(
                        'ventescales/pdfepsonescalrp/' . $this->session->company->ekey . '/'
                        . rawurlencode($item->idclescal) . '/' . rawurlencode($tf) . '/' . rawurlencode($lh) . '/'
                        . rawurlencode($bus_stop->idengare) . '/' . $conex->roleattribut . '/' . $bus_stop->idsousgare
                    );
                    $del = site_url(
                        'ventescales/supprimescal/' . $this->session->company->ekey . '/'
                        . rawurlencode($item->idclescal) . '/' . $conex->roleattribut . '/'
                        . rawurlencode($bus_stop->idengare) . '/' . $bus_stop->idsousgare
                    );
                ?>
                    <div class="r17-row-actions">
                        <a href="<?= htmlspecialchars($print, ENT_QUOTES, 'UTF-8'); ?>" class="r17-btn">
                            <span class="r17-ico"><i class="fas fa-print"></i></span>
                            <span class="r17-txt">
                                <?= htmlspecialchars($item->idclescal, ENT_QUOTES, 'UTF-8'); ?>
                                <span class="r17-sub"><?= htmlspecialchars(
                                    trim((isset($item->nom_client) ? $item->nom_client : '') . ' ' . (isset($item->prenom_client) ? $item->prenom_client : ''))
                                    . ($od !== '' ? (' · ' . $od) : ''),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?></span>
                            </span>
                        </a>
                        <a href="<?= htmlspecialchars($del, ENT_QUOTES, 'UTF-8'); ?>"
                           class="r17-del"
                           title="Supprimer ce ticket"
                           onclick="return confirm('Supprimer définitivement ce ticket escale ?');">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="r17-tab-panel" <?= $tab === 'bagage' ? '' : 'hidden'; ?>>
        <p class="r17-hint">Reçus bagage du jour · 57×40. Si la liste est vide, facturez d’abord un bagage sur un ticket de votre escale.</p>
        <?php if (empty($bags)): ?>
            <p class="r17-empty">Aucun bagage facturé aujourd’hui — rien à réimprimer.</p>
        <?php else: ?>
            <div class="r17-grid">
                <?php foreach ($bags as $b):
                    $print = site_url(
                        'historique_passagers/pdfepsonbagesc/' . $this->session->company->ekey . '/'
                        . $b->id_bagageesc . '/' . rawurlencode($bus_stop->idengare) . '/'
                        . $conex->roleattribut . '/' . $bus_stop->idsousgare
                    );
                    $del = site_url(
                        'ventescales/supprimebagesc/' . $this->session->company->ekey . '/'
                        . $b->id_bagageesc . '/' . $conex->roleattribut . '/'
                        . rawurlencode($bus_stop->idengare) . '/' . $bus_stop->idsousgare
                    );
                ?>
                    <div class="r17-row-actions">
                        <a href="<?= htmlspecialchars($print, ENT_QUOTES, 'UTF-8'); ?>" class="r17-btn">
                            <span class="r17-ico"><i class="fas fa-suitcase"></i></span>
                            <span class="r17-txt">
                                <?= htmlspecialchars(!empty($b->codebagesc) ? $b->codebagesc : $b->id_bagageesc, ENT_QUOTES, 'UTF-8'); ?>
                                <span class="r17-sub"><?= htmlspecialchars(
                                    trim((isset($b->nom_client) ? $b->nom_client : '') . ' ' . (isset($b->prenom_client) ? $b->prenom_client : ''))
                                    . ' · ' . number_format((float) $b->prix_bagageesc, 0, '', ' ') . ' F',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?></span>
                            </span>
                        </a>
                        <a href="<?= htmlspecialchars($del, ENT_QUOTES, 'UTF-8'); ?>"
                           class="r17-del"
                           title="Annuler ce reçu bagage"
                           onclick="return confirm('Annuler ce reçu bagage ?');">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="r17-tab-panel" <?= $tab === 'courrier' ? '' : 'hidden'; ?>>
        <p class="r17-hint">Reçus courrier du jour · 57×40. Réessayez depuis cet onglet si un reçu était « introuvable ».</p>
        <?php if (empty($cours)): ?>
            <p class="r17-empty">Aucun courrier envoyé aujourd’hui — rien à réimprimer.</p>
        <?php else: ?>
            <div class="r17-grid">
                <?php foreach ($cours as $c):
                    $ex = !empty($c->expditid) ? $c->expditid : '0';
                    $re = !empty($c->receptid) ? $c->receptid : '0';
                    $tc = !empty($c->type_client) ? $c->type_client : 'Adulte';
                    $dp = !empty($c->departcolisesc) ? $c->departcolisesc : '0';
                    $print = site_url(
                        'historiquesescal/reditpdfesc/' . $this->session->company->ekey . '/'
                        . $c->courrierexpidesc . '/' . rawurlencode($dp) . '/' . rawurlencode($ex) . '/'
                        . rawurlencode($re) . '/' . rawurlencode($tc) . '/'
                        . rawurlencode($bus_stop->idengare) . '/'
                        . $conex->roleattribut . '/' . $bus_stop->idsousgare
                    );
                    $del = site_url(
                        'ventescales/supprimecouresc/' . $this->session->company->ekey . '/'
                        . $c->courrierexpidesc . '/' . $conex->roleattribut . '/'
                        . rawurlencode($bus_stop->idengare) . '/' . $bus_stop->idsousgare
                    );
                ?>
                    <div class="r17-row-actions">
                        <a href="<?= htmlspecialchars($print, ENT_QUOTES, 'UTF-8'); ?>" class="r17-btn">
                            <span class="r17-ico"><i class="fas fa-envelope"></i></span>
                            <span class="r17-txt">
                                <?= htmlspecialchars($c->num_couresc, ENT_QUOTES, 'UTF-8'); ?>
                                <span class="r17-sub"><?= htmlspecialchars(
                                    trim((isset($c->nom_client) ? $c->nom_client : '') . ' ' . (isset($c->prenom_client) ? $c->prenom_client : ''))
                                    . ' · ' . number_format((float) $c->prixcolisesc, 0, '', ' ') . ' F',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?></span>
                            </span>
                        </a>
                        <a href="<?= htmlspecialchars($del, ENT_QUOTES, 'UTF-8'); ?>"
                           class="r17-del"
                           title="Annuler ce courrier"
                           onclick="return confirm('Annuler ce courrier escale ?');">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= htmlspecialchars($accueil, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-table">
            <div class="card-header"><div class="title">Passager</div></div>
            <div class="card-body">
                <table class="table table-striped table-borderless" id="table1">
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Client / Contact</th>
                        <th>N° cni ou passport / Date / Lieu</th>
                        <th>Départ / Heure / Axe</th>
                        <th>Prix</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody class="no-border-x">
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="6" class="text-muted py-4">
                                Aucun ticket à réimprimer.
                                Depuis Historique passagers, validez la réimpression (icône crayon),
                                puis revenez ici.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($tickets as $item): ?>
                        <tr>
                            <td>
                                <span><?= htmlspecialchars($item->idclescal, ENT_QUOTES, 'UTF-8'); ?></span><br>
                                <a class="icon" title="epson"
                                    href="<?= site_url('ventescales/pdfepsonescalrp/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$item->typtarifesc.'/'.$item->id_lgeheur.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare);?>">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                            <td>
                                <span>Nom:<?= htmlspecialchars($item->nom_client, ENT_QUOTES, 'UTF-8'); ?><br></span>
                                <span>Prénom:<?= htmlspecialchars($item->prenom_client, ENT_QUOTES, 'UTF-8'); ?><br></span>
                                <span>Contact:<?= htmlspecialchars($item->contact_client, ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                            <td>
                                <span>Cni ou passport:<?= htmlspecialchars($item->num_CNIB, ENT_QUOTES, 'UTF-8'); ?></span><br>
                                <span>Délivrée le:<?= htmlspecialchars($item->date_delivre, ENT_QUOTES, 'UTF-8'); ?></span>
                                <span>Lieu:<?= htmlspecialchars($item->lieu_delivre, ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td>
                                <span>Départ:<?= htmlspecialchars($item->datedepescal, ENT_QUOTES, 'UTF-8'); ?><br>
                                <span>Heure:<?= htmlspecialchars(isset($item->heure) ? $item->heure : '', ENT_QUOTES, 'UTF-8'); ?></span></span>
                                <span>Axe:<?= htmlspecialchars(isset($item->nom_ligne) ? $item->nom_ligne : '', ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars(isset($item->quartier_escal) ? $item->quartier_escal : '', ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td>
                                <span><?= number_format((float) $item->prixescal, 0, '', ' '); ?></span>
                            </td>
                            <td>
                                <a class="icon" title="epson"
                                    href="<?= site_url('ventescales/pdfepsonescalrp/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$item->typtarifesc.'/'.$item->id_lgeheur.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare);?>">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
