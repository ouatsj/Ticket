<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$code_gaexp = !empty($code_gaexp_vente)
    ? $code_gaexp_vente
    : (!empty($bus_stop->code_gaexp) ? $bus_stop->code_gaexp : (isset($bus_stop->gareprinceid) ? $bus_stop->gareprinceid : ''));
$escales_depart = !empty($escales_depart) ? $escales_depart : array();
$escale_fixe = !empty($escale_depart_fixe) ? (string) $escale_depart_fixe : '';
$escale_fixe_label = !empty($escale_depart_label) ? (string) $escale_depart_label : $escale_fixe;
?>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="ticketescal-0" style="perspective: none">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">VENTE MOBILE ESCAL</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body adventeescale-libre"
             data-cle-compagnie="<?= $this->session->company->ekey; ?>"
             data-code-gaexp="<?= htmlspecialchars($code_gaexp, ENT_QUOTES, 'UTF-8'); ?>"
             <?php if ($escale_fixe !== ''): ?>
             data-depart-fixe="<?= htmlspecialchars($escale_fixe, ENT_QUOTES, 'UTF-8'); ?>"
             <?php endif; ?>>
            <?= form_open('', array('class' => 'form', 'id' => 'escalLibreForm', 'autocomplete' => 'off')); ?>
                <input type="hidden" id="pascompagnieescal" name="clientcompescal" value="">
                <input type="hidden" id="rclientcpescal" name="cprclientescal" value="">
                <input type="hidden" id="prnclientcpescal" name="cpprclientescal" value="">
                <input type="hidden" id="prix_axeescal" name="prixescal" value="">
                <input type="hidden" name="gareconnectescal" value="<?= htmlspecialchars($bus_stop->idengare, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="sousgareconnectescal" value="<?= htmlspecialchars($bus_stop->idsousgare, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="userconnectedescal" value="<?= htmlspecialchars($conex->roleattribut, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="compconnectedescal" value="<?= htmlspecialchars($conex->cpuser_id, ENT_QUOTES, 'UTF-8'); ?>">

                <?php if ($escale_fixe !== ''): ?>
                    <input type="hidden" name="escale_depart" id="escale_depart" value="<?= htmlspecialchars($escale_fixe, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="form-group">
                        <label>Escale de départ</label>
                        <p class="form-control-plaintext font-weight-bold mb-0">
                            <?= htmlspecialchars($escale_fixe_label, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="escale_depart">Escale de départ</label>
                        <select class="form-control" name="escale_depart" id="escale_depart" required>
                            <option value="">Choisir l'escale…</option>
                            <?php foreach ($escales_depart as $pt): ?>
                                <option value="<?= htmlspecialchars($pt->value, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($pt->label, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="id_escale_dest">Destination</label>
                    <select class="form-control" name="destination_vente" id="id_escale_dest" required>
                        <option value="">Choisir la destination…</option>
                    </select>
                    <small id="prix_escale_hint"></small>
                </div>

                <div class="form-group">
                    <label for="rnclient_contactescal">Téléphone</label>
                    <input class="form-control" type="tel" name="rclient_contactescal"
                           id="rnclient_contactescal" autocomplete="tel"
                           inputmode="tel" placeholder="Numéro de téléphone" required>
                </div>

                <div class="form-group">
                    <label for="rclientescal">Nom</label>
                    <input class="form-control" type="text" name="rclientescal"
                           id="rclientescal" autocomplete="family-name"
                           autocapitalize="characters" placeholder="Nom" required>
                </div>

                <div class="form-group">
                    <label for="prnclientescal">Prénom</label>
                    <input class="form-control" type="text" name="prclientescal"
                           id="prnclientescal" autocomplete="given-name"
                           autocapitalize="words" placeholder="Prénom" required>
                </div>

                <div class="modal-footer px-0">
                    <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER</button>
                    <input class="btn btn-success" type="submit" name="epsonescal" value="IMPRIMER" id="bottonescal_libre">
                </div>
            <?= form_close(); ?>
        </div>
    </div>
</div>
