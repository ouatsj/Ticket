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
                      <? $ckey = $this->session->company->ekey;
                        $this->entreprise = $this->m_entreprises->get_key($ckey);
                          if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                                $reponse = $this->m_passager->getad($this->entreprise->ekey, $item->tamponcod, $item->typetarif, $item->id_ligneheure);
                                
                                $fiche = $this->m_non_passager->getad($this->entreprise->ekey, $itemar->code_non_pass);
                          }
                          else
                          {
                                $reponse = $this->m_passager->get($this->entreprise->ekey, $item->tamponcod, $item->typetarif, $item->id_ligneheure);
                                $fiche = $this->m_non_passager->getad($this->entreprise->ekey, $itemar->code_non_pass);
                          }

                          $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne, $item->id_ligneheure);

                          if($ressougare->possitiongare === 'Maintenant'){

                                $g = explode(":", $reponse->heure);
                                $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                                $heur = ($gt / 60); 
                                $secondes = round($gt % 60);
                                $heures = sprintf("%02d:%02d", $heur, $secondes);
                                //$heures = $heur.':'.$secondes;
                          }

                          if($ressougare->possitiongare === 'Avant'){
                                $g = explode(":", $reponse->heure);
                                $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                                $heur = ($gt / 60); 
                                $secondes = round($gt % 60);
                                      
                                $heures = sprintf("%02d:%02d", $heur, $secondes);
                          }

                          if($ressougare->possitiongare === 'Apres'){
                                $g = explode(":", $reponse->heure);
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
                          $cdbus = $reponse->code_progr;
                          $codb = substr($cdbus, 6, 6);

                          $dat = explode("-", $reponse->date_progr);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];?>
                   
                        <tr><td width="25%" style="font-size: 15px;"><?= $item->code_ticket; ?></td><td colspan="2" align=left style="font-size: 15px;"></td><td align=center style="font-size: 12px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td><td align=center style="font-size: 9px;"><?= $item->tamponcod; ?> emis : <?= $dtoday; ?></td><td style="font-size: 9px;"></td>
                        </tr>
                        <tr><td style="font-size: 15px;"><?= $fiche->codeticket; ?></td><td colspan="2" align=left style="font-size: 17px;"></td><td align=center><?= $item->nom_ligne; ?></td><td align=left>ALLER</td><td align=left>RETOUR</td></tr>
                        <tr><td align=left style="font-size: 15px;"><?= $item->nom_ligne; ?></td><td colspan="2" align=left style="font-size: 12px;"></td><td align=center style="font-size: 13px;">QUARTIER:<?= $item->quart;?></td><td style="font-size: 15px;" align=left><?= $item->code_ticket; ?></td><td style="font-size: 15px;" align=left><?= $fiche->codeticket; ?></td>
                        </tr>
                        <tr><td align=left><?= $ressougare->nomsousgare; ?></td><td align=left colspan="2" style="font-size: 10px;"></td><td align=center style="font-size: 12px;">PRIX:<?= number_format($item->prix+$item->prix, 0, '', ' ');?>FCFA</td><td align=left style="font-size: 15px;"><?= $ressougare->nomsousgare; ?>:<?= $day;?></td><td align=left style="font-size: 15px;">DATE:</td>
                        </tr>
                        <tr><td align=left style="font-size: 15px;"><?= $day; ?> <?= $heures;?> <?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="2" align=center></td><td style="font-size: 12px;" align=center>TEL:<?= $item->contact_client; ?></td><td style="font-size: 15px;">HEURE:<?= $heures;?></td><td style="font-size: 15px;">HEURE:</td>
                        </tr>
                        <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="2" align=left></td><td align=center style="font-size: 15px;"></td><td align=left style="font-size: 15px;">SIEGE:<?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td align=left style="font-size: 15px;">SIEGE:</td>
                        </tr>
                        <tr><td align=left><?= $item->prenom_client; ?></td><td align=center colspan="3"> <img src="<?php echo site_url('render/Barcode/'.$item->tamponcod);?>" alt="" width="250" height="30"></td><td align=left></td>
                        </tr>
                        <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet valable 1 mois. Billet non remboursable</td><td align=left colspan="2">CONVOCATION 45 mn avant le départ</td>
                        </tr>
                        <tr><td align=left><?= number_format($item->prix+$item->prix, 0, '', ' ');?> FCFA <?= $codb;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $item->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
                        </tr>
                        <tr><td align=left style="font-size: 9px;"><?= $item->tamponcod;?></td><td colspan="3" align=center style="font-size: 9px;">Suivez et surveillez bien vos bagages</td>
                        </tr>
                        <tr><td align=left style="font-size: 8px;">ALLER-RETOUR</td><td colspan="3" align=center style="font-size: 9px;">BON VOYAGE AVEC <?= $item->nom_compagnie;?>&nbsp;&nbsp;&nbsp;<?= $codb;?></td>
                        
                        </tr>
                        </table>
                        
                    </body>
                </div>
            </div>

        </div>

    </div>
</div>

<!--End of file: editpdfar.php-->
<!--File location: application/views/beagle/pages/_tickets/editpdfar.php-->