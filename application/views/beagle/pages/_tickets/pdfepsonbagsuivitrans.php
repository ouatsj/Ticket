<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bagagesuivimobile/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
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
                        
                         $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itembags->code_gaexp, $itembags->idsgarebag , $itembags->ident_ligne, $itembags->id_ligneheure);

                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $itembags->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                              
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $itembags->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $itembags->heure);
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
                        $nom = $itembags->code_progr;
                        $nge = substr($nom, 6, 6);

                        $dat = explode("-", $itembags->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                        ?>
                        <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($itembags->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 65px;"><b>Reçu N° : <?= str_pad($itembags->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                        <tr><td style="font-size: 60px;"><?= "{$itembags->nom_gaep}"; ?> <?= "{$ressougare->nomsousgare}"; ?>-<?= "{$itembags->nom_gadest}"; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact exp:<?= $itembags->contactexpedi; ?> <?= $itembags->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact dest:<?= $itembags->contact_client; ?></td></tr>
                        <tr><td style="font-size: 65px;"> <?= $itembags->nom_client; ?> <?= $itembags->prenom_client; ?></td></tr>
                        
                        <tr><td style="font-size: 65px;">Nombre bagage:<?= $itembags->nombrebagage; ?>&nbsp;(<?=$itembags->typebagages; ?>)</td></tr>
                        <tr><td style="font-size: 65px;">Contenu:<?= $itembags->contenubagage; ?></td></tr>
                        <tr><td style="font-size: 65px;">Valeur:<?= $itembags->valeurbagage; ?>FCFA</td></tr>
                        <tr><td style="font-size: 65px;"><b><?= $day; ?>&nbsp;&nbsp; <?= $heures; ?></b></td></tr>
                        <tr><td style="font-size: 65px;">Prix:<?= number_format($itembags->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 40px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itembags->username; ?> code <?= $itembags->codebag; ?></td></tr>
                        <tr><td style="font-size: 40px;"><?= $itembags->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 40px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 40px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 50px;">BON VOYAGE AVEC <?= $itembags->nom_compagnie;?></td></tr>
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
                        
                         $ressougare2 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itembags2->code_gaexp, $itembags2->idsgarebag , $itembags2->ident_ligne, $itembags2->id_ligneheure);

                        if($ressougare2->possitiongare === 'Maintenant'){

                              $g2 = explode(":", $itembags2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] + $ressougare2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                            $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                              
                        }

                        if($ressougare2->possitiongare === 'Avant'){
                              $g2 = explode(":", $itembags2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] - $ressougare2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                            $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                        }

                        if($ressougare2->possitiongare === 'Apres'){
                              $g2 = explode(":", $itembags2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] + $ressougare2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                            $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                        }
                        $tim1 = date('H', time('H'));

                          if($tim1 === '00')
                          {
                              $dats2 = date('01:00:00', time('01:00:00'));
                          }
                          else
                          {
                              $dats2 = date('H:i:s', time('H:i:s'));
                          }

                          $key1 = mdate("%Y-%m-%d", now());
                          $dtoday2 = $key1.' à '.$dats2;
                          
                          $dat2 = explode("-", $itembags2->date_progr);
                          $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];
                          ?>
                          <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($itembags2->logo);?>" width="850" height="350"></td></tr>
                          <tr><td style="font-size: 65px;"><b>Reçu N° : <?= str_pad($itembags2->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                          <tr><td style="font-size: 60px;"><?= "{$itembags2->nom_gaep}"; ?> <?= "{$ressougare2->nomsousgare}"; ?>-<?= "{$itembags2->nom_gadest}"; ?></td></tr>
                          <tr><td style="font-size: 65px;">Contact exp:<?= $itembags2->contactexpedi; ?> <?= $itembags2->genrebagage; ?></td></tr>
                          <tr><td style="font-size: 65px;">Contact dest:<?= $itembags2->contact_client; ?></td></tr>
                          <tr><td style="font-size: 65px;"> <?= $itembags2->nom_client; ?> <?= $itembags2->prenom_client; ?></td></tr>
                          <tr><td style="font-size: 65px;">Nombre bagage:<?= $itembags2->nombrebagage; ?>&nbsp;(<?=$itembags2->typebagages; ?>)</td></tr>
                          <tr><td style="font-size: 65px;">Contenu:<?= $itembags2->contenubagage; ?></td></tr>
                          <tr><td style="font-size: 65px;">Valeur:<?= $itembags2->valeurbagage; ?>FCFA</td></tr>
                          <tr><td style="font-size: 65px;"><b><?= $day2; ?>&nbsp;&nbsp; <?= $heures2; ?></b></td></tr>
                          <tr><td style="font-size: 65px;">Prix:<?= number_format($itembags2->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                          <tr><td style="font-size: 40px;">emis : <?= $dtoday2; ?> &nbsp;par <?= $itembags2->username; ?> code <?= $itembags2 ->codebag; ?></td></tr>
                          <tr><td style="font-size: 40px;"><?= $itembags2->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                          <tr><td style="font-size: 40px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                          <tr><td style="font-size: 40px;">Suivez et surveillez bien vos bagages</td></tr>
                          <tr><td style="font-size: 50px;">BON VOYAGE AVEC <?= $itembags2->nom_compagnie;?></td></tr>
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
                        
                         $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itembags->code_gaexp, $itembags->idsgarebag , $itembags->ident_ligne, $itembags->id_ligneheure);

                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $itembags->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                              
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $itembags->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                            $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $itembags->heure);
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
                        $nom = $itembags->code_progr;
                        $nge = substr($nom, 6, 6);

                        $dat = explode("-", $itembags->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                        ?>
                        <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($itembags->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 65px;"><b>Reçu N° : <?= str_pad($itembags->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                        <tr><td style="font-size: 60px;"><?= "{$itembags->nom_gaep}"; ?> <?= "{$ressougare->nomsousgare}"; ?>-<?= "{$itembags->nom_gadest}"; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact exp:<?= $itembags->contactexpedi; ?> <?= $itembags->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact dest:<?= $itembags->contact_client; ?></td></tr>
                        <tr><td style="font-size: 65px;"> <?= $itembags->nom_client; ?> <?= $itembags->prenom_client; ?></td></tr>
                        <tr><td style="font-size: 65px;">Nombre bagage:<?= $itembags->nombrebagage; ?>&nbsp;(<?=$itembags->typebagages; ?>)</td></tr>
                        <tr><td style="font-size: 65px;">Contenu:<?= $itembags->contenubagage; ?></td></tr>
                        <tr><td style="font-size: 65px;">Valeur:<?= $itembags->valeurbagage; ?>FCFA</td></tr>
                        <tr><td style="font-size: 65px;"><b><?= $day; ?>&nbsp;&nbsp; <?= $heures; ?></b></td></tr>
                        <tr><td style="font-size: 65px;">Prix:<?= number_format($itembags->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 40px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itembags->username; ?> code <?= $itembags->codebag; ?></td></tr>
                        <tr><td style="font-size: 40px;"><?= $itembags->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 40px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 40px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 50px;">BON VOYAGE AVEC <?= $itembags->nom_compagnie;?></td></tr>
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
                        
                         $ressougare2 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itembags2->code_gaexp, $itembags2->idsgarebag , $itembags2->ident_ligne, $itembags2->id_ligneheure);

                        if($ressougare2->possitiongare === 'Maintenant'){

                              $g2 = explode(":", $itembags2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] + $ressougare2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                            $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                              
                        }

                        if($ressougare2->possitiongare === 'Avant'){
                              $g2 = explode(":", $itembags2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] - $ressougare2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                            $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                        }

                        if($ressougare2->possitiongare === 'Apres'){
                              $g2 = explode(":", $itembags2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] + $ressougare2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                            $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                        }
                          $tim1 = date('H', time('H'));

                          if($tim1 === '00')
                          {
                              $dats2 = date('01:00:00', time('01:00:00'));
                          }
                          else
                          {
                              $dats2 = date('H:i:s', time('H:i:s'));
                          }

                          $key1 = mdate("%Y-%m-%d", now());
                          $dtoday2 = $key1.' à '.$dats2;
                          
                          $dat2 = explode("-", $itembags2->date_progr);
                          $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];
                          ?>
                          <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($itembags2->logo);?>" width="850" height="350"></td></tr>
                          <tr><td style="font-size: 65px;"><b>Reçu N° : <?= str_pad($itembags2->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                          <tr><td style="font-size: 60px;"><?= "{$itembags2->nom_gaep}"; ?> <?= "{$ressougare2->nomsousgare}"; ?>-<?= "{$itembags2->nom_gadest}"; ?></td></tr>
                          <tr><td style="font-size: 65px;">Contact exp:<?= $itembags2->contactexpedi; ?> <?= $itembags2->genrebagage; ?></td></tr>
                          <tr><td style="font-size: 65px;">Contact dest:<?= $itembags2->contact_client; ?></td></tr>
                          <tr><td style="font-size: 65px;"> <?= $itembags2->nom_client; ?> <?= $itembags2->prenom_client; ?></td></tr>
                          <tr><td style="font-size: 65px;">Nombre bagage:<?= $itembags2->nombrebagage; ?>&nbsp;(<?=$itembags2->typebagages; ?>)</td></tr>
                          <tr><td style="font-size: 65px;">Contenu:<?= $itembags2->contenubagage; ?></td></tr>
                          <tr><td style="font-size: 65px;">Valeur:<?= $itembags2->valeurbagage; ?>FCFA</td></tr>
                          <tr><td style="font-size: 65px;"><b><?= $day2; ?>&nbsp;&nbsp; <?= $heures2; ?></b></td></tr>
                          <tr><td style="font-size: 65px;">Prix:<?= number_format($itembags2->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                          <tr><td style="font-size: 40px;">emis : <?= $dtoday2; ?> &nbsp;par <?= $itembags2->username; ?> code <?= $itembags2 ->codebag; ?></td></tr>
                          <tr><td style="font-size: 40px;"><?= $itembags2->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                          <tr><td style="font-size: 40px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                          <tr><td style="font-size: 40px;">Suivez et surveillez bien vos bagages</td></tr>
                          <tr><td style="font-size: 50px;">BON VOYAGE AVEC <?= $itembags2->nom_compagnie;?></td></tr>
                        </table>
                    </body>
                </div>
            </div>

        </div>
    </div>

<!--End of file: pdfepsonbagsuivitrans.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfepsonbagsuivitrans.php-->