<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
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
