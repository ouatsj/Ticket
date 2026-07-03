<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bagageescal/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
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
                        
                        $dat = explode("-", $itemescbag->date_createesc);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                        ?>
                        <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($itemescbag->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 65px;"><b>Reçu N° : <?= str_pad($itemescbag->id_bagageesc, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                        <tr><td style="font-size: 60px;"><?= $itemescbag->nom_gaep; ?> <?= $itemescbag->nomsousgare; ?>-<?= $itemescbag->nom_gadest; ?> <?= $itemescbag->quartier_escal; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact exp:<?= $itemescbag->contactexpediesc; ?> <?= $itemescbag->genrebagageesc; ?></td></tr>
                        <tr><td style="font-size: 65px;">Nombre bagage&nbsp;:&nbsp;<b><?= $itemescbag->nombrebagageesc; ?>&nbsp;(<?=$itemescbag->typebagagesesc; ?>)</b></td></tr>
                        <tr><td style="font-size: 60px;">Contenu:<?= $itemescbag->contenubagageesc; ?></td></tr>
                        
                        <tr><td style="font-size: 65px;">Valeur:<?= $itemescbag->valeurbagageesc; ?>&nbsp;FCFA</td></tr>
                        <tr><td style="font-size: 65px;"><b><?= $day; ?>&nbsp;&nbsp; <?= $itemescbag->heure; ?></b></td></tr>
                        <tr><td style="font-size: 65px;"><?= number_format($itemescbag->prix_bagageesc, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 40px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itemescbag->username; ?> code <?= $itemescbag->codebagesc; ?></td></tr>
                        <tr><td style="font-size: 40px;"><?= $itemescbag->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 40px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 40px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 50px;">BON VOYAGE AVEC <?= $itemescbag->nom_compagnie;?></td></tr>
                        </table>
                    </body>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
      
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
    </div>
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
                        
                        $dat = explode("-", $itemescbag->date_createesc);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                        ?>
                        <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($itemescbag->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 65px;"><b>Reçu N° : <?= str_pad($itemescbag->id_bagageesc, 3, "0", STR_PAD_LEFT); ?></b></td></tr>
                        <tr><td style="font-size: 60px;"><?= $itemescbag->nom_gaep; ?> <?= $itemescbag->nomsousgare; ?>-<?= $itemescbag->nom_gadest; ?> <?= $itemescbag->quartier_escal; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact exp:<?= $itemescbag->contactexpediesc; ?> <?= $itemescbag->genrebagageesc; ?></td></tr>
                        <tr><td style="font-size: 65px;">Nombre bagage&nbsp;:&nbsp;<b><?= $itemescbag->nombrebagageesc; ?>&nbsp;(<?=$itemescbag->typebagagesesc; ?>)</b></td></tr>
                        <tr><td style="font-size: 60px;">Contenu:<?= $itemescbag->contenubagageesc; ?></td></tr>
                        
                        <tr><td style="font-size: 65px;">Valeur:<?= $itemescbag->valeurbagageesc; ?>&nbsp;FCFA</td></tr>
                        <tr><td style="font-size: 65px;"><b><?= $day; ?>&nbsp;&nbsp; <?= $itemescbag->heure; ?></b></td></tr>
                        <tr><td style="font-size: 65px;"><?= number_format($itemescbag->prix_bagageesc, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 40px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itemescbag->username; ?> code <?= $itemescbag->codebagesc; ?></td></tr>
                        <tr><td style="font-size: 40px;"><?= $itemescbag->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 40px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 40px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 50px;">BON VOYAGE AVEC <?= $itemescbag->nom_compagnie;?></td></tr>
                      </table>
                    </body>
                </div>
            </div>

        </div>
    </div>

<!--End of file: pdfepsonbagesc.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfepsonbagesc.php-->