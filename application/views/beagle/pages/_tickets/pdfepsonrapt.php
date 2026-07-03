<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("caisses/compte/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
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
                            <? $montantglobal = 0;
                            $montantglobalr = 0; ?>
                            <? foreach ($reponsealler as $reponsealler => $repba): ?>
                            <tr>
                              <td style="font-size: 60px;" align="left"><strong><?=$repba->nom_ligne; ?></strong></td>
                              <td style="font-size: 60px;" align="left"><strong><?=$repba->cd; ?></strong></td>
                              <td style="font-size: 70px;" align="left"><strong><?=$repba->total/$repba->cd; ?></strong></td>
                              <td style="font-size: 80px;"align="left"><strong><?=number_format($repba->total, 0, '', ' '); ?></strong></td>
                            </tr>
                              <? $montantglobal +=$repba->total; ?>
                              <? endforeach; ?>
                            <?foreach ($reponseretour as $reponseretour => $repou) : ?>
                            <? $aler = explode("-", $repou->nom_ligne);
                                $allerretour = $aler[1]. '-' .$aler[0]; ?>
                            <tr>
                              <td style="font-size: 60px;" align="left"><strong><?= $allerretour; ?></strong></td>
                              <td style="font-size: 60px;" align="left"><strong><?=$repou->cod; ?></strong></td>
                              <td style="font-size: 70px;" align="left"><strong><?=$repou->totalr/$repou->cod; ?></strong></td>
                              <td style="font-size: 80px;" align="left"><strong><?=number_format($repou->totalr, 0, '', ' '); ?></strong></td>
                            </tr>
                              <? $montantglobalr +=$repou->totalr; ?>
                              <? endforeach; ?>
                            </body>
                        </table>
                  
                    <h2 style="font-size: 60px;" align="left">recette totale :<?= number_format($montantglobal+$montantglobalr, 0, '', ' '); ?> </h2>
                    
                    <h1 style="font-size: 60px;" align="left">Reprogrammation</h1>
                    <table border="1" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th style="font-size: 60px;" align="left"><strong>LIGNES</strong></th>
                              <th style="font-size: 60px;" align="left"><strong>NBR</strong></th>
                            </tr>
                        </thead>
                        <body>
                        
                            <?foreach ($reponserepro as $reponserepr => $reponserep): ?>
                                <tr>
                                  <td style="font-size: 60px;" align="left"><strong><?=$reponserep->nom_ligne; ?></strong></td>
                                  <td style="font-size: 60px;" align="left"><strong><?= $reponserep->cdrep; ?></strong></td>
                                </tr>
                            <? endforeach; ?>
                        </body>
                    </table>
                    <h1 style="font-size: 60px;" align="left">Confirmation</h1>
                    <table border="1" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th style="font-size: 60px;" align="left"><strong>LIGNES</strong></th>
                              <th style="font-size: 60px;" align="left"><strong>NBR</strong></th>
                            </tr>
                        </thead>
                        <body>
                        
                            <?foreach ($reponseconf as $reponsecon => $reponsecf): ?>
                          
                            <tr>
                                  <td style="font-size: 60px;" align="left"><strong><?=$reponsecf->nom_ligne; ?></strong></td>
                                  <td style="font-size: 60px;" align="left"><strong><?= $reponsecf->cdconf; ?></strong></td>
                            </tr>
                            <? endforeach; ?>
                        </body>
                    </table>
                </div>
            </div>

        </div>
    </div>