<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
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

    <div class="col-lg-6">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>

                    <table>
                        <? $ckey = $this->session->company->ekey;
                        $this->entreprise = $this->m_entreprises->get_key($ckey);
                        
                         $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $item->code_gaexp, $item->departclient_idgare, $item->ident_ligne, $item->id_ligneheure);

                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $item->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                              
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $item->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $item->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }
                            $tim = date('H', time('H'));

                          if($tim === '00')
                          {
                              $dats = date('01:00:00', time('01:00:00'));
                          }
                          else
                          {
                              $dats = date('H:i:s', time('H:i:s'));
                          }

                        $key = mdate("%Y-%m-%d", now());
                        $dtoday = $key.' à '.$dats;
                        $nom = $item->code_progr;
                        $nge = substr($nom, 6, 6);

                        $dx = explode($item->gareidentif, $item->depart_code);

                          $x =$dx[1];

                        $dat = explode("-", $item->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                        ?>
                        <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($item->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 60px;"><b>TICKET CODE : <?= "{$item->code_ticket}"; ?></b></td></tr>
                        <tr><td style="font-size: 60px;"><?= "{$item->nom_gaep}"; ?> <?= "{$ressougare->nomsousgare}"; ?>-<?= "{$item->nom_gadest}"; ?> <?= "{$item->quart}"; ?></td></tr>
                        <tr><td style="font-size: 65px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td></tr>
                        <tr><td style="font-size: 70px;">Contact:<?= "{$item->contact_client}"; ?></td></tr>
                        
                        <tr><td style="font-size: 70px;"><b><?= "{$day}"; ?>&nbsp;&nbsp; <?= $heures; ?></b></td></tr>
                        <tr><td style="font-size: 60px;">Siege: <?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?><b>&nbsp;&nbsp;&nbsp;<?= number_format("{$item->prix}", 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="border:2px solid; font-size: 60px;"><b>N° BUS :<?=$x;?></b></td></tr>
                        <tr><td style="font-size: 45px;">BON VOYAGE AVEC <?= $item->nom_compagnie;?> <?= $nge;?></td></tr>
                        <tr><td style="font-size: 35px; width:40%;"> <?= ticket_barcode_img($item->tamponcod, 450, 40); ?></td></tr>
                        <tr><td style="font-size: 60px;">emis : <?= $dtoday; ?></td></tr>
                        </table>
                    </body>
                </div>
            </div>

        </div>
    </div>

<!--End of file: pdfepsonreduit.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfepsonreduit.php-->