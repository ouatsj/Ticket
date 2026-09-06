<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$code_gaexp = !empty($code_gaexp_vente)
    ? $code_gaexp_vente
    : (!empty($bus_stop->code_gaexp) ? $bus_stop->code_gaexp : (isset($bus_stop->gareprinceid) ? $bus_stop->gareprinceid : ''));
$escales_depart = !empty($escales_depart) ? $escales_depart : array();
?>
<div class="row">
    <p class="mt-0 mb-2 ml-4">
        <a href="<?= site_url('gares/'.$this->session->company->ekey.'/gTc/'. $bus_stop->idengare.'/compte/'. $conex->roleattribut .'/'. $bus_stop->idsousgare.'/'. mdate("%d/%m/%Y", now('UTC'))); ?>" class="btn btn-space btn-secondary">
            <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR ACCUEIL&nbsp;
        </a>
    </p>
</div>
<div class="row">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card card-border-color card-border-color-primary adventeescale-libre"
             data-cle_compagnie="<?= $this->session->company->ekey; ?>"
             data-code-gaexp="<?= htmlspecialchars($code_gaexp, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="card-header">
                <strong>Vente escale</strong>
                <span class="text-muted"> — <?= htmlspecialchars($bus_stop->garenom . ' / ' . $bus_stop->nomsousgare, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <div class="card-body">
                <?= form_open('', array('class' => 'form', 'id' => 'escalLibreForm')); ?>
                    <input type="hidden" id="pascompagnieescal" name="clientcompescal" value="">
                    <input type="hidden" id="rclientcpescal" name="cprclientescal" value="">
                    <input type="hidden" id="prnclientcpescal" name="cpprclientescal" value="">
                    <input type="hidden" id="prix_axeescal" name="prixescal" value="">
                    <input type="hidden" name="gareconnectescal" value="<?= htmlspecialchars($bus_stop->idengare, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="sousgareconnectescal" value="<?= htmlspecialchars($bus_stop->idsousgare, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="userconnectedescal" value="<?= htmlspecialchars($conex->roleattribut, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="compconnectedescal" value="<?= htmlspecialchars($conex->cpuser_id, ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-group">
                        <label for="escale_depart">Escale de départ</label>
                        <select class="form-control form-control-sm" name="escale_depart" id="escale_depart" required>
                            <option value="">Choisir l'escale…</option>
                            <?php foreach ($escales_depart as $pt): ?>
                                <option value="<?= htmlspecialchars($pt->value, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($pt->label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($escales_depart)): ?>
                            <small class="text-danger">Aucune escale tarifée sur les itinéraires de cette gare. Configurez-les dans LIGNES → Escales tarifées.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="id_escale_dest">Destination</label>
                        <select class="form-control form-control-sm" name="destination_vente" id="id_escale_dest" required>
                            <option value="">Choisir la destination…</option>
                        </select>
                        <small class="text-muted" id="prix_escale_hint"></small>
                    </div>

                    <div class="form-group">
                        <label for="rnclient_contactescal">Téléphone</label>
                        <input class="form-control form-control-sm" type="text" name="rclient_contactescal"
                               id="rnclient_contactescal" autocomplete="off" placeholder="Numéro de téléphone" required>
                    </div>

                    <div class="form-group">
                        <label for="rclientescal">Nom</label>
                        <input class="form-control form-control-sm" type="text" name="rclientescal"
                               id="rclientescal" autocomplete="off" placeholder="Nom" required>
                    </div>

                    <div class="form-group">
                        <label for="prnclientescal">Prénom</label>
                        <input class="form-control form-control-sm" type="text" name="prclientescal"
                               id="prnclientescal" autocomplete="off" placeholder="Prénom" required>
                    </div>

                    <div class="modal-footer px-0">
                        <button class="btn btn-secondary" type="reset" id="idresetescal_libre">ANNULER</button>
                        <input class="btn btn-success" type="submit" name="epsonescal" value="IMPRIMER" id="bottonescal_libre">
                    </div>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
