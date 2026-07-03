<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>

        <? if ($cptbagescd == ''):?>
            <a href="<?= site_url("confirmation/bagageescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-print text-success"></i>&nbsp;BAGAGE&nbsp;
            </a>

        <? endif; ?>
            <a href="<?= site_url("comptecaisses/arcompteescalbag/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                class="btn btn-secondary btn-space md-trigger" data-modal="">
                <i class="fas fa-puzzle-piece text-info"></i>
                &nbsp;COMPTE ESCAL BAGAGE&nbsp;
            </a>
    </p>
</div>
    <? $rt = 0; $mt = 0; ?>
    <? if ($cptbages==''): ?><? $rt=0;?><? else:?> &nbsp;
                        
            <? $rt = $cptbages->bagtot;
                $mt = $rt;?>

        <div><span>RECETTE BAGAGE&nbsp;:&nbsp;<?= number_format($mt, 0, '', ' '); ?></span>                   

        </div>
    <?endif;?>