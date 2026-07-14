<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="row">
    <div class="col-12">
        <p class="mt-0 mb-2 ml-3">
            <a href="<?= site_url('home/main'); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR AUX GARES&nbsp;
            </a>
        </p>
    </div>
 <? if (!empty($sousgares)) : ?>
        
        <div class="col-lg-12">
            <div class="card">
                <? if (isset($agent_userole) && in_array($agent_userole, array('1', '2'), TRUE)): ?>
                    <div class="card-header">
                    
                        <div class="tools">
                            <button class="btn btn-space btn-info md-trigger" data-modal="new-sousgare">
                                <span class="icon mdi mdi-plus-1 text-white"></span>
                            </button>
                        </div>
                    
                    </div>
                <?endif;?>
                <div class="card-body"></div>

	                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
				     id="new-sousgare" style="perspective: 1300px;">
				    <div class="modal-content">
				        <div class="modal-header modal-header-colored">
				            <h3 class="modal-title">UNE SOUS GARE DE DEPART</h3>
				            <button class="close modal-close" type="button"
				                    data-dismiss="modal" aria-hidden="true">
				                <span class="mdi mdi-close text-white"></span></button>
				        </div>
				        <?= form_open('Programmes/adsousgare/' . (isset($company_ekey) ? $company_ekey : $this->session->company->ekey).'/'.$bus_stop->idengare, array('class' => 'modal-body form')); ?>
				        <div class="row">
				            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
				            
				            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
				            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
				            <div class="form-group col-sm-4">
				                <label>NOM SOUS GARE</label>
				                <input class="form-control form-control-sm"
				                    type="text"
				                    name="_nomsousgare"
				                    placeholder="nom sous gare" autocomplete="off" required>
				            </div>

				            <!-- CONTACT -->
				            <div class="form-group col-sm-4">
				                <label>CONTACT</label>
				                <input class="form-control form-control-sm" name="contact" type="text" autocomplete="off">
				            </div>  
				              
				        </div>
				        <div class="modal-footer">
				            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER
				            </button>
				            <button class="btn btn-success md_trigger" type="submit" data-dismiss="modal">OK
				            </button>
				        </div>
				        
				        <?= form_close(); ?>

				    </div>

				</div>

            </div>
        </div>
        <? foreach ($sousgares as $item): ?>

            <div class="col-lg-3">

                <div class="card card-border card-full">
                	<div class="card-header card-header-divider"><?= $item->nomsousgare; ?>
	                    <div class="card-header card-header-divider">
	                            
	                    </div>

	                    <div class="card-body">
	                        <p>Code:<?= $item->idengare; ?></p>
	                        <p>Ville:<?= $item->nom_ville; ?></p>
                        <p>Contact:<?= $item->contactsousgare;?></p>
	                        <a href="<?= isset($item->voir_url) ? $item->voir_url : site_url('gares/'.(isset($company_ekey) ? $company_ekey : $this->session->company->ekey).'/gTc/'. $item->idengare.'/compte/'. $conex->roleattribut.'/'. $item->idsousgare.'/'. (isset($date_jour) ? $date_jour : mdate("%d/%m/%Y", now('UTC')))); ?>"
	                           class="btn btn-block btn-rounded text-dark bg-white">
	                            <span class="fas fa-eye"></span>
	                            VOIR
	                        </a>
	                       
	                    </div>
	                </div>
                </div>

            </div>
        
        <? endforeach; ?>
  <? else: ?>

    <div class="col-lg-4 offset-lg-4">

        <div class="card">

            <div class="card-header card-header-divider"><?= isset($company_nom) ? $company_nom : $this->session->company->nom_entreprise; ?></div>

            <div class="card-body text-center text-capitalize">
                <h2>AUCUNE GARE TROUVEE</h2>
                <p>Vous pouvez en ajouter par ici
                    <button class="btn btn-rounded btn-space btn-success md-trigger" data-modal="new-sousgare">
                        <i class="icon icon-left mdi mdi-bus"></i>
                        AJOUTER UNE GARE
                    </button>
                </p>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
			     id="new-sousgare" style="perspective: 1300px;">
			    <div class="modal-content">
			        <div class="modal-header modal-header-colored">
			            <h3 class="modal-title">UNE SOUS GARE</h3>
			            <button class="close modal-close" type="button"
			                    data-dismiss="modal" aria-hidden="true">
			                <span class="mdi mdi-close text-white"></span></button>
			        </div>
			        <?= form_open('Programmes/adsousgare/' . (isset($company_ekey) ? $company_ekey : $this->session->company->ekey).'/'.$bus_stop->idengare, array('class' => 'modal-body form')); ?>
			        <div class="row">
			            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
			            
			            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
			            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
			            <div class="form-group col-sm-4">
			                <label>NOM SOUS GARE</label>
			                <input class="form-control form-control-sm"
			                    type="text"
			                    name="_nomsousgare"
			                    placeholder="nom sous gare" autocomplete="off" required>
			            </div>

			            <!-- CONTACT -->
			            <div class="form-group col-sm-4">
			                <label>CONTACT</label>
			                <input class="form-control form-control-sm" name="contact" type="text" autocomplete="off">
			            </div>  
			              
			        </div>
			        <div class="modal-footer">
			            <button class="btn btn-secondary modal-close" type="button" data-dismiss="modal">ANNULER
			            </button>
			            <button class="btn btn-success md_trigger" type="submit" data-dismiss="modal">OK
			            </button>
			        </div>
			        
			        <?= form_close(); ?>

			    </div>

			</div>

            </div>

        </div>

    </div>
    
<? endif; ?>
</div>