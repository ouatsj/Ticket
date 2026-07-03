<div class="col-lg-6">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                      <p align="left" style="font-size: 20px;"> <?=mdate("%d/%m/%Y", now('UTC')); ?> <?= $conex->username; ?></p><b>&nbsp;&nbsp;
                        <p align="left" style="font-size: 20px;"> Rapport bagage : <?= $ncomp->nom_compagnie;?></p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <table border="2" cellpadding="0">
                        <thead> 
                            <tr> 
                              <th width="90px" align="left"><strong>LIGNES</strong></th>
                              <th width="20px" align="left"><strong>NBR</strong></th>
                              <th width="90px" align="left"><strong>PU</strong></th> 
                              <th width="90px" align="left"><strong>TOTAL</strong></th>
                            </tr>
                        </thead>
                        <body>
                            <? $montantglobal = 0;
                            $montantglobalr = 0; ?>
                            <? foreach ($reponsebagage as $reponses => $repba): ?>
                            <tr>
                              <td width="90px" align="left"><strong><?=$repba->nom_ligne; ?></strong></td>
                              <td width="20px" align="left"><strong><?=$repba->cbg; ?></strong></td>
                              <td width="90px" align="left"><strong><?=$repba->prix_bagage; ?></strong></td>
                              <td width="90px" align="left"><strong><?=number_format($repba->bagtotal, 0, '', ' '); ?></strong></td>
                            </tr>
                           
                              <? $montantglobal +=$repba->bagtotal; ?>
                            <? endforeach; ?> 
                              
                            </body>
                        </table>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <p align="left" style="font-size: 20px;">recette totale :<?= number_format($montantglobal, 0, '', ' '); ?> </p>
                </div>
            </div>
        </div>
    </div>