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
                        <?php
                        $agentNom = (isset($nam) && $nam && isset($nam->first_name))
                            ? trim($nam->first_name . ' ' . $nam->last_name)
                            : '';
                        $convoyeurNom = '';
                        if (isset($onprogrambordaxe) && $onprogrambordaxe) {
                            $convoyeurNom = trim(urldecode((string) $onprogrambordaxe->busconvoybordbag));
                            if ($convoyeurNom === '' || $convoyeurNom === '0' || strcasecmp($convoyeurNom, 'null') === 0) {
                                $convoyeurNom = '';
                            }
                        }
                        ?>
                        <p class="meta"><strong><?= mdate("%d/%m/%Y", now('UTC')); ?></strong></p>
                        <p class="meta"><?= htmlspecialchars(isset($onprogrambordaxe->dateheure_prog) ? $onprogrambordaxe->dateheure_prog : ''); ?></p>
                        <p class="meta"><strong>BORDEREAU N°:</strong> <?= htmlspecialchars(isset($onprogrambordaxe->identbordbag) ? (string) $onprogrambordaxe->identbordbag : ''); ?></p>
                        <p class="meta"><strong>CHAUFF:</strong> <?= htmlspecialchars(isset($onprogrambordaxe->buschauffbordbag) ? urldecode($onprogrambordaxe->buschauffbordbag) : ''); ?></p>
                        <p class="meta"><?= htmlspecialchars((isset($onprogrambordaxe->nom_ligne) ? $onprogrambordaxe->nom_ligne : '') . '  ' . (isset($onprogrambordaxe->quartierbordbag) ? $onprogrambordaxe->quartierbordbag : '')); ?></p>
                        <table>
                        <thead> 
                            <tr>
                              <th>NUM_BAG</th>
                              <th>QTE / DESIGNATION</th> 
                              <th>NOM ET PRENOM / CONTACT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <? foreach ($onbord as $departhbord => $lementbord): ?>
                            <tr>
                              <td><strong><?= htmlspecialchars((string) $lementbord->identbagas); ?></strong></td>
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
                </div>
            </div>
        </div>
    </div>
<!--End of file: reimpressionsuivi.php-->
<!--File location: application/views/beagle/pages/_tickets/reimpressionsuivi.php-->
