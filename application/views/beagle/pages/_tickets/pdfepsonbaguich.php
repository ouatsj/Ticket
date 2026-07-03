<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/voirbagage/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i> 
        </a>
    </p>
</div>
<script type="text/javascript">
    window.onload = function() {
      window.print();
    }
</script>
<style>
  .saut-page {
    page-break-after: always;
  }
</style>
    <div class="col-lg-6">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>

                    <table>
                        <? $ckey = $this->session->company->ekey;
                        $this->entreprise = $this->m_entreprises->get_key($ckey);
                        
                         $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itembag->code_gaexp, $itembag->departclient_idgare, $itembag->ident_ligne, $itembag->id_ligneheure);

                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $itembag->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                              
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $itembag->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $itembag->heure);
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
                        $nom = $itembag->code_progr;
                        $nge = substr($nom, 6, 6);

                        $dat = explode("-", $itembag->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                        ?>
                          <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembag->logo);?>" width="300" height=""></td></tr>
                          <tr><td style="font-size: 20px;"><b>Reçu N° : <?= str_pad($itembag->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                          <tr><td style="font-size: 20px;"><?= $itembag->nom_gaep; ?> <?= $ressougare->nomsousgare; ?>-<?= $itembag->nom_gadest; ?> <?= $itembag->quart; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact exp:<?= $itembag->contactexpedi; ?> <?= $itembag->genrebagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Nombre bagage&nbsp;:&nbsp;<b><?= $itembag->nombrebagage; ?>&nbsp;(<?=$itembag->typebagages; ?>)</b></td></tr>
                          <tr><td style="font-size: 20px;">Contenu:<?= $itembag->contenubagage; ?></td></tr>
                          
                          <tr><td style="font-size: 20px;">Valeur:<?= $itembag->valeurbagage; ?>&nbsp;FCFA</td></tr>
                          <tr><td style="font-size: 20px;"><b><?= $day; ?>&nbsp;&nbsp; <?= $heures; ?></b></td></tr>
                          <tr><td style="font-size: 20px;">Siege: <?= str_pad($itembag->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?><b>&nbsp;&nbsp;&nbsp;<?= number_format($itembag->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                          <tr><td style="font-size: 15px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itembag->username; ?> code <?= $itembag->codebag; ?></td></tr>
                          <tr><td style="font-size: 15px;"><?= $itembag->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 15px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 15px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 20px;">BON VOYAGE AVEC <?= $itembag->nom_compagnie;?></td></tr>
                        </table>
                    </body>
                </div>
            </div>

        </div>
    </div>
    <div class="saut-page"></div>
    <div class="col-lg-6">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>

                    <table>
                        <? $ckey = $this->session->company->ekey;
                        $this->entreprise = $this->m_entreprises->get_key($ckey);
                        
                         $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itembag->code_gaexp, $itembag->departclient_idgare, $itembag->ident_ligne, $itembag->id_ligneheure);

                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $itembag->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                              
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $itembag->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $itembag->heure);
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
                        $nom = $itembag->code_progr;
                        $nge = substr($nom, 6, 6);

                        $dat = explode("-", $itembag->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                        ?>
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembag->logo);?>" width="300" height=""></td></tr>
                        <tr><td style="font-size: 20px;"><b>Reçu N° : <?= str_pad($itembag->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                        <tr><td style="font-size: 20px;"><?= $itembag->nom_gaep; ?> <?= $ressougare->nomsousgare; ?>-<?= $itembag->nom_gadest; ?> <?= $itembag->quart; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact exp:<?= $itembag->contactexpedi; ?> <?= $itembag->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Nombre bagage&nbsp;:&nbsp;<b><?= $itembag->nombrebagage; ?>&nbsp;(<?=$itembag->typebagages; ?>)</b></td></tr>
                        <tr><td style="font-size: 20px;">Contenu:<?= $itembag->contenubagage; ?></td></tr>
                        
                        <tr><td style="font-size: 20px;">Valeur:<?= $itembag->valeurbagage; ?>&nbsp;FCFA</td></tr>
                        <tr><td style="font-size: 20px;"><b><?= $day; ?>&nbsp;&nbsp; <?= $heures; ?></b></td></tr>
                        <tr><td style="font-size: 20px;">Siege: <?= str_pad($itembag->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?><b>&nbsp;&nbsp;&nbsp;<?= number_format($itembag->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 15px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itembag->username; ?> code <?= $itembag->codebag; ?></td></tr>
                          <tr><td style="font-size: 15px;"><?= $itembag->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 15px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 15px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 20px;">BON VOYAGE AVEC <?= $itembag->nom_compagnie;?></td></tr>
                        </table>
                    </body>
                </div>
            </div>

        </div>
    </div>

<!--End of file: pdfepsonbaguich.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfepsonbaguich.php-->