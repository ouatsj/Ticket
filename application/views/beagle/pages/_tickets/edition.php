<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

?>

<div class="row">

    <div class="col-12 col-lg-4">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    <p class="mt-0 mb-2 ml-4">
                        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
                            <i class="fas fa-arrow-circle-left text-info"></i>
                        </a>
                    </p>
                     <?

                        $ckey = $this->session->company->ekey;
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
                        ; ?>
                    <h4><?= $item->nom_client; ?> <?= $item->prenom_client; ?></h4>
                    <? 
                        $nod = $item->depart_code;
                          $nget = substr($nod, 3);

                        if($item->gareidentif === 'OUA12')
                        {

                           $de = 'O';
                           $cx = $de.$nget;

                           $d = explode($item->gareidentif, $cx);
                           $x = $d[1];
                           
                        }
                        else
                        {

                            $d = explode($item->gareidentif, $item->depart_code);

                            $x = $d[1];

                        }
                    ?>

                    <p>Ligne:<?= "{$item->nom_ligne}"; ?></p>
                    <p>Date_depart:<?= "{$item->date_progr}"; ?></p>
                    <p>Heure_depart:<?= "{$heures}"; ?></p>
                    <p>Siege:<?= "{$item->num_siege_categorie}"; ?></p>
                    <p><b>N° BUS : <?=$x;?></b></p>
                    
                    <p>Tel:<?= "{$item->contact_client}"; ?></p>

                </div>
            </div>

        </div>

    </div>

</div>

<!--End of file: edition.php-->
<!--File location: application/views/beagle/pages/_tickets/edition.php-->