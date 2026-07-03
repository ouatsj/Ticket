<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    <div class="col-lg-6">
        <p class="mt-0 mb-2 ml-4" align="right">
            <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
            class="btn btn-secondary btn-space" data-modal="">
            <i class="fas fa-arrow-circle-left text-info"></i>
            &nbsp;RETOUR ACCUEIL&nbsp;
            </a>
        </p>
    <div>
    <script type="text/javascript">
        window.onload = function () {
            window.print();
        }
    </script>
                 
    <div class="col-lg-12">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    
                    <body>
                        
                      <? $ckey = $this->session->company->ekey;
                        $this->entreprise = $this->m_entreprises->get_key($ckey);
                          
                          $dat = explode("-", $single->dateenvoiesc);
                          $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];?>
                        	<?

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
                            
                            $j = $single->nombrecolis;

                            for ($i=1; $i<=$j; $i++) { 
                                   $n =($i.'/'.$j);
                            }
                        ?>
                        <tr><td style="font-size: 70px; width: 40%;"> <img src="<?echo site_url($single->logo);?>" width="850" height="350"></td></tr>
                        <div style="border:2px solid; font-size: 60px; width: 90%;" align="center"><b>
                            Code:<?= $single->num_couresc;?> <?= $n;?>
                        </div>
                      
                        <table>
                            <tr><td style="font-size: 60px;">Trajet:<?= $single->nom_gaep; ?> (<?= $single->nomsousgare; ?>)-<?= $single->nom_gadest; ?> (<?= $single->quartier_courrieresc; ?>)</td></tr>
                        </table>
                        <h4 style="text-decoration: underline;" align="center"></h4>
                        <table width="90%" style="font-size: 60px;">
                          <tr><th>Expedié le</th><th>à</th><th>Courrier</th></tr>
                          <tr><td><?= $day;?></td><td><?= $single->heure; ?></td><td></td></tr>
                        
                        </table>
                        <div style="border:2px solid; margin:2px; width:90%;">
                          
                            <table width="70%" style="font-size: 60px;">
                              
                              <tr><th style="text-decoration: underline solid;" align="center">Exp:</th></tr>
                            <tr><th><?= $exped->nom_client;?></th><th> <?= $exped->prenom_client;?></th></tr>
                            <tr><th><?= $exped->contact_client;?></th><th><?= $exped->num_CNIB;?></th><th></th></tr>
                            <tr><th><?= $exped->date_delivre;?></th><th><?= $exped->lieu_delivre;?></th><th></th></tr>
                            </table>
                        </div>
                        <div style="border:2px solid; margin:2px; width:90%;">
                         
                          <table width="90%" style="font-size: 60px;">
                            
                            <tr><th style="text-decoration: underline solid;" align="center">Dest:</th></tr>
                            <tr><th><?= $destin->nomprenom_perso;?></th><th></th><th><?= $destin->contact_perso;?></th></tr>
                            
                          </table>
                        </div>
                        <div style="border:2px solid; margin:2px; width:90%;" style="font-size: 60px;">
                            
                            <table width="90%" style="font-size: 60px;">
                              <tr><th style="text-decoration: underline solid;" align="center">Contenu</th></tr>
                              <tr><th>Designation</th><th>Valeur</th><th>Frais</th></tr>
                              <tr><td><?= $single->nombrecolis;?><?= $single->naturecoli;?> <?= $single->naturecourrieresc;?></td><td><?= $single->valeurscoli;?> FCFA </td><td><?= number_format($single->prixcolisesc, 0, '', ' ');?> FCFA</td></tr>
                            </table>
                        </div>
                        <table>
                        <tr><td style="font-size: 40px;">émis : <?= $dtoday;?> par <?=$conex->username;?></td></tr>
                        </table>
                        <div>
                          <table>
                            <tr><td style="font-size: 40px;">N.B : La garantie des objets expediés est de 72</td></tr>
                            <tr><td style="font-size: 40px;">heures à destination. L'argent, le mandat,</td></tr>
                            <tr><td style="font-size: 40px;">les bons d'essence, les cheques bancaires et les objets de valeurs ne sont pas autorisés dans</td></tr>
                            <tr><td style="font-size: 40px;"> les enveloppes. Car, <?= $single->nom_entreprise;?> décline toutes </td></tr>
                            <tr><td style="font-size: 40px;">responsabilités en cas de perte.</td></tr>
                            <tr><td style="font-size: 40px;">Tout colis dont la valeur n'a pas été declarée,</td></tr>
                            <tr><td style="font-size: 40px;">ne peut être remboursé qu'à hauteur de vingt cinq milles (25 000) francs en cas de perte.</td></tr>
                          </table>
                        </div>             
                    </body>
                </div>
            </div>

        </div>
    </div>
   
<!--End of file: escindexcolis2.php-->