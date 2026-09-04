<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('beagle/pages/guichet/_accueil_tabs_styles'); ?>
<div class="guichet-accueil-toolbar card card-border-color card-border-color-primary mb-3">
  <div class="card-body p-2">
    <div class="guichet-retour-row">
      <?php $this->load->view('_partials/btn_retour_gare'); ?>
    </div>
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#guichet-tab-1-vente" role="tab" aria-selected="true"><i class="fas fa-bus mr-1"></i>Vente</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#guichet-tab-1-tickets" role="tab" aria-selected="false"><i class="fas fa-ticket-alt mr-1"></i>Tickets</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#guichet-tab-1-caisse" role="tab" aria-selected="false"><i class="fas fa-cash-register mr-1"></i>Caisse</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#guichet-tab-1-consultation" role="tab" aria-selected="false"><i class="fas fa-eye mr-1"></i>Consultation</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#guichet-tab-1-bagages" role="tab" aria-selected="false"><i class="fas fa-suitcase mr-1"></i>Bagages & escales</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#guichet-tab-1-rapports" role="tab" aria-selected="false"><i class="fas fa-chart-bar mr-1"></i>Rapports</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#guichet-tab-1-declarations" role="tab" aria-selected="false"><i class="fas fa-file-signature mr-1"></i>Déclarations</a></li>
      <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#guichet-tab-1-admin" role="tab" aria-selected="false"><i class="fas fa-cogs mr-1"></i>Administration</a></li>
    </ul>
    <div class="tab-content pt-3">
      <div class="tab-pane fade show active" id="guichet-tab-1-vente" role="tabpanel">
        <div class="guichet-btn-grid">
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addventemobile md-trigger" data-modal="ticketmobil-0">
                                <i class="fas fa-bus text-success"></i>&nbsp;VENTE MOBILE&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/ventemobile/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}");?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; VENTE MOBILE&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey;?>"
                                class="btn btn-secondary btn-space addventeticket md-trigger" data-modal="ticketaller-0">
                                <i class="fas fa-bus text-info"></i>&nbsp;VENTE GUICHET&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey;?>"
                                class="btn btn-secondary btn-space addventeticketfi md-trigger" data-modal="ticketallerfi-0">
                                <i class="fas fa-edit text-info"></i>&nbsp;AUTRES VENTE&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/listeventegratuit/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; TICKET&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addrecu md-trigger" data-modal="ticketrecu-0">
                                <i class="fas fa-bus text-danger"></i>&nbsp;FAIRE RECU&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addbon md-trigger" data-modal="bon-0">
                                <i class="fas fa-book text-info"></i>&nbsp;BON MILITAIRE&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addcarte md-trigger" data-modal="carte-0">
                                <i class="fas fa-book text-success"></i>&nbsp;CARTE DE VOYAGE&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmbon md-trigger" data-modal="adconfirmbon-0">
                                <i class="fas fa-book text-success"></i>&nbsp;CONFIRMER BON &nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmcarte md-trigger" data-modal="adconfirmcarte-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER CARTE &nbsp;
                            </a>
        </div>
      </div>
      <div class="tab-pane fade" id="guichet-tab-1-tickets" role="tabpanel">
        <div class="guichet-btn-grid">
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey;?>"
                                class="btn btn-secondary btn-space addreprogramme md-trigger" data-modal="repro-0">
                                <i class="fas fa-edit text-warning"></i>&nbsp;REPROGRAMMER TICKET&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey;?>"
                                class="btn btn-secondary btn-space addreprogrammetransit md-trigger" data-modal="reprotransit-0">
                                <i class="fas fa-edit text-warning"></i>&nbsp;REPROGRAMMER TICKET&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addreprogadmin md-trigger" data-modal="adminrepro-0">
                                <i class="fas fa-edit text-warning"></i>&nbsp;REPROGRAMMER AUTRE TICKET&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirme md-trigger" data-modal="confirm-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER AUTRE TICKET&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addconfirmadmintran md-trigger" data-modal="adminconfirmtran-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;CONFIRMER TICKET&nbsp;
                            </a>
          <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addreserve md-trigger" data-modal="reserve-0">
                                <i class="fas fa-book text-warning"></i>&nbsp;RESERVATION&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/listeconfirmation/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; TICKET CONFIRMER DU JOUR&nbsp;
                            </a>
          <a href="<?= site_url("reserves/listereservation/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-list-alt text-warning"></i>&nbsp;VALIDER RESERVATION&nbsp;
                            </a>
        </div>
      </div>
      <div class="tab-pane fade" id="guichet-tab-1-caisse" role="tabpanel">
        <div class="guichet-btn-grid">
          <a href="<?= site_url("comptecaisses/compte/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;COMPTEBAGAGE&nbsp;
                            </a>
          <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTv/'
                            . $bus_stop->idengare.'/cais/'.$conex->roleattribut.'/'. $bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-dark"></i>&nbsp;VOIR CAISSE&nbsp;
                            </a>
          <button class="btn btn-secondary btn-space adreportversgljs md-trigger"
                                    data-modal="form-reportversgl-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;RECETTE GLOBALE TICKET&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space advers md-trigger"
                                data-modal="form-tri-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;RECETTE PAR OPERATEUR TICKET&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adverssg md-trigger"
                                data-modal="form-trisg-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>" data-idsggare="<?= $bus_stop->idsousgare; ?>">
                                <i></i>&nbsp;RECETTE GLOBALE TICKET PAR GARE&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtrio md-trigger"
                                    data-modal="form-trio-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;VERSEMENT TICKET GUICHETIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtriobag md-trigger"
                                    data-modal="form-triobag-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;VERSEMENT BAGAGES&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtriocour md-trigger"
                                data-modal="form-triocour-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;VERSEMENT COURRIER GUICHETIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-tri-1" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;TRI VERSEMENTS POUR MODIFICATION&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-tricr-1" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;MODIFICATION VERSEMENT COURRIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-tribg-1" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;MODIFICATION VERSEMENT BAGAGE&nbsp;
                            </button>
          <a href="<?= site_url("caisses/caissieres/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>" class="btn btn-secondary btn-space md-trigger">
                            <i class="fas fa-user text-info"></i>
                                <span>VOIR CAISSE PRINCIPALE</span>
                            </a>
        </div>
      </div>
      <div class="tab-pane fade" id="guichet-tab-1-consultation" role="tabpanel">
        <div class="guichet-btn-grid">
          <a href="<?= site_url("reserves/listeprogrammes/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-list-alt text-success"></i>&nbsp;LISTES&nbsp;
                            </a>
          <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTv/'
                            . $bus_stop->idengare.'/prog/'.$conex->roleattribut.'/'. $bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-warning"></i>&nbsp;VOIR PROGRAMME&nbsp;
                            </a>
          <a href="<?= site_url('historique_passagers/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-info"></i>&nbsp;VOIR ETATS&nbsp;
                            </a>
          <a href="<?= site_url('ligneheure/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-secondary btn-space">
                                <i class="fas fa-directions text-success"></i>&nbsp;
                                <span class="">DEPARTS</span>
                            </a>
          <a href="<?= site_url('tarifs/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-secondary btn-space">
                                <i class="fas fa-edit text-info"></i>&nbsp;
                                <span class="">TARIFICATION</span>
                            </a>
          <a href="<?= site_url('statut_gares/statutheure/'.$this->session->company->ekey.'/'.$conex->roleattribut.'/'.$bus_stop->idengare.'/'.$bus_stop->idsousgare); ?>" class="btn btn-secondary btn-space">
                                    <i class="fas fa-edit text-success"></i>&nbsp;
                                    <span class="">STATUT_HEURE_GARE</span>
                            </a>
          <a href="<?= site_url('programmes/bus/'.$this->session->company->ekey.'/'.$bus_stop->idengare.'/'.$conex->roleattribut.'/'.$bus_stop->idsousgare); ?>" class="btn btn-secondary btn-space">
                                <i class="fas fa-edit text-success"></i>&nbsp;
                                <span class="">PROGRAMME DE BUS</span>
                            </a>
          <a href="<?= site_url("ventescales/voirreimpri/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; VOIR REIMPRESSION&nbsp;
                            </a>
        </div>
      </div>
      <div class="tab-pane fade" id="guichet-tab-1-bagages" role="tabpanel">
        <div class="guichet-btn-grid">
          <button class="btn btn-secondary btn-space recaptbagexop md-trigger"
                                data-modal="form-recaptbgop-0" data-ekey="<?= $this->session->company->ekey; ?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL BAGAGE OPERATEUR&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space recaptbagexopesc md-trigger"
                                data-modal="form-recaptbgopesc-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL BAGAGEESCAL OPERATEUR&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptbg-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL BAGAGE&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                data-modal="form-recaptbgesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL BAGAGEESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptbgheb-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST BAGAGE&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptbgescheb-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST BAGAGEESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtrioexobag md-trigger"
                                    data-modal="form-trioexobag-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) BAGAGE&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtrioexobagesc md-trigger"
                                    data-modal="form-trioexobagesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) BAGAGEESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space recaptbagglop md-trigger"
                                data-modal="form-recaptbgopgl-0" data-ekey="<?= $this->session->company->ekey; ?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL BAGAGE OPERATEUR&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space recaptbagglopesc md-trigger"
                                data-modal="form-recaptbgopglesc-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL BAGAGEESCAL OPERATEUR&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapbg-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP GLOBAL BAGAGE&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapbgesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP GLOBAL BAGAGE ESCAL&nbsp;
                            </button>
          <a href="<?= site_url("confirmation/bagagemobile/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; FACTURATION BAGAGES AVEC TICKET&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/bagagesuivimobile/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; FACTURATION BAGAGES ENVOI&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/bagagenonfact/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; BAGAGES AVEC TICKET NON FACTURER&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/autrebagagefc/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-book text-warning"></i>&nbsp;AUTRES FACTURATION BAGAGES&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/bordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; BORDEREAU SUIVI BAGAGES&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/voirbordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; VOIR BORDEREAU BAGAGES(HISTORIQUE)&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/bagageescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;BAGAGE ESCAL&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/courrierescales/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;COURRIER ESCAL&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/validerarr/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;VALIDER ARRIVER&nbsp;
                            </a>
          <a href="<?= site_url("confirmation/validerarresc/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;VALIDER ARRIVER ESCAL&nbsp;
                            </a>
        </div>
      </div>
      <div class="tab-pane fade" id="guichet-tab-1-rapports" role="tabpanel">
        <div class="guichet-btn-grid">
          <a href="<?= site_url("caisses/compte/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-puzzle-piece text-info"></i>
                                &nbsp;COMPTE&nbsp;
                            </a>
          <button class="btn btn-secondary btn-space adreportjs md-trigger"
                                    data-modal="form-report-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL TICKET GUICHETIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adreportjsesc md-trigger"
                                  data-modal="form-reportesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                              <i></i>&nbsp;EXERCICE MENSUEL TICKET GUICHETIER ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapt-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL TICKET&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptes-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-nifestheb-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;MANIFEST TICKET&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtrioexo md-trigger"
                                    data-modal="form-trioexo-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) TICKET&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exopassagers-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE PASSAGERS&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-nifesthebesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;MANIFEST TICKET ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtrioexoesc md-trigger"
                                    data-modal="form-trioexoesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE)TICKET ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exopassagersesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE PASSAGERS ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adreportpli md-trigger"
                                    data-modal="form-reportplis-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL COURRIER GUICHETIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adreportpliesc md-trigger"
                                    data-modal="form-reportplisesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL COURRIERESCAL GUICHETIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptcr-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;RECAP EX MENSUEL COURRIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptcresc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;RECAP EX MENSUEL COURRIERESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptheb-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST COURRIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapthebesc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST COURRIERESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtrioexoplis md-trigger"
                                    data-modal="form-trioexopli-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) COURRIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adtrioexoplisesc md-trigger"
                                    data-modal="form-trioexopliesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) COURRIERESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exocourriers-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE COURRIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exocourriersesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE COURRIERESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adreportgl md-trigger"
                                    data-modal="form-repor-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL TICKET GUICHETIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adreportglesc md-trigger"
                                  data-modal="form-reporesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                              <i></i>&nbsp;ETAT GLOBAL TICKET GUICHETIER ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adreportglcours md-trigger"
                                    data-modal="form-reporcourglo-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL COURRIER GUICHETIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adreportglcoursesc md-trigger"
                                    data-modal="form-reporcourgloesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL COURRIERESCAL GUICHETIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recap-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP GLOBAL TICKET&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                  data-modal="form-recapesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                              <i></i>&nbsp;RECAP GLOBAL TICKET ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapglcr-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;RECAP GLOBAL COURRIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapglcresc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;RECAP GLOBAL COURRIERESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                data-modal="exopassagersgl-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;LISTE GLOBALE PASSAGERS&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exopassagersglesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;LISTE GLOBALE PASSAGERS ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                data-modal="triglcourrier-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;LISTE GLOBALE COURRIER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                data-modal="triglcourrieresc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;LISTE GLOBALE COURRIERESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space adreportgldepcour md-trigger"
                                data-modal="form-reportcour-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;RECAP DEPENSE COURRIER&nbsp;
                            </button>
        </div>
      </div>
      <div class="tab-pane fade" id="guichet-tab-1-declarations" role="tabpanel">
        <div class="guichet-btn-grid">
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecapt-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecapt-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;DECLARER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecaptcr-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION COURRIERS&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptcr-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;COURRIERS DECLARER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecaptcresc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION COURRIERSESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptcresc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;COURRIERSESCAL DECLARER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecapes-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION
                                ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptes-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;DECLARER
                                ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecaptbg-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION BAGAGES&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptbg-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;BAGAGES DECLARER&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-clarrecaptbgesc-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;DECLARATION BAGAGES ESCAL&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-declarrecaptbgesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;BAGAGES ESCAL DECLARER&nbsp;
                            </button>

        </div>
      </div>
      <div class="tab-pane fade" id="guichet-tab-1-admin" role="tabpanel">
        <div class="guichet-btn-grid">
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-arch-1" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;TRI POUR ARCHIVER DES DONNEES&nbsp;
                            </button>
          <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-archcr-1" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;ARCHIVER DONNEES COURRIER&nbsp;
                            </button>
        </div>
      </div>
    </div>
  </div>
</div>
