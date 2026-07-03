<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bagagesuivimobile/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-space btn-secondary">
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
                          $dat = explode("-", $itembags->date_create);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                          $cdebg = explode("-", $itembags->id_bagage);
                          $encd = $cdebg[0];
                        ?>
                        <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($itembags->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 65px;"><b>Reçu N° : <?=$encd; ?></b></td></tr>
                        <tr><td style="font-size: 60px;"><?= "{$itembags->nom_gaep}"; ?> <?= "{$bus_stop->nomsousgare}"; ?>-<?= "{$itembags->nom_gadest}"; ?> <?= $itembags->quartarr_bg; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact exp:<?= $itembags->contactexpedi; ?> <?= $itembags->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact dest:<?= $itembags->contact_client; ?></td></tr>
                        <tr><td style="font-size: 65px;"> <?= $itembags->nom_client; ?> <?= $itembags->prenom_client; ?></td></tr>
                        
                        <tr><td style="font-size: 65px;">Nombre bagage:<?= $itembags->nombrebagage; ?>&nbsp;(<?=$itembags->typebagages; ?>)</td></tr>
                        <tr><td style="font-size: 65px;">Contenu:<?= $itembags->contenubagage; ?></td></tr>
                        <tr><td style="font-size: 65px;">Valeur:<?= $itembags->valeurbagage; ?>FCFA</td></tr>
                        <tr><td style="font-size: 65px;">Facturer le <b><?= $day; ?></td></tr>
                        <tr><td style="font-size: 65px;">Prix:<?= number_format($itembags->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 40px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itembags->username; ?> code <?= $itembags->codebag; ?></td></tr>
                        <tr><td style="font-size: 40px;"><?= $itembags->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 40px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 40px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 50px;">BON VOYAGE AVEC <?= $itembags->nom_compagnie;?></td></tr>
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
                          $dat = explode("-", $itembags->date_create);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                          $cdebg = explode("-", $itembags->id_bagage);
                          $encd = $cdebg[0];
                        ?>
                        <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($itembags->logo);?>" width="850" height="350"></td></tr>
                        <tr><td style="font-size: 65px;"><b>Reçu N° : <?=$encd; ?></b></td></tr>
                        <tr><td style="font-size: 60px;"><?= "{$itembags->nom_gaep}"; ?> <?= "{$bus_stop->nomsousgare}"; ?>-<?= "{$itembags->nom_gadest}"; ?> <?= $itembags->quartarr_bg; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact exp:<?= $itembags->contactexpedi; ?> <?= $itembags->genrebagage; ?></td></tr>
                        <tr><td style="font-size: 65px;">Contact dest:<?= $itembags->contact_client; ?></td></tr>
                        <tr><td style="font-size: 65px;"> <?= $itembags->nom_client; ?> <?= $itembags->prenom_client; ?></td></tr>
                        <tr><td style="font-size: 65px;">Nombre bagage:<?= $itembags->nombrebagage; ?>&nbsp;(<?=$itembags->typebagages; ?>)</td></tr>
                        <tr><td style="font-size: 65px;">Contenu:<?= $itembags->contenubagage; ?></td></tr>
                        <tr><td style="font-size: 65px;">Valeur:<?= $itembags->valeurbagage; ?>FCFA</td></tr>
                        <tr><td style="font-size: 65px;">Facturer le <b><?= $day; ?></td></tr>
                        <tr><td style="font-size: 65px;">Prix:<?= number_format($itembags->prix_bagage, 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 40px;">emis : <?= $dtoday; ?> &nbsp;par <?= $itembags->username; ?> code <?= $itembags->codebag; ?></td></tr>
                        <tr><td style="font-size: 40px;"><?= $itembags->nom_compagnie;?> décline toute responsabilité en cas de</td></tr>
                        <tr><td style="font-size: 40px;">perte ou de vol de billet et de bagages même payés.</td></tr>
                        <tr><td style="font-size: 40px;">Suivez et surveillez bien vos bagages</td></tr>
                        <tr><td style="font-size: 50px;">BON VOYAGE AVEC <?= $itembags->nom_compagnie;?></td></tr>
                        </table>
                    </body>
                </div>
            </div>

        </div>
    </div>

<!--End of file: spdfepsonbagsuivi.php-->
<!--File location: application/views/beagle/pages/_tickets/spdfepsonbagsuivi.php-->