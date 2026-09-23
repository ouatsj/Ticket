<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6 no-print" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url("confirmation/bordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space md-trigger" data-modal="">
            <i class="fas fa-print text-info"></i>&nbsp; VOIR ENVOYES&nbsp;
        </a>
    </p>
</div>
<style>
    @page { size: A4 landscape; margin: 10mm; }
    @media print {
        .no-print { display: none !important; }
        body { font-size: 12pt; }
    }
    .bordereau-envoi-print {
        font-size: 14px;
        line-height: 1.35;
        max-width: 100%;
    }
    .bordereau-envoi-print table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0.75rem;
    }
    .bordereau-envoi-print th,
    .bordereau-envoi-print td {
        border: 1px solid #222;
        padding: 8px 10px;
        font-size: 13px;
        text-align: left;
        vertical-align: top;
    }
    .bordereau-envoi-print th { font-size: 14px; }
    .bordereau-envoi-print .meta { font-size: 16px; margin: 0.25rem 0; }
    .bordereau-signatures {
        display: flex;
        justify-content: space-between;
        gap: 2rem;
        margin-top: 2.5rem;
        page-break-inside: avoid;
    }
    .bordereau-signatures .sig-bloc { width: 48%; min-height: 100px; }
    .bordereau-signatures .sig-ligne {
        margin-top: 1.5rem;
        border-bottom: 1px solid #333;
        min-height: 1.4rem;
    }
</style>
<script type="text/javascript">
    window.onload = function() {
      window.print();
    }
</script>

    <div class="col-lg-12">
        <div class="tab-container tab-left">
            <div class="tab-content">
                <div class="tab-pane active show bordereau-envoi-print" id="icon1" role="tabpanel">
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

                      $agentNom = ($nam && isset($nam->first_name))
                          ? trim($nam->first_name . ' ' . $nam->last_name)
                          : '';
                      $convoyeurNom = trim(urldecode((string) (isset($onprogrambordaxe->busconvoybordbag) ? $onprogrambordaxe->busconvoybordbag : $cvbord)));
                      if ($convoyeurNom === '' || $convoyeurNom === '0' || strcasecmp($convoyeurNom, 'null') === 0) {
                          $convoyeurNom = '';
                      }

                  ?>
                        <p class="meta"><strong><?= mdate("%d/%m/%Y", now('UTC')); ?></strong>
                            — <?= htmlspecialchars(isset($onprogrambordaxe->dateheure_prog) ? $onprogrambordaxe->dateheure_prog : ''); ?>
                            — N° BORDEREAU : <?= htmlspecialchars(isset($onprogrambordaxe->identbordbag) ? (string) $onprogrambordaxe->identbordbag : (string) $numb); ?>
                        </p>
                        <?php if (isset($ncomp) && $ncomp && isset($ncomp->nom_compagnie)): ?>
                        <p class="meta"><strong><?= htmlspecialchars($ncomp->nom_compagnie); ?></strong></p>
                        <?php endif; ?>
                        <table>
                        <thead> 
                            <tr>
                              <th>NUM_BAG</th>
                              <th>CODE</th>
                              <th>QTE / DESIGNATION</th> 
                              <th>NOM ET PRENOM / CONTACT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <? foreach ($onbord as $departhbord => $lementbord): ?>
                            <tr>
                              <td><strong><?= htmlspecialchars((string) $lementbord->identbagas); ?></strong></td>
                              <td><strong><?= htmlspecialchars((string) $lementbord->codebag); ?></strong></td>
                              <td><strong><?= htmlspecialchars($lementbord->nombrebagageenv .'/'.$lementbord->nombrebagage. ' ' . $lementbord->typebagagesenv . ' '.$lementbord->contenubagageenv); ?></strong></td>
                              <td><strong><?= htmlspecialchars($lementbord->nom_client . ' ' . $lementbord->prenom_client . ' ' . $lementbord->contact_client); ?></strong></td>
                            </tr>
                          <? endforeach; ?> 
                        </tbody>
                      </table>

                    <div class="bordereau-signatures">
                        <div class="sig-bloc">
                            <div><strong>AGENT (bordereau)</strong></div>
                            <div>Nom : <strong><?= $agentNom !== '' ? htmlspecialchars($agentNom) : '……………………………………'; ?></strong></div>
                            <div class="sig-ligne">Signature</div>
                        </div>
                        <div class="sig-bloc" style="text-align:right;">
                            <div><strong>CONVOYEUR</strong></div>
                            <div>Nom : <strong><?= $convoyeurNom !== '' ? htmlspecialchars($convoyeurNom) : '……………………………………'; ?></strong></div>
                            <div class="sig-ligne">Signature</div>
                        </div>
                    </div>
                <? } ?>
                </div>
            </div>
        </div>
    </div>
<!--End of file: pdfsuivi.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfsuivi.php-->
