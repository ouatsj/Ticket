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
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembags->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 20px;"><b>Reçu N° : <?= str_pad($itembags->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                        <tr><td style="font-size: 20px;"><?= "{$itembags->nom_gaep}"; ?> <?= "{$ressougare->nomsousgare}"; ?>-<?= "{$itembags->nom_gadest}"; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact exp:<?= $itembags->contactexpedi; ?> <?= $itembags->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact dest:<?= $itembags->contact_client; ?></td></tr>
                        <tr><td style="font-size: 20px;"> <?= $itembags->nom_client; ?> <?= $itembags->prenom_client; ?></td></tr>
                        
                        <tr><td style="font-size: 20px;">Nombre bagage:<?= $itembags->nombrebagage; ?>&nbsp;(<?=$itembags->typebagages; ?>)</td></tr>
                        <tr><td style="font-size: 20px;">Contenu:<?= $itembags->contenubagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Valeur:<?= $itembags->valeurbagage; ?>FCFA</td></tr>
                        <tr><td style="font-size: 20px;"><b><?= $day; ?>&nbsp;&nbsp; <?= $heures; ?></b></td></tr>
                        <tr><td style="font-size: 20px;">Prix:<?= number_format($itembags->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 15px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itembags->username; ?> code <?= $itembags->codebag; ?></td></tr>
                        <tr><td style="font-size: 15px;"><?= $itembags->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 15px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 15px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 20px;">BON VOYAGE AVEC <?= $itembags->nom_compagnie;?></td></tr>
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
                          <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembags2->logo);?>" width="850" height="350"></td></tr>
                          <tr><td style="font-size: 20px;"><b>Reçu N° : <?= str_pad($itembags2->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                          <tr><td style="font-size: 20px;"><?= "{$itembags2->nom_gaep}"; ?> <?= "{$ressougare2->nomsousgare}"; ?>-<?= "{$itembags2->nom_gadest}"; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact exp:<?= $itembags2->contactexpedi; ?> <?= $itembags2->genrebagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact dest:<?= $itembags2->contact_client; ?></td></tr>
                          <tr><td style="font-size: 20px;"> <?= $itembags2->nom_client; ?> <?= $itembags2->prenom_client; ?></td></tr>
                          <tr><td style="font-size: 20px;">Nombre bagage:<?= $itembags2->nombrebagage; ?>&nbsp;(<?=$itembags2->typebagages; ?>)</td></tr>
                          <tr><td style="font-size: 20px;">Contenu:<?= $itembags2->contenubagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Valeur:<?= $itembags2->valeurbagage; ?>FCFA</td></tr>
                          <tr><td style="font-size: 20px;"><b><?= $day2; ?>&nbsp;&nbsp; <?= $heures2; ?></b></td></tr>
                          <tr><td style="font-size: 20px;">Prix:<?= number_format($itembags2->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                          <tr><td style="font-size: 15px;">emis : <?= $dtoday2; ?> &nbsp;par <?= $itembags2->username; ?> code <?= $itembags2 ->codebag; ?></td></tr>
                          <tr><td style="font-size: 15px;"><?= $itembags2->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 15px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 15px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 20px;">BON VOYAGE AVEC <?= $itembags2->nom_compagnie;?></td></tr>
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
                        
                         $ressougare3 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itembags3->code_gaexp, $itembags3->idsgarebag , $itembags3->ident_ligne, $itembags3->id_ligneheure);

                        if($ressougare3->possitiongare === 'Maintenant'){

                              $g3 = explode(":", $itembags3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] + $ressougare3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                            $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                              
                        }

                        if($ressougare3->possitiongare === 'Avant'){
                              $g3 = explode(":", $itembags3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] - $ressougare3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                            $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                        }

                        if($ressougare3->possitiongare === 'Apres'){
                              $g3 = explode(":", $itembags3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] + $ressougare3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                            $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                        }
                          $tim1 = date('H', time('H'));

                          if($tim1 === '00')
                          {
                              $dats3 = date('01:00:00', time('01:00:00'));
                          }
                          else
                          {
                              $dats3 = date('H:i:s', time('H:i:s'));
                          }

                          $key1 = mdate("%Y-%m-%d", now());
                          $dtoday3 = $key1.' à '.$dats3;
                          
                          $dat3 = explode("-", $itembags3->date_progr);
                          $day3 = $dat3[2]. '-'. $dat3[1]. '-' .$dat3[0];
                          ?>
                          <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembags3->logo);?>" width="300" height=""></td></tr>
                          <tr><td style="font-size: 20px;"><b>Reçu N° : <?= str_pad($itembags3->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                          <tr><td style="font-size: 20px;"><?= "{$itembags3->nom_gaep}"; ?> <?= "{$ressougare3->nomsousgare}"; ?>-<?= "{$itembags3->nom_gadest}"; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact exp:<?= $itembags3->contactexpedi; ?> <?= $itembags3->genrebagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact dest:<?= $itembags3->contact_client; ?></td></tr>
                          <tr><td style="font-size: 20px;"> <?= $itembags3->nom_client; ?> <?= $itembags3->prenom_client; ?></td></tr>
                          <tr><td style="font-size: 20px;">Nombre bagage:<?= $itembags3->nombrebagage; ?>&nbsp;(<?=$itembags3->typebagages; ?>)</td></tr>
                          <tr><td style="font-size: 20px;">Contenu:<?= $itembags3->contenubagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Valeur:<?= $itembags3->valeurbagage; ?>FCFA</td></tr>
                          <tr><td style="font-size: 20px;"><b><?= $day3; ?>&nbsp;&nbsp; <?= $heures3; ?></b></td></tr>
                          <tr><td style="font-size: 20px;">Prix:<?= number_format($itembags3->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                          <tr><td style="font-size: 15px;">emis : <?= $dtoday3; ?> &nbsp;par <?= $itembags3->username; ?> code <?= $itembags3->codebag; ?></td></tr>
                          <tr><td style="font-size: 15px;"><?= $itembags3->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 15px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 15px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 20px;">BON VOYAGE AVEC <?= $itembags3->nom_compagnie;?></td></tr>
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
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembags->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 20px;"><b>Reçu N° : <?= str_pad($itembags->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                        <tr><td style="font-size: 20px;"><?= "{$itembags->nom_gaep}"; ?> <?= "{$ressougare->nomsousgare}"; ?>-<?= "{$itembags->nom_gadest}"; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact exp:<?= $itembags->contactexpedi; ?> <?= $itembags->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact dest:<?= $itembags->contact_client; ?></td></tr>
                        <tr><td style="font-size: 20px;"> <?= $itembags->nom_client; ?> <?= $itembags->prenom_client; ?></td></tr>
                        <tr><td style="font-size: 20px;">Nombre bagage:<?= $itembags->nombrebagage; ?>&nbsp;(<?=$itembags->typebagages; ?>)</td></tr>
                        <tr><td style="font-size: 20px;">Contenu:<?= $itembags->contenubagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Valeur:<?= $itembags->valeurbagage; ?>FCFA</td></tr>
                        <tr><td style="font-size: 20px;"><b><?= $day; ?>&nbsp;&nbsp; <?= $heures; ?></b></td></tr>
                        <tr><td style="font-size: 20px;">Prix:<?= number_format($itembags->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 15px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itembags->username; ?> code <?= $itembags->codebag; ?></td></tr>
                        <tr><td style="font-size: 15px;"><?= $itembags->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 15px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 15px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 20px;">BON VOYAGE AVEC <?= $itembags->nom_compagnie;?></td></tr>
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
                          <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembags2->logo);?>" width="300" height=""></td></tr>
                          <tr><td style="font-size: 20px;"><b>Reçu N° : <?= str_pad($itembags2->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                          <tr><td style="font-size: 20px;"><?= "{$itembags2->nom_gaep}"; ?> <?= "{$ressougare2->nomsousgare}"; ?>-<?= "{$itembags2->nom_gadest}"; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact exp:<?= $itembags2->contactexpedi; ?> <?= $itembags2->genrebagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact dest:<?= $itembags2->contact_client; ?></td></tr>
                          <tr><td style="font-size: 20px;"> <?= $itembags2->nom_client; ?> <?= $itembags2->prenom_client; ?></td></tr>
                          <tr><td style="font-size: 20px;">Nombre bagage:<?= $itembags2->nombrebagage; ?>&nbsp;(<?=$itembags2->typebagages; ?>)</td></tr>
                          <tr><td style="font-size: 20px;">Contenu:<?= $itembags2->contenubagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Valeur:<?= $itembags2->valeurbagage; ?>FCFA</td></tr>
                          <tr><td style="font-size: 20px;"><b><?= $day2; ?>&nbsp;&nbsp; <?= $heures2; ?></b></td></tr>
                          <tr><td style="font-size: 20px;">Prix:<?= number_format($itembags2->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                          <tr><td style="font-size: 15px;">emis : <?= $dtoday2; ?> &nbsp;par <?= $itembags2->username; ?> code <?= $itembags2 ->codebag; ?></td></tr>
                          <tr><td style="font-size: 15px;"><?= $itembags2->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 15px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 15px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 20px;">BON VOYAGE AVEC <?= $itembags2->nom_compagnie;?></td></tr>
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
                        
                         $ressougare3 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itembags3->code_gaexp, $itembags3->idsgarebag , $itembags3->ident_ligne, $itembags3->id_ligneheure);

                        if($ressougare3->possitiongare === 'Maintenant'){

                              $g3 = explode(":", $itembags3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] + $ressougare3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                            $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                              
                        }

                        if($ressougare3->possitiongare === 'Avant'){
                              $g3 = explode(":", $itembags3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] - $ressougare3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                            $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                        }

                        if($ressougare3->possitiongare === 'Apres'){
                              $g3 = explode(":", $itembags3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] + $ressougare3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                            $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                        }
                          $tim1 = date('H', time('H'));

                          if($tim1 === '00')
                          {
                              $dats3 = date('01:00:00', time('01:00:00'));
                          }
                          else
                          {
                              $dats3 = date('H:i:s', time('H:i:s'));
                          }

                          $key1 = mdate("%Y-%m-%d", now());
                          $dtoday3 = $key1.' à '.$dats3;
                          
                          $dat3 = explode("-", $itembags3->date_progr);
                          $day3 = $dat3[2]. '-'. $dat3[1]. '-' .$dat3[0];
                          ?>
                          <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembags3->logo);?>" width="300" height=""></td></tr>
                          <tr><td style="font-size: 20px;"><b>Reçu N° : <?= str_pad($itembags3->id_bagage, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                          <tr><td style="font-size: 20px;"><?= "{$itembags3->nom_gaep}"; ?> <?= "{$ressougare3->nomsousgare}"; ?>-<?= "{$itembags3->nom_gadest}"; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact exp:<?= $itembags3->contactexpedi; ?> <?= $itembags3->genrebagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact dest:<?= $itembags3->contact_client; ?></td></tr>
                          <tr><td style="font-size: 20px;"> <?= $itembags3->nom_client; ?> <?= $itembags3->prenom_client; ?></td></tr>
                          <tr><td style="font-size: 20px;">Nombre bagage:<?= $itembags3->nombrebagage; ?>&nbsp;(<?=$itembags3->typebagages; ?>)</td></tr>
                          <tr><td style="font-size: 20px;">Contenu:<?= $itembags3->contenubagage; ?></td></tr>
                          <tr><td style="font-size: 20px;">Valeur:<?= $itembags3->valeurbagage; ?>FCFA</td></tr>
                          <tr><td style="font-size: 20px;"><b><?= $day3; ?>&nbsp;&nbsp; <?= $heures3; ?></b></td></tr>
                          <tr><td style="font-size: 20px;">Prix:<?= number_format($itembags3->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                          <tr><td style="font-size: 15px;">emis : <?= $dtoday3; ?> &nbsp;par <?= $itembags3->username; ?> code <?= $itembags3->codebag; ?></td></tr>
                          <tr><td style="font-size: 15px;"><?= $itembags3->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 15px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 15px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 20px;">BON VOYAGE AVEC <?= $itembags3->nom_compagnie;?></td></tr>
                        </table>
                    </body>
                </div>
            </div>

        </div>
    </div>
<!--End of file: pdfepsonbagsuivitransg1.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfepsonbagsuivitransg1.php-->