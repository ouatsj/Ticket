<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-12 col-lg-4">

        <div class="tab-container tab-left">

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    <h4><?= $banque->nom_bank; ?></h4>
                    <p><?= "{$banque->code_bank} / {$banque->code_agence} / {$banque->num_compte}"; ?></p>
                    <p><?= "{$banque->cle_RIB}"; ?></p>
                </div>
            </div>

        </div>

    </div>
</div>

<!--End of file: edition.php-->
<!--File location: application/views/beagle/pages/_banque/edition.php-->