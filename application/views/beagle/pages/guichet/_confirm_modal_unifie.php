<?php defined('BASEPATH') OR exit('No direct script access allowed');
$role_confirm = isset($this->session->agent->userole) ? (string) $this->session->agent->userole : '';
// Chef / admin / superviseur / chef allégé : code d’ailleurs autorisé.
$allow_externe = in_array($role_confirm, array('1', '2', '5', '15'), true);
?>
<style>
#confirm-unifie-0.modal-container { max-width: 920px; width: 96%; }
#confirm-unifie-0 .confirm-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 16px;
    margin: 8px 0 14px;
    padding: 10px 12px;
    background: #f7f7f7;
    border-radius: 4px;
    font-size: 13px;
}
#confirm-unifie-0 .confirm-info-grid p { margin: 0; }
#confirm-unifie-0 .confirm-section {
    margin: 0 0 14px;
    padding: 12px;
    border: 1px solid #dde2e6;
    border-radius: 4px;
    background: #fafbfc;
}
#confirm-unifie-0 .confirm-section-title {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #5a6a7a;
    margin: 0 0 10px;
}
#confirm-unifie-0 .confirm-result-box {
    padding: 14px;
    background: #eef8ee;
    border: 1px solid #b7dfb7;
    border-radius: 4px;
    font-size: 14px;
    line-height: 1.5;
}
#confirm-unifie-0 .confirm-seg {
    margin: 0 0 12px;
    padding: 10px;
    border: 1px solid #e3e7eb;
    border-radius: 4px;
    background: #fff;
}
#confirm-unifie-0 .confirm-seg h6 {
    font-size: 13px;
    font-weight: 700;
    margin: 0 0 8px;
    color: #2c3e50;
}
@media (max-width: 576px) {
    #confirm-unifie-0 .confirm-info-grid { grid-template-columns: 1fr; }
}
</style>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="confirm-unifie-0" style="perspective: none;"
     data-allow-externe="<?= $allow_externe ? '1' : '0'; ?>"
     data-role="<?= htmlspecialchars($role_confirm, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title" id="cTitleUnifie">CONFIRMATION</h3>
            <button class="close modal-close" type="button"
                    data-dismiss="modal" aria-hidden="true"><span
                    class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open('', array('class' => 'modal-body form', 'id' => 'cFormUnifie')); ?>
        <input type="hidden" id="confirm_mode_unifie" name="confirm_mode" value="retour">
        <input type="hidden" id="confirm_client_id" name="client_id" value="">
        <input type="hidden" id="confirm_code_ticket" name="code_ticket" value="">
        <input type="hidden" id="confirm_code_non_pass" name="code_non_pass" value="">
        <input type="hidden" id="confirm_nom_ligne" name="nom_ligne" value="">
        <input type="hidden" id="confirm_ident_ligne" name="ident_ligne" value="">
        <input type="hidden" id="confirm_gaexp" name="gaexp" value="">
        <input type="hidden" id="confirm_gadest" name="gadest" value="">
        <input type="hidden" id="confirm_code_pro" name="code_pro" value="">
        <input type="hidden" id="confirm_id_ligneheure" name="id_ligneheure" value="">
        <input type="hidden" id="confirm_typetarif" name="typetarif" value="">
        <input type="hidden" id="confirm_categori" name="categori" value="">
        <input type="hidden" id="confirm_id_compaga" name="id_compaga" value="">
        <input type="hidden" id="confirm_depart_gid" name="departclient_idgare" value="">
        <input type="hidden" id="confirm_intervalle1" name="intervalle1" value="">
        <input type="hidden" id="confirm_intervalle2" name="intervalle2" value="">
        <input type="hidden" id="confirm_path_mode" name="confirm_path_mode" value="direct">
        <input type="hidden" id="confirm_nbr_seg" name="confirm_nbr_seg" value="0">
        <input type="hidden" id="id_escale_vente_confirm" name="id_escale_vente_confirm" value="">
        <input type="hidden" id="code_gadest_vente_confirm" name="code_gadest_vente_confirm" value="">
        <input type="hidden" id="nom_dest_vente_confirm" name="nom_dest_vente_confirm" value="">
        <?php for ($si = 0; $si < 4; $si++): ?>
        <input type="hidden" name="confirm_seg_prog_<?= $si; ?>" id="confirm_seg_prog_<?= $si; ?>" value="">
        <input type="hidden" name="confirm_seg_ligne_<?= $si; ?>" id="confirm_seg_ligne_<?= $si; ?>" value="">
        <input type="hidden" name="confirm_seg_siege_<?= $si; ?>" id="confirm_seg_siege_<?= $si; ?>" value="">
        <input type="hidden" name="confirm_seg_compaga_<?= $si; ?>" id="confirm_seg_compaga_<?= $si; ?>" value="">
        <input type="hidden" name="confirm_seg_cat_<?= $si; ?>" id="confirm_seg_cat_<?= $si; ?>" value="">
        <input type="hidden" name="confirm_seg_date_<?= $si; ?>" id="confirm_seg_date_<?= $si; ?>" value="">
        <input type="hidden" name="confirm_seg_heure_<?= $si; ?>" id="confirm_seg_heure_<?= $si; ?>" value="">
        <?php endfor; ?>
        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?= $bus_stop->idengare; ?>">
        <input class="form-control form-control-sm" type="hidden" name="gareconnect_code" id="confirm_gareconnect_code" value="<?= !empty($bus_stop->code_gaexp) ? $bus_stop->code_gaexp : $bus_stop->gareprinceid; ?>">
        <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?= $conex->roleattribut; ?>">
        <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?= $bus_stop->idsousgare; ?>">
        <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?= $conex->cpuser_id; ?>">

        <div class="confirm-section" id="confirm_lookup_wrap">
            <div class="confirm-section-title">Ticket à confirmer</div>
            <div class="form-row align-items-end">
                <div class="form-group col-md-6 mb-2">
                    <label class="small mb-0">Code</label>
                    <input class="form-control form-control-sm" type="text"
                           id="code_lookup_confirm" autocomplete="off" required
                           placeholder="Code ticket retour">
                </div>
                <div class="form-group col-md-6 mb-2">
                    <?php if ($allow_externe): ?>
                    <label class="mb-1 d-block">
                        <input type="checkbox" id="mode_externe_confirm" value="1">
                        Code provenant d’ailleurs
                    </label>
                    <?php endif; ?>
                    <span class="btn btn-success btn-sm btn-block" type="button" id="confirm_infos_btn">
                        Vérifier le 1<sup>er</sup> code
                    </span>
                </div>
            </div>
            <div id="confirm_extra_codes_wrap" style="display:none">
                <p class="small text-muted mb-2" id="confirm_extra_codes_msg">
                    Ticket transit : saisissez les autres codes retour.
                </p>
                <div id="confirm_extra_codes_box"></div>
            </div>
            <input type="hidden" id="confirm_codes_json" name="confirm_codes" value="">
            <div class="confirm-info-grid" id="confirm_infos_wrap" style="display:none">
                <p id="confirm_nom_cl"></p>
                <p id="confirm_prenom_cl"></p>
                <p id="confirm_contact_cl"></p>
                <p id="confirm_direction_cl"></p>
                <p id="confirm_ligne_cl"></p>
                <p id="confirm_code_cl"></p>
                <p id="confirm_prix_info_cl" class="text-muted"></p>
            </div>
            <div class="text-danger small" id="confirm_sms_wrap" style="display:none">
                <p id="confirm_sms_err" class="mb-0"></p>
            </div>
            <p class="small text-success mb-0" id="confirm_gratis_hint" style="display:none">
                Confirmation gratuite (0 F) — incluse à l’arrêt ; rapports = nombre seulement.
            </p>
        </div>

        <div class="confirm-section" id="confirm_depart_wrap" style="display:none">
            <div class="confirm-section-title">Nouveau départ</div>
            <div class="form-row">
                <div class="form-group col-md-4 mb-2">
                    <label class="small mb-0">Date</label>
                    <input class="form-control form-control-sm" type="date" id="date_confirm_unifie" name="date_confirm">
                </div>
                <div class="form-group col-md-8 mb-2" id="confirm_escale_wrap">
                    <div class="form-check mt-1">
                        <label class="custom-control custom-checkbox custom-control-inline mb-0">
                            <input class="custom-control-input" type="checkbox" id="confirm_escale_check" value="1">
                            <span class="custom-control-label">Escale</span>
                        </label>
                    </div>
                    <div id="confirm_escale_fields" style="display:none; margin-top:6px;">
                        <label class="small mb-0" for="confirm_escale_select">Destination escale</label>
                        <select class="form-control form-control-sm" id="confirm_escale_select">
                            <option value="">Choisissez l&apos;escale</option>
                        </select>
                        <small class="form-text text-muted" id="confirm_escale_help">
                            Escales de la ligne (dernière jambe en correspondance). Confirmation gratuite.
                        </small>
                    </div>
                </div>
            </div>

            <div id="confirm_direct_fields_wrap">
                <div class="form-row">
                    <div class="form-group col-md-4 mb-2">
                        <label class="small mb-0">Heure (départs programmes)</label>
                        <select class="form-control form-control-sm" id="heure_confirm_unifie">
                            <option value="">Choisissez l'heure</option>
                        </select>
                        <small class="form-text text-muted">
                            Programmes de la date : même heure → 1ER, 2ème…
                        </small>
                    </div>
                    <!-- Compagnie retirée : le choix se fait dans Heure (1 programme = 1 option). -->
                    <div class="form-group col-md-4 mb-2" id="confirm_cie_wrap" style="display:none" hidden>
                        <label class="small mb-0">Compagnie</label>
                        <select class="form-control form-control-sm" id="cie_confirm_unifie">
                            <option value="">Choisissez la compagnie</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 mb-2">
                        <label class="small mb-0">Siège</label>
                        <select class="form-control form-control-sm" id="siege_confirm_unifie" name="num_siege">
                            <option value="">Choisissez le siège</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="confirm_transit_wrap" style="display:none">
                <div class="confirm-section-title">Itinéraire (correspondance)</div>
                <p class="small text-muted mb-2" id="confirm_transit_msg">
                    Aucun direct depuis la gare de confirmation : choisissez une correspondance vers la destination.
                </p>
                <div class="form-group mb-2">
                    <label class="small mb-0">Itinéraire</label>
                    <select class="form-control form-control-sm" id="confirm_itineraire_select">
                        <option value="">Choisissez un itinéraire</option>
                    </select>
                </div>
                <div id="confirm_segments_box"></div>
            </div>

            <div id="confirm_transit_hint" class="small text-warning" style="display:none"></div>
            <div class="text-right mt-2">
                <button class="btn btn-success" type="button" id="confirm_ok_btn" disabled>
                    OK — Confirmer
                </button>
            </div>
        </div>

        <div class="confirm-section" id="confirm_externe_wrap" style="display:none">
            <div class="confirm-section-title">Ticket externe (code d’ailleurs)</div>
            <p class="small text-muted mb-2">Client + trajet depuis la gare de confirmation. Confirmation gratuite (0 F).</p>
            <div class="form-row">
                <div class="form-group col-md-4 mb-2">
                    <label class="small mb-0">Téléphone</label>
                    <input class="form-control form-control-sm" type="text" inputmode="numeric"
                           name="ext_tel" id="ext_tel_confirm" autocomplete="off"
                           placeholder="Numéro">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small mb-0">Nom</label>
                    <input class="form-control form-control-sm" type="text"
                           name="ext_nom" id="ext_nom_confirm" autocomplete="off">
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label class="small mb-0">Prénom</label>
                    <input class="form-control form-control-sm" type="text"
                           name="ext_prenom" id="ext_prenom_confirm" autocomplete="off">
                </div>
            </div>
            <p class="small text-info mb-2" id="ext_client_msg"></p>

            <div id="confirm_externe_od_fields">
                <div class="form-row">
                    <div class="form-group col-md-6 mb-2">
                        <label class="small mb-0">Départ (gare de confirmation)</label>
                        <input class="form-control form-control-sm" type="text" id="ext_depart_display" readonly
                               value="<?= htmlspecialchars(
                                   !empty($bus_stop->nom_gaexp)
                                       ? $bus_stop->nom_gaexp . ' (' . (!empty($bus_stop->code_gaexp) ? $bus_stop->code_gaexp : $bus_stop->gareprinceid) . ')'
                                       : (!empty($bus_stop->code_gaexp) ? $bus_stop->code_gaexp : $bus_stop->gareprinceid),
                                   ENT_QUOTES,
                                   'UTF-8'
                               ); ?>">
                    </div>
                    <div class="form-group col-md-6 mb-2">
                        <label class="small mb-0">Arrivée</label>
                        <div class="px-0 pb-1" data-compagnies-arrivee-for="arrsgare_confirm"></div>
                        <select class="form-control form-control-sm" id="arrsgare_confirm" name="ext_gadest">
                            <option value="">Choisissez la gare d'arrivée</option>
                            <?php
                            $this->load->view('beagle/pages/guichet/_options_gare_arrivee', array(
                                'garearrivees' => !empty($garearrivees) ? $garearrivees : array(),
                                'value_format' => 'code',
                            ));
                            ?>
                        </select>
                    </div>
                </div>
                <div class="text-right">
                    <button class="btn btn-outline-success btn-sm" type="button" id="confirm_externe_od_btn">
                        Continuer — programmes
                    </button>
                </div>
                <p class="small text-danger mb-0 mt-1" id="ext_od_err" style="display:none"></p>
            </div>
        </div>

        <div class="confirm-section" id="confirm_result_wrap" style="display:none">
            <div class="confirm-section-title" id="confirm_result_title">À noter sur le ticket</div>
            <div class="confirm-result-box" id="confirm_result_body"></div>
            <div class="text-right mt-3">
                <button class="btn btn-primary mr-2" type="button" id="confirm_result_print_btn" style="display:none">
                    Imprimer le ticket
                </button>
                <button class="btn btn-success" type="button" id="confirm_result_ok_btn">
                    OK — Retour accueil
                </button>
            </div>
        </div>

        <?= form_close(); ?>
    </div>
</div>
