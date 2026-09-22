<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Modales de choix : fusion recettes / versements / modifications.
 * Chaque option ouvre le formulaire existant ; résultats in-app + export.
 */
$modif_sans_ticket = !empty($modif_sans_ticket);
$recette_sans_operateur = !empty($recette_sans_operateur);
?>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-choix-recette-0" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">RECETTE TICKET — TYPE DE TRI</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body form">
            <div class="form-group">
                <label>Choisir le rapport (même résultat qu’avant)</label>
                <select class="form-control form-control-sm" id="choix-recette-type">
                    <option value="trig-recette-globale">Recette globale</option>
                    <?php if (!$recette_sans_operateur): ?>
                    <option value="trig-recette-operateur">Par opérateur (ventes tickets)</option>
                    <?php endif; ?>
                    <option value="trig-recette-sousgare">Par gare (sous-gare)</option>
                </select>
                <p class="text-muted mt-2 mb-0" style="font-size:12px;">
                    Les résultats s’affichent dans l’application (export PDF / CSV / Excel ensuite).
                    Recette = billets vendus ; Versement = montants validés en caisse.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success" type="button" id="choix-recette-go"
                        data-choix-go="choix-recette-type" data-choix-modal="form-choix-recette-0">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;CONTINUER&nbsp;
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-choix-versement-0" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">VERSEMENT — ACTIVITÉ</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body form">
            <div class="form-group">
                <label>Choisir l’activité</label>
                <select class="form-control form-control-sm" id="choix-versement-type">
                    <option value="trig-versement-ticket">Ticket guichetier (montants versés)</option>
                    <option value="trig-versement-bagage">Bagages</option>
                    <option value="trig-versement-courrier">Courrier guichetier</option>
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success" type="button" id="choix-versement-go"
                        data-choix-go="choix-versement-type" data-choix-modal="form-choix-versement-0">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;CONTINUER&nbsp;
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="form-choix-modif-versement-0" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">MODIFICATION VERSEMENT — ACTIVITÉ</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <div class="modal-body form">
            <div class="form-group">
                <label>Choisir l’activité</label>
                <select class="form-control form-control-sm" id="choix-modif-versement-type">
                    <?php if (!$modif_sans_ticket): ?>
                    <option value="trig-modif-versement-ticket">Ticket</option>
                    <?php endif; ?>
                    <option value="trig-modif-versement-courrier">Courrier</option>
                    <option value="trig-modif-versement-bagage">Bagage</option>
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                </button>
                <button class="btn btn-success" type="button" id="choix-modif-versement-go"
                        data-choix-go="choix-modif-versement-type" data-choix-modal="form-choix-modif-versement-0">
                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;CONTINUER&nbsp;
                </button>
            </div>
        </div>
    </div>
</div>
