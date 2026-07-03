<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-12 col-lg-4">

        <div class="tab-container tab-left">

        
            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    <h4><?= $categorie->categorie; ?></h4>
                    <p><?= "{$categorie->nbr_place}"; ?></p>
                    <p><?= "{$categorie->nbr_colonne}"; ?></p>

                </div>
            </div>

        </div>

    </div>

</div>

<!--End of file: detail.php-->
<!--File location: application/views/beagle/pages/_categorie/detail.php-->