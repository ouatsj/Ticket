<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$compte_arret_only_compte = !empty($compte_arret_only_compte) || !empty($compte_arret_blocked);
?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                        <?php $this->load->view('beagle/pages/guichet/_compte_arret_alerts'); ?>
                        <p>
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTs/'
                            . $bus_stop->idengare.'/sousgare/'.$conex->roleattribut.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;
                            </a>
                            

                            <? if (!$compte_arret_only_compte && $cptallerescd == ''):?>
                                <a href="<?= site_url("confirmation/ventemobilescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");?>"
                                    class="btn btn-secondary btn-space md-trigger" data-modal="">
                                    <i class="fas fa-print text-info"></i>&nbsp; VENTE MOBILE ESCAL&nbsp;
                                </a>
                                <a href="<?= site_url("ventescales/voirreimpri/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; VOIR REIMPRESSION&nbsp;
                                </a>
                            <? endif; ?>
                            <? if (!$compte_arret_only_compte): ?>
                            <a href="<?= site_url("confirmation/bagageescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;BAGAGE ESCAL&nbsp;
                            </a>
                            <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;COURRIER ESCAL&nbsp;
                            </a>

                            <a href="<?= site_url("confirmation/validerarr/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;VALIDER ARRIVER&nbsp;
                            </a>
                            <? endif; ?>
                            <a href="<?= site_url("caisses/compteescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;COMPTE ESCAL&nbsp;
                            </a>
                        </p>
                    </div>
                    
                    <? if ($cptalleresc==''): ?><? $al=0;?><? else:?> &nbsp;
                                
                            <? $al = $cptalleresc->total;?>
                            <? 
                                $m = $al;?>
                
                        <div><span>SOLDE&nbsp;:&nbsp;<?= number_format($m, 0, '', ' '); ?></span>                   
                
                        </div>
                    <?endif;?>
                </div>
                
            </div>
