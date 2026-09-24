<?php defined('BASEPATH') OR exit('No direct script access allowed');
$__retour_fb = retour_sousgare_url(
    $this->session->company->ekey,
    $this->uri->segment(4),
    (!empty($user_connect) && !empty($user_connect->roleattribut)) ? $user_connect->roleattribut : $this->uri->segment(6)
);
$__retour_href = retour_url($__retour_fb);
?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= htmlspecialchars($__retour_href, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR&nbsp;
        </a>
    </p>
</div>
    <div class=row>
        <div class="col-8 text-center">

            <div class="card card-table">

                <div class="card-header">

                    <div class="tools dropdown">

                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                            <span class="icon mdi mdi-more-vert"></span>

                        </a>

                    </div>

                    <div class="title">validation recette du jour</div>

                </div>
                <div class="card-body">

                    <div class="table-responsive noSwipe">

                        <table class="table table-striped table-hover" id="table1">

                            <thead>
                            <tr>
                                <th>RECETTE GLOBAL DU JOUR</th>
                                <th>VALIDER</th>
                            </tr>
                            </thead>

                            <tbody>
                            
                                <? foreach ($recettes as $item): ?>
                                    <td><?=$item->total;?></td>
                                    <td>
                                        
                                    </td>
                                <?endforeach;?>
                            </tbody>

                        </table>

                    </div>

                </div>
            </div>
            
        </div>
        <div class="col-8 text-center">

            <div class="card card-table">

                <div class="card-header">

                    <div class="tools dropdown">

                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                            <span class="icon mdi mdi-more-vert"></span>

                        </a>

                    </div>

                    <div class="title">validation depense du jour</div>

                </div>
                <div class="card-body">

                    <div class="table-responsive noSwipe">

                        <table class="table table-striped table-hover" id="table3">

                            <thead>
                                <tr>
                                    <th>DEPENSE GLOBAL DU JOUR</th>
                                    <th>VALIDER</th>
                                </tr>
                            </thead>

                            <tbody>
                            
                                <? foreach ($depenses as $item): ?>
                                    <td><?=$item->total;?></td>
                                    <td>
                                        
                                    </td>
                                <?endforeach;?>
                            </tbody>
                        
                        </table>

                    </div>

                </div>
            </div>
            
        </div>
        <div class="col-8 text-center">

            <div class="card card-table">

                <div class="card-header">

                    <div class="tools dropdown">

                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                            <span class="icon mdi mdi-more-vert"></span>

                        </a>

                    </div>

                    <div class="title">validation depot caisse</div>

                </div>
                <div class="card-body">

                    <div class="table-responsive noSwipe">

                        <table class="table table-striped table-hover" id="table2">

                            <thead>
                            <tr>
                                <th>DEPOT GLOBAL</th>
                                <th>VALIDER</th>
                            </tr>
                            </thead>

                            <tbody>
                                <? foreach ($depots as $item): ?>
                                
                                <td><?=$item->total;?></td>
                                <td>
                                    

                                </td>
                                <? endforeach; ?>
                            </tbody>

                        </table>

                    </div>

                </div>
        </div>
        <div class="col-8 text-center">

            <div class="card card-table">

                <div class="card-header">

                    <div class="tools dropdown">

                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">

                            <span class="icon mdi mdi-more-vert"></span>

                        </a>

                    </div>

                    <div class="title">validation versement caisse</div>

                </div>
                <div class="card-body">

                    <div class="table-responsive noSwipe">

                        <table class="table table-striped table-hover" id="table4">

                            <thead>
                            <tr>
                                <th>VERSEMENT GLOBAL</th>
                                <th>VALIDER</th>
                            </tr>
                            </thead>

                            <tbody>
                                <? foreach ($versements as $item): ?>
                                
                                <td><?=$item->montant_solde;?></td>
                                <td>
                                    

                                </td>
                                <? endforeach; ?>
                            </tbody>

                        </table>

                    </div>

                </div>
            </div>
            
        </div>
    </div>
<!--End of file: validationcaisse.php-->
<!--File location: application/views/beagle/pages/_caisse/validationcaisse.php-->
