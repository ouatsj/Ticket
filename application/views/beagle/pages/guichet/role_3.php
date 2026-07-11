<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                       <p> 
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTs/'
                            . $bus_stop->idengare.'/sousgare/'.$conex->cpuser_id.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;
                            </a>
                            <a href="<?= site_url('gares/'
                            . $this->session->company->ekey . '/gTv/'
                            . $bus_stop->idengare . '/prog/'.$conex->roleattribut.'/'. $bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-warning"></i>&nbsp;VOIR PROGRAMME&nbsp;
                            </a>
                            
                        </p>
                    </div>
                </div>
                
            </div>
