<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="row">
    <?php if (!empty($accueil_gare_filtree)) : ?>
    <div class="col-12 mb-3">
        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between mb-0">
            <span>
                Gare active :
                <strong><?= htmlspecialchars($accueil_active_garenom, ENT_QUOTES, 'UTF-8'); ?></strong>
            </span>
            <?php if (!empty($accueil_changer_gare_url)) : ?>
            <a class="btn btn-sm btn-outline-primary mt-2 mt-md-0"
               href="<?= htmlspecialchars($accueil_changer_gare_url, ENT_QUOTES, 'UTF-8'); ?>">
                Changer de gare
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
        
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
                                VOIR GARES
                            </a>
                    </div>
                </div>
            </div>
        </div>
    <? endforeach;?>
</div>