<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$compte_arret_only_compte = !empty($compte_arret_only_compte) || !empty($compte_arret_blocked);
$compte_arret_grace = !empty($compte_arret_grace);
?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                        <? if ($compte_arret_only_compte && !empty($compte_arret_message)): ?>
                        <div class="alert alert-warning mx-3 mb-3" role="alert">
                            <?= htmlspecialchars($compte_arret_message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <? elseif ($compte_arret_grace && !empty($compte_arret_message)): ?>
                        <div class="alert alert-info mx-3 mb-3" role="alert">
                            <?= htmlspecialchars($compte_arret_message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <? endif; ?>
                        <p>
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTs/'
                            . $bus_stop->idengare.'/sousgare/'.$conex->cpuser_id.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;
                            </a>
                            <? if (!$compte_arret_only_compte):?>  
                            <a href="<?= site_url("confirmation/ventemobile/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; VENTE MOBILE&nbsp;
                            </a>
                            <a href="<?= site_url("confirmation/bagageguichet/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-success"></i>&nbsp; BAGAGE&nbsp;
                            </a>
                            <? endif; ?>
                            <a href="<?= site_url("caisses/compte/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;COMPTE&nbsp;
                            </a>
                        </p>
                    </div>
                    <? $r = 0; $al=0; $m = 0; ?>
                    <? if ($cptretour==''): ?><? $r=0;?><? else:?> &nbsp;
                                        
                            <? $r = $cptretour->totalr;?>
                    <? endif; ?>
                    <? if ($cptaller==''): ?><? $al=0;?><? else:?> &nbsp;
                                
                                <? $al = $cptaller->total;?>
                                <? 
                                $m = $al+$r;?>
                
                        <div><span>SOLDE&nbsp;:&nbsp;<?= number_format($m, 0, '', ' '); ?></span>                   
                
                        </div>
                    <?endif;?>
                </div>
                
            </div>
