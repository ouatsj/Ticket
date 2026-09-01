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

                    $dat = explode("-", $item->datedepescal);
                    $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

                      
                    ?>
                    <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($item->logo);?>" width="850" height="350"></td></tr>
                    <tr><td style="font-size: 65px;"><b>CODE:<?= "{$item->idclescal}"; ?></b></td></tr>
                    <tr><td style="font-size: 60px;"><?= "{$item->nom_gaep}"; ?> <?= "{$item->nomsousgare}"; ?>-<?= "{$item->nom_gadest}"; ?> <?= "{$item->quartier_escal}"; ?></td></tr>
                    <tr><td style="font-size: 65px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td></tr>
                    <tr><td style="font-size: 70px;">Contact:<?= "{$item->contact_client}"; ?></td></tr>
                    <tr><td style="font-size: 70px;"><b><?= "{$day}"; ?>&nbsp;&nbsp; <?= $item->heure; ?></b></td></tr>
                    <tr><td style="font-size: 70px;">Prix : <?= number_format("{$item->prix}", 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                    <tr><td style="font-size: 50px;"><b>BON VOYAGE AVEC <?= $item->nom_compagnie;?></b></td></tr>
                    <tr><td style="font-size: 35px; width:40%;"> <?= ticket_barcode_img($item->idclescal, 400, 40); ?></td></tr>
                    <tr><td style="font-size: 50px;">emis : <?= $dtoday; ?></td></tr>
                    </table>
                </body>
            </div>
        </div>

    </div>
</div>

<!--<script type="text/javascript">
    window.onload = function() {
        document.deve.style.display = 'none';
    }
</script>-->

<!--End of file: pdfepsonescal.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfepsonescal.php-->