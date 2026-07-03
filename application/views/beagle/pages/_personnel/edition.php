<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="row">

    <div class="col-12 col-lg-4">

        <div class="tab-container tab-left">

            <ul class="nav nav-tabs nav-tabs-primary nav-tabs-classic" role="tablist">

                <li class="nav-item">
                    <a class="nav-link active show" href="#icon1" data-toggle="tab" role="tab" aria-selected="false">
                        <span class="fas fa-face"></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#icon2" data-toggle="tab" role="tab" aria-selected="false">
                        <span class="fas fa-home"></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#icon3" data-toggle="tab" role="tab" aria-selected="true">
                        <span class="fas fa-driver"></span>
                    </a>
                </li>

            </ul>

            <div class="tab-content">

                <div class="tab-pane active show" id="icon1" role="tabpanel">
                    <h4><?= $personnel->matricule; ?></h4>
                    <p><?= "{$personnel->nom_conducteur} / {$personnel->contact_perso} / {$personnel->contact2}"; ?></p>
                    <p><?= "{$personnel->adresse}"; ?></p>
                    <p><?= "{$personnel->pieces1} / {$personnel->date_delivre1} / {$personnel->date_delivre2}"; ?></p>
                    <p><?= "{$personnel->pieces2} / {$personnel->date_delivre2} / {$personnel->date_expire2}"; ?></p>
                </div>
            </div>

        </div>

    </div>
</div>

<!--End of file: edition.php-->
<!--File location: application/views/beagle/pages/_personnel/edition.php-->