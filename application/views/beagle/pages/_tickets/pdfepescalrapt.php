<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("caisses/compteescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
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
                    
                    <h1 align="left"> <?=mdate("%d/%m/%Y", now('UTC')); ?> <?= $conex->username; ?></h1><b>
                        <h1 align="left"> Rapport : <?= $ncomp->nom_compagnie;?></h1>
                    <table border="1" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th style="font-size: 60px;" align="left"><strong>LIGNES</strong></th>
                              <th style="font-size: 60px;" align="left"><strong>NBR</strong></th>
                              <th style="font-size: 70px;" align="left"><strong>PU</strong></th> 
                              <th style="font-size: 80px;" align="left"><strong>TOTAL</strong></th>
                            </tr>
                        </thead>
                        <body>
                            <? $montantglobal = 0;?>
                            <? foreach ($reponsealler as $reponsealler => $repba): ?>
                            <tr>
                              <td style="font-size: 60px;" align="left"><strong><?=$repba->nom_ligne; ?></strong></td>
                              <td style="font-size: 60px;" align="left"><strong><?=$repba->cd; ?></strong></td>
                              <td style="font-size: 70px;" align="left"><strong><?=$repba->prixescal; ?></strong></td>
                              <td style="font-size: 80px;"align="left"><strong><?=number_format($repba->total, 0, '', ' '); ?></strong></td>
                            </tr>
                              <? $montantglobal +=$repba->total; ?>
                              <? endforeach; ?>
                            
                            </body>
                    </table>
                  
                      <h2 style="font-size: 60px;" align="left">recette totale :<?= number_format($montantglobal, 0, '', ' '); ?> </h2>
                  </div>
            </div>

        </div>
    </div>