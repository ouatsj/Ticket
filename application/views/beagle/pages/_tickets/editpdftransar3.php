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
                            <tr><td align=left><?= $ressougare->nomsousgare; ?></td><td align=left colspan="2" style="font-size: 10px;"></td><td align=center style="font-size: 12px;">PRIX:<?= number_format(ticket_impression_prix($item)+ticket_impression_prix($item), 0, '', ' ');?>FCFA</td><td align=left style="font-size: 15px;"><?= $ressougare->nomsousgare; ?>:<?= $day;?></td><td align=left style="font-size: 15px;">DATE:</td>
                            </tr>
                            <tr><td align=left style="font-size: 15px;"><?= $day; ?> <?= $heures;?> <?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="2" align=center></td><td style="font-size: 12px;" align=center>TEL:<?= $item->contact_client; ?></td><td style="font-size: 15px;">HEURE:<?= $heures;?></td><td style="font-size: 15px;">HEURE:</td>
                            </tr>
                            <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="2" align=left></td><td align=center style="font-size: 15px;"></td><td align=left style="font-size: 15px;">SIEGE:<?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td align=left style="font-size: 15px;">SIEGE:</td>
                            </tr>
                            <tr><td align=left><?= $item->prenom_client; ?></td><td align=center colspan="3"> <img src="<?php echo site_url('render/Barcode/'.$item->tamponcod);?>" alt="" width="250" height="30"></td><td align=left></td>
                            </tr>
                            <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet valable 1 mois. Billet non remboursable</td><td align=left colspan="2">CONVOCATION 45 mn avant le départ</td>
                            </tr>
                            <tr><td align=left><?= number_format(ticket_impression_prix($item)+ticket_impression_prix($item), 0, '', ' ');?> FCFA <?= $codb;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $item->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
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
    <div class="row">
      <div> 
          <label style="display:none" id="ligne1">Ligne transite1</label>
          <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
              id="lignesitineraire" disabled="">
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart1">Quartier</label>
          <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="row">
      <div> 
          <label style="display:none" id="ligne1">Ligne transite1</label>
          <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
              id="lignesitineraire" disabled="">
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart1">Quartier</label>
          <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="row">
      
     
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="col-lg-12">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>
                        
                    <table>
                            <? $ckey = $this->session->company->ekey;
                            $this->entreprise = $this->m_entreprises->get_key($ckey);
                    
                          $fichetrans = $this->m_non_passager->getad($this->entreprise->ekey, $itemartrans->code_non_pass);
                         
                          $reponsetrans = $this->m_passager->get($this->entreprise->ekey, $itemtrans->tamponcod, $item->typetarif, $itemtrans->id_ligneheure);

                            $ressougaretra = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponsetrans->code_gaexp, $reponsetrans->departclient_idgare, $reponsetrans->ident_ligne, $itemtrans->id_ligneheure);

                         
                          if($ressougaretra->possitiongare === 'Maintenant'){

                              $g1 = explode(":", $reponsetrans->heure);
                              $gt1 = (($g1[0] * 60) + $g1[1] + $ressougaretra->minutetemps); 
                              $heur1 = ($gt1 / 60); 
                              $secondes1 = round($gt1 % 60);
                              $heures1 = sprintf("%02d:%02d", $heur1, $secondes1);
                            }

                            if($ressougaretra->possitiongare === 'Avant'){
                                  $g1 = explode(":", $reponsetrans->heure);
                                  $gt1 = (($g1[0] * 60) + $g1[1] - $ressougaretra->minutetemps); 
                                  $heur1 = ($gt1 / 60); 
                                  $secondes1 = round($gt1 % 60);
                                  $heures1 = sprintf("%02d:%02d", $heur1, $secondes1);                   
                            }

                            if($ressougaretra->possitiongare === 'Apres'){
                                  $g1 = explode(":", $reponsetrans->heure);
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

                              $cdbus1 = $reponsetrans->code_progr;
                              $codb1 = substr($cdbus1, 6, 6);
                              
                              $dat1 = explode("-", $reponsetrans->date_progr);
                              $day1 = $dat1[2]. '-'. $dat1[1]. '-' .$dat1[0];?>
                   
                            <tr><td width="25%" style="font-size: 15px;"><?= $itemtrans->code_ticket; ?></td><td colspan="2" align=left style="font-size: 15px;"></td><td align=center style="font-size: 12px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td><td align=center style="font-size: 9px;"><?= $itemtrans->tamponcod; ?> emis : <?= $dtoday; ?></td><td style="font-size: 9px;"></td>
                            </tr>
                            <tr><td style="font-size: 15px;"><?= $fichetrans->codeticket; ?></td><td colspan="2" align=left style="font-size: 17px;"></td><td align=center><?= $itemtrans->nom_ligne; ?></td><td align=left>ALLER</td><td align=left>RETOUR</td></tr>

                            <tr><td align=left style="font-size: 15px;"><?= $itemtrans->nom_ligne; ?></td><td colspan="2" align=left style="font-size: 12px;"></td><td align=center style="font-size: 13px;">QUARTIER:<?= $itemtrans->quart;?></td><td style="font-size: 15px;" align=left><?= $itemtrans->code_ticket; ?></td><td style="font-size: 15px;" align=left><?= $fichetrans->codeticket; ?></td>
                            </tr>
                            <tr><td align=left><?= $ressougaretra->nomsousgare; ?></td><td align=left colspan="2" style="font-size: 10px;"></td><td align=center style="font-size: 12px;">PRIX:<?= number_format(ticket_impression_prix($itemtrans)+ticket_impression_prix($itemtrans), 0, '', ' ');?>FCFA</td><td align=left style="font-size: 15px;"><?= $ressougaretra->nomsousgare; ?>:<?= $day1;?></td><td align=left style="font-size: 15px;">DATE:</td>
                            </tr>
                            <tr><td align=left style="font-size: 15px;"><?= $day1; ?> <?= $heures1;?> <?= str_pad($itemtrans->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="2" align=center></td><td style="font-size: 12px;" align=center>TEL:<?= $item->contact_client; ?></td><td style="font-size: 15px;">HEURE:<?= $heures1;?></td><td style="font-size: 15px;">HEURE:</td>
                            </tr>
                            <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="2" align=left></td><td align=center style="font-size: 15px;"></td><td align=left style="font-size: 15px;">SIEGE:<?= str_pad($itemtrans->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td align=left style="font-size: 15px;">SIEGE:</td>
                            </tr>
                            <tr><td align=left><?= $item->prenom_client; ?></td><td align=center colspan="3"> <img src="<?php echo site_url('render/Barcode/'.$itemtrans->tamponcod);?>" alt="" width="250" height="30"></td><td align=left></td>
                            </tr>
                            <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet valable 1 mois. Billet non remboursable</td><td align=left colspan="2">CONVOCATION 45 mn avant le départ</td>
                            </tr>
                            <tr><td align=left><?= number_format(ticket_impression_prix($itemtrans)+ticket_impression_prix($itemtrans), 0, '', ' ');?> FCFA <?= $codb1;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $itemtrans->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
                            </tr>
                            <tr><td align=left style="font-size: 9px;"><?= $itemtrans->tamponcod;?></td><td colspan="3" align=center style="font-size: 9px;">Suivez et surveillez bien vos bagages</td>
                            </tr>
                            <tr><td align=left style="font-size: 8px;">ALLER-RETOUR</td><td colspan="3" align=center style="font-size: 9px;">BON VOYAGE AVEC <?= $itemtrans->nom_compagnie;?>&nbsp;&nbsp;&nbsp;<?= $codb1;?></td>
                            
                            </tr>
                        </table>
                        
                    </body>
                </div>
            </div>

        </div>

    </div>
    <div class="row">
      <div> 
          <label style="display:none" id="ligne1">Ligne transite1</label>
          <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
              id="lignesitineraire" disabled="">
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart1">Quartier</label>
          <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="row">
      <div> 
          <label style="display:none" id="ligne1">Ligne transite1</label>
          <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
              id="lignesitineraire" disabled="">
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart1">Quartier</label>
          <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="row">
      
     
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="col-lg-12">
        
        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>
                        
                    <table>
                            <? $ckey = $this->session->company->ekey;
                            $this->entreprise = $this->m_entreprises->get_key($ckey);
                    
                          $fichetrans2 = $this->m_non_passager->getad($this->entreprise->ekey, $itemartrans2->code_non_pass);
                         
                          $reponsetrans2 = $this->m_passager->get($this->entreprise->ekey, $itemtrans2->tamponcod, $item->typetarif, $itemtrans2->id_ligneheure);

                          $ressougaretra2 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponsetrans2->code_gaexp, $reponsetrans2->departclient_idgare, $reponsetrans2->ident_ligne, $itemtrans2->id_ligneheure);

                         
                          if($ressougaretra2->possitiongare === 'Maintenant'){

                              $g2 = explode(":", $reponsetrans2->heure);
                              $gt2 = (($g2[0] * 60) + $g2[1] + $ressougaretra2->minutetemps); 
                              $heur2 = ($gt2 / 60); 
                              $secondes2 = round($gt2 % 60);
                              $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
                            }

                            if($ressougaretra2->possitiongare === 'Avant'){
                                  $g2 = explode(":", $reponsetrans2->heure);
                                  $gt2 = (($g2[0] * 60) + $g2[1] - $ressougaretra2->minutetemps); 
                                  $heur2 = ($gt1 / 60); 
                                  $secondes2 = round($gt2 % 60);
                                  $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);                   
                            }

                            if($ressougaretra2->possitiongare === 'Apres'){
                                  $g2 = explode(":", $reponsetrans2->heure);
                                  $gt2 = (($g2[0] * 60) + $g2[1] + $ressougaretra2->minutetemps); 
                                  $heur2 = ($gt2 / 60); 
                                  $secondes2 = round($gt2 % 60);
                                  $heures2 = sprintf("%02d:%02d", $heur2, $secondes2);
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

                              $cdbus2 = $reponsetrans2->code_progr;
                              $codb2 = substr($cdbus2, 6, 6);
                              
                              $dat2 = explode("-", $reponsetrans2->date_progr);
                              $day2 = $dat2[2]. '-'. $dat2[1]. '-' .$dat2[0];?>
                   
                            <tr><td width="25%" style="font-size: 15px;"><?= $itemtrans2->code_ticket; ?></td><td colspan="2" align=left style="font-size: 15px;"></td><td align=center style="font-size: 12px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td><td align=center style="font-size: 9px;"><?= $itemtrans2->tamponcod; ?> emis : <?= $dtoday; ?></td><td style="font-size: 9px;"></td>
                            </tr>
                            <tr><td style="font-size: 15px;"><?= $fichetrans2->codeticket; ?></td><td colspan="2" align=left style="font-size: 17px;"></td><td align=center><?= $itemtrans2->nom_ligne; ?></td><td align=left>ALLER</td><td align=left>RETOUR</td></tr>

                            <tr><td align=left style="font-size: 15px;"><?= $itemtrans2->nom_ligne; ?></td><td colspan="2" align=left style="font-size: 12px;"></td><td align=center style="font-size: 13px;">QUARTIER:<?= $itemtrans2->quart;?></td><td style="font-size: 15px;" align=left><?= $itemtrans2->code_ticket; ?></td><td style="font-size: 15px;" align=left><?= $fichetrans2->codeticket; ?></td>
                            </tr>
                            <tr><td align=left><?= $ressougaretra2->nomsousgare; ?></td><td align=left colspan="2" style="font-size: 10px;"></td><td align=center style="font-size: 12px;">PRIX:<?= number_format(ticket_impression_prix($itemtrans2)+ticket_impression_prix($itemtrans2), 0, '', ' ');?>FCFA</td><td align=left style="font-size: 15px;"><?= $ressougaretra2->nomsousgare; ?>:<?= $day2;?></td><td align=left style="font-size: 15px;">DATE:</td>
                            </tr>
                            <tr><td align=left style="font-size: 15px;"><?= $day2; ?> <?= $heures2;?> <?= str_pad($itemtrans2->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="2" align=center></td><td style="font-size: 12px;" align=center>TEL:<?= $item->contact_client; ?></td><td style="font-size: 15px;">HEURE:<?= $heures2;?></td><td style="font-size: 15px;">HEURE:</td>
                            </tr>
                            <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="2" align=left></td><td align=center style="font-size: 15px;"></td><td align=left style="font-size: 15px;">SIEGE:<?= str_pad($itemtrans2->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td align=left style="font-size: 15px;">SIEGE:</td>
                            </tr>
                            <tr><td align=left><?= $item->prenom_client; ?></td><td align=center colspan="3"> <img src="<?php echo site_url('render/Barcode/'.$itemtrans2->tamponcod);?>" alt="" width="250" height="30"></td><td align=left></td>
                            </tr>
                            <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet valable 1 mois. Billet non remboursable</td><td align=left colspan="2">CONVOCATION 45 mn avant le départ</td>
                            </tr>
                            <tr><td align=left><?= number_format(ticket_impression_prix($itemtrans2)+ticket_impression_prix($itemtrans2), 0, '', ' ');?> FCFA <?= $codb2;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $itemtrans2->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
                            </tr>
                            <tr><td align=left style="font-size: 9px;"><?= $itemtrans3->tamponcod;?></td><td colspan="3" align=center style="font-size: 9px;">Suivez et surveillez bien vos bagages</td>
                            </tr>
                            <tr><td align=left style="font-size: 8px;">ALLER-RETOUR</td><td colspan="3" align=center style="font-size: 9px;">BON VOYAGE AVEC <?= $itemtrans2->nom_compagnie;?>&nbsp;&nbsp;&nbsp;<?= $codb2;?></td>
                            
                            </tr>
                        </table>
                        
                    </body>
                </div>
            </div>

        </div>

    </div>
    <div class="row">
      <div> 
          <label style="display:none" id="ligne1">Ligne transite1</label>
          <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
              id="lignesitineraire" disabled="">
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart1">Quartier</label>
          <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="row">
      <div> 
          <label style="display:none" id="ligne1">Ligne transite1</label>
          <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
              id="lignesitineraire" disabled="">
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart1">Quartier</label>
          <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="row">
      
     
      <div class="form-group col-sm-4">
          <label style="display:none" id="siegitine">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
              <option value="">Choisissez siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans1">Départ transite1</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin1">Ligne transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart2">Quartier</label>
          <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin1">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine1">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans2">Départ transite2</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin2">Ligne transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart3">Quartier</label>
          <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin2">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>

      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine2">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans3">Départ transite3</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
              
          </select>
      </div>
      
      <div class="form-group col-sm-4">
          <label style="display:none" id="arritin3">Ligne transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
              <option value="">Choisissez la ligne</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="idquart4">Quartier</label>
          <select style="display:none" name="quartconfirme4" class="form-control form-control-sm" id="quartier4">
                  <option value="">Choisissez le quartier</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="heureitin3">Heure</label>
          <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
              <option value="">Choisissez heure départ</option>
              
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none;" id="siegitine3">Siège</label>
          <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
              <option value="">Choisissez le siège</option>
          </select>
      </div>
      <div class="form-group col-sm-4">
          <label style="display:none" id="iddeptrans4">Départ transite4</label>
          <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
              
          </select>
      </div>
    </div>
    <div class="col-lg-12">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>
                        
                    <table>
                            <? $ckey = $this->session->company->ekey;
                            $this->entreprise = $this->m_entreprises->get_key($ckey);
                    
                          $fichetrans3 = $this->m_non_passager->getad($this->entreprise->ekey, $itemartrans3->code_non_pass);
                         
                          $reponsetrans3 = $this->m_passager->get($this->entreprise->ekey, $itemtrans3->tamponcod, $item->typetarif, $itemtrans3->id_ligneheure);

                          $ressougaretra3 = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponsetrans3->code_gaexp, $reponsetrans3->departclient_idgare, $reponsetrans3->ident_ligne, $itemtrans3->id_ligneheure);

                         
                          if($ressougaretra3->possitiongare === 'Maintenant'){

                              $g3 = explode(":", $reponsetrans3->heure);
                              $gt3 = (($g3[0] * 60) + $g3[1] + $ressougaretra3->minutetemps); 
                              $heur3 = ($gt3 / 60); 
                              $secondes3 = round($gt3 % 60);
                              $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
                            }

                            if($ressougaretra3->possitiongare === 'Avant'){
                                  $g3 = explode(":", $reponsetrans3->heure);
                                  $gt3 = (($g3[0] * 60) + $g3[1] - $ressougaretra3->minutetemps); 
                                  $heur3 = ($gt3 / 60); 
                                  $secondes3 = round($gt3 % 60);
                                  $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);                   
                            }

                            if($ressougaretra3->possitiongare === 'Apres'){
                                  $g3 = explode(":", $reponsetrans3->heure);
                                  $gt3 = (($g3[0] * 60) + $g3[1] + $ressougaretra3->minutetemps); 
                                  $heur3 = ($gt3 / 60); 
                                  $secondes3 = round($gt3 % 60);
                                  $heures3 = sprintf("%02d:%02d", $heur3, $secondes3);
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
                              
                              $cdbus3 = $reponsetrans3->code_progr;
                              $codb3 = substr($cdbus3, 6, 6);
                              
                              $dat3 = explode("-", $reponsetrans3->date_progr);
                              $day3 = $dat3[2]. '-'. $dat3[1]. '-' .$dat3[0];?>
                   
                            <tr><td width="25%" style="font-size: 15px;"><?= $itemtrans3->code_ticket; ?></td><td colspan="2" align=left style="font-size: 15px;"></td><td align=center style="font-size: 12px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td><td align=center style="font-size: 9px;"><?= $itemtrans3->tamponcod; ?> emis : <?= $dtoday; ?></td><td style="font-size: 9px;"></td>
                            </tr>
                            <tr><td style="font-size: 15px;"><?= $fichetrans3->codeticket; ?></td><td colspan="2" align=left style="font-size: 17px;"></td><td align=center><?= $itemtrans3->nom_ligne; ?></td><td align=left>ALLER</td><td align=left>RETOUR</td></tr>

                            <tr><td align=left style="font-size: 15px;"><?= $itemtrans3->nom_ligne; ?></td><td colspan="2" align=left style="font-size: 12px;"></td><td align=center style="font-size: 13px;">QUARTIER:<?= $itemtrans3->quart;?></td><td style="font-size: 15px;" align=left><?= $itemtrans3->code_ticket; ?></td><td style="font-size: 15px;" align=left><?= $fichetrans3->codeticket; ?></td>
                            </tr>
                            <tr><td align=left><?= $ressougaretra3->nomsousgare; ?></td><td align=left colspan="2" style="font-size: 10px;"></td><td align=center style="font-size: 12px;">PRIX:<?= number_format(ticket_impression_prix($itemtrans3)+ticket_impression_prix($itemtrans3), 0, '', ' ');?>FCFA</td><td align=left style="font-size: 15px;"><?= $ressougaretra3->nomsousgare; ?>:<?= $day3;?></td><td align=left style="font-size: 15px;">DATE:</td>
                            </tr>
                            <tr><td align=left style="font-size: 15px;"><?= $day3; ?> <?= $heures3;?> <?= str_pad($itemtrans3->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="2" align=center></td><td style="font-size: 12px;" align=center>TEL:<?= $item->contact_client; ?></td><td style="font-size: 15px;">HEURE:<?= $heures3;?></td><td style="font-size: 15px;">HEURE:</td>
                            </tr>
                            <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="2" align=left></td><td align=center style="font-size: 15px;"></td><td align=left style="font-size: 15px;">SIEGE:<?= str_pad($itemtrans3->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td align=left style="font-size: 15px;">SIEGE:</td>
                            </tr>
                            <tr><td align=left><?= $item->prenom_client; ?></td><td align=center colspan="3"> <img src="<?php echo site_url('render/Barcode/'.$itemtrans3->tamponcod);?>" alt="" width="250" height="30"></td><td align=left></td>
                            </tr>
                            <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet valable 1 mois. Billet non remboursable</td><td align=left colspan="2">CONVOCATION 45 mn avant le départ</td>
                            </tr>
                            <tr><td align=left><?= number_format(ticket_impression_prix($itemtrans3)+ticket_impression_prix($itemtrans3), 0, '', ' ');?> FCFA <?= $codb3;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $itemtrans3->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
                            </tr>
                            <tr><td align=left style="font-size: 9px;"><?= $itemtrans3->tamponcod;?></td><td colspan="3" align=center style="font-size: 9px;">Suivez et surveillez bien vos bagages</td>
                            </tr>
                            <tr><td align=left style="font-size: 8px;">ALLER-RETOUR</td><td colspan="3" align=center style="font-size: 9px;">BON VOYAGE AVEC <?= $itemtrans3->nom_compagnie;?>&nbsp;&nbsp;&nbsp;<?= $codb3;?></td>
                            
                            </tr>
                        </table>
                        
                    </body>
                </div>
            </div>

        </div>

    </div>
</div>

<!--End of file: editpdftransar3.php-->
<!--File location: application/views/beagle/pages/_tickets/editpdftransar3.php-->