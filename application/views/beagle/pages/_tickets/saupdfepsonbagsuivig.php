<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/autrebagagefc/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
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
                          $dat = explode("-", $itembags->date_create);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                          $cdebg = explode("-", $itembags->id_bagage);
                          $encd = $cdebg[0];
                        ?>
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembags->logo);?>" width="300" height=""></td></tr>
                        <tr><td style="font-size: 20px;"><b>Reçu N° : <?=$encd; ?></b></td></tr>
                        <tr><td style="font-size: 20px;"><?= "{$itembags->nom_gaep}"; ?> <?= "{$bus_stop->nomsousgare}"; ?>-<?= "{$itembags->nom_gadest}"; ?> <?= $itembags->quartarr_bg; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact exp:<?= $itembags->contactexpedi; ?> <?= $itembags->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact dest:<?= $itembags->contact_client; ?></td></tr>
                        <tr><td style="font-size: 20px;"> <?= $itembags->nom_client; ?> <?= $itembags->prenom_client; ?></td></tr>
                        
                        <tr><td style="font-size: 20px;">Nombre bagage:<?= $itembags->nombrebagage; ?>&nbsp;(<?=$itembags->typebagages; ?>)</td></tr>
                        <tr><td style="font-size: 20px;">Contenu:<?= $itembags->contenubagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Valeur:<?= $itembags->valeurbagage; ?>FCFA</td></tr>
                        <tr><td style="font-size: 20px;">Facturer le <b><?= $day; ?></td></tr>
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
                          $dat = explode("-", $itembags->date_create);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                          $cdebg = explode("-", $itembags->id_bagage);
                          $encd = $cdebg[0];
                        ?>
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembags->logo);?>" width="300" height=""></td></tr>
                        <tr><td style="font-size: 20px;"><b>Reçu N° : <?=$encd; ?></b></td></tr>
                        <tr><td style="font-size: 20px;"><?= "{$itembags->nom_gaep}"; ?> <?= "{$bus_stop->nomsousgare}"; ?>-<?= "{$itembags->nom_gadest}"; ?> <?= $itembags->quartarr_bg; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact exp:<?= $itembags->contactexpedi; ?> <?= $itembags->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Contact dest:<?= $itembags->contact_client; ?></td></tr>
                        <tr><td style="font-size: 20px;"> <?= $itembags->nom_client; ?> <?= $itembags->prenom_client; ?></td></tr>
                        <tr><td style="font-size: 20px;">Nombre bagage:<?= $itembags->nombrebagage; ?>&nbsp;(<?=$itembags->typebagages; ?>)</td></tr>
                        <tr><td style="font-size: 20px;">Contenu:<?= $itembags->contenubagage; ?></td></tr>
                        <tr><td style="font-size: 20px;">Valeur:<?= $itembags->valeurbagage; ?>FCFA</td></tr>
                        <tr><td style="font-size: 20px;">Facturer le <b><?= $day; ?></td></tr>
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

<!--End of file: saupdfepsonbagsuivig.php-->
<!--File location: application/views/beagle/pages/_tickets/saupdfepsonbagsuivig.php-->