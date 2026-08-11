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
                        if($item->prixvente === '0.00'){

                              $pri = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $pri = number_format($item->prixvente, 0, '', ' ').'FCFA';
                        }
                          $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $item->code_gaexp, $item->departclient_idgare, $item->ident_ligne, $item->id_ligneheure);

                          if($ressougare->possitiongare === 'Maintenant'){

                                $g = explode(":", $item->heure);
                                $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                                $heur = ($gt / 60); 
                                $secondes = round($gt % 60);
                                $heures = sprintf("%02d:%02d", $heur, $secondes);
                                //$heures = $heur.':'.$secondes;
                          }

                          if($ressougare->possitiongare === 'Avant'){
                                $g = explode(":", $item->heure);
                                $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                                $heur = ($gt / 60); 
                                $secondes = round($gt % 60);
                                      
                                //$heures = $heur.':'.$secondes;
                                $heures = sprintf("%02d:%02d", $heur, $secondes);
                          }

                          if($ressougare->possitiongare === 'Apres'){
                                $g = explode(":", $item->heure);
                                $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                                $heur = ($gt / 60); 
                                $secondes = round($gt % 60);
                                $heures = sprintf("%02d:%02d", $heur, $secondes);      
                                //$heures = $heur.':'.$secondes;

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

                          $dat = explode("-", $item->date_progr);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];?>
                        
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($item->logo);?>" width="300" height=""></td></tr>
                        <tr><td style="font-size: 20px;"><b>TICKET CODE : <?= "{$item->code_ticket}"; ?></b></td></tr>
                        <tr><td style="font-size: 20px;"><?= "{$item->nom_gaep}"; ?> <?= "{$ressougare->nomsousgare}"; ?>-<?= ticket_destination_label($item); ?> <?= "{$item->quart}"; ?></td></tr>
                        <tr><td style="font-size: 20px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td></tr>
                        <tr><td style="font-size: 20px;"><b><?= $day; ?>&nbsp;&nbsp;&nbsp;<?= $heures; ?></b></td></tr>
                        <tr><td style="font-size: 20px;">Siege : <b><?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b></td></tr>
                        <tr><td style="font-size: 20px;"><?= $pri; ?> &nbsp;<?= "{$item->contact_client}"; ?></td></tr>
                        <tr><td>CONVOCATION 45 mn avant le départ</td></tr>
                        <tr><td style="font-size: 9px;">Billet valable 1 mois. Billet non remboursable</td></tr>
                        <tr><td style="font-size: 9px;"><?= $item->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 9px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 9px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td>BON VOYAGE AVEC <?= $item->nom_compagnie;?> <?= $nge;?></td></tr>
                        <tr><td style="font-size: 40px; width: 100%;"> <img src="<?echo site_url('render/Barcode/'.$item->tamponcod);?>" width="350" height="45"></td></tr>
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
                    if($item->prixvente === '0.00'){

                        $pri = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $pri = number_format($item->prixvente, 0, '', ' ').'FCFA';
                        }
                  $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $item->code_gaexp, $item->departclient_idgare, $item->ident_ligne, $item->id_ligneheure);

                  if($ressougare->possitiongare === 'Maintenant'){

                        $g = explode(":", $item->heure);
                        $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                        $heur = ($gt / 60); 
                        $secondes = round($gt % 60);
                        $heures = sprintf("%02d:%02d", $heur, $secondes);
                        //$heures = $heur.':'.$secondes;
                  }

                  if($ressougare->possitiongare === 'Avant'){
                        $g = explode(":", $item->heure);
                        $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                        $heur = ($gt / 60); 
                        $secondes = round($gt % 60);
                              
                        //$heures = $heur.':'.$secondes;
                        $heures = sprintf("%02d:%02d", $heur, $secondes);
                  }

                  if($ressougare->possitiongare === 'Apres'){
                        $g = explode(":", $item->heure);
                        $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                        $heur = ($gt / 60); 
                        $secondes = round($gt % 60);
                        $heures = sprintf("%02d:%02d", $heur, $secondes);      
                        //$heures = $heur.':'.$secondes;

                  }
                  $nom = $item->code_progr;
                  $nge = substr($nom, 6, 6);

                  $dat = explode("-", $item->date_progr);
                  $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];?>
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($item->logo);?>" width="300" height=""></td></tr>
                      <tr><td style="font-size: 20px;"><b><?= "{$item->tamponcod}"; ?></b></td></tr>
                      <tr><td style="font-size: 20px;"><b><?= "{$item->code_ticket}"; ?></b></td></tr>
                      <tr><td style="font-size: 20px;"><?= "{$item->nom_gaep}"; ?> <?= "{$ressougare->nomsousgare}"; ?>-<?= ticket_destination_label($item); ?> <?= "{$item->quart}"; ?></td></tr>
                      <tr><td style="font-size: 20px;"><?= $item->nom_client; ?>&nbsp; <?= $item->prenom_client; ?></td></tr>
                      <tr><td style="font-size: 20px;"><b><?= $day; ?> &nbsp;<?= $heures; ?></td></tr>
                      <tr><td style="font-size: 20px;"><b><?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td></tr>
                      <tr><td style="font-size: 20px;">Prix:<?= $pri; ?> &nbsp;<?= "{$item->contact_client}"; ?></td></tr>
                        
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
                  
                          if($itemtrans->prixvente === '0.00'){

                        $prixt = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prixt = number_format($itemtrans->prixvente, 0, '', ' ').'FCFA';
                        }
                        
                          $ressougaretra = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itemtrans->code_gaexp, $itemtrans->departclient_idgare, $itemtrans->ident_ligne, $itemtrans->id_ligneheure);

                         
                          if($ressougaretra->possitiongare === 'Maintenant'){

                              $g1 = explode(":", $itemtrans->heure);
                              $gt1 = (($g1[0] * 60) + $g1[1] + $ressougaretra->minutetemps); 
                              $heur1 = ($gt1 / 60); 
                              $secondes1 = round($gt1 % 60);
                              $heures1 = sprintf("%02d:%02d", $heur1, $secondes1);
                            }

                            if($ressougaretra->possitiongare === 'Avant'){
                                  $g1 = explode(":", $itemtrans->heure);
                                  $gt1 = (($g1[0] * 60) + $g1[1] - $ressougaretra->minutetemps); 
                                  $heur1 = ($gt1 / 60); 
                                  $secondes1 = round($gt1 % 60);
                                  $heures1 = sprintf("%02d:%02d", $heur1, $secondes1);                   
                            }

                            if($ressougaretra->possitiongare === 'Apres'){
                                  $g1 = explode(":", $itemtrans->heure);
                                  $gt1 = (($g1[0] * 60) + $g1[1] + $ressougaretra->minutetemps); 
                                  $heur1 = ($gt1 / 60); 
                                  $secondes1 = round($gt1 % 60);
                                  $heures1 = sprintf("%02d:%02d", $heur1, $secondes1);
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
                              $nom1 = $itemtrans->code_progr;
                              $nge1 = substr($nom1, 6, 6);

                              
                              $dat1 = explode("-", $itemtrans->date_progr);
                              $day1 = $dat1[2]. '-'. $dat1[1]. '-' .$dat1[0];
                        ?>
                       <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itemtrans->logo);?>" width="300" height=""></td></tr>
                        	<tr><td style="font-size: 20px;"><b>TICKET CODE : <?= "{$itemtrans->code_ticket}"; ?></b></td></tr>
          							<tr><td style="font-size: 20px;"><?= "{$itemtrans->nom_gaep}"; ?> <?= "{$ressougaretra->nomsousgare}"; ?>-<?= ticket_destination_label($itemtrans); ?> <?= "{$itemtrans->quart}"; ?></td></tr>
          							<tr><td style="font-size: 20px;"><?= $item->nom_client; ?>&nbsp; <?= $item->prenom_client; ?></td></tr>
          							<tr><td style="font-size: 20px;"><b><?= "{$day1}"; ?> &nbsp;<?= $heures1; ?></b></td></tr>
          							<tr><td style="font-size: 20px;">Siege : <b><?= str_pad($itemtrans->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b></td></tr>
          							<tr><td style="font-size: 20px;"><?= $prixt; ?>&nbsp; <?= "{$item->contact_client}"; ?></td></tr>
          							<tr><td>CONVOCATION 45 mn avant le départ</td></tr>
          							<tr><td style="font-size: 9px;">Billet valable 1 mois. Billet non remboursable</td></tr>
          							<tr><td style="font-size: 9px;"><?= $itemtrans->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
          							<tr><td style="font-size: 9px;">perte ou de vol de billet et de bagages même payés.</td></tr>
          							<tr><td style="font-size: 9px;">Suivez et surveillez bien vos bagages</td></tr>
          							<tr><td>BON VOYAGE AVEC <?= $itemtrans->nom_compagnie;?> <?= $nge1;?></td></tr>
          							<tr><td style="font-size: 35px; width: 90%;"> <img src="<?echo site_url('render/Barcode/'.$itemtrans->tamponcod);?>" width="350" height="45"></td></tr>
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
                        if($itemtrans->prixvente === '0.00'){

                        $prixt = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prixt = number_format($itemtrans->prixvente, 0, '', ' ').'FCFA';
                        }
                      $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $item->code_gaexp, $item->departclient_idgare, $item->ident_ligne, $item->id_ligneheure);
                      
                          $ressougaretra = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itemtrans->code_gaexp, $itemtrans->departclient_idgare, $itemtrans->ident_ligne, $itemtrans->id_ligneheure);

                         
                          if($ressougaretra->possitiongare === 'Maintenant'){

                              $g1 = explode(":", $itemtrans->heure);
                              $gt1 = (($g1[0] * 60) + $g1[1] + $ressougaretra->minutetemps); 
                              $heur1 = ($gt1 / 60); 
                              $secondes1 = round($gt1 % 60);
                              $heures1 = sprintf("%02d:%02d", $heur1, $secondes1);
                            }

                            if($ressougaretra->possitiongare === 'Avant'){
                                  $g1 = explode(":", $itemtrans->heure);
                                  $gt1 = (($g1[0] * 60) + $g1[1] - $ressougaretra->minutetemps); 
                                  $heur1 = ($gt1 / 60); 
                                  $secondes1 = round($gt1 % 60);
                                  $heures1 = sprintf("%02d:%02d", $heur1, $secondes1);                   
                            }

                            if($ressougaretra->possitiongare === 'Apres'){
                                  $g1 = explode(":", $itemtrans->heure);
                                  $gt1 = (($g1[0] * 60) + $g1[1] + $ressougaretra->minutetemps); 
                                  $heur1 = ($gt1 / 60); 
                                  $secondes1 = round($gt1 % 60);
                                  $heures1 = sprintf("%02d:%02d", $heur1, $secondes1);
                            }
                              
                              $nom1 = $itemtrans->code_progr;
                              $nge1 = substr($nom1, 6, 6);

                              
                              $dat1 = explode("-", $itemtrans->date_progr);
                              $day1 = $dat1[2]. '-'. $dat1[1]. '-' .$dat1[0];
                        ?>
                       
                  <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itemtrans->logo);?>" width="300" height=""></td></tr>
								<tr><td style="font-size: 20px;"><b><?= "{$itemtrans->tamponcod}"; ?></b></td></tr>
							  <tr><td style="font-size: 20px;"><b><?= "{$itemtrans->code_ticket}"; ?></b></td></tr>
							  <tr><td style="font-size: 20px;"><?= "{$itemtrans->nom_gaep}"; ?> <?= "{$ressougaretra->nomsousgare}"; ?>-<?= ticket_destination_label($itemtrans); ?> <?= "{$itemtrans->quart}"; ?></td></tr>
							  <tr><td style="font-size: 20px;"><?= $item->nom_client; ?> &nbsp;<?= $item->prenom_client; ?></td></tr>
							  <tr><td style="font-size: 20px;"><b><?= $day1; ?>&nbsp; <?= "{$heures1}"; ?></b></td></tr>
							  <tr><td style="font-size: 20px;"><b><?= str_pad($itemtrans->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b></td></tr>
							  <tr><td style="font-size: 20px;">Prix:<?= $prixt ?> &nbsp;&nbsp;<?= "{$item->contact_client}"; ?></td></tr>
                        
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
                  
                          if($itemtrans2->prixvente === '0.00'){

                        $prixt2 = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prixt2 = number_format($itemtrans2->prixvente, 0, '', ' ').'FCFA';
                        }
                        
                          $ressougaretra2 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itemtrans2->code_gaexp, $itemtrans2->departclient_idgare, $itemtrans2->ident_ligne, $itemtrans2->id_ligneheure);

                         
                          if($ressougaretra2->possitiongare === 'Maintenant'){

                              $g2 = explode(":", $itemtrans2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] + $ressougaretra2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                              $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                            }

                            if($ressougaretra2->possitiongare === 'Avant'){
                                  $g2 = explode(":", $itemtrans2->heure);
                                  $gt2 = (($g2[0] * 60) + $g2[1] - $ressougaretra2->minutetemps); 
                                  $heur2 = ($gt2 / 60); 
                                  $secondes2 = round($gt2 % 60);
                                  $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);                   
                            }

                            if($ressougaretra2->possitiongare === 'Apres'){
                                  $g2 = explode(":", $itemtrans2->heure);
                                  $gt2 = (($g2[0] * 60) + $g2[1] + $ressougaretra2->minutetemps); 
                                  $heur2 = ($gt2 / 60); 
                                  $secondes2 = round($gt2 % 60);
                                  $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                            }
                              
                              $nom2 = $itemtrans2->code_progr;
                              $nge2 = substr($nom2, 6, 6);

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
                              $dat2 = explode("-", $itemtrans2->date_progr);
                              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];
                        ?>
                       <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itemtrans2->logo);?>" width="300" height=""></td></tr>
            							<tr><td style="font-size: 20px;"><b>TICKET CODE : <?= "{$itemtrans2->code_ticket}"; ?></b></td></tr>
            							<tr><td style="font-size: 20px;"><?= "{$itemtrans2->nom_gaep}"; ?> <?= "{$ressougaretra2->nomsousgare}"; ?>-<?= ticket_destination_label($itemtrans2); ?> <?= "{$itemtrans2->quart}"; ?></td></tr>
            							<tr><td style="font-size: 20px;"><?= $item->nom_client; ?>&nbsp; <?= $item->prenom_client; ?></td></tr>
            							<tr><td style="font-size: 20px;"><b><?= "{$day2}"; ?> &nbsp;<?= $heures2; ?></td></tr>
            							<tr><td style="font-size: 20px;"><b><?= str_pad($itemtrans2->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b></td></tr>
            							<tr><td style="font-size: 20px;"><?= $prixt2; ?> &nbsp;<?= "{$item->contact_client}"; ?></td></tr>
            							<tr><td>CONVOCATION 45 mn avant le départ</td></tr>
            							<tr><td style="font-size: 9px;">Billet valable 1 mois. Billet non remboursable</td></tr>
            							<tr><td style="font-size: 9px;"><?= $itemtrans2->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
            							<tr><td style="font-size: 9px;">perte ou de vol de billet et de bagages même payés.</td></tr>
            							<tr><td style="font-size: 9px;">Suivez et surveillez bien vos bagages</td></tr>
            							<tr><td>BON VOYAGE AVEC <?= $itemtrans2->nom_compagnie;?> <?= $nge2;?></td></tr>
            							<tr><td style="font-size: 35px; width: 90%;"> <img src="<?echo site_url('render/Barcode/'.$itemtrans2->tamponcod);?>" width="350" height="45"></td></tr>
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
                        if($itemtrans2->prixvente === '0.00'){

                        $prixt2 = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prixt2 = number_format($itemtrans2->prixvente, 0, '', ' ').'FCFA';
                        }
                      
                          $ressougaretra2 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itemtrans2->code_gaexp, $itemtrans2->departclient_idgare, $itemtrans2->ident_ligne, $itemtrans2->id_ligneheure);

                         
                          if($ressougaretra2->possitiongare === 'Maintenant'){

                              $g2 = explode(":", $itemtrans2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] + $ressougaretra2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                              $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                            }

                            if($ressougaretra2->possitiongare === 'Avant'){
                                  $g2 = explode(":", $itemtrans2->heure);
                                  $gt2 = (($g2[0] * 60) + $g2[1] - $ressougaretra2->minutetemps); 
                                  $heur2 = ($gt2 / 60); 
                                  $secondes2 = round($gt2 % 60);
                                  $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);                   
                            }

                            if($ressougaretra2->possitiongare === 'Apres'){
                                  $g2 = explode(":", $itemtrans2->heure);
                                  $gt2 = (($g2[0] * 60) + $g2[1] + $ressougaretra2->minutetemps); 
                                  $heur2 = ($gt2 / 60); 
                                  $secondes2 = round($gt2 % 60);
                                  $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                            }
                              
                              $nom2 = $itemtrans2->code_progr;
                              $nge2 = substr($nom2, 6, 6);

                              
                              $dat2 = explode("-", $itemtrans2->date_progr);
                              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];
                        ?>
                       
                        <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itemtrans2->logo);?>" width="300" height=""></td></tr>
          							  <tr><td style="font-size: 20px;"><b><?= "{$itemtrans2->tamponcod}"; ?></b></td></tr>
          							  <tr><td style="font-size: 20px;"><b><?= "{$itemtrans2->code_ticket}"; ?></b></td></tr>
          							  <tr><td style="font-size: 20px;"><?= "{$itemtrans2->nom_gaep}"; ?> <?= "{$ressougaretra2->nomsousgare}"; ?>-<?= ticket_destination_label($itemtrans2); ?> <?= "{$itemtrans2->quart}"; ?></td></tr>
          							  <tr><td style="font-size: 20px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td></tr>
          							  <tr><td style="font-size: 20px;"><b><?= $day2; ?>&nbsp; <?= "{$heures2}"; ?></b></td></tr>
          							  <tr><td style="font-size: 20px;"><b><?= str_pad($itemtrans2->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b></td></tr>
          							  <tr><td style="font-size: 20px;">Prix:<?= $prixt2 ?> &nbsp;&nbsp;<?= "{$item->contact_client}"; ?></td></tr>
                        
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
                  
                          if($itemtrans3->prixvente === '0.00'){

                        $prixt3 = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prixt3 = number_format($itemtrans3->prixvente, 0, '', ' ').'FCFA';
                        }
                        
                          $ressougaretra3 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itemtrans3->code_gaexp, $itemtrans3->departclient_idgare, $itemtrans3->ident_ligne, $itemtrans3->id_ligneheure);

                         
                          if($ressougaretra3->possitiongare === 'Maintenant'){

                              $g3 = explode(":", $itemtrans3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] + $ressougaretra3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                              $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                            }

                            if($ressougaretra3->possitiongare === 'Avant'){
                                  $g3 = explode(":", $itemtrans3->heure);
                                  $gt3 = (($g3[0] * 60) + $g3[1] - $ressougaretra3->minutetemps); 
                                  $heur3 = ($gt3 / 60); 
                                  $secondes3 = round($gt3 % 60);
                                  $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);                   
                            }

                            if($ressougaretra3->possitiongare === 'Apres'){
                                  $g3 = explode(":", $itemtrans3->heure);
                                  $gt3 = (($g3[0] * 60) + $g3[1] + $ressougaretra3->minutetemps); 
                                  $heur3 = ($gt3 / 60); 
                                  $secondes3 = round($gt3 % 60);
                                  $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                            }
                              
                              $nom3 = $itemtrans3->code_progr;
                              $nge3 = substr($nom3, 6, 6);

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
                              
                              $dat3 = explode("-", $itemtrans3->date_progr);
                              $day3 = $dat3[2]. '-'. $dat3[1]. '-' .$dat3[0];
                        ?>
                       <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itemtrans3->logo);?>" width="300" height=""></td></tr>
            							<tr><td style="font-size: 20px;"><b>TICKET CODE : <?= "{$itemtrans3->code_ticket}"; ?></b></td></tr>
            							<tr><td style="font-size: 20px;"><?= "{$itemtrans3->nom_gaep}"; ?> <?= "{$ressougaretra3->nomsousgare}"; ?>-<?= ticket_destination_label($itemtrans3); ?> <?= "{$itemtrans3->quart}"; ?></td></tr>
            							<tr><td style="font-size: 20px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td></tr>
            							<tr><td style="font-size: 20px;"><b><?= "{$day3}"; ?>&nbsp;<?= $heures3; ?></b></td></tr>
            							<tr><td style="font-size: 20px;">Siege : <b><?= str_pad($itemtrans3->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b></td></tr>
            							<tr><td style="font-size: 20px;"><?= $prixt3; ?>&nbsp;<?= "{$item->contact_client}"; ?></td></tr>
            							<tr><td>CONVOCATION 45 mn avant le départ</td></tr>
            							<tr><td style="font-size: 9px;">Billet valable 1 mois. Billet non remboursable</td></tr>
            							<tr><td style="font-size: 9px;"><?= $itemtrans3->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
            							<tr><td style="font-size: 9px;">perte ou de vol de billet et de bagages même payés.</td></tr>
            							<tr><td style="font-size: 9px;">Suivez et surveillez bien vos bagages</td></tr>
            							<tr><td>BON VOYAGE AVEC <?= $itemtrans3->nom_compagnie;?> <?= $nge3;?></td></tr>
            							<tr><td style="font-size: 35px; width: 90%;"> <img src="<?echo site_url('render/Barcode/'.$itemtrans3->tamponcod);?>" width="350" height="45"></td></tr>
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
                        if($itemtrans3->prixvente === '0.00'){

                        $prixt3 = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prixt3 = number_format($itemtrans3->prixvente, 0, '', ' ').'FCFA';
                        }
                      
                          $ressougaretra3 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $itemtrans3->code_gaexp, $itemtrans3->departclient_idgare, $itemtrans3->ident_ligne, $itemtrans3->id_ligneheure);

                         
                          if($ressougaretra3->possitiongare === 'Maintenant'){

                              $g3 = explode(":", $itemtrans3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] + $ressougaretra3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                              $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                            }

                            if($ressougaretra3->possitiongare === 'Avant'){
                                  $g3 = explode(":", $itemtrans3->heure);
                                  $gt3 = (($g3[0] * 60) + $g3[1] - $ressougaretra3->minutetemps); 
                                  $heur3 = ($gt3 / 60); 
                                  $secondes3 = round($gt3 % 60);
                                  $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);                   
                            }

                            if($ressougaretra3->possitiongare === 'Apres'){
                                  $g3 = explode(":", $itemtrans2->heure);
                                  $gt3 = (($g3[0] * 60) + $g3[1] + $ressougaretra3->minutetemps); 
                                  $heur3 = ($gt3 / 60); 
                                  $secondes3 = round($gt3 % 60);
                                  $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                            }
                              
                              $nom3 = $itemtrans3->code_progr;
                              $nge3 = substr($nom3, 6, 6);

                              
                              $dat3 = explode("-", $itemtrans3->date_progr);
                              $day3 = $dat3[2]. '-'. $dat3[1]. '-' .$dat3[0];
                        ?>
                       
                          <tr><td style="font-size: 55px; width: 50%;"> <img src="<?echo site_url($itemtrans3->logo);?>" width="300" height=""></td></tr>
          							  <tr><td style="font-size: 20px;"><b><?= "{$itemtrans3->tamponcod}"; ?></b></td></tr>
          							  <tr><td style="font-size: 20px;"><b><?= "{$itemtrans3->code_ticket}"; ?></b></td></tr>
          							  <tr><td style="font-size: 20px;"><?= "{$itemtrans3->nom_gaep}"; ?> <?= "{$ressougaretra3->nomsousgare}"; ?>-<?= ticket_destination_label($itemtrans3); ?> <?= "{$itemtrans3->quart}"; ?></td></tr>
          							  <tr><td style="font-size: 20px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td></tr>
          							  <tr><td style="font-size: 20px;"><b><?= $day3; ?><?= "{$heures3}"; ?></b></td></tr>
          							  <tr><td style="font-size: 20px;"><b><?= str_pad($itemtrans3->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></b></td></tr>
          							  <tr><td style="font-size: 20px;">Prix:<?= $prixt3 ?> &nbsp;&nbsp;<?= "{$item->contact_client}"; ?></td></tr>
                        
                        </table>
                    </body>
                </div>
            </div>

        </div>
    </div>
<!--End of file: editeppdftransfi3.php-->
<!--File location: application/views/beagle/pages/_tickets/editeppdftransfi3.php-->