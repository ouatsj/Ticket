<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="row">
                <div class="col-sm-12">
                    <div class="text-center">
                        <p>
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTs/'
                            . $bus_stop->idengare.'/sousgare/'.$conex->roleattribut.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-arrow-circle-left text-info"></i>&nbsp;RETOUR GARE&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addventeticket md-trigger" data-modal="ticketaller-0">
                                <i class="fas fa-bus text-info"></i>&nbsp;VENTE GUICHET&nbsp;
                            </a>
                            
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
                                class="btn btn-secondary btn-space addventeticketfi md-trigger" data-modal="ticketallerfi-0">
                                <i class="fas fa-edit text-info"></i>&nbsp;AUTRES VENTE&nbsp;
                            </a>
                            <a href="#" data-cle_compagnie="<?= $this->session->company->ekey; ?>"
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
                            <a href="<?= site_url("reserves/listereservation/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-list-alt text-warning"></i>&nbsp;VALIDER RESERVATION&nbsp;
                            </a>
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTv/'
                            . $bus_stop->idengare.'/prog/'.$conex->roleattribut.'/'. $bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-warning"></i>&nbsp;VOIR PROGRAMME&nbsp;
                            </a>
                            <a href="<?= site_url('gares/'. $this->session->company->ekey . '/gTv/'
                            . $bus_stop->idengare.'/cais/'.$conex->roleattribut.'/'. $bus_stop->idsousgare.'/' . mdate("%d/%m/%Y", now('UTC'))); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-eye text-dark"></i>&nbsp;VOIR CAISSE&nbsp;
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
                            <a href="<?= site_url("reserves/listeprogrammes/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-list-alt text-success"></i>&nbsp;LISTES&nbsp;
                            </a>
                           
                            
                            <button class="btn btn-secondary btn-space adreportjs md-trigger"
                                    data-modal="form-report-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL TICKET GUICHETIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adreportjsesc md-trigger"
                                data-modal="form-reportesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                              <i></i>&nbsp;EXERCICE MENSUEL TICKET GUICHETIER ESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space recaptbagexop md-trigger"
                                data-modal="form-recaptbgop-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL BAGAGE OPERATEUR&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space recaptbagexopesc md-trigger"
                                data-modal="form-recaptbgopesc-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE MENSUEL BAGAGEESCAL OPERATEUR&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapt-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL TICKET&nbsp;
                            </button>
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
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptes-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL ESCAL&nbsp;
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
                                    data-modal="form-recaptbg-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL BAGAGE&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                data-modal="form-recaptbgesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP EX MENSUEL BAGAGEESCAL&nbsp;
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
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exopassagers-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE PASSAGERS&nbsp;
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
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptbgheb-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST BAGAGE&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recaptbgescheb-0" data-ekey="<?= $this->session->company->ekey;?>">
                                <i></i>&nbsp;MANIFEST BAGAGEESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adtrioexoplis md-trigger"
                                    data-modal="form-trioexopli-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) COURRIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adtrioexoplisesc md-trigger"
                                    data-modal="form-trioexopliesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) COURRIERESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adtrioexobag md-trigger"
                                    data-modal="form-trioexobag-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) BAGAGE&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space adtrioexobagesc md-trigger"
                                    data-modal="form-trioexobagesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;BROUILLARD(EXERCICE) BAGAGEESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exocourriers-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE COURRIER&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="exocourriersesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;EXERCICE LISTE COURRIERESCAL&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space adreportversgljs md-trigger"
                                    data-modal="form-reportversgl-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idgares="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;RECETTE GLOBALE TICKET&nbsp;
                            </button>
                            
                            <button class="btn btn-secondary btn-space adreportgl md-trigger"
                                    data-modal="form-repor-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL TICKET GUICHETIER&nbsp;
                            </button>


                            <button class="btn btn-secondary btn-space adreportglesc md-trigger"
                                  data-modal="form-reporesc-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                              <i></i>&nbsp;ETAT GLOBAL TICKET GUICHETIER ESCAL&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space recaptbagglop md-trigger"
                                data-modal="form-recaptbgopgl-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare;?>">
                                <i></i>&nbsp;ETAT GLOBAL BAGAGE OPERATEUR&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space recaptbagglopesc md-trigger"
                                data-modal="form-recaptbgopglesc-0" data-ekey="<?= $this->session->company->ekey;?>"
                                data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;ETAT GLOBAL BAGAGEESCAL OPERATEUR&nbsp;
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
                                    data-modal="form-recapbg-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP GLOBAL BAGAGE&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-recapbgesc-0" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;RECAP GLOBAL BAGAGE ESCAL&nbsp;
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
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-arch-1" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;TRI POUR ARCHIVER DES DONNEES&nbsp;
                            </button>
                            <button class="btn btn-secondary btn-space md-trigger"
                                    data-modal="form-archcr-1" data-ekey="<?= $this->session->company->ekey; ?>">
                                <i></i>&nbsp;ARCHIVER DONNEES COURRIER&nbsp;
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

                            <button class="btn btn-secondary btn-space adtriocour md-trigger"
                                    data-modal="form-triocour-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;VERSEMENT COURRIER GUICHETIER&nbsp;
                            </button>

                            <button class="btn btn-secondary btn-space adreportgldepcour md-trigger"
                                    data-modal="form-reportcour-0" data-ekey="<?= $this->session->company->ekey; ?>" data-idsgare="<?= $bus_stop->idengare; ?>">
                                <i></i>&nbsp;RECAP DEPENSE COURRIER&nbsp;
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
                            <a href="<?= site_url("confirmation/bordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; BORDEREAU SUIVI BAGAGES&nbsp;
                            </a>
                            <a href="<?= site_url("confirmation/voirbordereaubagages/{$this->session->company->ekey}/{$conex->roleattribut}/{$bus_stop->idengare}/{$bus_stop->idsousgare}"); ?>"
                                class="btn btn-secondary btn-space md-trigger" data-modal="">
                                <i class="fas fa-print text-info"></i>&nbsp; VOIR BORDEREAU BAGAGES(HISTORIQUE)&nbsp;
                            </a>
                            
                        </p>
                    </div>
                </div>
                <div class="col-12 col-lg-6 col-xl-3">
                    
                    <div class="widget widget-tile">
                        
                        <div class="chart sparkline" id="spark1">
                            
                            <canvas width="85" height="35"
                                    style="display: inline-block; width: 85px; height: 35px; vertical-align: top;"></canvas>
                        
                        </div>
                        
                        <div class="data-info">
                            
                            <div class="desc">Axes</div>
                            
                                <div class="value">
                                    <span class="indicator indicator-equal mdi mdi-chevron-right"></span>
                                    <span class="number" data-toggle="counter"
                                          data-end="<?= $this->db->count_all_results('lignes'); ?>">
                                    <?= $this->db->count_all_results('lignes'); ?></span>
                                </div>
                        </div>
                    
                    </div>
                
                </div>
                
                <div class="col-12 col-lg-6 col-xl-3">
                    
                    <div class="widget widget-tile">
                        
                        <div class="chart sparkline" id="spark3">
                            
                            <canvas width="85" height="35"
                                    style="display: inline-block; width: 85px; height: 35px; vertical-align: top;"></canvas>
                        
                        </div>
                        
                        <div class="data-info">
                            
                            <div class="desc">Clients</div>
                            
                            <div class="value">
                                <span class="indicator indicator-positive mdi mdi-chevron-up"></span>
                                <span class="number" data-toggle="counter"
                                data-end="<?= $this->db->count_all_results('client'); ?>">
                                    <?= $this->db->count_all_results('client'); ?></span>
                            </div>
                        
                        </div>
                    
                    </div>
                
                </div>
            
                
                <div class="col-12 col-lg-6">
                    
                    <div class="card card-table">
                        
                        <div class="card-header">
                            <div class="tools dropdown">
                                <span class="icon mdi mdi-download"></span>
                                <a class="dropdown-toggle"
                                href="#" role="button"
                                data-toggle="dropdown">
                                    <span class="icon mdi mdi-more-vert"></span></a>
                            </div>
                        </div>
                        <div class="title"></div>
                        
                        <div class="card-body table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:37%;">Lignes</th>
                                        <th style="width:36%;">nombre passager</th>
                                        
                                    </tr>
                                </thead>
                                <tbody id="passagers-stats-body">
                                    <?php if (empty($passagers_deferred)): ?>
                                    <? foreach ($passagers as $pas): ?>
                                        <tr>
                                            <td><?= $pas->nom_ligne; ?></td>
                                            <td><?= $pas->cod; ?></td>  
                                        </tr>                              
                                    <? endforeach; ?>
                                    <?php else: ?>
                                    <tr><td colspan="2" class="text-muted">Chargement…</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    
                    </div>
                
            <?php if (!empty($passagers_deferred)): ?>
            <script>
            (function () {
                var url = <?= json_encode(site_url('gares/' . $this->session->company->ekey . '/ajax_passagers')); ?>;
                fetch(url, { credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (rows) {
                        var tb = document.getElementById('passagers-stats-body');
                        if (!tb) return;
                        if (!rows || !rows.length) {
                            tb.innerHTML = '<tr><td colspan="2">Aucune donnée</td></tr>';
                            return;
                        }
                        tb.innerHTML = rows.map(function (p) {
                            return '<tr><td>' + String(p.nom_ligne).replace(/</g, '&lt;') + '</td><td>' + p.cod + '</td></tr>';
                        }).join('');
                    })
                    .catch(function () {
                        var tb = document.getElementById('passagers-stats-body');
                        if (tb) tb.innerHTML = '<tr><td colspan="2">Erreur de chargement</td></tr>';
                    });
            })();
            </script>
            <?php endif; ?>
                </div>
                
            </div>
            <div class="row">

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trisg-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caiTitlesg"></h3>
                            <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaisFormssg')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagsg">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgarsg" id="encaisgarsg">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedsg" id="iddatedebutsg">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefsg" id="iddatefinsg">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>OPERATEUR</label>
                                <select class="form-control form-control-sm" name="vendeuseidsg" id="idvendeusesg">
                                    <option value="">Choississez operateur</option>
                                    
                                </select>
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="ticketaller-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="taTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true">
                                    <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'taForm')); ?>
                            <input type="hidden" id="pascompagnie" name="clientcomp">
                            <input type="hidden" id="rclientcp" name="cprclient">
                            <input type="hidden" id="prnclientcp" name="cpprclient">
                            <input type="hidden" id="cnibcp" name="cpcnib">
                            <input type="hidden" id="date_cnibcp" name="cpdate_cnib">
                            <input type="hidden" id="lieudelivrecp" name="cplieudelivr">
                            <input type="hidden" id="codeligne" name="codelign">
                            <input type="hidden" id="nomligne" name="nomlign">
                            <input type="hidden" id="inter1" name="interv1">
                            <input type="hidden" id="inter2" name="interv2">
                            <input type="hidden" id="depligne" name="departligne">
                            <input type="hidden" id="ligneh" name ="lignehr">
                            <input type="hidden" id="rtime" name="rtime">
                            <input type="hidden" id="program" name="progcod">
                            <input type="hidden" id="program1" name="progcod1">
                            
                            <input type="hidden" id="tarifattrib" name="tarifattribuer">
                            
                            <input type="hidden" id="datepr">
                            <input type="hidden" id="lign" name="lignedepa">
                            <input type="hidden" id="her">
                            <input type="hidden" id="typegare">
                            <input type="hidden" id="cate" name="catgorie">
                            <input type="hidden" id="pvendable" name="vendable">
                            <input type="hidden" id="dvendable" name="dpvendable">
                            <input type="hidden" id="nomitin" name="nomitine">
                            <input type="hidden" id="prix_axe" name="prix">
                            <input type="hidden" id="siegselect">
                            <input type="hidden" id="idtampo">
                            <input type="hidden" id="siegselect2">
                            <input type="hidden" id="idtampo2">
                            <input type="hidden" id="siegselect3">
                            <input type="hidden" id="idtampo3">
                            <input type="hidden" id="codelignetrans" name="codeligntrans">
                            <input type="hidden" id="nomlignetrans" name="nomligntrans">
                            <input type="hidden" id="intertrans1" name="intervtrans1">
                            <input type="hidden" id="intertrans2" name="intervtrans2">
                            <input type="hidden" id="deplignetrans" name="departlignetrans">
                            <input type="hidden" id="deplignetrans1" name="departlignetrans1">
                            <input type="hidden" id="lignehtrans" name ="lignehrtrans">
                            <input type="hidden" id="rtimetrans" name="rtimetrans">
                            <input type="hidden" id="programtrans" name="progcodtrans">
                            <input type="hidden" id="traprogramtrans" name="traprogcodtrans">
                            <input type="hidden" id="traintertrans1" name="traintervtrans1">
                            <input type="hidden" id="traintertrans2" name="traintervtrans2">
                            <input type="hidden" id="dateprtrans">
                            <input type="hidden" id="ligntrans" name="lignedepatrans">
                            <input type="hidden" id="ligntrans1" name="lignedepatrans1">
                            <input type="hidden" id="ligntrans2" name="lignedepatrans2">
                            <input type="hidden" id="ligntrans3" name="lignedepatrans3">

                            <input type="hidden" id="hertrans">
                            <input type="hidden" id="typegaretrans">
                            <input type="hidden" id="catetrans" name="catgorietrans">
                            <input type="hidden" id="pvendabletrans" name="vendabletrans">
                            <input type="hidden" id="dvendabletrans" name="dpvendabletrans">
                            <input type="hidden" id="nomitintrans" name="nomitinetrans">
                            <input type="hidden" id="nomitintrans1" name="nomitinetrans1">
                            <input type="hidden" id="nomitintrans2" name="nomitinetrans2">
                            <input type="hidden" id="nomitintrans3" name="nomitinetrans3">
                            <input type="hidden" id="prix_axetrans" name="prixtrans">
                            <input type="hidden" id="prix_axetransit" name="prixtransit">
                            <input type="hidden" id="catetransit" name="catgorietransit">
                            <input type="hidden" id="siegselecttrans">
                            <input type="hidden" id="idtampotrans">
                            <input type="hidden" id="siegselect1">
                            <input type="hidden" id="idtampo1">
                            <input type="hidden" id="nbrtrans" name="nombretransite">
                            <input type="hidden" id="gidtrans" name="gidtransite">
                            <input type="hidden" id="idcompg" name="compg">
                            <input type="hidden" id="idcompg1" name="compg1">
                            <input type="hidden" id="idcompg2" name="compg2">
                            <input type="hidden" id="idcompg3" name="compg3">
                        
                            <input type="hidden" id="prix_axetransit1" name="prixtransit1">
                            <input type="hidden" id="catetransit1" name="catgorietransit1">

                            <input type="hidden" id="prix_axetransit2" name="prixtransit2">
                            <input type="hidden" id="catetransit2" name="catgorietransit2">
                            <input type="hidden" id="gidtrans1" name="gidtransite1">
                            <input type="hidden" id="gidtrans2" name="gidtransite2">

                            <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actu" name="dactuel">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="card-header text-center">Information sur le depart </div>
                            
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="smsdt">
                                    <p id="erreurSmsdt"></p>
                                </div>
                            
                                <div class="form-group row pt-1 pb-1">
                                    <label class="col-12 col-sm-3 col-form-label text-sm-right">Ticket</label>
                                    <div class="col-12 col-sm-8 col-lg-6 form-check mt-1">
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inline" value="aller" id="aller" checked="" type="radio"><span class="custom-control-label">Aller</span>
                                        </label>
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inline" value="aller_retour" id="aller_retour" type="radio"><span class="custom-control-label">Aller_Retour</span>
                                        </label>
                                        
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="iddep">Départ</label>
                                        <select style="display:block" class="form-control form-control-sm" name="depargare" id="depargare">
                                            <? foreach ($garedeparts as $garedepart): ?>
                                                <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                    <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="arrid">Arrivée</label>
                                        <select style="display:block" class="form-control form-control-sm" name="arrgare" id="arrsgare">
                                            <option value="">Choisissez l'arrivée</option>
                                            <? foreach ($garearrivees as $garearrivee): ?>
                                                <option value="<?= $garearrivee->code_gadest; ?>/<?= $garearrivee->id_compaga; ?>">
                                                    <?= $garearrivee->nom_gadest; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="idquart">Quartier</label>
                                        <select style="display:block" name="quartconfirme" class="form-control form-control-sm" id="quartier">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Date depart</label>
                                        <input class="form-control form-control-sm" type="date" name="datedepart" id="date_depheure">
                                    </div>
                                    
                                    <div class="card-header text-center" id="tran" style="display:none">Transite</div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="hrid">Heure</label>
                                        <select style="display:block" class="form-control form-control-sm" name="heuredept" id="hdepart">
                                            <option value="">Choisissez départ</option>
                                            
                                        </select>
                                    </div>                   
                                    <div class="form-group col-sm-4">
                                        <label style="display:block;" id="sigid">Siège</label>
                                        <select style="display:block" class="form-control form-control-sm" name="passagersieges" id="psieges">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-4 text-center text-danger" style="display:none"
                                        id="mess">
                                        <p id="erreurMess"></p>
                                    </div>
                                                   
                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="itincode"
                                            id="itinecode">
                                    </div>
                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="lignetineraires"
                                            id="lignetineraire">
                                    </div>

                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="itincodees"
                                            id="itinecodes">
                                    </div>
                                    
                                    <div> 
                                        <label style="display:none" id="ligne1">Ligne transite1</label>
                                        <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitineraires"
                                            id="lignesitineraire" disabled="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart1">Quartier</label>
                                        <select style="display:none" name="quartconfirme1" class="form-control form-control-sm" id="quartier1">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="heuredeptitine" id="hdepartitine">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="siegitine">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines" id="psiegesitines">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans1">Départ transite1</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare1" id="transitedepargare1">
                                            
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin1">Ligne transite2</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin" id="idchemins">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart2">Quartier</label>
                                        <select style="display:none" name="quartconfirme2" class="form-control form-control-sm" id="quartier2">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin1">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure" id="idcheminsheur">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine1">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1" id="psiegesitines1">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans2">Départ transite2</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare2" id="transitedepargare2">
                                            
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin2">Ligne transite3</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin1" id="idchemins1">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart3">Quartier</label>
                                        <select style="display:none" name="quartconfirme3" class="form-control form-control-sm" id="quartier3">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin2">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure1" id="idcheminsheur1">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine2">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2" id="psiegesitines2">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans3">Départ transite3</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare3" id="transitedepargare3">
                                            
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin3">Ligne transite4</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin2" id="idchemins2">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin3">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure2" id="idcheminsheur2">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine3">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3" id="psiegesitines3">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans4">Départ transite4</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare4" id="transitedepargare4">
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="card-header text-center">Information du client</div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label>Type</label>
                                        <select class="form-control form-control-sm" name="type" id="cltype">
                                            <? foreach ($typesclients as $item): ?>
                                            <option value="<?=$item->nom_type;?>"><?=$item->nom_type;?></option>
                                            <?endforeach;?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Contact</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                            name="rclient_contact"
                                            id="rnclient_contact"
                                            autocomplete="off"
                                            placeholder="contact client">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Nom</label>
                                        <input class="form-control form-control-sm" type="text" name="rclient"
                                            id="rclient"
                                            autocomplete="off"
                                            placeholder="nom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Prénom</label>
                                        <input class="form-control form-control-sm" type="text" name="prclient"
                                            id="prnclient"
                                            autocomplete="off" 
                                            placeholder="prenom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Cni ou Passport</label>
                                        <input class="form-control form-control-sm" type="text" name="cnib"
                                            id="cnib"
                                            autocomplete="off"
                                            placeholder="cni ou passport">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Délivré(e)le</label>
                                        <input class="form-control form-control-sm" type="date" name="date_cnib" value="<?= mdate("%Y-%m-%d", now());?>"
                                            id="date_cnib">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label class="col-sm-4 text-left">Lieu</label>
                                        <input class="form-control form-control-sm" type="text" name="lieu"
                                            id="lieudelivre"
                                            autocomplete="off"
                                            placeholder="lieu d'établissement">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="motif">Motif</label>
                                        <select class="form-control form-control-sm" name="commentclient" style="display:none"
                                                id="motifrefus">
                                            <option value="">Choisissez une cause</option>
                                            <option value="refus">refus</option>
                                            <option value="pas de contact">pas de contact</option>
                                            <option value="pas de cnib">pas de cnib</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label class="col-sm-4" style="display:none" id="doc">numéro_document</label>
                                        <input class="form-control form-control-sm" type="text" name="document"
                                            id="num_doc" style="display:none"
                                            autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="docdelivre">Délivré(e)le</label>
                                        <input class="form-control form-control-sm" type="date" name="date_doc" value="<?= mdate("%Y-%m-%d", now());?>"
                                        style="display:none" id="datedocdel">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset" id="idreset">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <input class="btn btn-success md-trigger" type="submit" name="ordinaire" value="ORDINAIRE" disabled="">
                                        <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON" id="bottontick">
                                    </div>
                                </div>
                            
                            </div>
                        <?= form_close(); ?>
                        
                </div>
                
                <!--reprogrammer ticket-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="repro-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="rTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'rForm')); ?>
                        <input type="hidden" id="passerp" name="passerid">
                        <input type="hidden" id="codetickets" name="codeticketsclient">
                        <input type="hidden" id="passagersieg" name="siegpas">
                        <input type="hidden" id="pasnomp" name="passnom">
                        <input type="hidden" id="pasprenomp" name="passprenom">
                        <input type="hidden" id="pascontactp" name="passcontact">
                        <input type="hidden" id="passaxep" name="passaxe">
                        <input type="hidden" id="pascnibp" name="passcnib">
                        <input type="hidden" id="pasdatep" name="passdate">
                        <input type="hidden" id="nsiegep" name="nsiege">
                        <input type="hidden" id="idsiegep" name="idsiege">
                        <input type="hidden" id="newd" name="newdpart">
                        <input type="hidden" id="depold" name="adepcl">
                        <input type="hidden" id="client_idp" name="client_id">
                        <input type="hidden" id="garedp" name="garedpa">
                        <input type="hidden" id="gareidp">
                        <input type="hidden" id="replign">
                        <input type="hidden" id="repher">
                        <input type="hidden" id="datereprogramme">
                        <input type="hidden" id="directp" name="directpa">
                        <input type="hidden" id="delivrelie" name="dlieu">
                        <input type="hidden" id="placevendu" name="placevd">
                        <input type="hidden" id="dplacevendu" name="dplacevd">
                        <input type="hidden" id="codeid" name="rpcode">
                        <input type="hidden" id="coaxeid" name="rpaxecode">
                        <input type="hidden" id="idclpasserid" name="clpasserid">
                        <input type="hidden" id="depgid" name="departgid">
                        <input type="hidden" id="catreprogramme" name="catreprogram">
                        <input type="hidden" id="programrep" name="repmcod">
                        <input type="hidden" id="dateprrep">
                        <input type="hidden" id="codenonp" name="codenonpassager">
                        <input type="hidden" id="statconf" name="statconfirm">
                        <input type="hidden" id="statrep" name="statrepro">
                        <input type="hidden" id="siegselectrep">
                        <input type="hidden" id="idtamporep">
                        <input type="hidden" id="dateventerep">
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actueldaterep" name="dateactuelrep">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                        name="codeclient"
                                        id="codeclientp"
                                        autocomplete="off" required="" 
                                        placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                    <span class="btn btn-success" type="button" id="reprogrammer_infos">
                                        <i></i>Afficher les informations
                                    </span>
                            </div>
                            
                        </div>
                        <p name="nomcl" id="nomclp"></p>
                        <p name="prenmclp" id="prenomclp"></p>
                        <p name="contactcl" id="contactclp"></p>
                        <p name="refcl" id="refclp"></p>
                        <p name="directioncl" id="directionclp"></p>
                        <p name="codecl" id="codeclp"></p>
                        <p name="heurecl" id="heureclp"></p>
                        <div class="form-group row">
                            
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="heuredepart"
                                        style="display:none"
                                        id="heuredepartp">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="numsiege"
                                        style="display:none"
                                        id="numsiegep">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>
                            <div class="col-sm-4 text-center text-danger"
                                    id="erreursieg" style="display:none">
                                <p id="erreurSiege"></p>
                            </div> 
                            <div class="col-sm-6 text-center text-danger"
                                    id="smsp" style="display:none">
                                <p id="erreurSmsp"></p>
                            </div>
                            <div class="col-sm-6 text-center text-danger"
                                    id="billetrep" style="display:none">
                                <p id="billetSmsrep"></p>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset" id="rese">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" name="ordinaire" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON">
                            </div>
                        </div>
                    </div>
                        <?= form_close(); ?>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="reprotransit-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="rTitletransit"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                    <?= form_open("", array('class' => 'modal-body form', 'id' => 'rFormtransit')); ?>
                        <input type="hidden" id="passerptransit" name="passeridtransit">
                        <input type="hidden" id="codeticketstransit" name="codeticketsclienttransit">
                        <input type="hidden" id="lgcodeticketstransit" name="lgecodeticketstransit">
                        <input type="hidden" id="passagersiegtransit" name="siegpastransit">
                        <input type="hidden" id="pasnomptransit" name="passnomtransit">
                        <input type="hidden" id="pasprenomptransit" name="passprenomtransit">
                        <input type="hidden" id="pascontactptransit" name="passcontacttransit">
                        <input type="hidden" id="passaxeptransit" name="passaxetransit">
                        <input type="hidden" id="pascnibptransit" name="passcnibtransit">
                        <input type="hidden" id="pasdateptransit" name="passdatetransit">
                        <input type="hidden" id="nsiegeptransit" name="nsiegetransit">
                        <input type="hidden" id="idsiegeptransit" name="idsiegetransit">
                        <input type="hidden" id="newdtransit" name="newdparttransit">
                        <input type="hidden" id="depoldtransit" name="adepcltransit">
                        <input type="hidden" id="client_idptransit" name="client_idtransit">
                        <input type="hidden" id="garedptransit" name="garedpatransit">
                        <input type="hidden" id="gareidptransit">
                        <input type="hidden" id="id_compagatr" name="trid_compaga">
                        <input type="hidden" id="repligntransit" name="repligntransit">

                        <input type="hidden" id="idrepligntransit" name="idrpligntransit">
                        <input type="hidden" id="rephertransit">
                        <input type="hidden" id="datereprogrammetransit">
                        <input type="hidden" id="directptransit" name="directpatransit">
                        <input type="hidden" id="delivrelietransit" name="dlieutransit">
                        <input type="hidden" id="placevendutransit" name="placevdtransit">
                        <input type="hidden" id="dplacevendutransit" name="dplacevdtransit">
                        <input type="hidden" id="codeidtransit" name="rpcodetransit">
                        <input type="hidden" id="coaxeidtransit" name="rpaxecodetransit">
                        <input type="hidden" id="idclpasseridtransit" name="clpasseridtransit">
                        <input type="hidden" id="depgidtransit" name="departgidtransit">
                        <input type="hidden" id="catreprogrammetransit" name="catreprogramtransit">
                        <input type="hidden" id="programreptransit" name="repmcodtransit">
                        <input type="hidden" id="dateprreptransit">
                        <input type="hidden" id="codenonptransit" name="codenonpassagertransit">
                        <input type="hidden" id="statconftransit" name="statconfirmtransit">
                        <input type="hidden" id="statreptransit" name="statreprotransit">
                        
                        <input type="hidden" id="gareidentiftransit" name="gareidentiftrans">
                        <input type="hidden" id="departclientidgare" name="departclientidgaretr">
                        <input type="hidden" id="siegselectreptransit">
                        <input type="hidden" id="idtamporeptransit">
                        <input type="hidden" id="dateventereptransit">
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actueldatereptransit" name="dateactuelreptransit">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="smsdttr">
                                <p id="erreurSmsdttr"></p>
                            </div>
                            
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                    name="codeclienttransit"
                                    id="codeclientptransit"
                                    autocomplete="off" required="" 
                                    placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                <span class="btn btn-success" type="button" id="reprogrammer_infostransit">
                                    <i></i>Afficher les informations
                                </span>
                            </div>
                            
                        </div>
                        <p name="nomcltransit" id="nomclptransit"></p>
                        <p name="prenmclptransit" id="prenomclptransit"></p>
                        <p name="contactcltransit" id="contactclptransit"></p>
                        <p name="refcltransit" id="refclptransit"></p>
                        <p name="directioncltransit" id="directionclptransit"></p>
                        <p name="codecltransit" id="codeclptransit"></p>
                        <p name="heurecltransit" id="heureclptransit"></p>
                        <div class="form-group row">
                            
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="heuredeparttransit"
                                    style="display:none"
                                    id="heuredepartptransit">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <input class="form-control form-control-sm" type="hidden" name="compgcftranst" id="compgcftransit">
                            
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="numsiegetransit"
                                        style="display:none"
                                        id="numsiegeptransit">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>
                            <div class="col-sm-4 text-center text-danger"
                                    id="erreursiegtransit" style="display:none">
                                <p id="erreurSiegetransit"></p>
                            </div> 
                            <div class="col-sm-6 text-center text-danger"
                                    id="smsptransit" style="display:none">
                                <p id="erreurSmsptransit"></p>
                            </div>
                            <div class="col-sm-6 text-center text-danger"
                                    id="billetreptransit" style="display:none">
                                <p id="billetSmsreptransit"></p>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset" id="resetransit">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" name="ordinairetransit" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" name="epsontransit" value="EPSON">
                            </div>
                        </div>
                    <?= form_close(); ?>
                    </div>
                </div>
                <!--confirmation-->
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="adminconfirm-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="admincTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'admincForm')); ?>
                        <input type="hidden" id="adminpassep" name="passeid">
                        <input type="hidden" id="adminclientidp" name="clientid">
                        <input type="hidden" id="adminpascodetick" name="codedep">
                        <input type="hidden" id="admincodecpas" name="codedepas">
                        <input type="hidden" id="adminpasnomp" name="passnom">
                        <input type="hidden" id="adminpasprenomp" name="passprenom">
                        <input type="hidden" id="adminpascnibp" name="passcnib">
                        <input type="hidden" id="adminpascontactp" name="pascontact">
                        <input type="hidden" id="adminpasdatep" name="passdate">
                        <input type="hidden" id="adminlieu" name="adminlieudel">
                        <input type="hidden" id="admimtype" name="admintypeclient">
                        <input type="hidden" id="adcommentclient" name="admincommentclient">
                        <input type="hidden" id="adcaissepvend_" name="caispvende">
                        <input type="hidden" id="adcaissedpvend_" name="caisdpvende">
                        <input type="hidden" id="adcatego" name="numcate">
                        <input type="hidden" id="dateventeconf">
                        <input type="hidden" id="addirectid">
                        <input type="hidden" id="adminsiegselectconf">
                        <input type="hidden" id="adminidtampoconf">
                        <input type="hidden" id="axeligneconf">
                        <input type="hidden" id="ligneconflg">
                        <input type="hidden" id="adconfheure">

                        <input type="hidden" id="addateconfirme">
                        <input type="hidden" id="admincodeconfi" name="adcodeconfirm">
                        <input type="hidden" id="adlignehconf" name="adlignhconf">
                        <input type="hidden" id="adprogramconf" name="adprogrammconf">
                        <div class="col-sm-6 text-center text-danger" style="display:none"
                                id="adminmessagep">
                                <p id="adminerreurMessagep"></p>
                        </div>
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="datactu" name="dactuelle">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                       name="codeconfirmad"
                                       id="admincodeconfirm"
                                       autocomplete="off" required
                                       placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                        <span class="btn btn-success" type="button" id="adminconfirme_info">
                                            <i></i>Afficher les informations
                                        </span>
                            </div>
                            
                        </div>
                        <p name="nom" id="adminnomp"></p>
                        <p name="prenom" id="adminprenomp"></p>
                        <p name="contact" id="admincontactp"></p>
                        <p name="ref" id="adminrefp"></p>
                        <p name="direction" id="admindirectionp"></p>
                        <p name="codec" id="admincodecp"></p>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <select class="form-control form-control-sm" name="depargare" id="depargares">
                                    <? foreach ($garedeparts as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                            <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="axesconfirm" class="form-control form-control-sm" id="axeconfirm" style="display:none">
                                    <option value="">Choisissez l'axe</option>
                                    
                                </select>
                            
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="adminquartconfirm" class="form-control form-control-sm" id="adminquartconf" style="display:none">
                                    <option value="">Choisissez le quartier</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="adheuredep"
                                        style="display:none"
                                        id="adminheured">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" style="display:none"
                                        name="addepsiege"
                                        id="admindepsieg">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>

                        </div>
                        <div class="col-sm-4 text-center text-danger" style="display:none"
                            id="adminmessconf">
                            <p id="adminerreurMessconf"></p>
                        </div>
                        <div class="col-sm-6 text-center text-danger"
                                    id="billet" style="display:none">
                                <p id="billetSms"></p>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal" id="adminconfreset">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" id="vali" name="ordinaire" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" id="vali" name="epson" value="EPSON">

                            </div>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                     id="adminconfirmtran-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="admincTitletran"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'admincFormtran')); ?>
                        <input type="hidden" id="adminpasseptran" name="passeidtran">
                        <input type="hidden" id="adminclientidptran" name="clientidtran">
                        <input type="hidden" id="adminpascodeticktran" name="codedeptran">
                        <input type="hidden" id="admincodecpastran" name="codedepastran">
                        <input type="hidden" id="adminpasnomptran" name="passnomtran">
                        <input type="hidden" id="adminpasprenomptran" name="passprenomtran">
                        <input type="hidden" id="adminpascnibptran" name="passcnibtran">
                        <input type="hidden" id="adminpascontactptran" name="pascontacttran">
                        <input type="hidden" id="adminpasdateptran" name="passdatetran">
                        <input type="hidden" id="adminlieutran" name="adminlieudeltran">
                        <input type="hidden" id="admimtypetran" name="admintypeclienttran">
                        <input type="hidden" id="adcommentclienttran" name="admincommentclienttran">
                        <input type="hidden" id="adcaissepvend_tran" name="caispvendetran">
                        <input type="hidden" id="adcaissedpvend_tran" name="caisdpvendetran">
                        <input type="hidden" id="adcategotran" name="numcatetran">
                        <input type="hidden" id="dateventeconftran">
                        <input type="hidden" id="addirectidtran">
                        <input type="hidden" id="adminsiegselectconftran">
                        <input type="hidden" id="adminidtampoconftran">
                        <input type="hidden" id="axeligneconftran">
                        <input type="hidden" id="ligneconflgtran">
                        <input type="hidden" id="adconfheuretran">

                        <input type="hidden" id="addateconfirmetran">
                        <input type="hidden" id="admincodeconfitran" name="adcodeconfirmtran">
                        <input type="hidden" id="adlignehconftran" name="adlignhconftran">
                        <input type="hidden" id="adprogramconftran" name="adprogrammconftran">
                        <div class="col-sm-6 text-center text-danger" style="display:none"
                            id="adminmessageptran">
                            <p id="adminerreurMessageptran"></p>
                        </div>
                        <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="datactutran" name="dactuelletran">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <input class="form-control form-control-sm" type="text"
                                   name="codeconfirmadtran"
                                   id="admincodeconfirmtran"
                                   autocomplete="off" required
                                   placeholder="Entrez le code du ticket">
                            </div>
                            <div class="col-sm-4">
                                <span class="btn btn-success" type="button" id="adminconfirme_infotran">
                                    <i></i>Afficher les informations
                                </span>
                            </div>
                            
                        </div>
                        <p name="nomtran" id="adminnomptran"></p>
                        <p name="prenomtran" id="adminprenomptran"></p>
                        <p name="contacttran" id="admincontactptran"></p>
                        <p name="reftran" id="adminrefptran"></p>
                        <p name="directiontran" id="admindirectionptran"></p>
                        <p name="codectrantran" id="admincodecptran"></p>
                        <div class="form-group row">
                            
                            <div class="form-group col-sm-4">
                                <select name="axesconfirmtran" class="form-control form-control-sm" id="axeconfirmtran" style="display:none">
                                    <option value="">Choisissez l'axe</option>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <select class="form-control form-control-sm" name="depargaretran" id="depargarestran">
                                    <option value="">Choisissez la gare de depart</option></select>
                            </div>
                            <div class="form-group col-sm-4">
                                <select name="adminquartconfirmtran" class="form-control form-control-sm" id="adminquartconftran" style="display:none">
                                    <option value="">Choisissez le quartier</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="adheuredeptran"
                                    style="display:none"
                                    id="adminheuredtran">
                                    <option value="">Choisissez l'heure</option>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" style="display:none"
                                    name="addepsiegetran"
                                    id="admindepsiegtran">
                                    <option value="">Choisissez le numéro de siège</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 text-center text-danger" style="display:none"
                            id="adminmessconftran">
                            <p id="adminerreurMessconftran"></p>
                        </div>
                        <div class="col-sm-6 text-center text-danger"
                            id="billettran" style="display:none">
                            <p id="billetSmstran"></p>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                    data-dismiss="modal" id="adminconfresettran">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <input class="btn btn-success md-trigger" type="submit" id="valitran" name="ordinairetran" value="ORDINAIRE" disabled="">
                                <input class="btn btn-success md-trigger" type="submit" id="valitran" name="epsontran" value="EPSON">
                            </div>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="ticketallerfi-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="tafiTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true">
                                    <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'tafiForm')); ?>
                            <input type="hidden" id="pascompagniefid" name="clientcompfid">
                            <input type="hidden" id="rclientcpfid" name="cprclientfid">
                            <input type="hidden" id="prnclientcpfid" name="cpprclientfid">
                            <input type="hidden" id="cnibcpfid" name="cpcnibfid">
                            <input type="hidden" id="date_cnibcpfid" name="cpdate_cnibfid">
                            <input type="hidden" id="lieudelivrecpfid" name="cplieudelivrfid">
                            <input type="hidden" id="codelignefid" name="codelignfid">
                            <input type="hidden" id="nomlignefid" name="nomlignfid">
                            <input type="hidden" id="inter1fid" name="interv1fid">
                            <input type="hidden" id="inter2fid" name="interv2fid">
                            <input type="hidden" id="deplignefid" name="departlignefid">
                            <input type="hidden" id="lignehfid" name ="lignehrfid">
                            <input type="hidden" id="rtimefid" name="rtimefid">
                            <input type="hidden" id="programfid" name="progcodfid">
                            <input type="hidden" id="programfid1" name="progcodfid1">
                            <input type="hidden" id="dateprfid">
                            <input type="hidden" id="lignfid" name="lignedepafid">
                            <input type="hidden" id="herfid">
                            <input type="hidden" id="typegarefid">
                            <input type="hidden" id="catefid" name="catgoriefid">
                            <input type="hidden" id="pvendablefid" name="vendablefid">
                            <input type="hidden" id="dvendablefid" name="dpvendablefid">
                            <input type="hidden" id="nomitinfid" name="nomitinefid">
                            <input type="hidden" id="siegselectfid">
                            <input type="hidden" id="idtampofid">
                            <input type="hidden" id="siegselect1fid">
                            <input type="hidden" id="idtampo1fid">
                            <input type="hidden" id="siegselect2fid">
                            <input type="hidden" id="idtampo2fid">
                            <input type="hidden" id="codelignetransfid" name="codeligntransfid">
                            <input type="hidden" id="nomlignetransfid" name="nomligntransfid">
                            <input type="hidden" id="intertrans1fid" name="intervtrans1fid">
                            <input type="hidden" id="intertrans2fid" name="intervtrans2fid">
                            <input type="hidden" id="deplignetransfid" name="departlignetransfid">
                            <input type="hidden" id="deplignetrans1fid" name="departlignetrans1fid">
                            <input type="hidden" id="lignehtransfid" name ="lignehrtransfid">
                            <input type="hidden" id="rtimetransfid" name="rtimetransfid">
                            <input type="hidden" id="programtransfid" name="progcodtransfid">
                            <input type="hidden" id="programtransfid1" name="progcodtransfid1">
                            <input type="hidden" id="traprogramtransfid" name="traprogcodtransfid">
                            <input type="hidden" id="traintertrans1fid" name="traintervtrans1fid">
                            <input type="hidden" id="traintertrans2fid" name="traintervtrans2fid">
                            <input type="hidden" id="dateprtransfid">
                            <input type="hidden" id="ligntransfid" name="lignedepatransfid">
                            <input type="hidden" id="ligntrans1fid" name="lignedepatrans1fid">
                            <input type="hidden" id="ligntrans2fid" name="lignedepatrans2fid">
                            <input type="hidden" id="ligntrans3fid" name="lignedepatrans3fid">

                            <input type="hidden" id="hertransfid">
                            <input type="hidden" id="typegaretransfid">
                            <input type="hidden" id="catetransfid" name="catgorietransfid">
                            <input type="hidden" id="pvendabletransfid" name="vendabletransfid">
                            <input type="hidden" id="dvendabletransfid" name="dpvendabletransfid">
                            <input type="hidden" id="nomitintransfid" name="nomitinetransfid">
                            <input type="hidden" id="nomitintrans1fid" name="nomitinetrans1fid">
                            <input type="hidden" id="nomitintrans2fid" name="nomitinetrans2fid">
                            <input type="hidden" id="nomitintrans3fid" name="nomitinetrans3fid">
                            <input type="hidden" id="catetransitfid" name="catgorietransitfid">
                            <input type="hidden" id="siegselecttransfid">
                            <input type="hidden" id="idtampotransfid">
                            <input type="hidden" id="siegselect1fid">
                            <input type="hidden" id="idtampo1fid">
                            <input type="hidden" id="nbrtransfid" name="nombretransitefid">
                            <input type="hidden" id="gidtransfid" name="gidtransitefid">

                            <input type="hidden" id="catetransit1fid" name="catgorietransit1fid">

                            <input type="hidden" id="catetransit2fid" name="catgorietransit2fid">
                            <input type="hidden" id="gidtrans1fid" name="gidtransite1fid">
                            <input type="hidden" id="gidtrans2fid" name="gidtransite2fid">

                            <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actufid" name="dactuelfid">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="card-header text-center">Information sur le depart </div>
                            
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="smsdtfid">
                                    <p id="erreurSmsdtfid"></p>
                                </div>
                            
                                <div class="form-group row pt-1 pb-1">
                                    <label class="col-12 col-sm-3 col-form-label text-sm-right">Ticket</label>
                                    <div class="col-12 col-sm-8 col-lg-6 form-check mt-1">
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inlinefid" value="aller" id="allerfid" checked="" type="radio"><span class="custom-control-label">Aller</span>
                                        </label>
                                        <label class="custom-control custom-radio custom-control-inline">
                                        <input class="custom-control-input" name="radio-inlinefid" value="aller_retour" id="aller_retourfid" type="radio"><span class="custom-control-label">Aller_Retour</span>
                                        </label>
                                        
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="iddepfid">Départ</label>
                                        <select style="display:block" class="form-control form-control-sm" name="depargarefid" id="depargarefid">
                                            <? foreach ($garedeparts as $garedepart): ?>
                                                <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                    <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="arridfid">Arrivée</label>
                                        <select style="display:block" class="form-control form-control-sm" name="arrigarefid" id="arrsgarefid">
                                            <option value="">Choisissez l'arrivée</option>
                                            <? foreach ($garearrivees as $garearrivee): ?>
                                                <option value="<?= $garearrivee->code_gadest; ?>">
                                                    <?= $garearrivee->nom_gadest; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="idquartfid">Quartier</label>
                                        <select style="display:block" name="quartconfirmefid" class="form-control form-control-sm" id="quartierfid">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Date depart</label>
                                        <input class="form-control form-control-sm" type="date" name="datedepartfid" id="date_depheurefid">
                                    </div>
                                    
                                    <div class="card-header text-center" id="tranfid" style="display:none">Transite</div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block" id="hridfid">Heure</label>
                                        <select style="display:block" class="form-control form-control-sm" name="heuredeptfid" id="hdepartfid">
                                            <option value="">Choisissez départ</option>
                                            
                                        </select>
                                    </div>                   
                                    <div class="form-group col-sm-4">
                                        <label style="display:block;" id="sigidfid">Siège</label>
                                        <select style="display:block" class="form-control form-control-sm" name="passagersiegesfid" id="psiegesfid">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:block;" id="prix_axefid1">Prix</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixfid"
                                            style="display:block;" id="prix_axefid" autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Pour ordre(P/O) ou numero CV:</label>
                                        <input class="form-control form-control-sm" type="text" name="pourordre" required="" autocomplete="off">
                                    </div>
                                    <div class="col-sm-4 text-center text-danger" style="display:none"
                                        id="messfid">
                                        <p id="erreurMessfid"></p>
                                    </div>
                                                   
                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="itincodefid"
                                            id="itinecodefid">
                                    </div>
                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="lignetinerairesfid"
                                            id="lignetinerairefid">
                                    </div>

                                    <div> 
                                        <input class="form-control form-control-sm" type="hidden" name="itincodeesfid"
                                            id="itinecodesfid">
                                    </div>
                                    
                                    <div> 
                                        <label style="display:none" id="ligne1fid">Ligne transite1</label>
                                        <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitinerairesfid"
                                            id="lignesitinerairefid" disabled="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart1fid">Quartier</label>
                                        <select style="display:none" name="quartconfirme1fid" class="form-control form-control-sm" id="quartier1fid">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitinfid">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="heuredeptitinefid" id="hdepartitinefid">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="siegitinefid">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitinesfid" id="psiegesitinesfid">
                                            <option value="">Choisissez siège</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans1fid">Départ transite1</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare1fid" id="transitedepargare1fid">
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="prix_axetransfid1">Prix transit1</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixtransfid" style="display:none;" id="prix_axetransfid"
                                            autocomplete="off">
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin1fid">Ligne transite2</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminfid" id="idcheminsfid">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart2fid">Quartier</label>
                                        <select style="display:none" name="quartconfirme2fid" class="form-control form-control-sm" id="quartier2fid">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin1fid">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheurefid" id="idcheminsheurfid">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine1fid">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines1fid" id="psiegesitines1fid">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans2fid">Départ transite2</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare2fid" id="transitedepargare2fid">
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="prix_axetransitfid1">Prix transit2</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixtransitfid" style="display:none;" id="prix_axetransitfid"
                                        autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin2fid">Ligne transite3</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin1fid" id="idchemins1fid">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="idquart3fid">Quartier</label>
                                        <select style="display:none" name="quartconfirme3fid" class="form-control form-control-sm" id="quartier3fid">
                                                <option value="">Choisissez le quartier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin2fid">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure1fid" id="idcheminsheur1fid">
                                            <option value="">Choisissez heure départ</option>
                                            
                                        </select>
                                    </div>

                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine2fid">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines2fid" id="psiegesitines2fid">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans3fid">Départ transite3</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare3fid" id="transitedepargare3fid">
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="prix_axetransit1fid1">Prix transit3</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixtransit1fid" style="display:none;" id="prix_axetransit1fid"
                                            autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="arritin3d">Ligne transite4</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idchemin2fid" id="idchemins2fid">
                                            <option value="">Choisissez la ligne</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="heureitin3fid">Heure</label>
                                        <select style="display:none" class="form-control form-control-sm" name="idcheminheure2fid" id="idcheminsheur2fid">
                                            <option value="">Choisissez heure départ</option> 
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none;" id="siegitine3fid">Siège</label>
                                        <select style="display:none" class="form-control form-control-sm" name="passagersiegesitines3fid" id="psiegesitines3fid">
                                            <option value="">Choisissez le siège</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="iddeptrans4fid">Départ transite4</label>
                                        <select style="display:none" class="form-control form-control-sm" name="transitedepargare4fid" id="transitedepargare4fid">
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label style="display:none;" id="prix_axetransit2fid1">Prix transit4</label>
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'');" name="prixtransit2fid" style="display:none;" id="prix_axetransit2fid"
                                        autocomplete="off">
                                </div>
                                </div>
                                <div class="card-header text-center">Information du client</div>
                                <div class="row">
                                    <div class="form-group col-sm-4">
                                        <label>Type</label>
                                        <select class="form-control form-control-sm" name="typefid" id="cltypefid">
                                            <? foreach ($typesclients as $item): ?>
                                            <option value="<?=$item->nom_type;?>"><?=$item->nom_type;?></option>
                                            <?endforeach;?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Contact</label>
                                        <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                            name="rclient_contactfid"
                                            id="rnclient_contactfid"
                                            autocomplete="off" required
                                            placeholder="contact client">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Nom</label>
                                        <input class="form-control form-control-sm" type="text" name="rclientfid"
                                            id="rclientfid"
                                            autocomplete="off"
                                            placeholder="nom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Prénom</label>
                                        <input class="form-control form-control-sm" type="text" name="prclientfid"
                                            id="prnclientfid"
                                            autocomplete="off" 
                                            placeholder="prenom" required>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Cni ou Passport</label>
                                        <input class="form-control form-control-sm" type="text" name="cnibfid"
                                            id="cnibfid"
                                            autocomplete="off"
                                            placeholder="cni ou passport">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>Délivré(e)le</label>
                                        <input class="form-control form-control-sm" type="date" name="date_cnibfid" value="<?= mdate("%Y-%m-%d", now());?>"
                                            id="date_cnibfid">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label class="col-sm-4 text-left">Lieu</label>
                                        <input class="form-control form-control-sm" type="text" name="lieufid"
                                            id="lieudelivrefid"
                                            autocomplete="off"
                                            placeholder="lieu d'établissement">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="motiffid">Motif</label>
                                        <select class="form-control form-control-sm" name="commentclientfid" style="display:none"
                                                id="motifrefusfid">
                                            <option value="">Choisissez une cause</option>
                                            <option value="refus">refus</option>
                                            <option value="pas de contact">pas de contact</option>
                                            <option value="pas de cnib">pas de cnib</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label class="col-sm-4" style="display:none" id="docfid">numéro_document</label>
                                        <input class="form-control form-control-sm" type="text" name="documentfid"
                                            id="num_docfid" style="display:none" value=""
                                            autocomplete="off">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label style="display:none" id="docdelivrefid">Délivré(e)le</label>
                                        <input class="form-control form-control-sm" type="date" name="date_docfid" value="<?= mdate("%Y-%m-%d", now());?>"
                                        style="display:none" id="datedocdelfid">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="reset" id="idresetfid">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <input class="btn btn-success md-trigger" type="submit" name="ordinaire" value="ORDINAIRE" disabled="">
                                        <input class="btn btn-success md-trigger" type="submit" name="epson" value="EPSON" id="bottontickfid">
                                    
                                    </div>
                                </div>
                            
                            </div>
                        <?= form_close(); ?>
                        
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                        id="reserve-0" style="perspective: none">
                        
                        <div class="modal-content">
                            <div class="modal-header modal-header-colored">
                                <h3 class="modal-title" id="reTitle"></h3>
                                <button class="close modal-close" type="button"
                                        data-dismiss="modal" aria-hidden="true">
                                    <span class="mdi mdi-close text-white"></span>
                                </button>
                            </div>
                            <?= form_open("", array('class' => 'modal-body form', 'id' => 'reForm')); ?>
                            <input type="hidden" id="pfinvendable" name="finvendable">
                            <input type="hidden" id="siegfinvendable" name="debutpvendable">
                            <input type="hidden" id="directreserve" name="directreserv">
                            <input type="hidden" id="reserveheure" name="reserveheur">
                            <input type="hidden" id="gareid_reserve" name="gareid_reserv">
                            <input type="hidden" id="datereserve" name="datereserv">
                            <input type="hidden" id="reservetime" name="timereserve">
                            <input type="hidden" id="tarifattribtime" name="timereservetfb">
                            <input type="hidden" id="timeaxeid" name="axe_ident">
                            <input type="hidden" id="cpidnomcl" name="idnomclcp">
                            <input type="hidden" id="cpidprenomcl" name="idprenomclcp">
                            <input type="hidden" id="prixtick" name="ticketprix">
                            <input type="hidden" id="categbus" name="categoriebus">
                            <input type="hidden" id="lhreserve">
                            <input type="hidden" id="siegselectreserve">
                            <input type="hidden" id="idtamporeserve">
                            <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actueldate" name="dateactuel">                    
                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                                <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                                <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <label>Départ</label>
                                    <select class="form-control form-control-sm" name="depargare" id="depargarets">
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>Axe</label>
                                        <select name="axreserve" class="form-control form-control-sm" id="axereserve">
                                            <option value="">Choisissez l'axe</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Quartier</label>
                                        <select name="quartreserve" class="form-control form-control-sm" id="quartreser">
                                            <option value="">Choisissez le quartier</option>
                                        </select>
                                </div>
                                        
                                <div class="form-group col-sm-4">
                                <label>l'heure</label>
                                    <select class="form-control form-control-sm" name="hredepart"
                                            id="heuredepart">
                                        <option value="">Choisissez l'heure</option>
                                    
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                <label>siege</label>
                                    <select class="form-control form-control-sm" name="pasgsieges"
                                            id="passgsieges">
                                        <option value="">Choisissez siège</option>
                                    </select>
                                </div>
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                    id="messreserv">
                                    <p id="erreurMessreserv"></p>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Contact</label>
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');" id="idcontactcl"
                                        name="contactclient"
                                        autocomplete="off"
                                        placeholder="contact client">
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label>Nom</label>
                                    <input class="form-control form-control-sm" type="text" name="nomclient"
                                        autocomplete="off" id="idnomcl"
                                        placeholder="nom">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>Prénom</label>
                                    <input class="form-control form-control-sm" type="text" name="prenomclient"
                                        autocomplete="off" id="idprenomcl"
                                        placeholder="prenom">
                                </div>
                                <input type="hidden" name="codclient" id="idclientcomp">
                            </div>
                        
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" id="idreserv">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <input class="btn btn-success md-trigger" type="submit"
                                            value="RESERVER">
                                
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="confirm-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="confTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' => 'modal-body form', 'id' => 'confForm')); ?>
                        <input type="hidden" id="codelignecf" name="codeligncf">
                        <input type="hidden" id="nomlignecf" name="nomligncf">
                        <input type="hidden" id="intercf1" name="intervcf1">
                        <input type="hidden" id="intercf2" name="intervcf2">
                        <input type="hidden" id="deplignecf" name="departlignecf">
                        <input type="hidden" id="lignehcf" name ="lignehrcf">
                        <input type="hidden" id="rtimecf" name="rtimecf">
                        <input type="hidden" id="programcf" name="progcodcf">
                        <input type="hidden" id="programcf1" name="progcodcf1">
                        
                        <input type="hidden" id="tarifattribcf" name="tarifattribuercf">
                        <input type="hidden" id="dateprcf">
                        <input type="hidden" id="ligncf" name="lignedepacf">
                        <input type="hidden" id="hercf">
                        <input type="hidden" id="typegarecf">
                        <input type="hidden" id="catecf" name="catgoriecf">
                        <input type="hidden" id="pvendablecf" name="vendablecf">
                        <input type="hidden" id="dvendablecf" name="dpvendablecf">
                        <input type="hidden" id="nomitincf" name="nomitinecf">
                        <input type="hidden" id="prix_axecf" name="prixcf">
                        <input type="hidden" id="siegselectcf">
                        <input type="hidden" id="idtampocf">
                        <input type="hidden" id="siegselectcf2">
                        <input type="hidden" id="idtampocf2">
                        <input type="hidden" id="siegselectcf3">
                        <input type="hidden" id="idtampocf3">
                        <input type="hidden" id="codelignetranscf" name="codeligntranscf">
                        <input type="hidden" id="nomlignetranscf" name="nomligntranscf">
                        <input type="hidden" id="intertranscf1" name="intervtranscf1">
                        <input type="hidden" id="intertranscf2" name="intervtranscf2">
                        <input type="hidden" id="deplignetranscf" name="departlignetranscf">
                        <input type="hidden" id="deplignetranscf1" name="departlignetranscf1">
                        <input type="hidden" id="lignehtranscf" name ="lignehrtranscf">
                        <input type="hidden" id="rtimetranscf" name="rtimetranscf">
                        <input type="hidden" id="programtranscf" name="progcodtranscf">
                        <input type="hidden" id="traprogramtranscf" name="traprogcodtranscf">
                        <input type="hidden" id="traintertranscf1" name="traintervtranscf1">
                        <input type="hidden" id="traintertranscf2" name="traintervtranscf2">
                        <input type="hidden" id="dateprtranscf">
                        <input type="hidden" id="ligntranscf" name="lignedepatranscf">
                        <input type="hidden" id="ligntranscf1" name="lignedepatranscf1">
                        <input type="hidden" id="ligntranscf2" name="lignedepatranscf2">
                        <input type="hidden" id="ligntranscf3" name="lignedepatranscf3">

                        <input type="hidden" id="hertranscf">
                        <input type="hidden" id="typegaretranscf">
                        <input type="hidden" id="catetranscf" name="catgorietranscf">
                        <input type="hidden" id="pvendabletranscf" name="vendabletranscf">
                        <input type="hidden" id="dvendabletranscf" name="dpvendabletranscf">
                        <input type="hidden" id="nomitintranscf" name="nomitinetranscf">
                        <input type="hidden" id="nomitintranscf1" name="nomitinetranscf1">
                        <input type="hidden" id="nomitintranscf2" name="nomitinetranscf2">
                        <input type="hidden" id="nomitintranscf3" name="nomitinetranscf3">
                        <input type="hidden" id="prix_axetranscf" name="prixtranscf">
                        <input type="hidden" id="prix_axetransitcf" name="prixtransitcf">
                        <input type="hidden" id="catetransitcf" name="catgorietransitcf">
                        <input type="hidden" id="siegselecttranscf">
                        <input type="hidden" id="idtampotranscf">
                        <input type="hidden" id="siegselectcf1">
                        <input type="hidden" id="idtampocf1">
                        <input type="hidden" id="nbrtranscf" name="nombretransitecf">
                        <input type="hidden" id="gidtranscf" name="gidtransitecf">
                        <input type="hidden" id="idcompgcf" name="compgcf">
                        <input type="hidden" id="idcompgcf1" name="compgcf1">
                        <input type="hidden" id="idcompgcf2" name="compgcf2">
                        <input type="hidden" id="idcompgcf3" name="compgcf3">
                    
                        <input type="hidden" id="prix_axetransitcf1" name="prixtransitcf1">
                        <input type="hidden" id="catetransitcf1" name="catgorietransitcf1">

                        <input type="hidden" id="prix_axetransitcf2" name="prixtransitcf2">
                        <input type="hidden" id="catetransitcf2" name="catgorietransitcf2">
                        <input type="hidden" id="gidtranscf1" name="gidtransitecf1">
                        <input type="hidden" id="gidtranscf2" name="gidtransitecf2">
                        <input type="hidden" id="passep" name="passeid">
                        <input type="hidden" id="gidp" value="">
                        <input type="hidden" id="clientidp" name="clientid">
                        <input type="hidden" id="confheure">
                        <input type="hidden" id="dateconfirme">
                        <input type="hidden" id="catconfirme" name="catconfirm">
                        <input type="hidden" id="lignehconf" name="lignehrconf">
                        <input type="hidden" id="caissepvend_" name="caispvende">
                        <input type="hidden" id="caissedpvend_" name="caisdpvende">
                        <input type="hidden" id="passaxep_" name="passaaxep">
                        <input type="hidden" id="directid" name="directiond">
                        <input type="hidden" id="clientconfirmeid" name="clientconfirme">
                        <input type="hidden" name="cppasnompconf" id="pasnompconfcp">
                        <input type="hidden" name="cppasprenompconf" id="pasprenompconfcp">
                        <input type="hidden" name="cppascnibpconf" id="pascnibpconfcp">
                        <input type="hidden" name="cppasdatepconf" id="pasdatepconfcp">
                        <input type="hidden" name="lieupconf" id="lieucnibconf">
                        <input type="hidden" name="gareid" id="gareid_dep">
                        <input type="hidden" id="siegselectconf">
                        <input type="hidden" id="idtampoconf">
                        <input type="hidden" id="programconf" name="codproconf">
                        <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                        <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                        <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                        <div class="col-sm-6 text-center text-danger" style="display:none"
                            id="messagep">
                            <p id="erreurMessagep"></p>
                        </div>

                            <div class="form-group row">
                                <input type="hidden" value ="<?= mdate("%Y-%m-%d", now());?>" id="actuel" name="dateactuel">
                                <div class="form-group col-sm-6">
                                    <label>Code</label>
                                    <input class="form-control form-control-sm" type="text"
                                        name="codeconfirm"
                                        id="codeconfirm" required
                                        autocomplete="off"
                                        placeholder="Entrez le code du ticket">
                                </div>
                                <div class="form-group">
                                        <span class="btn btn-success" type="button" id="confirmer_infos">
                                            <i></i>Verification code
                                        </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                
                                <div class="form-group col-sm-4">
                                    <select class="form-control form-control-sm" name="depargare" id="depargare">
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>/<?= $garedepart->idsousgare; ?>">
                                                <?= $garedepart->nom_gaep; ?>/<?= $garedepart->nomsousgare; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <select name="axeconfirme" class="form-control form-control-sm" id="axeconf">
                                        <option value="">Choisissez l'axe</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                
                                </div>
                                <div class="form-group col-sm-4">
                                    <select name="quartconfirm" class="form-control form-control-sm" id="quartconf">
                                    <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                                
                            </div>
                            <div class="form-group row">
                                <div class="card-header text-center" id="trancf" style="display:none">Transite</div>
                                <div>
                                    <label style="display:none" id="lignecf1">Ligne transite1</label>
                                    <input class="form-control form-control-sm" style="display:none" type="text" name="lignesitinerairescf"
                                        id="lignesitinerairecf" disabled="">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="idquartcf1">Quartier transite1</label>
                                    <select style="display:none" name="quartconfirmecf1" class="form-control form-control-sm" id="quartiercf1">
                                        <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <select class="form-control form-control-sm" name="heuredep"
                                        id="heured" style="display:none">
                                        <option value="">Choisissez l'heure</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4"> 
                                    <select class="form-control form-control-sm"
                                            name="depsiege" style="display:none"
                                            id="depsieg">
                                        <option value="">Choisissez le numéro de siège</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4 text-center text-danger" style="display:none"
                                id="messconf">
                                <p id="erreurMessconf"></p>
                                </div>

                                <div> 
                                    <input class="form-control form-control-sm" type="hidden" name="itincodecf"
                                        id="itinecodecf">
                                </div>
                                <div> 
                                    <input class="form-control form-control-sm" type="hidden" name="lignetinerairescf"
                                        id="lignetinerairecf">
                                </div>

                                <div> 
                                    <input class="form-control form-control-sm" type="hidden" name="itincodeescf" id="itinecodescf">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="iddeptranscf1">Départ transite1</label>
                                    <select style="display:none" class="form-control form-control-sm" name="transitedepargarecf1" id="transitedepargarecf1">
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="arritincf1">Ligne transite2</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idchemincf" id="idcheminscf">
                                        <option value="">Choisissez la ligne</option>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="heureitincf">Heure transite2</label>
                                    <select style="display:none" class="form-control form-control-sm" name="heuredeptitinecf" id="hdepartitinecf">
                                        <option value="">Choisissez heure départ</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="siegitinecf">Siège transite2</label>
                                    <select style="display:none" class="form-control form-control-sm" name="passagersiegesitinescf" id="psiegesitinescf">
                                        <option value="">Choisissez siège</option>
                                    </select>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="iddeptranscf2">Départ transite2</label>
                                    <select style="display:none" class="form-control form-control-sm" name="transitedepargarecf2" id="transitedepargarecf2">
                                    </select>
                                </div>
                             
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="arritincf2">Ligne transite3</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idchemincf1" id="idcheminscf1">
                                        <option value="">Choisissez la ligne</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="idquartcf2">Quartier transite3</label>
                                    <select style="display:none" name="quartconfirmecf2" class="form-control form-control-sm" id="quartiercf2">
                                        <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="heureitincf1">Heure transite3</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idcheminheurecf" id="idcheminsheurcf">
                                        <option value="">Choisissez heure départ</option>
                                        
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none;" id="siegitinecf1">Siège transite3</label>
                                    <select style="display:none" class="form-control form-control-sm" name="passagersiegesitinescf1" id="psiegesitinescf1">
                                        <option value="">Choisissez le siège</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="iddeptranscf3">Départ transite3</label>
                                    <select style="display:none" class="form-control form-control-sm" name="transitedepargarecf3" id="transitedepargarecf3">
                                    </select>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="arritincf3">Ligne transite4</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idchemincf2" id="idcheminscf2">
                                        <option value="">Choisissez la ligne</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="idquartcf3">Quartier transite4</label>
                                    <select style="display:none" name="quartconfirmecf3" class="form-control form-control-sm" id="quartiercf3">
                                        <option value="">Choisissez le quartier</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="heureitincf2">Heure transite4</label>
                                    <select style="display:none" class="form-control form-control-sm" name="idcheminheurecf1" id="idcheminsheurcf1">
                                        <option value="">Choisissez heure départ</option>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label style="display:none;" id="siegitinecf2">Siège transite4</label>
                                    <select style="display:none" class="form-control form-control-sm" name="passagersiegesitinescf2" id="psiegesitinescf2">
                                        <option value="">Choisissez le siège</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label style="display:none" id="iddeptranscf4">Départ transite4</label>
                                    <select style="display:none" class="form-control form-control-sm" name="transitedepargarecf4" id="transitedepargarecf4">
                                    </select>
                                </div> 
                            </div>
                            <div class="card-header text-center">Information du client</div>
                            <div class="row">
                                <div class="form-group col-sm-4">
                                    <select class="form-control form-control-sm" name="typeclient">
                                        <? foreach ($typesclients as $item): ?>
                                        <option value="<?=$item->nom_type;?>"><?=$item->nom_type;?></option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9+]/g,'');"
                                        name="rcfclient_contact" placeholder="contact"
                                        id="pascontactpconf" style="display:none"
                                        autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="rcfclient"
                                        id="pasnompconf" style="display:none" placeholder="nom"
                                        autocomplete="off" required>
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="prcfclient"
                                        id="pasprenompconf" style="display:none" placeholder="prenom"
                                        autocomplete="off" required>
                                </div>
                            
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="cnibcf"
                                        id="pascnibpconf" style="display:none" placeholder="numéro cnib"
                                        autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="date" name="cfdate_cnib" placeholder="date"
                                        id="pasdatepconf" style="display:none" value ="<?= mdate("%Y-%m-%d", now());?>">
                                </div>
                                <div class="form-group col-sm-4">
                                    <input class="form-control form-control-sm" type="text" name="lieucf" placeholder="lieu"
                                        id="delivrelieu"
                                        autocomplete="off" style="display:none">
                                </div>
                                
                            </div>
                            
                            <div class="form-group row">
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="reset" id="confreset">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    
                                    <input class="btn btn-success md-trigger" type="submit" style="display:none" id="valid" name="ordinaire" value="ORDINAIRE" disabled="">
                                    <input class="btn btn-success md-trigger" type="submit" style="display:none" id="validep" name="epson" value="EPSON">
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                    
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-nifesthebesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">MANIFEST TICKET ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/manifesthebdoesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgaresc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trioexoesc-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitleexoesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormexoesc')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagexoesc">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgarexoesc" id="encaisgarsexoesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedexoesc" id="iddatedebutexoesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefexoesc" id="iddatefinexoesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidexoesc" id="idvendeusesexoesc">
                                    <option value="">Tous les guichetiers</option>
                                </select>
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="exopassagersesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE PASSAGERS ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/trinombrepassesc/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcompsesc">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="nomgaresesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1esc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2esc">
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptbgesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                        <h3 class="modal-title">ETATS DES BAGAGES ESCAL DECLARER</h3>
                        <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                        </button>
                    </div>
                
                        <?= form_open("Rapport/exerdeclarerbgesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                         <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdbgesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                        <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                        </option>
                                    <? endforeach; ?>
                                        </select>
                             </div>
                                <div class="form-group col-sm-4">
                            <label>GARE DEPART</label>
                            <select class="form-control form-control-sm" name="departgardbgesc">
                            <option value=""></option>
                            <? foreach ($garedepartcomp as $garedepart): ?>
                                <option value="<?= $garedepart->code_gaexp; ?>">
                                    <?= "{$garedepart->nom_gaep}"; ?></option>
                            <? endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedebutdbgesc">
                            </div> 
                            <div class="form-group col-sm-4">
                            <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefindbgesc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>LIGNE</label>
                                <select class="form-control form-control-sm" name="axelignedbgesc">
                                    <option value="">Toutes lignes</option>
                                    <? foreach ($lignes as $ligne): ?>
                                        <option value="<?= $ligne->ident_ligne; ?>">
                                            <?= $ligne->nom_ligne; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="button"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptbgesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION BAGAGES ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarerbgesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdcbgesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgardcbgesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutdcbgesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefindcbgesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignedcbgesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>"><?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptcr-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION COURRIERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoclarercourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrcl">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrcl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrcl">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrcl">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourscl" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrcl">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES COURRIERS DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exodeclarercourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrcld">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrcld">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrcld">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrcld">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourscld" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrcld">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptcresc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION COURRIERSESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoclarercourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrclesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrclesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrclesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrclesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursclesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrclesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptcresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES COURRIERSESCAL DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exodeclarercourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrcldesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrcldesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcrcldesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincrcldesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourscldesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrcldesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecaptbg-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION BAGAGES</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarerbg/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdcbg">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgardcbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutdcbg">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefindcbg">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignedcbg">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>"><?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES BAGAGES DECLARER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclarerbg/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdbg">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgardbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindbg">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedbg">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecapt-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES TICKETS DECLARER</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclarer/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagd">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgard">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutd"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefind"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligned" id="ligneaxed">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecapt-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION TICKETS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclarer/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgardc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdc"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindc"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptes-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitlees">RECAP EX MENSUEL TICKET ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerciceses/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compages">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgares">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutes">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefines">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignees" id="ligneaxees">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-declarrecaptes-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">ETATS DES TICKETS DECLARER ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerdeclareres/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdes">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgardes">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdes"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindes"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedes" id="ligneaxedes">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-clarrecapes-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">DECLARATION ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exerclareres/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagdces">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgardces">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutdces"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefindces"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignedces">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;DECLARER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reportesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgaresc" id="departgaridentifesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissieresc" id="idcaissiersesc">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc" id="ligneaxeesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL TICKET ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reporticketesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgaresc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reporesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepsesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormsesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgaresc" id="garidentifsesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissieresc" id="idscaissieresc">
                                            <option value="">Choississez guichetier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneesc" id="ligneaxesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgop-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitle"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormop')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagop">
                                        <option value=""></option>
                                            <? foreach($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <?endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagop" id="departgardpbagop">
                                    <option value=""></option>
                                    <?foreach($garedepartcomp as $garedepart):?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagop">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagop">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidop" id="idvendeuseop">
                                        <option value="">Choississez operateur</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagop">
                                        <option value="">Toutes lignes</option>
                                        <? foreach($lignes as $ligne): ?>
                                            <option value="<?=$ligne->ident_ligne; ?>">
                                                <?=$ligne->nom_ligne;?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgopesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitleesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopesc">
                                        <option value=""></option>
                                            <? foreach($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <?endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopesc" id="departgardpbagopesc">
                                    <option value=""></option>
                                    <?foreach($garedepartcomp as $garedepart):?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <?endforeach;?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopesc" id="idvendeuseopesc">
                                        <option value="">Choississez operateur</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach($lignes as $ligne): ?>
                                            <option value="<?=$ligne->ident_ligne; ?>">
                                                <?=$ligne->nom_ligne;?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgopgl-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitlegl"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopgl')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopgl">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?=$compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopgl" id="departgardpbagopgl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopgl">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopgl">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopgl" id="idvendeuseopgl">
                                        <option value="">Choississez operateur</option> 
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopgl">
                                        <option value="">Toutes lignes</option>
                                        <?foreach($lignes as $ligne):?>
                                            <option value="<?= $ligne->ident_ligne;?>">
                                                <?= $ligne->nom_ligne;?>
                                            </option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgopglesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="optitleglesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormopglesc')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagopglesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?=$compagnie->cle_compagnie;?>">
                                                    <?="{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach;?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagopglesc" id="departgardpbagopglesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?=$garedepart->code_gaexp;?>">
                                            <?="{$garedepart->nom_gaep}";?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutbagopglesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefinbagopglesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>OPERATEUR</label>
                                    <select class="form-control form-control-sm" name="vendeuseidopglesc" id="idvendeuseopglesc">
                                        <option value="">Choississez operateur</option> 
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignebagopglesc">
                                        <option value="">Toutes lignes</option>
                                        <?foreach($lignes as $ligne):?>
                                            <option value="<?= $ligne->ident_ligne;?>">
                                                <?= $ligne->nom_ligne;?>
                                            </option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-repor-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlereps"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickForms')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgar" id="garidentifs">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissier" id="idscaissier">
                                            <option value="">Choississez guichetier</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- recapitulatif global-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recap-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP GLOBAL TICKET</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reporticket/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgar">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>" data-garesid="<?= $garedepart->garesid; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>SOUS-GARE</label>
                                    <select class="form-control form-control-sm" name="sousgaretgl">
                                        <option value="">Toutes</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <!-- recapitulatif global courrier-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapglcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/recaptglcourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrgl">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrgl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>" data-garesid="<?= $garedepart->garesid; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>SOUS-GARE</label>
                                    <select class="form-control form-control-sm" name="sousgarecrgl">
                                        <option value="">Toutes</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrgl">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrgl">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursgl" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrgl">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapglcresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/recaptglcourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrglesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reportbag/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbg">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbg">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebg" id="ligneaxebg">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="form-recapbgesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP GLOBAL BAGAGE ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/reportbagesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbgesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbgesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbgesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbgesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebgesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-tri-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caiTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaisForms')); ?>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compag">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgar" id="encaisgar">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="dated"
                                    id="iddatedebut">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datef"
                                    id="iddatefin">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>OPERATEUR</label>
                                <select class="form-control form-control-sm" name="vendeuseid" id="idvendeuse">
                                    <option value="">Choississez operateur</option>
                                    
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivend' id="identvendeuse">
                            <input type="hidden" name='dbu' id="intdebut">
                            <input type="hidden" name='fin' id="intfin">
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- tri-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-report-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerep"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickForm')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgar" id="departgaridentif">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissier" id="idcaissiers">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-nifestheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">MANIFEST TICKET</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/manifesthebdo/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgar">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- recapitulatif-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapt-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP EX MENSUEL TICKET</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercices/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compag">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgar">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebut"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefin"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligne" id="ligneaxe">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbg-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="trpTitle">RECAP EX MENSUEL BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercicesbag/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbag">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbag">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbag">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbag">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebag" id="ligneaxebag">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL BAGAGEESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exercicesbagesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagbagesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarbagesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutbagesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinbagesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignebagesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!-- recapitulatif exo courrier-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptcr-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exocourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcr">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcr">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcr">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincr">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcours" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignecr">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptcresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">RECAP EX MENSUEL COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exocourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcresc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcresc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcresc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincresc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignecresc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courriermanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagheb">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarheb">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutheb">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinheb">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursheb" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneheb">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recapthebesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courriermanifesthebesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarhebesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebesc">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebesc">
                                    </div>
                                    <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcourshebesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebesc">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/bagagemanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebbg">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarhebbg">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebbg">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebbg">
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebbg">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-recaptbgescheb-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MANIFEST BAGAGEESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/bagageescmanifestheb/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">

                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compaghebbge">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarhebbge">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?>
                                                
                                            </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebuthebbge">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinhebbge">
                                    </div>
                                    
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axelignehebbge">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                                <option value="<?= $ligne->ident_ligne; ?>">
                                                    <?= $ligne->nom_ligne; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trio-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitle"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentForm')); ?>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compag">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgar" id="encaisgars">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="dated"
                                    id="iddatedebut">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datef"
                                    id="iddatefin">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseid" id="idvendeuses">
                                    <option value="">Choississez guichetier</option>
                                    
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivend' id="identvendeuse">
                            <input type="hidden" name='dbu' id="intdebut">
                            <input type="hidden" name='fin' id="intfin">
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-triobag-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitlebag"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormbag')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagbag">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgarbag" id="encaisgarsbag">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedbag">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefbag">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidbag" id="idvendeusesbag">
                                    <option value="">Choississez guichetier</option>
                                    
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-tri-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">TRI DES ETATS DE VERSEMENT</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Caisses/indexversement/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compag">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgar">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="dated"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datef"
                                    id="iddatefin">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseid" id="idvendeuse">
                                    <option value="">Tous les guichetiers</option>
                                    <? foreach ($nom_vendeuses as $nom_vendeuse): ?>
                                        <option value="<?= $nom_vendeuse->roleattribut; ?>">
                                            <?= $nom_vendeuse->username; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivend'>
                            <input type="hidden" name='dbu'>
                            <input type="hidden" name='fin'>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-tricr-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MODIFICATION DES VERSEMENTS COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Caisses/indexversementcr/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_crcompag">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgarcr">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedcr"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefcr"
                                    id="">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidcr" id="">
                                    <option value="">Tous les guichetiers</option>
                                    <? foreach ($nom_vendeuses as $nom_vendeuse): ?>
                                        <option value="<?= $nom_vendeuse->roleattribut; ?>">
                                            <?= $nom_vendeuse->username; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-tribg-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">MODIFICATION DES VERSEMENTS BAGAGE</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Caisses/indexversementbgs/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagbgs">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgarbgs">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedbgs"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefbgs">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidbgs">
                                    <option value="">Tous les guichetiers</option>
                                    <? foreach ($nom_vendeuses as $nom_vendeuse): ?>
                                        <option value="<?= $nom_vendeuse->roleattribut; ?>">
                                            <?= $nom_vendeuse->username; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-arch-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">ARCHIVAGE</h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                    class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Historique_Passagers/archivre/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="archivrecompag">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Départ</label>
                                <select class="form-control form-control-sm" name="archivredepargare">
                                <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepartcp): ?>
                                        <option value="<?= $garedepartcp->code_gaexp; ?>">
                                            <?= $garedepartcp->nom_gaep; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="debutenreg"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="finenreg"
                                    id="iddatefin">
                            </div>
                            
                            <input type="hidden" name='dbu'>
                            <input type="hidden" name='fin'>
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-archcr-1" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="">ARCHIVAGE COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Historique_Passagers/archivrecr/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="archivrecompagcr">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>Départ</label>
                                <select class="form-control form-control-sm" name="archivredepargarecr">
                                <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepartcp): ?>
                                        <option value="<?= $garedepartcp->code_gaexp; ?>">
                                            <?= $garedepartcp->nom_gaep; ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="debutenregcr"
                                    >
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="finenregcr"
                                    id="">
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div> 
                <!--report courrier-->
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-triocour-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitlecour"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormcour')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagcour">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgarcour" id="encaisgarscour">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedcour"
                                    id="iddatedebutcour">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefcour"
                                    id="iddatefincour">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidcour" id="idvendeusescour">
                                    <option value="">Tous les guichetiers</option>
                                    
                                </select>
                            </div>
                            
                            
                            <input type="hidden" name='ivendcour' id="identvendeusecour">
                            <input type="hidden" name='dbucour' id="intdebutcour">
                            <input type="hidden" name='fincour' id="intfincour">
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <!--depense courrier -->

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reportcour-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepscourdep"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickFormscourdep')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcourdep">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcourdep" id="garidentifscourdep">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?="{$garedepart->nom_gaep}"; ?>
                                                
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutcourdep"
                                            id="">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefincourdep"
                                            id="">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissiercourdep" id="idscaissiercourdep">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-reportversgl-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlerepversgl"></h3>
                            <button class="close modal-close" type="button"
                            data-dismiss="modal" aria-hidden="true"><span
                            class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'tickversglForm')); ?>
                            <div class="form-group row">
                                <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagversgl">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarversgl" id="departgaridentifversgl">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                            
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedebutversgl">
                                    </div> 
                                    <div class="form-group col-sm-4">
                                    <label>AU</label>
                                        <input class="form-control form-control-sm" type="date" name="datefinversgl">
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>GUICHETIERS</label>
                                        <select class="form-control form-control-sm" name="caissierversgl" id="idcaissiersversgl">
                                            <option value="">Tous les guichetiers</option>
                                            
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-4">
                                        <label>LIGNE</label>
                                        <select class="form-control form-control-sm" name="axeligneversgl" id="ligneaxeversgl">
                                            <option value="">Toutes lignes</option>
                                            <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-secondary modal-close" type="button"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                        </button>
                                        <button class="btn btn-success md-trigger" type="submit"
                                                data-dismiss="modal">
                                            <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                        </button>
                                    </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="form-trioexo-0" style="perspective: none;">
                    
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="caisTitleexo"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'encaismentFormexo')); ?>
                        <div class="form-group row">
                            <input class="form-control form-control-sm" type="hidden" name="gareconnect" value="<?=$bus_stop->idengare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="userconnected" value="<?=$conex->roleattribut;?>">
                            <input class="form-control form-control-sm" type="hidden" name="sousgareconnect" value="<?=$bus_stop->idsousgare;?>">
                            <input class="form-control form-control-sm" type="hidden" name="compconnected" value="<?=$conex->cpuser_id;?>">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="_compagexo">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="departgarexo" id="encaisgarsexo">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?>
                                    
                                    </option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DU</label>
                                <input class="form-control form-control-sm" type="date" name="datedexo"
                                    id="iddatedebutexo">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="datefexo"
                                    id="iddatefinexo">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GUICHETIERS</label>
                                <select class="form-control form-control-sm" name="vendeuseidexo" id="idvendeusesexo">
                                    <option value="">Tous les guichetiers</option>
                                    
                                </select>
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reportplis-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglob"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobForms')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpli">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="deptgaresidpli" id="gares">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspli">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspli">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpli" id="idcaisse">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspli" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespli">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reportplisesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobesc"></h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpliesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="deptgaresidpliesc" id="garesesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspliesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspliesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpliesc" id="idcaisseesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspliesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespliesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexopli-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobvers"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsvers')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnplivers">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="deptgaresidplivers" id="garesvers">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutsplivers">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinsplivers">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidplivers" id="idcaissevers">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_coursplivers" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexopliesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversesc"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpliversesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="deptgaresidpliversesc" id="garesversesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedeparts as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspliversesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspliversesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpliversesc" id="idcaisseversesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="types_courspliversesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                                $ty2 = 'COLIS';
                                                if($typs->categ === 'Gros_plis'){
                                                    $ty3 = $ty2;
                                                }elseif($typs->categ === 'Petit_plis'){
                                                $ty3 = $ty;}?>

                                            <option value="<?= $typs->categ; ?>">
                                                <?= $ty3; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexobag-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversbg"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversbg')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagexobg">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarexobg" id="departgarexobge">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedexobg">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefexobg">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="vendeuseidexobg" id="dvendeuseidexobg">
                                        <option value="">Choississez guichetier</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-trioexobagesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobversbgesc"></h3>
                            <button class="close modal-close" type="button" data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsversbgesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagexobgesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarexobgesc" id="departgarexobgeesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datedexobgesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefexobgesc">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="vendeuseidexobgesc" id="dvendeuseidexobgesc">
                                        <option value="">Choississez guichetier</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reporcourglo-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobg"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsg')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnplig">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="deptgaresidplig" id="garesg">
                                    <option value="">Toutes gares</option>
                                        <? foreach ($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutsplig">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinsplig">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidplig" id="idcaisseg">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>Type courriers</label>
                                    <select name="types_coursplig" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignesplig">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                 id="form-reporcourgloesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title" id="Titlexpglobgesc"></h3>
                            <button class="close modal-close" type="button"
                                    data-dismiss="modal" aria-hidden="true"><span
                                        class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        <?= form_open("", array('class' =>'modal-body form', 'id' => 'expglobFormsgesc')); ?>
                           <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagnpligesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                                <option value="<?= $compagnie->cle_compagnie; ?>">
                                                    <?= "{$compagnie->nom_compagnie}"; ?>
                                                </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="deptgaresidpligesc" id="garesgesc">
                                    <option value="">Toutes gares</option>
                                        <? foreach($garedepartcomp as $garedepart): ?>
                                            <option value="<?= $garedepart->code_gaexp; ?>">
                                                <?= $garedepart->nom_gaep; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                        <label>DU</label>
                                        <input class="form-control form-control-sm" type="date" name="datesdebutspligesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                    <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datesfinspligesc">
                                </div>
                                 <div class="form-group col-sm-4">
                                    <label>GUICHETIERS</label>
                                    <select class="form-control form-control-sm" name="caissesidpligesc" id="idcaissegesc">
                                        <option value="">Tous les guichetiers</option>
                                        
                                    </select>
                                </div>
                               
                                <div class="form-group col-sm-4">
                                    <label>Type courriers</label>
                                    <select name="types_courspligesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignespligesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>

                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="exopassagers-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE PASSAGERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepass/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcomps">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="nomgares">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2">
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="exopassagersgl-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE PASSAGERS</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepassglob/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcomps">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="nomgares">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2">
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                id="exopassagersglesc-0" style="perspective: none;">
                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE PASSAGERS ESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                     <?= form_open("Rapport/trinombrepassglobesc/{$this->session->company->ekey}", array('class' => 'modal-body form')); ?>

                        <div class="form-group row">
                            <div class="form-group col-sm-4">
                                <label>COMPAGNIE</label>
                                    <select class="form-control form-control-sm" name="nomcompsesc">
                                    <option value=""></option>
                                        <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                                <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>GARE DEPART</label>
                                <select class="form-control form-control-sm" name="nomgaresesc">
                                <option value=""></option>
                                <? foreach ($garedepartcomp as $garedepart): ?>
                                    <option value="<?= $garedepart->code_gaexp; ?>">
                                        <?= "{$garedepart->nom_gaep}"; ?></option>
                                <? endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-4">
                                <label>DATE: DU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps1esc">
                            </div>
                            <div class="form-group col-sm-4">
                                <label>AU</label>
                                <input class="form-control form-control-sm" type="date" name="dateps2esc">
                            </div>
                            
                        </div>
                        <div class="form-group row">
                            <div class="modal-footer">
                                <button class="btn btn-secondary modal-close" type="reset"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                </button>
                                <button class="btn btn-success md-trigger" type="submit"
                                        data-dismiss="modal">
                                    <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;RECHERCHER&nbsp;
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="exocourriers-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoscourrier/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrex">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrex">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrex">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrex">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursex" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglex">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="exocourriersesc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">EXERCICE LISTE COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/exoscourrieresc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrexesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrexesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrexesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrexesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursexesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriers as $typs): ?>
                                            <? $ty = 'PLIS';
                                            $ty2 = 'COLIS';
                                            if($typs->categ === 'Gros_plis'){
                                                $ty3 = $ty2;
                                            }elseif($typs->categ === 'Petit_plis'){
                                            $ty3 = $ty;}?>

                                        <option value="<?= $typs->categ; ?>">
                                            <?= $ty3; ?>
                                        </option>
                                    <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglexesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="triglcourrier-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE COURRIER</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courrierglob/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglb">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrglb">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglb">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglb">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglb" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglb">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
                <div class="modal-container colored-header colored-header-success custom-width modal-effect-7"
                    id="triglcourrieresc-0" style="perspective: none;">

                    <div class="modal-content">
                        <div class="modal-header modal-header-colored">
                            <h3 class="modal-title">LISTE GLOBALE COURRIERESCAL</h3>
                            <button class="close modal-close" type="button"
                                data-dismiss="modal" aria-hidden="true"><span
                                class="mdi mdi-close text-white"></span>
                            </button>
                        </div>
                        
                        <?= form_open("Rapport/courrierglobesc/{$this->session->company->ekey}/{$bus_stop->idengare}", array('class' => 'modal-body form')); ?>
                            <div class="form-group row">
                                <div class="form-group col-sm-4">
                                    <label>COMPAGNIE</label>
                                        <select class="form-control form-control-sm" name="_compagcrglbesc">
                                        <option value=""></option>
                                            <? foreach ($compagnies as $compagnie): ?>
                                            <option value="<?= $compagnie->cle_compagnie; ?>">
                                            <?= "{$compagnie->nom_compagnie}"; ?>
                                            </option>
                                            <? endforeach; ?>
                                        </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>GARE DEPART</label>
                                    <select class="form-control form-control-sm" name="departgarcrglbesc">
                                    <option value=""></option>
                                    <? foreach ($garedepartcomp as $garedepart): ?>
                                        <option value="<?= $garedepart->code_gaexp; ?>">
                                            <?= "{$garedepart->nom_gaep}"; ?></option>
                                    <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label>DU</label>
                                    <input class="form-control form-control-sm" type="date" name="datedebutcrglbesc">
                                </div> 
                                <div class="form-group col-sm-4">
                                <label>AU</label>
                                    <input class="form-control form-control-sm" type="date" name="datefincrglbesc">
                                </div>
                                
                                <div class="form-group col-sm-4">
                                    <label>TYPE_COURRIERS</label>
                                    <select name="typcoursglbesc" class="form-control form-control-sm">
                                        <option value ="">Choisissez le type</option>
                                        <? foreach ($typecourriersgl as $typs): ?>
                                            <option value="<?= $typs->categ; ?>">
                                                <?= $typs->categ; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group col-sm-4">
                                    <label>LIGNE</label>
                                    <select class="form-control form-control-sm" name="axelignecrglbesc">
                                        <option value="">Toutes lignes</option>
                                        <? foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne->ident_ligne; ?>">
                                                <?= $ligne->nom_ligne; ?>
                                            </option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary modal-close" type="button"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-undo"></i>&nbsp;ANNULER&nbsp;
                                    </button>
                                    <button class="btn btn-success md-trigger" type="submit"
                                            data-dismiss="modal">
                                        <i class="icon icon-left mdi mdi-check-all"></i>&nbsp;EXPORTER&nbsp;
                                    </button>
                                </div>
                            </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
