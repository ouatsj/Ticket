<?php defined('BASEPATH') OR exit('No direct script access allowed');?>

  <div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
          <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. (isset($bus_stop->idengare) ? $bus_stop->idengare : '').'/compte/'. (isset($conex->roleattribut) ? $conex->roleattribut : '').'/'. (isset($bus_stop->idsousgare) ? $bus_stop->idsousgare : '').'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
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
                      <? if (empty($item)) : ?>
                        <tr><td><strong>Billet introuvable.</strong> Réimprimez depuis l&apos;historique.</td></tr>
                      <? else :
                        $ctx = ticket_print_ctx($item, isset($bus_stop) ? $bus_stop : null);
                        $heures = $ctx['heures'];
                        $sg_label = $ctx['sg_label'];
                        $day = $ctx['day'];
                        $nge = $ctx['nge'];
                        $x = $ctx['nbus'];
                        $dtoday = $ctx['dtoday'];
                        $prix = isset($item->prix) ? $item->prix : 0;
                        ?>
                        
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($item->logo);?>" width="300" height=""></td></tr>
                        <tr><td style="font-size: 20px;"><b>TICKET CODE : <?= "{$item->code_ticket}"; ?></b></td></tr>
                        <tr><td style="font-size: 20px;"><?= "{$item->nom_gaep}"; ?> <?= $sg_label; ?>-<?= ticket_destination_label($item); ?> <?= "{$item->quart}"; ?></td></tr>
                        <tr><td style="font-size: 20px;"><?= $item->nom_client; ?>&nbsp; <?= $item->prenom_client; ?></td></tr>
                        <tr><td style="font-size: 20px;"><b><?= "{$day}"; ?> &nbsp;<?= "{$heures}"; ?></b></td></tr>
                        <tr><td style="font-size: 20px;">Siege : <b><?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b style="border:2px solid; font-size: 23px;"> N° BUS :<?=$x;?></td></b></tr>
                        <tr><td style="font-size: 20px;"><?= number_format((float) $prix, 0, '', ' '); ?> FCFA&nbsp; <?= "{$item->contact_client}"; ?></td></tr>
                        <tr><td>CONVOCATION 45 mn avant le départ</td></tr>
                        <tr><td style="font-size: 9px;">Billet valable 1 mois. Billet non remboursable</td></tr>
                        <tr><td style="font-size: 9px;"><?= $item->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 9px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 9px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td>BON VOYAGE AVEC <?= $item->nom_compagnie;?> <?= $nge;?></td></tr>
                        <tr><td style="font-size: 35px; width: 90%;"> <?= ticket_barcode_img($item->tamponcod, 250, 40); ?></td></tr>
                        <tr><td style="font-size: 15px;">emis : <?= $dtoday; ?> CONFIRMER</td></tr>
                      <? endif; ?>
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

                <table style="font-size: 20px; ">
                    <? if (empty($item)) : ?>
                      <tr><td><strong>Billet introuvable.</strong></td></tr>
                    <? else :
                      $ctx = ticket_print_ctx($item, isset($bus_stop) ? $bus_stop : null);
                      $heures = $ctx['heures'];
                      $sg_label = $ctx['sg_label'];
                      $day = $ctx['day'];
                      $prix = isset($item->prix) ? $item->prix : 0;
                      ?>
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($item->logo);?>" width="300" height=""></td></tr>
                      <tr><td style="font-size: 20px;"><b><?= "{$item->tamponcod}"; ?></b></td></tr>
                      <tr><td style="font-size: 20px;"><b><?= "{$item->code_ticket}"; ?></b></td></tr>
                      <tr><td style="font-size: 20px;"><?= "{$item->nom_gaep}"; ?> <?= $sg_label; ?>-<?= ticket_destination_label($item); ?> <?= "{$item->quart}"; ?></td></tr>
                      <tr><td style="font-size: 20px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td></tr>
                        <tr><td style="font-size: 20px;"><b><?= "{$day}"; ?> &nbsp;<?= "{$heures}"; ?></b></td></tr>
                      <tr><td style="font-size: 20px;">Siege:<b><?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b></td></tr>
						          <tr><td style="font-size: 20px;">Prix:<?= number_format((float) $prix, 0, '', ' '); ?> &nbsp;FCFA &nbsp;<?= "{$item->contact_client}"; ?></td></tr>
                        <tr><td>CONFIRMER</td></tr>
                    <? endif; ?>
                        </table>
                    </body>
                </div>
            </div>

        </div>

    </div>

<!--End of file: editpdfepconf.php-->
<!--File location: application/views/beagle/pages/_tickets/editpdfepconf.php-->
