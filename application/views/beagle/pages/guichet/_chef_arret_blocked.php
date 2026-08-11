<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="text-center">
            <div class="alert alert-danger mx-3 mb-3" role="alert">
                <strong>Compte temporairement bloqué.</strong><br>
                <?= htmlspecialchars($compte_arret_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <p>
                <a href="<?= site_url(
                    'caisses/compte/' . $this->session->company->ekey
                    . '/' . $conex->roleattribut
                    . '/' . $bus_stop->idengare
                    . '/' . $bus_stop->idsousgare
                ); ?>" class="btn btn-danger btn-space">
                    <i class="fas fa-puzzle-piece"></i>&nbsp;ARRÊT DE COMPTE
                </a>
            </p>
        </div>
    </div>
</div>
