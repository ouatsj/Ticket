<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">
    
    <div class="row">
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
    <div class="col-lg-12">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>
                        
                    <table>
                            <? if (empty($item)) : ?>
                        <tr><td colspan="5"><strong>Billet introuvable.</strong> Vérifiez le guichet ou réimprimez depuis l'historique.</td></tr>
                            <? else :
                            $ckey = $this->session->company->ekey;
                            $this->entreprise = $this->m_entreprises->get_key($ckey);
                          $gare_ref = !empty($bus_stop->idengare) ? $bus_stop->idengare : $item->code_gaexp;
                          $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $gare_ref, $item->departclient_idgare, $item->ident_ligne, $item->id_ligneheure);
                          $heures = $item->heure;
                          if ($ressougare && !empty($ressougare->possitiongare)) {
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
                          }
                          $sg_label = ($ressougare && !empty($ressougare->nomsousgare))
                              ? $ressougare->nomsousgare
                              : (isset($item->nomsousgare) ? $item->nomsousgare : '');

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
                   
                                <tr><td width="25%" style="font-size: 17px;"><?= $item->code_ticket; ?></td><td colspan="3" align=left><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td><td style="font-size: 9px;"><?= $item->tamponcod; ?> emis : <?= $dtoday; ?></td>
                                </tr>
                                <tr><td align=left style="font-size: 17px;"><?= $item->nom_ligne; ?></td><td colspan="3" align=left style="font-size: 17px;">CODE:<?= $item->code_ticket; ?></td><td style="font-size: 17px;" align=left>SIEGE:<?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT);?></td>
                                </tr>
                                <tr><td align=left><?= $sg_label; ?></td><td align=left colspan="3" style="font-size: 17px;">AXE:<?= $item->nom_ligne; ?></td><td align=left style="font-size: 15px;">QUARTIER:<?= $item->quart;?></td>
                                </tr>
                                <tr><td align=left style="font-size: 15px;"><?= $day; ?> <?= $heures;?> <?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="3" align=left style="font-size: 15px;"><?= $sg_label; ?>:<?= $day;?></td><td style="font-size: 17px;">HEURE:<?= $heures;?></td>
                                </tr>
                                <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="3" align=left>TEL:<?= $item->contact_client; ?></td><td align=left>PRIX:<?= number_format($item->prix, 0, '', ' ');?>FCFA</td>
                                </tr>
                                <tr><td align=left width="30%"><?= $item->prenom_client; ?></td><td align=center colspan="3"> <?= ticket_barcode_img($item->tamponcod, 200, 35); ?></td><td align=left></td>
                                </tr>
                                <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet non remboursable</td><td align=right>CONVOCATION 45 mn avant le départ</td>
                                </tr>
                                <tr><td align=left><?= number_format($item->prix, 0, '', ' ');?> FCFA <?= $nge;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $item->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
                                </tr>
                                <tr><td align=left style="font-size: 9px;"><?= $item->tamponcod;?></td><td align=left>NON REPROGRAMMABLE</td><td colspan="3" align=center style="font-size: 9px;">Suivez et surveillez bien vos bagages</td>
                                </tr>
                                <tr><td align=left style="font-size: 9px;">NON REPROGRAMMABLE</td><td colspan="3" align=center style="font-size: 9px;">BON VOYAGE AVEC <?= $item->nom_compagnie;?>&nbsp;&nbsp;&nbsp;<?= $nge;?></td>
                                
                                </tr>
                        </table>
                            <? endif; ?>
                        
                    </body>
                </div>
            </div>

        </div>

    </div>

</div>

<!--End of file: editreport.php-->
<!--File location: application/views/beagle/pages/_tickets/editreport.php-->