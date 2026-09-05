<?php defined('BASEPATH') OR exit('No direct script access allowed');
$role_unifie = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
$allow_tampon_unifie = in_array($role_unifie, array('1', '2', '5', '15'), true);
$allow_prix_diff_unifie = $allow_tampon_unifie; // admin + chef guichet : prix peut différer
?>
<style>
#repro-unifie-0.modal-container { max-width: 920px; width: 96%; }
#repro-unifie-0 .reprog-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 16px;
    margin: 8px 0 14px;
    padding: 10px 12px;
    background: #f7f7f7;
    border-radius: 4px;
    font-size: 13px;
}
#repro-unifie-0 .reprog-info-grid p { margin: 0; }
#repro-unifie-0 .reprog-section {
    margin: 0 0 14px;
    padding: 12px;
    border: 1px solid #dde2e6;
    border-radius: 4px;
    background: #fafbfc;
}
#repro-unifie-0 .reprog-section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #5a6a7a;
    margin: 0 0 10px;
}
#repro-unifie-0 .reprog-seg {
    margin: 0 0 12px;
    padding: 10px;
    border: 1px solid #e3e7eb;
    border-radius: 4px;
    background: #fff;
}
#repro-unifie-0 .reprog-seg h6 {
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 8px;
    color: #2c3e50;
}
@media (max-width: 576px) {
    #repro-unifie-0 .reprog-info-grid { grid-template-columns: 1fr; }
}
</style>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="repro-unifie-0" style="perspective: none;"
     data-allow-tampon="<?= $allow_tampon_unifie ? '1' : '0'; ?>"
     data-allow-prix-diff="<?= $allow_prix_diff_unifie ? '1' : '0'; ?>">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="rTitleUnifie">REPROGRAMMATION</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open('', array('class' => 'modal-body form', 'id' => 'rFormUnifie')); ?>
        <input type="hidden" id="passerpunifie" name="passeridtransit">
        <input type="hidden" id="codeticketsunifie" name="codeticketsclienttransit">
        <input type="hidden" id="lgcodeticketsunifie" name="lgecodeticketstransit">
        <input type="hidden" id="passagersiegunifie" name="siegpastransit">
        <input type="hidden" id="pasnompunifie" name="passnomtransit">
        <input type="hidden" id="pasprenompunifie" name="passprenomtransit">
        <input type="hidden" id="pascontactpunifie" name="passcontacttransit">
        <input type="hidden" id="passaxepunifie" name="passaxetransit">
        <input type="hidden" id="pascnibpunifie" name="passcnibtransit">
        <input type="hidden" id="pasdatepunifie" name="passdatetransit">
        <input type="hidden" id="nsiegepunifie" name="nsiegetransit">
        <input type="hidden" id="idsiegepunifie" name="idsiegetransit">
        <input type="hidden" id="newdunifie" name="newdparttransit">
        <input type="hidden" id="depoldunifie" name="adepcltransit">
        <input type="hidden" id="client_idpunifie" name="client_idtransit">
        <input type="hidden" id="garedpunifie" name="garedpatransit">
        <input type="hidden" id="gareidpunifie">
        <input type="hidden" id="id_compaga_unifie" name="trid_compaga">
        <input type="hidden" id="replignunifie" name="repligntransit">
        <input type="hidden" id="idreplignunifie" name="idrpligntransit">
        <input type="hidden" id="repherunifie">
        <input type="hidden" id="datereprogrammeunifie">
        <input type="hidden" id="directpunifie" name="directpatransit">
        <input type="hidden" id="delivrelieunifie" name="dlieutransit">
        <input type="hidden" id="placevenduunifie" name="placevdtransit">
        <input type="hidden" id="dplacevenduunifie" name="dplacevdtransit">
        <input type="hidden" id="codeidunifie" name="rpcodetransit">
        <input type="hidden" id="coaxeidunifie" name="rpaxecodetransit">
        <input type="hidden" id="idclpasseridunifie" name="clpasseridtransit">
        <input type="hidden" id="depgidunifie" name="departgidtransit">
        <input type="hidden" id="catreprogrammeunifie" name="catreprogramtransit">
        <input type="hidden" id="programrepunifie" name="repmcodtransit">
        <input type="hidden" id="dateprrepunifie">
        <input type="hidden" id="codenonpunifie" name="codenonpassagertransit">
        <input type="hidden" id="statconfunifie" name="statconfirmtransit">
        <input type="hidden" id="statrepunifie" name="statreprotransit">
        <input type="hidden" id="gareidentifunifie" name="gareidentiftrans">
        <input type="hidden" id="departclientidgareunifie" name="departclientidgaretr">
        <input type="hidden" id="siegselectrepunifie">
        <input type="hidden" id="idtamporepunifie">
        <input type="hidden" id="dateventerepunifie">
        <input type="hidden" id="prixventeunifie" name="prixventeunifie" value="">
        <input type="hidden" id="prixventeunifie_ref" name="prixventeunifie_ref" value="">
        <input type="hidden" id="codeclient_ticket_unifie" name="codeclienttransit" value="">
        <input type="hidden" id="gaexp_unifie" value="">
        <input type="hidden" id="gadest_unifie" value="">
        <input type="hidden" id="axe_unifie" value="">
        <input type="hidden" name="heuredeparttransit" id="heuredepart_post_unifie" value="">
        <input type="hidden" name="compgcftranst" id="compgcfunifie" value="">
        <input type="hidden" name="numsiegetransit" id="numsiege_post_unifie" value="">
        <input type="hidden" name="reprog_mode" id="reprog_mode_unifie" value="direct">
        <input type="hidden" name="reprog_nbr_seg" id="reprog_nbr_seg_unifie" value="0">
        <input type="hidden" name="reprog_is_transit_ticket" id="reprog_is_transit_ticket" value="0">
        <input type="hidden" id="passerpunifie2" name="passeridtransit2" value="">
        <input type="hidden" id="codeticketsunifie2" name="codeticketsclienttransit2" value="">
        <input type="hidden" id="codeclient_ticket_unifie2" name="codeclienttransit2" value="">
        <input type="hidden" id="prixventeunifie2" name="prixventeunifie2" value="">
        <?php for ($si = 0; $si < 4; $si++): ?>
        <input type="hidden" name="reprog_seg_prog_<?= $si; ?>" id="reprog_seg_prog_<?= $si; ?>" value="">
        <input type="hidden" name="reprog_seg_siege_<?= $si; ?>" id="reprog_seg_siege_<?= $si; ?>" value="">
        <input type="hidden" name="reprog_seg_compaga_<?= $si; ?>" id="reprog_seg_compaga_<?= $si; ?>" value="">
        <input type="hidden" name="reprog_seg_cat_<?= $si; ?>" id="reprog_seg_cat_<?= $si; ?>" value="">
        <input type="hidden" name="reprog_seg_prix_<?= $si; ?>" id="reprog_seg_prix_<?= $si; ?>" value="">
        <?php endfor; ?>
        <input type="hidden" value="<?= mdate('%Y-%m-%d', now()); ?>" id="actueldaterepunifie" name="dateactuelreptransit">
        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?= $bus_stop->idengare; ?>">
        <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?= $conex->roleattribut; ?>">
        <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?= $bus_stop->idsousgare; ?>">
        <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?= $conex->cpuser_id; ?>">

        <div class="reprog-section">
            <div class="reprog-section-title">Ticket</div>
            <div class="form-row align-items-end">
                <div class="form-group col-md-6 mb-2">
                    <label class="small mb-0">Code</label>
                    <input class="form-control form-control-sm" type="text"
                           id="code_lookup_unifie" autocomplete="off" required=""
                           placeholder="Code ticket ou passager">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <?php if ($allow_tampon_unifie): ?>
                    <label class="mb-1 d-block">
                        <input type="checkbox" id="mode_tampon_unifie" value="1">
                        Autre code (passager / tampon)
                    </label>
                    <?php endif; ?>
                    <label class="mb-1 d-block">
                        <input type="checkbox" id="mode_transit_ticket_unifie" value="1">
                        Ticket transit (plusieurs codes)
                    </label>
                    <span class="btn btn-success btn-sm btn-block" type="button" id="reprogrammer_infos_unifie">
                        Afficher les informations
                    </span>
                </div>
            </div>
            <div class="form-row align-items-end" id="reprog_code2_wrap" style="display:none">
                <div class="form-group col-md-6 mb-2">
                    <label class="small mb-0">2<sup>e</sup> code (jambe transit)</label>
                    <input class="form-control form-control-sm" type="text"
                           id="code_lookup2_unifie" autocomplete="off"
                           placeholder="Code de la 2ᵉ jambe">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <span class="btn btn-outline-success btn-sm btn-block" type="button" id="reprogrammer_infos2_unifie">
                        Vérifier le 2<sup>e</sup> code
                    </span>
                </div>
            </div>
            <div class="reprog-info-grid" id="reprog_infos_wrap" style="display:none">
                <p id="nomclpunifie"></p>
                <p id="prenomclpunifie"></p>
                <p id="contactclpunifie"></p>
                <p id="refclpunifie"></p>
                <p id="directionclpunifie"></p>
                <p id="compagnieclpunifie"></p>
                <p id="codeclpunifie"></p>
                <p id="heureclpunifie"></p>
                <p id="prixclpunifie"></p>
                <p id="code2clpunifie" style="display:none"></p>
            </div>
            <div class="text-danger small" id="smspunifie" style="display:none"><p id="erreurSmspunifie" class="mb-0"></p></div>
            <div class="text-danger small" id="billetrepunifie" style="display:none"><p id="billetSmsrepunifie" class="mb-0"></p></div>
        </div>

        <div class="reprog-section" id="reprog_choix_wrap" style="display:none">
            <div class="reprog-section-title">Nouveau départ</div>
            <div class="form-row">
                <div class="form-group col-md-4 mb-2">
                    <label class="small mb-0">Date de report</label>
                    <input class="form-control form-control-sm" type="date" id="datereprog_unifie">
                </div>
                <div class="form-group col-md-4 mb-2" id="reprog_ancre_heure_wrap" style="display:none">
                    <label class="small mb-0">Heure d’ancrage</label>
                    <select class="form-control form-control-sm" id="heuredepartpunifie">
                        <option value="">Choisissez l'heure</option>
                    </select>
                </div>
                <div class="form-group col-md-4 mb-2" id="reprog_cie_ancre_wrap" style="display:none">
                    <label class="small mb-0">Compagnie</label>
                    <select class="form-control form-control-sm" id="compagniepunifie">
                        <option value="">Choisissez la compagnie</option>
                    </select>
                </div>
            </div>

            <div id="reprog_direct_wrap" style="display:none">
                <div class="reprog-section-title">Départ direct</div>
                <p class="small mb-2 text-muted" id="reprog_direct_info"></p>
                <div class="form-row">
                    <div class="form-group col-md-6 mb-2">
                        <label class="small mb-0">Numéro de siège</label>
                        <select class="form-control form-control-sm" id="numsiegepunifie">
                            <option value="">Choisissez le siège</option>
                        </select>
                    </div>
                </div>
                <div class="text-danger small" id="erreursiegunifie" style="display:none"><p id="erreurSiegeunifie" class="mb-0"></p></div>
            </div>

            <div id="corr_unifie_wrap" style="display:none">
                <div class="reprog-section-title">Itinéraires possibles</div>
                <p class="small text-muted mb-2" id="corr_unifie_hint">
                    Selon l’axe du ticket et la date de report : un itinéraire direct = 1 segment ;
                    une correspondance = plusieurs segments (compagnie, date, heure, siège).
                </p>
                <p class="text-warning small mb-2" id="corr_unifie_msg"></p>
                <div class="form-group mb-3">
                    <label class="small mb-0">Choisir l’itinéraire</label>
                    <select class="form-control form-control-sm" id="corr_unifie_select">
                        <option value="">Choisissez un itinéraire</option>
                    </select>
                </div>
                <div id="corr_segments_unifie"></div>
                <p class="small mb-0 mt-2" id="corr_prix_sum_unifie" style="display:none"></p>
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button class="btn btn-secondary modal-close" type="reset" id="reseunifie">
                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
            </button>
            <input class="btn btn-success md-trigger" type="submit" name="ordinairetransit" value="ORDINAIRE" disabled="">
            <input class="btn btn-success md-trigger" type="submit" name="epsontransit" value="EPSON">
        </div>
        <?= form_close(); ?>
    </div>
</div>
