<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("comptecaisses/compte/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i> 
        </a>
    </p>
</div>
<script type="text/javascript">
    window.onload = function() {
      window.print();
    }
</script>

    <div class="col-lg-6">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                      <p align="left" style="font-size: 60px;"> <?=mdate("%d/%m/%Y", now('UTC')); ?> <?= $conex->username; ?></p>&nbsp;&nbsp;<b>
                        <p align="left" style="font-size: 60px;"> Rapport : <?= $ncomp->nom_compagnie;?></p>&nbsp;&nbsp;
                        <table border="2" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th width="320px" align="left" style="font-size: 60px;"><strong>LIGNES</strong></th>
                              <th width="120px" align="left" style="font-size: 60px;"><strong>NBR</strong></th>
                              <th width="190px" align="left" style="font-size: 60px;"><strong>PU</strong></th> 
                              <th width="190px" align="left" style="font-size: 60px;"><strong>TOTAL</strong></th>
                            </tr>
                        </thead>
                        <body>
                            <? $montantglobal = 0;
                            $montantglobalr = 0; ?>
                            <? foreach ($reponsebagage as $reponses => $repba): ?>
                            <tr>
                              <td width="320px" align="left" style="font-size: 60px;"><strong><?=$repba->nom_ligne; ?></strong></td>
                              <td width="120px" align="left" style="font-size: 60px;"><strong><?=$repba->cbg; ?></strong></td>
                              <td width="190px" align="left" style="font-size: 60px;"><strong><?=$repba->prix_bagage; ?></strong></td>
                              <td width="190px" align="left" style="font-size: 60px;"><strong><?=number_format($repba->bagtotal, 0, '', ' '); ?></strong></td>
                            </tr>
                           
                              <? $montantglobal +=$repba->bagtotal; ?>
                            <? endforeach; ?> 
                              
                            </body>
                        </table>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                  
                    <p align="left" style="font-size: 60px;">recette totale :<?= number_format($montantglobal, 0, '', ' '); ?> </p>   
                </div>
            </div>

        </div>
    </div>

<!--End of file: pdfeprapt2.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfeprapt2.php-->