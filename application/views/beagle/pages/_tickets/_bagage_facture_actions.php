<?php defined('BASEPATH') OR exit('No direct script access allowed');
$triQs = '';
if (!empty($tri_bagage['ga']) && !empty($tri_bagage['d1']) && !empty($tri_bagage['d2'])) {
    $triQs = '?origine=tri&ga=' . rawurlencode($tri_bagage['ga'])
        . '&d1=' . rawurlencode($tri_bagage['d1'])
        . '&d2=' . rawurlencode($tri_bagage['d2']);
}
$modalId = 'edite-' . preg_replace('/[^A-Za-z0-9_-]/', '', (string) $item->id_bagage);
?>
<a class="icon" title="SUPPRIMER"
   onclick="return confirm('Supprimer entièrement cette facturation ?');"
   href="<?= site_url('Confirmation/annule/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$item->id_bagage.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$item->annulebag) . $triQs; ?>">
    <i class="fas fa-trash-alt text-danger"></i>
</a>&nbsp;&nbsp;&nbsp;
<a href="#" title="Modifier" class="md-trigger" data-modal="<?= $modalId; ?>">
    <i class="fas fa-edit text-warning"></i>
</a>
<div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
     id="<?= $modalId; ?>" style="perspective: none;">
    <div class="modal-content">
        <div class="modal-header modal-header-colored">
            <h3 class="modal-title">MODIFICATION BAGAGE</h3>
            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true">
                <span class="mdi mdi-close text-white"></span>
            </button>
        </div>
        <?= form_open('Confirmation/updateprix/' . $this->session->company->ekey .'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare.'/'.$item->id_bagage, array('class' => 'modal-body form')); ?>
        <?php if ($triQs !== ''): ?>
            <input type="hidden" name="origine" value="tri">
            <input type="hidden" name="departgarbg" value="<?= html_escape($tri_bagage['ga']); ?>">
            <input type="hidden" name="datedebutbg" value="<?= html_escape($tri_bagage['d1']); ?>">
            <input type="hidden" name="datefinbg" value="<?= html_escape($tri_bagage['d2']); ?>">
        <?php endif; ?>
        <input type="hidden" name="clientbag" value="<?= (int) $item->clientbag; ?>">
        <div class="row">
            <div class="form-group col-sm-4">
                <label>N° reçu</label>
                <input class="form-control form-control-sm" type="text" value="<?= html_escape($num_recu); ?>" readonly>
            </div>
            <div class="form-group col-sm-4">
                <label>Code</label>
                <input class="form-control form-control-sm" type="text" name="codebag" value="<?= html_escape($item->codebag); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Date</label>
                <input class="form-control form-control-sm" type="date" name="datebagage" value="<?= html_escape($item->date_create); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Nom</label>
                <input class="form-control form-control-sm" type="text" name="nom_client" value="<?= html_escape($item->nom_client); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Prénom</label>
                <input class="form-control form-control-sm" type="text" name="prenom_client" value="<?= html_escape($item->prenom_client); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Contact</label>
                <input class="form-control form-control-sm" type="text" name="contact_client" value="<?= html_escape($item->contact_client); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Contact expéditeur</label>
                <input class="form-control form-control-sm" type="text" name="contactexpedi" value="<?= html_escape($item->contactexpedi); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Type</label>
                <input class="form-control form-control-sm" type="text" name="typebagages" value="<?= html_escape($item->typebagages); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Quart</label>
                <input class="form-control form-control-sm" type="text" name="quartarr_bg" value="<?= html_escape($item->quartarr_bg); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Nombre</label>
                <input class="form-control form-control-sm" type="text" name="nombrebagage" inputmode="numeric" value="<?= html_escape($item->nombrebagage); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Valeur</label>
                <input class="form-control form-control-sm" type="text" name="valeurbagage" inputmode="numeric" value="<?= html_escape($item->valeurbagage); ?>">
            </div>
            <div class="form-group col-sm-4">
                <label>Prix</label>
                <input class="form-control form-control-sm" type="text" name="prixbagage" inputmode="numeric" value="<?= html_escape($item->prix_bagage); ?>">
            </div>
            <div class="form-group col-sm-8">
                <label>Contenu</label>
                <textarea class="form-control form-control-sm" name="contenubagage" rows="3"><?= html_escape($item->contenubagage); ?></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">
                <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
            </button>
            <button class="btn btn-success" type="submit">
                <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
            </button>
        </div>
        <?= form_close(); ?>
    </div>
</div>
