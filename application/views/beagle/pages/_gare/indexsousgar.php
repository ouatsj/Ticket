<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<div class="row">
    <div class="col-12">
        <p class="mt-0 mb-2 ml-3">
            <a href="<?= site_url('home/main'); ?>" class="btn btn-space btn-secondary">
                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR AUX GARES&nbsp;
            </a>
        </p>
    </div>
<?php if (!empty($show_onglets_lieu)): ?>
    <div class="col-12">
        <div class="sg-lieu-tabs" role="tablist">
            <button type="button" class="sg-lieu-tab is-active" data-sg-tab="quartier" role="tab" aria-selected="true">Quartier</button>
            <button type="button" class="sg-lieu-tab" data-sg-tab="escale" role="tab" aria-selected="false">Escale<?php if (!empty($escales_lieu)): ?> (<?= count($escales_lieu); ?>)<?php endif; ?></button>
        </div>
    </div>
<?php endif; ?>
<div id="sg-panel-quartier" style="display:contents">
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
<?php if (!empty($show_onglets_lieu)): ?>
<div id="sg-panel-escale" style="display:none">
    <?php if (!empty($escales_lieu)): ?>
        <?php foreach ($escales_lieu as $esc): ?>
            <?php
            $esc_label = trim((string) $esc->label);
            $esc_ligne = trim((string) $esc->ligne);
            $esc_nb = isset($esc->nb_agents) ? (int) $esc->nb_agents : 0;
            ?>
            <div class="col-lg-3">
                <div class="card card-border card-full">
                    <div class="card-header card-header-divider"><?= htmlspecialchars($esc_label, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="card-body">
                        <?php if ($esc_ligne !== ''): ?>
                            <p>Ligne : <?= htmlspecialchars($esc_ligne, ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php endif; ?>
                        <p>Agents : <?= $esc_nb; ?></p>
                        <?php if (!empty($esc->voir_url)): ?>
                            <a href="<?= htmlspecialchars($esc->voir_url, ENT_QUOTES, 'UTF-8'); ?>"
                               class="btn btn-block btn-rounded text-dark bg-white">
                                <span class="fas fa-eye"></span>
                                VOIR
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-lg-6 offset-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2>AUCUNE ESCALE CONFIGURÉE</h2>
                    <p>Aucun agent vente escale de cette gare n’a d’escale affectée.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<style>
.sg-lieu-tabs { display: flex; gap: 0.5rem; margin: 0 0 0.85rem; }
.sg-lieu-tab {
    flex: 1 1 50%;
    min-height: 52px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #1f2937;
    font-weight: 700;
    font-size: 1.05rem;
}
.sg-lieu-tab.is-active { background: #0d6efd; border-color: #0d6efd; color: #fff; }
</style>
<script>
(function () {
    var tabs = document.querySelectorAll('.sg-lieu-tab');
    var quartier = document.getElementById('sg-panel-quartier');
    var escale = document.getElementById('sg-panel-escale');
    if (!tabs.length || !quartier || !escale) return;
    function show(name) {
        quartier.style.display = name === 'quartier' ? 'contents' : 'none';
        escale.style.display = name === 'escale' ? 'contents' : 'none';
        for (var i = 0; i < tabs.length; i++) {
            var on = tabs[i].getAttribute('data-sg-tab') === name;
            tabs[i].classList.toggle('is-active', on);
            tabs[i].setAttribute('aria-selected', on ? 'true' : 'false');
        }
    }
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener('click', function () {
            show(this.getAttribute('data-sg-tab'));
        });
    }
    var onglet = '';
    if (window.location.search) {
        var parts = window.location.search.replace(/^\?/, '').split('&');
        for (var j = 0; j < parts.length; j++) {
            var pair = parts[j].split('=');
            if (decodeURIComponent(pair[0] || '') === 'onglet') {
                onglet = decodeURIComponent(pair[1] || '');
            }
        }
    }
    if (onglet === 'escale') {
        show('escale');
    }
})();
</script>
<?php endif; ?>
</div>