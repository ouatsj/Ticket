<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-print text-info"></i>&nbsp; VOIR ENVOYES&nbsp;
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
                  <?
                      $this->entreprise = $this->m_entreprises->get_key($ckey);
                      $cdbord = $this->input->post('courschauffeurbgt');
                    $cprgbord = $this->input->post('courdeptprograbgt');
                    $cvbord = $this->input->post('courconvoibgt');
                    $dabord = $this->input->post('courborddeptdateenbgt');
                    $lignebord = $this->input->post('deptscourlignebgt');
                    $usenam = $this->input->post('usernames');
                  $nam = $this->m_compte_user->for($usenam);
              $lignequart = $this->input->post('courdeptquartierbgt');
              $gd = $this->input->post('gareattribuer');
              $sgd = $this->input->post('sousgareconnect');
              $iduser = $this->input->post('usernameconect');
              
              $itinerairesg = $this->db->query("SELECT sg.nomsousgare, sg.idsousgare FROM sousgare sg WHERE sg.idsousgare = '$sgd'")->row();

              //identifiant l'heure dans la table ligne heure

              $ligne_lhbord = strpos($this->input->post('deptscourlignebgt'), '/');
              
              $lignehbord = substr($this->input->post('deptscourlignebgt'), 0, $ligne_lhbord);
              $lignelhrebord = substr($this->input->post('deptscourlignebgt'), $ligne_lhbord + 1, strlen($this->input->post('deptscourlignebgt')));
              
              $ligne_lhbord1 = strpos($lignehbord, '-');
              
              $lignehbord1 = substr($lignehbord, 0, $ligne_lhbord1);
                $lignelhrebord1 = substr($lignehbord, $ligne_lhbord1 + 1, strlen($lignehbord));
              
              $post_heurebord = strpos($this->input->post('courdeptprograbgt'), '/');

              $sub_heurebord = substr($this->input->post('courdeptprograbgt'), 0, $post_heurebord);

              $dprogbord = substr($this->input->post('courdeptprograbgt'), $post_heurebord + 1, strlen($this->input->post('courdeptprograbgt')));

              $post_heurebord1 = strpos($dprogbord, '/');

              $sub_heurebord1 = substr($dprogbord, 0, $post_heurebord1);

              $dprogbord1 = substr($dprogbord, $post_heurebord1 + 1, strlen($dprogbord));
              
              $post_heurebord2 = strpos($dprogbord1, '/');

              $sub_heurebord2 = substr($dprogbord1, 0, $post_heurebord2);

              $dprogbord2 = substr($dprogbord1, $post_heurebord2 + 1, strlen($dprogbord1));

              
              //$nombord = $cdpbord;
              //$ngebord = substr($nombord, 6, 6);

                if($cdbord != '' AND $cprgbord !='')
                {
                  $this->entreprise = $this->m_entreprises->get_key($ckey);
               
                    if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        
                        $onbord = $this->m_envoibagages->listad1($this->entreprise->ekey, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
               
                          $onprogrambordaxe = $this->m_bordereaubagage->get($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $lignequart, $dabord);
                    }
                    else
                    {
                        $onbord = $this->m_envoibagages->list1($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $sub_heurebord2, $dabord, $lignequart);
                        

                            
                            $onprogrambordaxe = $this->m_bordereaubagage->get($this->entreprise->ekey, $gd, $sgd, $sub_heurebord, $lignequart, $dabord);
                    }
                      
                     
                     $addtiragebordbg = array(
                        'idoperbordbag' => $iduser,
                        'idsousgdbordbag' => $sgd,
                        'programmebordbag' => $sub_heurebord,
                         'lignebordbag' => $lignehbord,
                         'quartierbordbag' => $this->input->post('courdeptquartierbgt'),
                         'datebordbag' => $this->input->post('courborddeptdateenbgt'),
                         'buschauffbordbag' => $this->input->post('courschauffeurbgt'),
                         'busconvoybordbag' => $this->input->post('courconvoibgt'),
                     );

                      if($onprogrambordbg === NULL){

                         $numb = $this->m_bordereaubagage->create($addtiragebordbg);

                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                      }
                      else
                      {

                        $this->m_bordereaubagage->update($onprogrambordbg->identbordbag, $addtiragebordbg);

                        $numb = $onprogrambordaxe->identbordbag;

                        $ln = $this->m_bordereaubagage->getnu($this->entreprise->ekey, $numb);
                      }

                  ?>
                        <p align="left" style="font-size: 40px;"> <?=mdate("%d/%m/%Y", now('UTC')); ?> <?= $onprogrambordaxe->dateheure_prog . '    N° BORDEREAU : '.$onprogrambordaxe->identbordbag; ?></p>&nbsp;&nbsp;<b>
                        <p align="left" style="font-size: 40px;"><?= $ncomp->nom_compagnie;?></p>&nbsp;&nbsp;
                        <table border="2" cellpadding="0">
                        <thead> 
                            <tr>
                              <th width="120px" align="left" style="font-size: 40px;"><strong>NUM_BAG</strong></th>
                              <th width="320px" align="left" style="font-size: 40px;"><strong>CODE</strong></th>
                              
                              <th width="190px" align="left" style="font-size: 40px;"><strong>QTE/DESIGNATION</strong></th> 
                              <th width="190px" align="left" style="font-size: 40px;"><strong>NOM ET PRENOM/CONTACT</strong></th>
                            </tr>
                        </thead>
                        <body>
                            <? $montantglobal = 0;
                            $montantglobalr = 0; ?>
                            <? foreach ($onbord as $departhbord => $lementbord): ?>
                            <tr>
                              <td width="320px" align="left" style="font-size: 40px;"><strong><?=$lementbord->identbagas; ?></strong></td>
                              <td width="120px" align="left" style="font-size: 40px;"><strong><?=$lementbord->codebag; ?></strong></td>
                              <td width="190px" align="left" style="font-size: 40px;"><strong><?=$lementbord->nombrebagageenv .'/'.$lementbord->nombrebagage. ' ' . $lementbord->typebagagesenv . ' '.$lementbord->contenubagageenv; ?></strong></td>
                              <td width="190px" align="left" style="font-size: 40px;"><strong><?=$lementbord->nom_client . '&nbsp;&nbsp;' . $lementbord->prenom_client . ' ' . $lementbord->contact_client; ?></strong></td>
                            </tr>
                          <? endforeach; ?> 
                              
                        </body>
                      </table>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                  
                    <p align="left" style="font-size: 40px;">Agent ecrivain bagage : <?= $nam->first_name.' '.$nam->last_name; ?> </p>
                    <p align="left" style="font-size: 40px;"> : <?= urldecode($onprogrambordaxe->busconvoybordbag); ?> </p>
                    <p align="left" style="font-size: 40px;">Agent recepteur : </p>
                <? } ?>
                </div>
            </div>

        </div>
    </div>
<!--End of file: pdfsuivi.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfsuivi.php-->