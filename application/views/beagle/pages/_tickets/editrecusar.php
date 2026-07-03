<?php defined('BASEPATH') OR exit('No direct script access allowed');
//require_once('Nel.php');
?>
<div class="row">
      
    <div class="row">
      <p class="mt-0 mb-2 ml-4">
          <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                  <i class="fas fa-arrow-circle-left text-info"></i>
          </a>
      </p>
    </div>
    <script type="text/javascript">
        window.onload = function() {
          window.print();
        }
    </script>
    <div class="col-lg-12">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>
                        
                    <table>
                      <? $ckey = $this->session->company->ekey;
                        $this->entreprise = $this->m_entreprises->get_key($ckey);

                        function int2str($a)
                      {
                          $convert = explode('.',$a);
                        /*if (isset($convert[1]) && $convert[1]!=''){
                        return int2str($convert[0]).'Dinars'.' et '.int2str($convert[1]).'Centimes' ;
                        }*/
                        if (isset($convert[1]) && $convert[1]!=''){
                        return int2str($convert[0]);
                        }
                        if ($a<0) return 'moins '.int2str(-$a);
                        if ($a<17){
                          switch ($a){
                          //case 0: return 'zero';
                          case 1: return 'un';
                          case 2: return 'deux';
                          case 3: return 'trois';
                          case 4: return 'quatre';
                          case 5: return 'cinq';
                          case 6: return 'six';
                          case 7: return 'sept';
                          case 8: return 'huit';
                          case 9: return 'neuf';
                          case 10: return 'dix';
                          case 11: return 'onze';
                          case 12: return 'douze';
                          case 13: return 'treize';
                          case 14: return 'quatorze';
                          case 15: return 'quinze';
                          case 16: return 'seize';
                          }
                        } else if ($a<20){
                          return 'dix-'.int2str($a-10);
                          } else if ($a<100){
                          if ($a%10==0){
                          switch ($a){
                          case 20: return 'vingt';
                          case 30: return 'trente';
                          case 40: return 'quarante';
                          case 50: return 'cinquante';
                          case 60: return 'soixante';
                          case 70: return 'soixante-dix';
                          case 80: return 'quatre-vingt';
                          case 90: return 'quatre-vingt-dix';
                          }
                          } elseif (substr($a, -1)==1){
                          if( ((int)($a/10)*10)<70 ){
                          return int2str((int)($a/10)*10).'-et-un';
                          } elseif ($a==71) {
                          return 'soixante-et-onze';
                          } elseif ($a==81) {
                          return 'quatre-vingt-un';
                          } elseif ($a==91) {
                          return 'quatre-vingt-onze';
                          }
                          } elseif ($a<70){
                          return int2str($a-$a%10).'-'.int2str($a%10);
                          } elseif ($a<80){
                          return int2str(60).'-'.int2str($a%20);
                          } else{
                          return int2str(80).'-'.int2str($a%20);
                          }
                          } else if ($a==100){
                          return 'cent';
                          } else if ($a<200){
                          return int2str(100).' '.int2str($a%100);
                          } else if ($a<1000){
                          return int2str((int)($a/100)).' '.int2str(100).' '.int2str($a%100);
                          } else if ($a==1000){
                          return 'mille';
                          } else if ($a<2000){
                          return int2str(1000).' '.int2str($a%1000).' ';
                          } else if ($a<1000000){
                          return int2str((int)($a/1000)).' '.int2str(1000).' '.int2str($a%1000);
                          }
                          else if ($a==1000000){
                          return 'millions';
                          }
                          else if ($a<2000000){
                          return int2str(1000000).' '.int2str($a%1000000).' ';
                          }
                          else if ($a<1000000000){
                          return int2str((int)($a/1000000)).' '.int2str(1000000).' '.int2str($a%1000000);
                          }
                      }
                        $reponsedate = mdate("%Y-%m-%d", now('UTC'));
                        $dat = explode("-", $reponsedate);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];?>
                   
                        <tr><td width="25%" style="font-size: 15px;">Reçu Nº:<?= $itemrecu->tamponcod; ?></td><td colspan="3" align=right></td><td align=center style="font-size: 15px;">Reçu Nº:<?= $itemrecu->tamponcod; ?></td>
                        </tr>
                        <tr><td align=left style="font-size: 17px;"><?= $itemrecu->nom_client; ?> <?= $itemrecu->prenom_client; ?> </td><td colspan="3" align=left style="font-size: 17px;"></td><td align=center style="font-size: 15px;"><?= $itemrecu->nom_client; ?> <?= $itemrecu->prenom_client; ?></td>
                        </tr>
                        
                        <tr><td align=left style="font-size: 15px;"> <?= $itemrecu->nom_ligne; ?>Aller-retour</td></td><td colspan="3" align=left style="font-size: 15px;"></td><td align=center style="font-size: 15px;"><?= $itemrecu->nom_ligne; ?> Aller-retour</td>
                        </tr>
                        <tr><td align=left>Somme de:<?= $itemrecu->prixvente+$itemrecu->prixvente; ?> (<?= number_format($itemrecu->prixvente+$itemrecu->prixvente, 0, '', ' ') ; ?>  F CFA)</td><td colspan="3" align=left></td><td align=center style="font-size: 15px;">Somme de:<?= $itemrecu->prixvente+$itemrecu->prixvente; ?> (<?= number_format($itemrecu->prixvente+$itemrecu->prixvente, 0, '', ' ') ; ?>  F CFA)</td>
                        </tr>
                        <tr><td align=left width="30%"><?=$itemrecu->nom_gaep ; ?>, le:<?= $day; ?> <?= $itemrecu->nom_compagnie;?></td><td align=center colspan="3"></td><td align=center style="font-size: 15px;"><?=$itemrecu->nom_gaep ; ?>, le:<?= $day; ?>  <?= $itemrecu->nom_compagnie;?></td>
                        </tr>
                      </table>
                        
                    </body>
                </div>
            </div>

        </div>

    </div>

</div>

<!--End of file: editrecusar.php-->
<!--File location: application/views/beagle/pages/_tickets/editrecusar.php-->