<?php defined('BASEPATH') OR exit('No direct script access allowed');
$role17_mode = !empty($role17_mode) && role17_is_agent();
$retour = $role17_mode
    ? role17_accueil_url($bus_stop, $conex)
    : site_url("historique_passagers/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");
$tickets = !empty($reponseallereimp) ? $reponseallereimp : array();
$bags = !empty($reimpri_bagages) ? $reimpri_bagages : array();
$cours = !empty($reimpri_courriers) ? $reimpri_courriers : array();
$tab = isset($_GET['tab']) ? (string) $_GET['tab'] : 'ticket';
if (!in_array($tab, array('ticket', 'bagage', 'courrier'), true)) {
    $tab = 'ticket';
}
?>
<div class="<?= $role17_mode ? 'r17-ops r17-shell' : ''; ?>">
<?php if ($role17_mode): ?>
    <?php $this->load->view('beagle/pages/guichet/_role17_ops_chrome'); ?>
<style>
.r17-tabs {
    display: flex; gap: 0.35rem; margin: 0.5rem 0 0.85rem; flex-wrap: wrap;
}
.r17-tabs a {
    flex: 1 1 30%; min-height: 44px; display: flex; align-items: center; justify-content: center;
    gap: 0.35rem; padding: 0.45rem 0.6rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem;
    text-decoration: none; color: #1e3a8a; background: #eff6ff; border: 1px solid #bfdbfe;
}
.r17-tabs a.is-active { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
.r17-tabs .r17-tab-n {
    display: inline-block; min-width: 1.25rem; padding: 0 0.3rem; border-radius: 999px;
    font-size: 0.75rem; background: rgba(0,0,0,.12);
}
.r17-tabs a.is-active .r17-tab-n { background: rgba(255,255,255,.25); }
.r17-tab-panel[hidden] { display: none !important; }
.r17-hint {
    margin: 0 0 0.75rem; padding: 0.55rem 0.7rem; border-radius: 8px;
    background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; font-size: 0.85rem;
}
</style>

    <div class="r17-tabs" role="tablist">
        <a href="?tab=ticket" class="<?= $tab === 'ticket' ? 'is-active' : ''; ?>" role="tab">
            Tickets <span class="r17-tab-n"><?= count($tickets); ?></span>
        </a>
        <a href="?tab=bagage" class="<?= $tab === 'bagage' ? 'is-active' : ''; ?>" role="tab">
            Bagage <span class="r17-tab-n"><?= count($bags); ?></span>
        </a>
        <a href="?tab=courrier" class="<?= $tab === 'courrier' ? 'is-active' : ''; ?>" role="tab">
            Courrier <span class="r17-tab-n"><?= count($cours); ?></span>
        </a>
    </div>

    <div class="r17-tab-panel" <?= $tab === 'ticket' ? '' : 'hidden'; ?>>
        <p class="r17-hint">
            Les tickets n’apparaissent ici qu’après validation du <strong>chef guichet</strong>
            (repositionnement réimpression). Une fois imprimés, ils quittent cette liste.
            Si le bouton ne produit rien : demandez au chef de repositionner le ticket.
        </p>
        <?php if (empty($tickets)): ?>
            <p class="text-muted">Aucun ticket autorisé à réimprimer pour le moment.</p>
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
                ?>
                    <a href="<?= htmlspecialchars($print, ENT_QUOTES, 'UTF-8'); ?>" class="r17-btn">
                        <span class="r17-ico"><i class="fas fa-print"></i></span>
                        <span class="r17-txt">
                            <?= htmlspecialchars($item->idclescal, ENT_QUOTES, 'UTF-8'); ?>
                            <span class="r17-sub"><?= htmlspecialchars(
                                trim($item->nom_client . ' ' . $item->prenom_client)
                                . ($od !== '' ? (' · ' . $od) : ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="r17-tab-panel" <?= $tab === 'bagage' ? '' : 'hidden'; ?>>
        <p class="r17-hint">Reçus bagage du jour · 57×40. Si la liste est vide, facturez d’abord un bagage sur un ticket de votre escale.</p>
        <?php if (empty($bags)): ?>
            <p class="text-muted">Aucun bagage facturé aujourd’hui — rien à réimprimer.</p>
        <?php else: ?>
            <div class="r17-grid">
                <?php foreach ($bags as $b):
                    $print = site_url(
                        'historique_passagers/pdfepsonbagesc/' . $this->session->company->ekey . '/'
                        . $b->id_bagageesc . '/' . rawurlencode($bus_stop->idengare) . '/'
                        . $conex->roleattribut . '/' . $bus_stop->idsousgare
                    );
                ?>
                    <a href="<?= htmlspecialchars($print, ENT_QUOTES, 'UTF-8'); ?>" class="r17-btn">
                        <span class="r17-ico"><i class="fas fa-suitcase"></i></span>
                        <span class="r17-txt">
                            <?= htmlspecialchars(!empty($b->codebagesc) ? $b->codebagesc : $b->id_bagageesc, ENT_QUOTES, 'UTF-8'); ?>
                            <span class="r17-sub"><?= htmlspecialchars(
                                trim($b->nom_client . ' ' . $b->prenom_client)
                                . ' · ' . number_format((float) $b->prix_bagageesc, 0, '', ' ') . ' F',
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="r17-tab-panel" <?= $tab === 'courrier' ? '' : 'hidden'; ?>>
        <p class="r17-hint">Reçus courrier du jour · 57×40. Si un reçu « introuvable » apparaît, réessayez depuis cette liste (JOINs assouplis).</p>
        <?php if (empty($cours)): ?>
            <p class="text-muted">Aucun courrier envoyé aujourd’hui — rien à réimprimer.</p>
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
                ?>
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
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= htmlspecialchars($retour, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
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
                    <? if (empty($reponseallereimp)): ?>
                        <tr>
                            <td colspan="6" class="text-muted py-4">
                                Aucun ticket à réimprimer.
                                Depuis Historique passagers, validez la réimpression (icône crayon),
                                puis revenez ici.
                            </td>
                        </tr>
                    <? endif; ?>
                    <? foreach ($reponseallereimp as $item): ?>
                        <tr>
                            <td>
                                <span><?= $item->idclescal; ?></span><br>
                                <a class="icon" title="epson"
                                    href="<?= site_url('ventescales/pdfepsonescalrp/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$item->typtarifesc.'/'.$item->id_lgeheur.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare);?>">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                            <td>
                                <span>Nom:<?= $item->nom_client; ?><br></span>
                                <span>Prénom:<?= $item->prenom_client; ?><br></span>
                                <span>Contact:<?= $item->contact_client; ?>
                            </td>
                            <td>
                                <span>Cni ou passport:<?= $item->num_CNIB; ?></span><br>
                                <span>Délivrée le:<?= $item->date_delivre; ?></span>
                                <span>Lieu:<?= $item->lieu_delivre; ?></span>
                            </td>
                            <td>
                                <span>Départ:<?= $item->datedepescal; ?><br>
                                <span>Heure:<?= $item->heure; ?></span></span>
                                <span>Axe:<?= $item->nom_ligne; ?> <?= $item->quartier_escal; ?></span>
                            </td>
                            <td>
                                <span><?= number_format($item->prixescal, 0, '', ' '); ?></span>
                            </td>
                            <td>
                                <a class="icon" title="epson"
                                    href="<?= site_url('ventescales/pdfepsonescalrp/'.$this->session->company->ekey.'/'.$item->idclescal.'/'.$item->typtarifesc.'/'.$item->id_lgeheur.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare);?>">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    <? endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</div>







