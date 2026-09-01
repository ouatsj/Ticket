<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="col-lg-6" align="right">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i> 
        </a>
    </p>
</div>
    <div class="col-lg-6">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <div id="ticketContent2">

                    <table>
                        <? $ckey = $this->session->company->ekey;
                        $this->entreprise = $this->m_entreprises->get_key($ckey);
                        
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

                        $dx = explode($item->gareidentif, $item->depart_code);

                          $x =$dx[1];

                        $dat = explode("-", $item->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                        ?>
                        <tr><td style="font-size: 5px; width: 4%;"> <img src="<?echo site_url($item->logo);?>" width="90" height="50"></td></tr>
                        <tr><td style="font-size: 6px;"><b>TICKET CODE : <?= "{$item->code_ticket}"; ?></b></td></tr>
                        <tr><td style="font-size: 6px;"><?= "{$item->nom_gaep}"; ?> <?= "{$ressougare->nomsousgare}"; ?>-<?= "{$item->nom_gadest}"; ?> <?= "{$item->quart}"; ?></td></tr>
                        <tr><td style="font-size: 6px;"><?= $item->nom_client; ?> <?= $item->prenom_client; ?>&nbsp;&nbsp;<?= "{$item->contact_client}"; ?></td></tr>
                        <tr><td style="font-size: 8px;"><b><?= "{$day}"; ?>&nbsp;&nbsp; <?= $heures; ?></b></td></tr>
                        <tr><td style="font-size: 8px;"><b>Siege: <?= str_pad($item->num_siege_categorie, 2, "0", STR_PAD_LEFT); ?><b>&nbsp;&nbsp;&nbsp;<?= number_format("{$item->prix}", 0, '', ' '); ?> &nbsp;FCFA </td></tr>
                        <tr><td style="font-size: 8px;"><b>N° BUS :<?=$x;?></b></td></tr>
                        <tr><td style="font-size: 5px;">BON VOYAGE AVEC <?= $item->nom_compagnie;?> <?= $nge;?></td></tr>
                        <tr><td style="font-size: 8px; width:5%;"> <?= ticket_barcode_img($item->tamponcod, 90, 35); ?></td></tr>
                        <tr><td style="font-size: 6px;">emis : <?= $dtoday; ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
  <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        // Cacher le contenu du ticket avant d'imprimer
        var ticketContent2 = document.getElementById("ticketContent2");
        
        // Créer une nouvelle fenêtre pour l'impression
        var printWindow = window.open('', '', 'width=50,height=30');

        // Ajouter le contenu du ticket dans la nouvelle fenêtre
        printWindow.document.write('<html><head><title>Ticket</title></head><body>');
        printWindow.document.write(ticketContent2.innerHTML);
        printWindow.document.write('</body></html>');
        
        // Lancer l'impression dans la nouvelle fenêtre
        printWindow.print();

        // Attendre que le contenu soit complètement chargé dans la nouvelle fenêtre
        printWindow.onload = function() {
            // Attendre quelques secondes pour que la page se charge avant d'imprimer
            setTimeout(function() {
                printWindow.print();
                //printWindow.close();
            }, 500); // Vous pouvez ajuster ce délai en fonction des besoins
        };

        printWindow.document.close();  // Fermer le flux de données
        printWindow.focus();
        // Fermer la fenêtre après l'impression (facultatif)
        //printWindow.close();

        /*printWindow.onafterprint = function () {
            printWindow.close();
        };*/

        //setTimeout(function() {
            //printWindow.close();  // Fermer la fenêtre après un délai
        //}, 3000); 
        
        // Masquer le ticket sur la page actuelle
        ticketContent2.style.display = 'none';
    });
  </script>
<!--End of file: pdfepsonreduit.php-->
<!--File location: application/views/beagle/pages/_tickets/pdfepsonreduit.php-->