<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<?php if ((string) $agent_userole === '18'): ?>
<div class="row">
    <div class="col-12 px-4 mb-3">
        <?php if ($msg = $this->session->flashdata('arret_global_success')): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
                <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
                <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        <?php endif; ?>
        <?php if ($msg = $this->session->flashdata('arret_global_error')): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
                <button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        <?php endif; ?>

        <button class="btn btn-success md-trigger"
                type="button"
                title="Arrêter le compte sur toutes les gares"
                data-modal="arret-compte-global-adjoint">
            <i class="fas fa-puzzle-piece"></i>&nbsp;ARRÊT COMPTE GLOBAL
        </button>
        <span class="text-muted small ml-2">
            Envoie les mouvements de toutes vos gares à la caissière pour validation.
        </span>

        <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
             id="arret-compte-global-adjoint"
             style="perspective: none;">
            <div class="modal-content">
                <div class="modal-header modal-header-colored">
                    <h3 class="modal-title">ARRÊT COMPTE GLOBAL</h3>
                    <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true">
                        <span class="mdi mdi-close text-white"></span>
                    </button>
                </div>
                <?= form_open(
                    'Arretcaisses/unstop_global_adjoint/' . $company_ekey,
                    array('class' => 'modal-body form')
                ); ?>
                    <p>
                        Confirmer l’arrêt de compte sur <strong>toutes les gares</strong> auxquelles
                        vous avez accès&nbsp;?
                    </p>
                    <p class="text-muted small mb-0">
                        Les recettes, dépenses et dépôts seront transmis au compte de la caissière
                        (section «&nbsp;Caissiers adjoints — à confirmer&nbsp;»).
                    </p>
                    <div class="modal-footer">
                        <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                            <i class="icon icon-left mdi mdi-undo text-dark"></i>&nbsp;ANNULER&nbsp;
                        </button>
                        <button class="btn btn-success" type="submit">
                            <i class="icon icon-left mdi mdi-check-all text-white"></i>&nbsp;CONFIRMER L’ARRÊT&nbsp;
                        </button>
                    </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">        
        
    <? foreach($gares as $item): ?>

        <div class="col-lg-4">

            <div class="card card-border card-full">
                <div class="card-header card-header-divider">AGENCE: <?= $item->garenom; ?>
                    <div class="card-header card-header-divider">
                            
                    </div>

                    <div class="card-body">
                        <p>Code:<?= $item->idengare; ?></p>
                        <p>Ville:<?= $item->nom_ville; ?></p>
                        <p>Contact:<?= $item->contactgares;?></p>
                    <? if (
                        (in_array($agent_userole, array('1', '2', '4'), TRUE))
                        AND isset($soldes[$item->idengare])
                    ) : ?>
                        <? $s = $soldes[$item->idengare]; ?>
                            <p>Recette totale:<strong><?= number_format($s['r'], 0, '', ' '); ?></strong></p>
                            <p>Depot total:<strong><?= number_format($s['dp'], 0, '', ' ');?></strong></p>
                            <p>Versement total:<strong><?= number_format($s['v'], 0, '', ' '); ?></strong></p>
                            <p>Depense totale:<strong><?= number_format($s['d'], 0, '', ' ');?></strong></p>
                            <p class="form-group text-center">Solde total:<strong><?=number_format($s['solde'], 0, '', ' ');?>F</strong></p>
                        <?endif;?>

                        <? if ($agent_userole === '18' AND isset($soldes[$item->idengare])) : ?>
                        <? $s = $soldes[$item->idengare]; ?>
                            <p>Recette totale:<strong><?= number_format($s['r'], 0, '', ' '); ?></strong></p>
                            <p>Depot total:<strong><?= number_format($s['dp'], 0, '', ' ');?></strong></p>
                            <p>Versement total:<strong><?= number_format($s['v'], 0, '', ' '); ?></strong></p>
                            <p>Depense totale:<strong><?= number_format($s['d'], 0, '', ' ');?></strong></p>
                            <p class="form-group text-center">Solde total:<strong><?=number_format($s['solde'], 0, '', ' ');?>F</strong></p>
                        <?endif;?>
                            <a href="<?= site_url('gares/'.$company_ekey.'/gTs/'.$item->idengare.'/sousgare/'.$item->roleattribut.'/'.mdate("%d/%m/%Y", now('UTC'))); ?>"
                               class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                <?= ((string) $agent_userole === '17') ? 'VOIR ITINÉRAIRES' : 'VOIR GARES'; ?>
                            </a>
                    </div>
                </div>
            </div>
        </div>
    <? endforeach;?>
</div>
