<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
<div class="tools">
        <a href="<?= site_url('utilisateurs/' . $this->session->company->ekey); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR 
        </a>
        
    </div>
    <div class="col-lg-12">

            <div class="card card-table">

                <div class="card-header"></div>

                <div class="card-body">

                    <table class="table table-striped table-hover" id="table1">

                        <thead>

                        <tr>
                            <th>Nom utilisateur</th>
                            <th>Profil</th>
                            <th>PAGE</th>
                            <th>Actions</th>
                        </tr>

                        </thead>

                        <tbody>
                        <? foreach ($profiluserspage as $usp): ?>
                            <tr>
                                <td>
                                    <span><?= $usp->username; ?></span>
                                </td>

                                <td>
                                    <span><?= $usp->type_rols; ?></span>
                                </td>
                                

                                <td>
                                    <span><?= $usp->typedossier; ?></span>

                                </td>
                                <td>
                                    
                                    <a href="<?= site_url('Utilisateurs/activepagegd/'. $this->session->company->ekey.'/'.$usp->idapdossrole.'/'.$usp->cpuser_id.'/'.$usp->activedosrole);?> "class="btn btn-space btn-secondary">
                                        <?= ($usp->activedosrole === '1') ? '<span class="icon mdi text-danger">désactiver</span>' : '<span
                                        class="icon mdi text-success">activer</span>' ?>
                                    </a>&nbsp;
                                    &nbsp;
                                    
                                    <a href="<?= "#?{$usp->idapdossrole}&idrole={$usp->idroleuse}&";?>"
                                        class="md-trigger" data-modal="pg-<?= $usp->idapdossrole; ?>" title="Modifier">
                                        <span class="icon mdi mdi-edit text-warning"></span>
                                    </a>
                                    <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                                    id="pg-<?= $usp->idapdossrole; ?>" style="">
                                    <div class="modal-content">
                                        <div class="modal-header modal-header-colored">
                                            <h3 class="modal-title">MODIFICATION</h3>
                                            <button class="close modal-close" type="button"
                                                data-dismiss="modal" aria-hidden="true"><span
                                                class="mdi mdi-close text-white"></span>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                        <?= form_open('Utilisateurs/updatepage_/' . $this->session->company->ekey . '/'.$usp->idapdossrole.'/' .$usp->cpuser_id.'/' .$usp->idroleuse,  
                                            array('class' => 'modal-body form')); ?>
                                            <?
                                            
                                                $dossiers = $this->db->query(
                                                "SELECT * FROM appdossier a
                                                    WHERE a.iddoss != '$usp->iddossrole '")->result(); 
                                            ?>
                                            <div class="form-group row">
                                                <label>Dossier:</label>
                                                <div class="col-12 col-sm-8 col-lg-6">
                                                    <select class="form-control form-control-sm" name="roledosattr">
                                                        <option value="<?= $usp->iddoss ; ?>"><?= $usp->typedossier ; ?></option>
                                                        <? foreach ($dossiers as $do): ?>
                                                        <option value="<?= $do->iddoss ; ?>"><?= $do->typedossier; ?></option>
                                                        <? endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary modal-close" type="reset" data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                                </button>
                                                <button class="btn btn-success md-trigger" type="submit"
                                                        data-dismiss="modal">
                                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;OK&nbsp;
                                                </button>
                                            </div>
                                            <?= form_close(); ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <? endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</div>
   
<!--End of file: afcomptpage.php-->
<!--File location: application/views/beagle/pages/_users/afcomptpage.php-->