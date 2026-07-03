<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-print text-info"></i>&nbsp; VOIR ENVOYES&nbsp;
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
                        <p align="left" style="font-size: 60px;"> <?=mdate("%d/%m/%Y", now('UTC')); ?></p>&nbsp;&nbsp;;&nbsp;&nbsp;<b>
                          <p align="left" style="font-size: 60px;"> <?= $onprogrambordaxe->dateheure_prog; ?></p>&nbsp;&nbsp;&nbsp;&nbsp;<b>
                          <p align="left" style="font-size: 60px;"> BORDEREAU N°: <?= $onprogrambordaxe->identbordbag; ?></p>&nbsp;&nbsp;&nbsp;&nbsp;<b>
                          <p align="left" style="font-size: 60px;">CHAUFF: <?= urldecode($onprogrambordaxe->buschauffbordbag); ?></p>&nbsp;&nbsp;&nbsp;&nbsp;<b>
                        <p align="left" style="font-size: 60px;"><?= $onprogrambordaxe->nom_ligne;?>  <?= $onprogrambordaxe->quartierbordbag;?></p>&nbsp;&nbsp;
                        <table border="2" cellpadding="0">
                        <thead> 
                            <tr>
                              <th width="120px" align="left" style="font-size: 40px;"><strong>NUM_BAG</strong></th>
                              
                              <th width="190px" align="left" style="font-size: 60px;"><strong>QTE/DESIGNATION</strong></th> 
                              <th width="200px" align="left" style="font-size: 60px;"><strong>NOM ET PRENOM/CONTACT</strong></th>
                            </tr>
                        </thead>
                        <body>
                            <? $montantglobal = 0;
                            $montantglobalr = 0; ?>
                            <? foreach ($onbord as $departhbord => $lementbord): ?>
                            <tr>
                              <td width="320px" align="left" style="font-size: 40px;"><strong><?=$lementbord->identbagas; ?></strong></td>
                              <td width="190px" align="left" style="font-size: 60px;"><strong><?=$lementbord->nombrebagageenv .'/'.$lementbord->nombrebagage. ' ' . $lementbord->typebagagesenv . ' '.$lementbord->contenubagageenv; ?></strong></td>
                              <td width="200px" align="left" style="font-size: 60px;"><strong><?=$lementbord->nom_client . '&nbsp;&nbsp;' . $lementbord->prenom_client . ' ' . $lementbord->contact_client; ?></strong></td>
                            </tr>
                          <? endforeach; ?> 
                              
                        </body>
                      </table>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                  
                    <p align="left" style="font-size: 60px;">Exp : <?= $nam->first_name.' '.$nam->last_name; ?> </p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <p align="left" style="font-size: 60px;">Conv: <?= urldecode($onprogrambordaxe->busconvoybordbag); ?> </p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <p align="left" style="font-size: 60px;">Recept : </p>
                </div>
            </div>

        </div>
    </div>
<!--End of file: reimpressionsuivi.php-->
<!--File location: application/views/beagle/pages/_tickets/reimpressionsuivi.php-->