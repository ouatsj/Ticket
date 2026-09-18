<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="adbagescale" data-cle_compagnie="<?= $this->session->company->ekey; ?>">
    <?= form_open('', array('class' => 'modal-body form', 'id' => 'escalFormbag')); ?>
        <input type="hidden" id="pascompagnieescalbag" name="clientcompescalbag">
        <input type="hidden" id="pascontactbagsansescbg" name="passcontactbagsansescbg">
        <input type="hidden" id="rclientcpescalbag" name="cprclientescalbag">
        <input type="hidden" id="nclientcpescalbag" name="nclientescalbag">
        <input type="hidden" id="prnclientcpescalbag" name="cpprclientescalbag">
        <input type="hidden" id="quartpasseesc" name="quartpassesesc">
        <input type="hidden" id="idcompagaescbag" name="idcompagadescbag">
        <input type="hidden" id="codtickbagsansesc" name="codetickbagsansesc">
        <input type="hidden" id="typesescalbag" name="typeescalbag">
        <input type="hidden" id="id_lgeheurescalbag" name="idlgeheurescalbag">
        <input type="hidden" id="lignescalbag" name="lignedepaescalbag">
        <input type="hidden" value="<?= mdate('%Y-%m-%d', now()); ?>" id="actuescalbag" name="dactuelescalbag">

        <input type="hidden" name="gareconnectescalbag" id="codebaggidesc" value="<?= htmlspecialchars((string) $bus_stop->idengare, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="sousgareconnectescalbag" id="codebagsousgidesc" value="<?= htmlspecialchars((string) $bus_stop->idsousgare, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="userconnectedescalbag" value="<?= htmlspecialchars((string) $conex->roleattribut, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="compconnectedescalbag" value="<?= htmlspecialchars((string) $conex->cpuser_id, ENT_QUOTES, 'UTF-8'); ?>">
        <?php if (!empty($escale_id_lignes)): ?>
            <input type="hidden" id="role17_bag_ligne" value="<?= htmlspecialchars((string) $escale_id_lignes, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>

        <p class="small text-muted mb-2">
            Saisissez le code du ticket vendu sur <strong>votre escale</strong>, puis vérifiez avant de facturer.
        </p>
        <div class="row">
            <div class="form-group col-sm-6">
                <label>Code ticket escale</label>
                <input class="form-control form-control-sm" type="text"
                    name="codeticketbagsesc" id="codeticketbagesc"
                    autocomplete="off" required
                    placeholder="Code du ticket">
            </div>
            <div class="form-group col-sm-6">
                <label>&nbsp;</label>
                <button class="btn btn-success btn-block" type="button" id="infocodeticketesc">
                    Vérifier le code
                </button>
            </div>
        </div>

        <input type="hidden" id="infobagasansesc" name="infobagasansesc" value="">
        <input type="hidden" id="nomligneescalbag" name="nomligneescalbag" value="">

        <div id="r17_bag_ticket_card" class="r17-bag-ticket-card" hidden>
            <div class="r17-bag-ticket-card__head">
                <span class="r17-bag-ticket-card__badge">Ticket vérifié</span>
                <span id="r17_bag_card_code" class="r17-bag-ticket-card__code"></span>
            </div>
            <div class="r17-bag-ticket-card__body">
                <div class="r17-bag-ticket-card__row">
                    <span class="lbl">Client</span>
                    <span id="r17_bag_card_client" class="val"></span>
                </div>
                <div class="r17-bag-ticket-card__row">
                    <span class="lbl">Trajet</span>
                    <span id="r17_bag_card_trajet" class="val is-ligne"></span>
                </div>
                <div class="r17-bag-ticket-card__row">
                    <span class="lbl">Heure</span>
                    <span id="r17_bag_card_heure" class="val"></span>
                </div>
            </div>
        </div>
        <small id="r17_bag_verif_err" class="text-danger d-block mb-2" style="display:none;"></small>

        <div class="form-group">
            <label>Type bagages</label>
            <div class="d-flex flex-wrap" style="gap:0.5rem 0.85rem;">
                <?php foreach (array('Colis', 'Carton', 'Sac', 'Moto', 'Velo', 'Demenagement') as $tb): ?>
                    <label class="mb-0">
                        <input type="checkbox" name="types_bagsansesc[]" value="<?= $tb; ?>" onclick="updateContenu()">
                        <?= $tb === 'Demenagement' ? 'Déménagement' : $tb; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-sm-6">
                <label>Contenu</label>
                <textarea class="form-control form-control-sm" name="naturebagagesansesc"
                    id="naturebagagesansesc" cols="30" rows="3" required></textarea>
            </div>
            <div class="form-group col-sm-3">
                <label>Nombre</label>
                <input class="form-control form-control-sm" name="nombrebagsansesc" type="number"
                    min="1" required placeholder="">
            </div>
            <div class="form-group col-sm-3">
                <label>Valeur</label>
                <input class="form-control form-control-sm" name="valeurbagagesansesc" type="number"
                    min="0" placeholder="Valeur">
            </div>
            <div class="form-group col-sm-6">
                <label>Frais bagage</label>
                <input class="form-control form-control-sm" name="fraisbagsansesc" type="number"
                    id="fraisbagsansesc" min="500" step="1" required
                    placeholder="Min. 500 F CFA">
                <small class="text-muted">Minimum 500 F CFA</small>
            </div>
        </div>
        <div class="modal-footer r17-wiz-nav" style="padding:0.5rem 0 0;">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">FERMER</button>
            <input class="btn btn-success" type="submit" name="epsonbagsansesc" value="FACTURER" id="bottonbagesc">
        </div>
    <?= form_close(); ?>
</div>
