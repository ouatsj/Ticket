<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                       <p> 
                            <?php $this->load->view('_partials/btn_retour_gare'); ?>
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
