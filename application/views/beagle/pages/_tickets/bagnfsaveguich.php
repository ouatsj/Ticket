<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bagagenonfact/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
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
                          $dat = explode("-", $itembag->date_create);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                          $cdebg = explode("-", $itembag->id_bagage);
                          $encd = $cdebg[0];
                        ?>
                          <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembag->logo);?>" width="300" height=""></td></tr>
                          <tr><td style="font-size: 20px;"><b>Reçu N° : <?=$encd; ?></b></td></tr>
                          <tr><td style="font-size: 20px;"><?= $itembag->nom_gaep; ?> <?= "{$bus_stop->nomsousgare}"; ?>-<?= $itembag->nom_gadest; ?> <?= $itembag->quartarr_bg; ?></td></tr>
                          <tr><td style="font-size: 20px;">Contact exp:<?= $itembag->contactexpedi; ?> <?= $itembag->genrebagage; ?></td></tr>

                          <tr><td style="font-size: 20px;">Client:<?= $itembag->nom_client; ?> <?= $itembag->prenom_client; ?></td></tr>

                          <tr><td style="font-size: 20px;">Nombre bagage&nbsp;:&nbsp;<b><?= $itembag->nombrebagage; ?>&nbsp;(<?=$itembag->typebagages; ?>)</b></td></tr>
                          <tr><td style="font-size: 20px;">Contenu:<?= $itembag->contenubagage; ?></td></tr>
                          
                          <tr><td style="font-size: 20px;">Valeur:<?= $itembag->valeurbagage; ?>&nbsp;FCFA</td></tr>

                          <tr><td style="font-size: 20px;">Enregistrer le :<b><?= $day; ?></b></td></tr>
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
                          $dat = explode("-", $itembag->date_create);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                           $cdebg = explode("-", $itembag->id_bagage);
                          $encd = $cdebg[0];
                        ?>

                            <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itembag->logo);?>" width="300" height=""></td></tr>
                              <tr><td style="font-size: 20px;"><b>Reçu N° : <?=$encd; ?></b></td></tr>
                              <tr><td style="font-size: 20px;"><?= $itembag->nom_gaep; ?> <?= "{$bus_stop->nomsousgare}"; ?>-<?= $itembag->nom_gadest; ?> <?= $itembag->quartarr_bg; ?></td></tr>
                              <tr><td style="font-size: 20px;">Contact exp:<?= $itembag->contactexpedi; ?> <?= $itembag->genrebagage; ?></td></tr>
                              <tr><td style="font-size: 20px;">Client:<?= $itembag->nom_client; ?> <?= $itembag->prenom_client; ?></td></tr>

                              <tr><td style="font-size: 20px;">Nombre bagage&nbsp;:&nbsp;<b><?= $itembag->nombrebagage; ?>&nbsp;(<?=$itembag->typebagages; ?>)</b></td></tr>
                              <tr><td style="font-size: 20px;">Contenu:<?= $itembag->contenubagage; ?></td></tr>
                              
                              <tr><td style="font-size: 20px;">Valeur:<?= $itembag->valeurbagage; ?>&nbsp;FCFA</td></tr>
                              
                              <tr><td style="font-size: 20px;">Enregistrer le :<b><?= $day; ?></b></td></tr>

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

<!--End of file: bagnfsaveguich.php-->
<!--File location: application/views/beagle/pages/_tickets/bagnfsaveguich.php-->