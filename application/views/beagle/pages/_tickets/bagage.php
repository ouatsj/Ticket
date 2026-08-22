<div class="row">
    <div class="col-sm-12">
        <div class="text-center">
            <p>
                <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTs/'
                . $bus_stop->idengare.'/sousgare/'.$conex->roleattribut.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                    class="btn btn-secondary btn-space md-trigger" data-modal="">
                    <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;
                </a>
                

                <? if ($recettebagagescd == ''):?>                            
                    <a href="<?= site_url("confirmation/bagagemobile/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                        class="btn btn-secondary btn-space md-trigger" data-modal="">
                        <i class="fas fa-print text-info"></i>&nbsp; FACTURATION BAGAGES AVEC TICKET&nbsp;
                    </a>

                    <a href="<?= site_url("confirmation/bagagesuivimobile/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                        class="btn btn-secondary btn-space md-trigger" data-modal="">
                        <i class="fas fa-print text-info"></i>&nbsp; FACTURATION BAGAGES ENVOI&nbsp;
                    </a>

                    <a href="<?= site_url("confirmation/bagagenonfact/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                        class="btn btn-secondary btn-space md-trigger" data-modal="">
                        <i class="fas fa-print text-info"></i>&nbsp; BAGAGES AVEC TICKET NON FACTURER&nbsp;
                    </a>
                    <a href="<?= site_url("confirmation/autrebagagefc/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                        class="btn btn-secondary btn-space md-trigger" data-modal="">
                        <i class="fas fa-book text-warning"></i>&nbsp;AUTRES FACTURATION BAGAGES&nbsp;
                    </a>
                    
                <? endif; ?>
                <a href="<?= site_url("confirmation/bordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                    class="btn btn-secondary btn-space md-trigger" data-modal="">
                    <i class="fas fa-print text-info"></i>&nbsp; BORDEREAU SUIVI BAGAGES&nbsp;
                </a>
                <a href="<?= site_url("confirmation/voirbordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                    class="btn btn-secondary btn-space md-trigger" data-modal="">
                    <i class="fas fa-print text-info"></i>&nbsp; VOIR BORDEREAU BAGAGES(HISTORIQUE)&nbsp;
                </a>
                <a href="<?= site_url("comptecaisses/compte/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                    class="btn btn-secondary btn-space md-trigger" data-modal="">
                    <i class="fas fa-puzzle-piece text-info"></i>
                    &nbsp;COMPTE&nbsp;
                </a>
            </p>
            <? $rt = 0; $mt = 0; ?>
            <? if ($recettebagages==''): ?><?$rt=0;?><?else:?> &nbsp;
                                
                    <?$rt = $recettebagages->bagtotal;
                        $mt = $rt;?>
        
                <div><span>RECETTE BAGAGE&nbsp;:&nbsp;<?= number_format($mt, 0, '', ' '); ?></span>                   
        
                </div>
            <?endif;?>
        </div>
    </div>
</div>