<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="row">

    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space" data-modal="">
            <i class="fas fa-arrow-circle-left text-info"></i>
            &nbsp;RETOUR ACCUEIL&nbsp;
        </a>
    </p>
</div>

<script type="text/javascript">
    window.onload = function () {
    window.print();
    }
</script>

    <div class="col-lg-6">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                      <p align="left" style="font-size: 60px;"> <?=mdate("%d/%m/%Y", now('UTC')); ?> <?= $conex->username; ?></p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <p align="left" style="font-size: 60px;">Rapport : <?= $ncomp->nom_compagnie;?></p>
                              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <table border="2" cellpadding="0">
                        <h1 align="left">Expedition</h1>
                        <table border="1" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th width="150px" align="left"><strong>LIGNES</strong></th>
                              <th width="50px" align="left"><strong>NBR</strong></th>
                               
                              <th width="90px" align="left"><strong>MONTANT</strong></th>
                            </tr>
                        </thead>
                         <table border="2" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th width="320px" align="left" style="font-size: 40px;"><strong>LIGNES</strong></th>
                              <th width="120px" align="left" style="font-size: 40px;"><strong>NBR</strong></th>
                              <th width="290px" align="left" style="font-size: 40px;"><strong>MONTANT</strong></th>
                            </tr>
                        </thead>
                        <body>
                            <? $montantglobexp = 0;?>
                            <? foreach ($reponsexp as $exp => $repexp): ?>
                            <tr>
                              <td width="320px" align="left" style="font-size: 40px;"><strong><?=$repexp->nom_ligne; ?></strong></td>
                              <td width="120px" align="left" style="font-size: 40px;"><strong><?=$repexp->nombres; ?></strong></td>
                              <td width="290px" align="left" style="font-size: 40px;"><strong><?=number_format($repexp->montant, 0, '', ' '); ?></strong></td>
                            </tr>
                           
                              <? $montantglobexp +=$repexp->montant;; ?>
                            <? endforeach; ?> 
                             
                            </body>
                        </table>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <p style="font-size: 60px;" align="left">solde total :<?= number_format($montantglobexp, 0, '', ' '); ?> </p>
                </div>
            </div>

        </div>
    </div>

<!--End of file: pdfeprapcour.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfeprappt.php-->