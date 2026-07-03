<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
                          
                            $fiche = $this->m_non_passager->getad($this->entreprise->ekey, $itemar->code_non_pass);

                            $key = mdate("%Y-%m-%d", now());

                            $tim = date('H', time('H'));

                            if($tim === '00')
                            {
                                $dats = date('01:00:00', time('01:00:00'));
                            }
                            else
                            {
                                $dats = date('H:i:s', time('H:i:s'));
                            }
                            $dtoday = $key.' à '.$dats;
                          ?>
                                <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($fiche->logo);?>" width="300" height=""></td></tr>
                                <tr><td style="font-size: 20px;"><b>RETOUR</b></td></tr>
                                <tr><td style="font-size: 20px;"><b><?= "{$fiche->codeticket}"; ?></b></td></tr>
                                <tr><td style="font-size: 20px;"><?= $fiche->nom_gadest; ?>-<?= $fiche->nom_gaep; ?></td></tr>
                                <tr><td style="font-size: 20px;"><?= $fiche->nom_client; ?>&nbsp; <?= $fiche->prenom_client; ?></td></tr>                        
                                <tr><td style="font-size: 20px;">Date_depart:</td></tr>
                                <tr><td style="font-size: 20px;">Heure_depart:</td></tr>
                                <tr><td style="font-size: 20px;">Prix:<?= number_format($fiche->prixretour, 0, '', ' '); ?> &nbsp;FCFA &nbsp;<?= "{$fiche->contact_client}"; ?></td></tr>
                                <tr><td style="border:2px solid; font-size: 23px;"><b>N° BUS :... </b></td></tr>
                                <tr><td style="font-size: 9px;">Billet valable 1 mois. Billet non remboursable</td></tr>
                                <tr><td style="font-size: 9px;"><?= $fiche->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                                <tr><td style="font-size: 9px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                                <tr><td style="font-size: 9px;">Suivez et surveillez bien vos bagages</td></tr>
                                <tr><td>BON VOYAGE AVEC <?= $fiche->nom_compagnie;?></td></tr>
                                <tr><td style="font-size: 35px; width: 90%;"> <img src="<?echo site_url('render/Barcode/'.$fiche->code_non_pass);?>" width="350" height="45"></td></tr>
                                <tr><td style="font-size: 15px;">emis : <?= $dtoday; ?></td></tr>
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
                          
                            $fiche = $this->m_non_passager->getad($this->entreprise->ekey, $itemar->code_non_pass);?>
                            <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($fiche->logo);?>" width="300" height=""></td></tr>
                          <tr><td style="font-size: 20px;"><b><?= "{$fiche->code_non_pass}"; ?></b></td></tr>
                          <tr><td style="font-size: 20px;"><b><?= "{$fiche->codeticket}"; ?></b></td></tr>
                          <tr><td style="font-size: 20px;"><?= $fiche->nom_gadest; ?>-<?= $fiche->nom_gaep; ?></td></tr>
                          <tr><td style="font-size: 20px;"><?= $fiche->nom_client; ?> &nbsp;<?= $fiche->prenom_client; ?></td></tr>
                          <tr><td style="font-size: 20px;"><b><?=$fiche->datevente; ?></b></td></tr>
						  <tr><td style="font-size: 20px;">Prix:<?= number_format($fiche->prixretour, 0, '', ' '); ?> &nbsp;FCFA &nbsp;<?= "{$fiche->contact_client}"; ?></td></tr>
							<tr><td>RETOUR</td></tr>
                        </table>
                    </body>
                </div>
            </div>

        </div>
    </div>

<!--End of file: epretour.php-->
<!--File location: application/views/beagle/pages/_tickets/epretour.php-->