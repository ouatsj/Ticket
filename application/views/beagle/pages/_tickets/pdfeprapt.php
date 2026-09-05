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
                              <th width="150px" align="left"><strong>LIGNES</strong></th>
                              <th width="50px" align="left"><strong>NBR</strong></th>
                              <th width="90px" align="left"><strong>PU</strong></th> 
                              <th width="90px" align="left"><strong>TOTAL</strong></th>
                            </tr>
                        </thead>
                        <body>
                            <? $montantglobal = 0;
                            $montantglobalr = 0; ?>
                            <? foreach ($reponsealler as $reponsealler => $repba): ?>
                            <tr>
                              <td width="150px" align="left"><strong><?=$repba->nom_ligne; ?></strong></td>
                              <td width="50px" align="left"><strong><?=$repba->cd; ?></strong></td>
                              <td width="90px" align="left"><strong><?=$repba->total/$repba->cd; ?></strong></td>
                              <td width="90px" align="left"><strong><?=number_format($repba->total, 0, '', ' '); ?></strong></td>
                            </tr>
                           
                              <? $montantglobal +=$repba->total; ?>
                            <? endforeach; ?> 
                            <?foreach ($reponseretour as $reponseretour => $repou) : ?>
                            <? $aler = explode("-", $repou->nom_ligne);
                                $allerretour = $aler[1]. '-' .$aler[0]; ?>
                            <tr>
                              <td width="150px" align="left"><strong><?= $allerretour; ?></strong></td>
                              <td width="50px" align="left"><strong><?=$repou->cod; ?></strong></td>
                              <td width="90px" align="left"><strong><?=$repou->totalr/$repou->cod; ?></strong></td>
                              <td width="90px" align="left"><strong><?=number_format($repou->totalr, 0, '', ' '); ?></strong></td>
                            </tr>
                            
                              <? $montantglobalr +=$repou->totalr; ?>
                             <? endforeach; ?>  
                            </body>
                        </table>
                  
                    <h2 align="left">recette totale :<?= number_format($montantglobal+$montantglobalr, 0, '', ' '); ?> </h2>

                    <?php
                    $montantglobal_rat = 0;
                    $reponsealler_rattrapage = isset($reponsealler_rattrapage) ? $reponsealler_rattrapage : array();
                    ?>
                    <?php if (!empty($reponsealler_rattrapage)): ?>
                    <h1 align="left">Rattrapage</h1>
                    <table border="1" cellpadding="0">
                        <thead>
                            <tr>
                              <th width="150px" align="left"><strong>LIGNES</strong></th>
                              <th width="50px" align="left"><strong>NBR</strong></th>
                              <th width="90px" align="left"><strong>PU</strong></th>
                              <th width="90px" align="left"><strong>TOTAL</strong></th>
                            </tr>
                        </thead>
                        <body>
                            <?php foreach ($reponsealler_rattrapage as $reprat): ?>
                            <tr>
                              <td width="150px" align="left"><strong><?=$reprat->nom_ligne; ?></strong></td>
                              <td width="50px" align="left"><strong><?=$reprat->cd; ?></strong></td>
                              <td width="90px" align="left"><strong><?=$reprat->cd ? ($reprat->total/$reprat->cd) : 0; ?></strong></td>
                              <td width="90px" align="left"><strong><?=number_format($reprat->total, 0, '', ' '); ?></strong></td>
                            </tr>
                              <?php $montantglobal_rat += $reprat->total; ?>
                            <?php endforeach; ?>
                        </body>
                    </table>
                    <h2 align="left">total rattrapage :<?= number_format($montantglobal_rat, 0, '', ' '); ?> </h2>
                    <h2 align="left">recette + rattrapage :<?= number_format($montantglobal+$montantglobalr+$montantglobal_rat, 0, '', ' '); ?> </h2>
                    <?php endif; ?>
                    
                    <h1 align="left">Reprogrammation</h1>
                    <table border="1" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th width="150px" align="left"><strong>LIGNES</strong></th>
                              <th width="50px" align="left"><strong>NBR</strong></th>
                            </tr>
                        </thead>
                        <body>
                        
                            <?foreach ($reponserepro as $reponserepr => $reponserep): ?>
                                <tr>
                                  <td width="150px" align="left"><strong><?=$reponserep->nom_ligne; ?></strong></td>
                                  <td width="50px" align="left"><strong><?= $reponserep->cdrep; ?></strong></td>
                                </tr>
                            <? endforeach; ?>
                        </body>
                    </table>
                    <h1 align="left">Confirmation</h1>
                    <table border="1" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th width="150px" align="left"><strong>LIGNES</strong></th>
                              <th width="50px" align="left"><strong>NBR</strong></th>
                            </tr>
                        </thead>
                        <body>
                        
                            <?foreach ($reponseconf as $reponsecon => $reponsecf): ?>
                          
                            <tr>
                                  <td width="150px" align="left"><strong><?=$reponsecf->nom_ligne; ?></strong></td>
                                  <td width="50px" align="left"><strong><?= $reponsecf->cdconf; ?></strong></td>
                            </tr>
                            <? endforeach; ?>
                        </body>
                    </table>
                    
                </div>
            </div>

        </div>
    </div>

<!--End of file: pdfeprapt.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfeprapt.php-->