<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$filterStart = isset($filter_date_start) ? $filter_date_start : mdate('%Y-%m-%d', now('UTC'));
$filterEnd = isset($filter_date_end) ? $filter_date_end : mdate('%Y-%m-%d', now('UTC'));
?>
<button class="btn btn-space btn-secondary md-trigger" data-modal="<?= $validation_filter_modal; ?>">
    <i class="fas fa-filter text-warning"></i>&nbsp;TRI POUR VALIDATION&nbsp;
</button>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="<?= $validation_filter_modal; ?>" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">TRIER LES LIGNES À VALIDER</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open($validation_filter_action, array('class' => 'modal-body form')); ?>
        <input type="hidden" name="idusecon" value="<?= (int) $cashbox_target_roleattribut; ?>">
        <div class="form-group row">
            <div class="form-group col-sm-4">
                <label>COMPAGNIE (FACULTATIF)</label>
                <select class="form-control form-control-sm" name="_compag">
                    <option value="">TOUTES LES COMPAGNIES</option>
                    <?php foreach ($compagnies as $compagnie): ?>
                        <option value="<?= html_escape($compagnie->cle_compagnie); ?>">
                            <?= html_escape($compagnie->nom_compagnie); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-sm-4">
                <label>DU</label>
                <input class="form-control form-control-sm" type="date" name="datedebut"
                       value="<?= html_escape($filterStart); ?>" required>
            </div>
            <div class="form-group col-sm-4">
                <label>AU</label>
                <input class="form-control form-control-sm" type="date" name="datefin"
                       value="<?= html_escape($filterEnd); ?>" required>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
            </button>
            <button class="btn btn-success" type="submit">
                <i class="icon icon-left mdi mdi-magnify"></i>&nbsp;AFFICHER LES LIGNES&nbsp;
            </button>
        </div>
        <?= form_close(); ?>
    </div>
</div>
