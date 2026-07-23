<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$compte_arret_only_compte = !empty($compte_arret_only_compte) || !empty($compte_arret_blocked);
if ($compte_arret_only_compte) {
    $this->load->view('beagle/pages/guichet/_chef_arret_blocked');
    return;
}
?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                        <p>
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTs/'
                            . $bus_stop->idengare.'/sousgare/'.$conex->cpuser_id.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;
                            </a>
                            
                            
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTv/'
                            . $bus_stop->idengare.'/cais/'.$conex->roleattribut.'/'. $bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-dark"></i>&nbsp;VOIR CAISSE&nbsp;
                            </a>

                        </p>
                    </div>
                </div>
               
            </div>
