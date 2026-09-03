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
                
                        if(ticket_est_gratuit($item->prixvente)){

                          $pr = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $pr = number_format($item->prixvente, 0, '', ' ').'FCFA';
                        }
                        if(ticket_est_gratuit($itemtrans->prixvente)){

                          $prfi = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prfi = number_format($itemtrans->prixvente, 0, '', ' ').'FCFA';
                        }
                        $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $item->code_gaexp, $item->departclient_idgare, $item->ident_ligne, $item->id_ligneheure);
                        
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
                            $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

                      ?>
                      <tr><td width="25%" style="font-size: 17px;"><?= $item->code_ticket; ?></td><td colspan="3" align=left><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td><td style="font-size: 9px;"><?= $item->tamponcod; ?> emis : <?=$dtoday; ?></td>
                      </tr>
                      <tr><td align=left style="font-size: 17px;"><?= $item->nom_ligne; ?></td><td colspan="3" align=left style="font-size: 17px;">CODE:<?= $item->code_ticket; ?></td><td style="font-size: 17px;" align=left>SIEGE:<?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT);?></td>
                      </tr>
                      <tr><td align=left><?= $ressougare->nomsousgare; ?></td><td align=left colspan="3" style="font-size: 17px;">AXE:<?= $item->nom_ligne; ?></td><td align=left style="font-size: 15px;"><?= $ressougare->nomsousgare; ?>:<?= $day;?></td>
                      </tr>
                      <tr><td align=left style="font-size: 15px;"><?= $day; ?> <?= $heures;?> <?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="3" align=left style="font-size: 15px;">QUARTIER:<?= $item->quart;?></td><td style="font-size: 17px;">HEURE:<?= $heures;?></td>
                      </tr>
                      <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="3" align=left>TEL:<?= $item->contact_client; ?></td><td align=left>PRIX:<?= $pr;?></td>
                      </tr>
                      <tr><td align=left width="30%"><?= $item->prenom_client; ?></td><td align=center colspan="3"> <?= ticket_barcode_img($item->tamponcod, 200, 35); ?></td><td align=left></td>
                      </tr>
                      <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet valable 1 mois. Billet non remboursable</td><td align=left>CONVOCATION 45 mn avant le départ</td>
                      </tr>
                      <tr><td align=left><?=$pr;?> <?= $nge;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $item->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
                      </tr>
                      <tr><td align=left style="font-size: 9px;"><?= $item->tamponcod;?></td><td colspan="3" align=center style="font-size: 9px;">Suivez et surveillez bien vos bagages</td>
                      </tr>
                      <tr><td align=left style="font-size: 9px;"></td><td colspan="3" align=center style="font-size: 9px;">BON VOYAGE AVEC <?= $item->nom_compagnie;?>&nbsp;&nbsp;&nbsp;<?= $nge;?></td>
                      
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
                
                        if(ticket_est_gratuit($item->prixvente)){

                          $pr = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $pr = number_format($item->prixvente, 0, '', ' ').'FCFA';
                        }
                        if(ticket_est_gratuit($itemtrans->prixvente)){

                          $prfi = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prfi = number_format($itemtrans->prixvente, 0, '', ' ').'FCFA';
                        }
                        $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $item->code_gaexp, $item->departclient_idgare, $item->ident_ligne, $item->id_ligneheure);
                        
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
                            $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

                      ?>
                      
                        <? $ckey = $this->session->company->ekey;
                        $this->entreprise = $this->m_entreprises->get_key($ckey);
                
                        
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
                                $gt1 = (($g1[0] * 60) + $g1[1] + $ressougaretra->minutetemps); 
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
                         
                          <tr><td width="25%" style="font-size: 17px;"><?= $itemtrans->code_ticket; ?></td><td colspan="3" align=left><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td><td style="font-size: 9px;"><?= $itemtrans->tamponcod; ?> emis : <?= $dtoday; ?></td>
                          </tr>
                          <tr><td align=left style="font-size: 17px;"><?= $itemtrans->nom_ligne; ?></td><td colspan="3" align=left style="font-size: 17px;">CODE:<?= $itemtrans->code_ticket; ?></td><td style="font-size: 17px;" align=left>SIEGE:<?= str_pad($itemtrans->num_siege_categorie, 2, "0", STR_PAD_LEFT);?></td>
                          </tr>
                          <tr><td align=left><?= $ressougaretra->nomsousgare; ?></td><td align=left colspan="3" style="font-size: 17px;">AXE:<?= $itemtrans->nom_ligne; ?></td><td align=left style="font-size: 15px;">QUARTIER:<?= $itemtrans->quart;?></td>
                          </tr>
                          <tr><td align=left style="font-size: 15px;"><?= $day1; ?> <?= $heures1;?> <?= str_pad($itemtrans->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="3" align=left style="font-size: 15px;"><?= $ressougaretra->nomsousgare; ?>:<?= $day1;?></td><td style="font-size: 17px;">HEURE:<?= $heures1;?></td>
                          </tr>
                          <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="3" align=left>TEL:<?= $item->contact_client; ?></td><td align=left>PRIX:<?=$prfi;?></td>
                          </tr>
                          <tr><td align=left width="30%"><?= $item->prenom_client; ?></td><td align=center colspan="3"> <?= ticket_barcode_img($itemtrans->tamponcod, 200, 35); ?></td><td align=left></td>
                          </tr>
                          <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet valable 1 mois. Billet non remboursable</td><td align=left>CONVOCATION 45 mn avant le départ</td>
                          </tr>
                          <tr><td align=left><?=$prfi;?> <?= $nge1;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $itemtrans->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
                          </tr>
                          <tr><td align=left style="font-size: 9px;"><?= $itemtrans->tamponcod;?></td><td colspan="3" align=center style="font-size: 9px;">Suivez et surveillez bien vos bagages</td>
                          </tr>
                          <tr><td align=left style="font-size: 9px;"></td><td colspan="3" align=center style="font-size: 9px;">BON VOYAGE AVEC <?= $itemtrans->nom_compagnie;?>&nbsp;&nbsp;&nbsp;<?= $nge1;?></td>
                          
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
                
                        
                        if(ticket_est_gratuit($itemtrans2->prixvente)){

                          $prfi2 = 'TICKET GRATUIT';
                        }
                        else
                        {
                          $prfi2 = number_format($itemtrans2->prixvente, 0, '', ' ').'FCFA';
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
                                $gt2 = (($g2[0] * 60) + $g2[1] + $ressougaretra2->minutetemps); 
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
                         
                          <tr><td width="25%" style="font-size: 17px;"><?= $itemtrans2->code_ticket; ?></td><td colspan="3" align=left><?= $item->nom_client; ?> <?= $item->prenom_client; ?></td><td style="font-size: 9px;"><?= $itemtrans2->tamponcod; ?> emis : <?= $dtoday; ?></td>
                          </tr>
                          <tr><td align=left style="font-size: 17px;"><?= $itemtrans2->nom_ligne; ?></td><td colspan="3" align=left style="font-size: 17px;">CODE:<?= $itemtrans2->code_ticket; ?></td><td style="font-size: 17px;" align=left>SIEGE:<?= str_pad($itemtrans2->num_siege_categorie, 2, "0", STR_PAD_LEFT);?></td>
                          </tr>
                          <tr><td align=left><?= $ressougaretra2->nomsousgare; ?></td><td align=left colspan="3" style="font-size: 17px;">AXE:<?= $itemtrans2->nom_ligne; ?></td><td align=left style="font-size: 15px;">QUARTIER:<?= $itemtrans2->quart;?></td>
                          </tr>
                          <tr><td align=left style="font-size: 15px;"><?= $day2; ?> <?= $heures2;?> <?= str_pad($itemtrans2->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?></td><td colspan="3" align=left style="font-size: 15px;"><?= $ressougaretra2->nomsousgare; ?>:<?= $day2;?></td><td style="font-size: 17px;">HEURE:<?= $heures2;?></td>
                          </tr>
                          <tr><td align=left><?= $item->nom_client; ?></td><td td colspan="3" align=left>TEL:<?= $item->contact_client; ?></td><td align=left>PRIX:<?=$prfi2;?></td>
                          </tr>
                          <tr><td align=left width="30%"><?= $item->prenom_client; ?></td><td align=center colspan="3"> <?= ticket_barcode_img($itemtrans2->tamponcod, 200, 35); ?></td><td align=left></td>
                          </tr>
                          <tr><td align=left><?= $item->contact_client; ?></td><td colspan="3" align=center style="font-size: 8px;">Billet valable 1 mois. Billet non remboursable</td><td align=left>CONVOCATION 45 mn avant le départ</td>
                          </tr>
                          <tr><td align=left><?=$prfi2;?> <?= $nge2;?></td><td colspan="3" align="center" style="font-size: 9px;"><?= $itemtrans2->nom_compagnie;?> décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.</td>
                          </tr>
                          <tr><td align=left style="font-size: 9px;"><?= $itemtrans2->tamponcod;?></td><td colspan="3" align=center style="font-size: 9px;">Suivez et surveillez bien vos bagages</td>
                          </tr>
                          <tr><td align=left style="font-size: 9px;"></td><td colspan="3" align=center style="font-size: 9px;">BON VOYAGE AVEC <?= $itemtrans2->nom_compagnie;?>&nbsp;&nbsp;&nbsp;<?= $nge2;?></td>
                          
                          </tr>
                      </table>
                  </body>
              </div>
          </div>

      </div>

  </div>
    
</div>

<!--End of file: editpdftransfi2.php-->
<!--File location: application/views/beagle/pages/_tickets/editpdftransfi2.php-->