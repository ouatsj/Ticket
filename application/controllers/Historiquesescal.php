<?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Historiquesescal extends MY_Controller
    {
        public $property = array(
            'title' => 'Historiquesescal',
            'UPDATE_SUCCESS' => FALSE,
            'INSERT_SUCCESS' => FALSE,
        );
        public $company;
        
        
        public function __construct()
        {
            parent::__construct();
            setlocale(LC_TIME, 'fr_FR', 'fra');
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }

        /**
         * Reçu courrier escale 57×40 mm — 3 exemplaires (POSPrinter).
         */
        protected function _print_couresc_57x40()
        {
            if (empty($this->property['single'])) {
                $bus = !empty($this->property['bus_stop']) ? $this->property['bus_stop'] : null;
                $conex = !empty($this->property['conex']) ? $this->property['conex'] : null;
                $this->session->set_flashdata(
                    'error',
                    'Reçu courrier introuvable — l’envoi n’a peut‑être pas été enregistré. Réessayez l’envoi ou la réimpression.'
                );
                if ($bus && $conex && function_exists('role17_is_agent') && role17_is_agent()) {
                    redirect(
                        'ventescales/voirreimpri/' . $this->session->company->ekey . '/'
                        . $conex->roleattribut . '/' . $bus->idengare . '/' . $bus->idsousgare
                        . '?tab=courrier'
                    );
                    return;
                }
                if ($bus && $conex) {
                    redirect(
                        'confirmation/courrierescales/' . $this->session->company->ekey . '/'
                        . $conex->roleattribut . '/' . $bus->idengare . '/' . $bus->idsousgare
                    );
                    return;
                }
                redirect('gares');
                return;
            }
            if (function_exists('role17_is_agent') && role17_is_agent()) {
                $this->property['role17_mode'] = true;
                if (!empty($this->property['conex']) && !empty($this->property['bus_stop'])) {
                    $this->property = role17_inject_property(
                        $this->property,
                        $this->property['conex']->roleattribut,
                        !empty($this->property['bus_stop']->idengare)
                            ? $this->property['bus_stop']->idengare
                            : ''
                    );
                }
            }
            $this->property['layout_print'] = TRUE;
            return $this->layout->view('_tickets/pdfepsoncouresc', $this->property);
        }
        
        /**
         *
         */
        public function editpdfesc($ckey, $coli_id, $ex, $des, $tds, $tr, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $cpus);
                $this->property['conex'] = $conex;

            if($tds === 'personnel' AND $tds !== 'Adulte' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tr === 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getper($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }
            elseif($tds === 'personnel' AND $tds !== 'membre' AND $tds !== 'Adulte' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tr !== 'personnel')
            {
               $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getper($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'partenaire_specifique' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'membre' AND $tds !== 'partenaire_specifique' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel' AND $tr !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'membre' AND $tds !== 'partenaire_specifique' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel' AND $tr === 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }
            elseif($tds === 'partenaire_client' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_specifique' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'partenaire_simple' AND $tds !== 'membre' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }
        
            elseif($tds === 'Adulte' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'personnel' AND $tr !== 'personnel')
            {

                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }

            elseif($tds === 'Adulte' AND $tds !== 'partenaire_simple' AND $tds !== 'membre' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'personnel' AND $tr === 'personnel')
            {

                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }
            else
            {
                // Types non reconnus → clients classiques (évite reçu perdu / page vide).
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                $this->property['single'] = $this->courriers;
                $this->expediteurs = $this->m_expediteur->getcl($ex);
                if (!$this->expediteurs) {
                    $this->expediteurs = $this->m_expediteur->getper($ex);
                }
                $this->property['exped'] = $this->expediteurs;
                $this->destinateurs = $this->m_recepteur->getcl($des);
                if (!$this->destinateurs) {
                    $this->destinateurs = $this->m_recepteur->getper($des);
                }
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

        }

        /*public function reditpdfesc($ckey, $coli_id, $ex, $des, $tds, $g, $cpus, $idsg)
        {


            $tyr = $this->db->query("SELECT cl.type_client, re.client_recept FROM client cl
                JOIN recepteurs re ON re.client_recept = cl.id_client
                WHERE re.idrecepetion = '$des'")->row();

            
            if($tyr === NULL){

                $tr = 'personnel';
            }
            else
            {
                
                $tr = $tyr->type_client;
            }
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $cpus);
                $this->property['conex'] = $conex;

            if($tds === 'personnel' AND $tds !== 'Adulte' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tr === 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getper($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }
            elseif($tds === 'personnel' AND $tds !== 'membre' AND $tds !== 'Adulte' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tr !== 'personnel')
            {
               $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getper($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'partenaire_specifique' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'membre' AND $tds !== 'partenaire_specifique' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel' AND $tr !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'membre' AND $tds !== 'partenaire_specifique' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel' AND $tr === 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }
            elseif($tds === 'partenaire_client' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_specifique' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'partenaire_simple' AND $tds !== 'membre' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }
        
            elseif($tds === 'Adulte' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'personnel' AND $tr !== 'personnel')
            {

                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }

            elseif($tds === 'Adulte' AND $tds !== 'partenaire_simple' AND $tds !== 'membre' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'personnel' AND $tr === 'personnel')
            {

                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }

        }*/

        public function reditpdfesc($ckey, $coli_id, $dpcoli_id, $ex, $des, $tds, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);

            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
            if (!$bus_stop) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['bus_stop'] = $bus_stop;
            $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $cpus);
            if (!$conex && $this->session->userdata('agent')) {
                $conex = $this->m_compte_user->getusergare(
                    $this->company->ekey,
                    $g,
                    $this->session->agent->roleattribut
                );
            }
            if (!$conex) {
                roleattribut_guard_fail_redirect_home($this->company->ekey);
                return;
            }
            $this->property['conex'] = $conex;

            $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
            if (!$this->courriers && $dpcoli_id) {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition1(
                    $this->company->ekey,
                    $dpcoli_id,
                    $coli_id
                );
            }
            $this->property['single'] = $this->courriers;

            $this->expediteurs = $this->m_expediteur->getcl($ex);
            if (!$this->expediteurs) {
                $this->expediteurs = $this->m_expediteur->getper($ex);
            }
            $this->property['exped'] = $this->expediteurs;

            $this->destinateurs = $this->m_recepteur->getcl($des);
            if (!$this->destinateurs) {
                $this->destinateurs = $this->m_recepteur->getper($des);
            }
            $this->property['destin'] = $this->destinateurs;

            $this->_print_couresc_57x40();
        }

        public function editpdfreimp($ckey, $coli_id, $copg, $ex, $des, $tds, $g, $cpus, $idsg)
        {
            
            $tyr = $this->db->query("SELECT cl.type_client, re.client_recept FROM client cl
                JOIN recepteurs re ON re.client_recept = cl.id_client
                WHERE re.idrecepetion = '$des'")->row();

            
            if($tyr === NULL){

                $tr = 'personnel';
            }
            else
            {
                
                $tr = $tyr->type_client;
            }
            
            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $cpus);
                $this->property['conex'] = $conex;

            if($tds === 'personnel' AND $tds !== 'Adulte' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tr === 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getper($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }
            elseif($tds === 'personnel' AND $tds !== 'membre' AND $tds !== 'Adulte' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tr !== 'personnel')
            {
               $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getper($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'partenaire_specifique' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'membre' AND $tds !== 'partenaire_specifique' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel' AND $tr !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'membre' AND $tds !== 'partenaire_specifique' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'Adulte' AND $tds !== 'personnel' AND $tr === 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }
            elseif($tds === 'partenaire_client' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_specifique' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }

            elseif($tds === 'partenaire_simple' AND $tds !== 'membre' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'Adulte' AND $tds !== 'personnel')
            {
                $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;
                $this->_print_couresc_57x40();
            }
        
            elseif($tds === 'Adulte' AND $tds !== 'membre' AND $tds !== 'partenaire_simple' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'personnel' AND $tr !== 'personnel')
            {

                $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }

            elseif($tds === 'Adulte' AND $tds !== 'partenaire_simple' AND $tds !== 'membre' AND $tds !== 'partenaire_client' AND $tds !== 'partenaire_specifique' AND $tds !== 'personnel' AND $tr === 'personnel')
            {

                $this->courriers = $this->m_courrier_expedieresc->getexpedition1($this->company->ekey, $copg, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getcl($ex);
                $this->property['exped'] = $this->expediteurs;

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
            }
           
        }

        public function editpdfperso($ckey, $coli_id, $ex, $des, $g, $cpus, $idsg)
        {

            $tyr = $this->db->query("SELECT cl.type_client, re.client_recept FROM client cl
                JOIN recepteurs re ON re.client_recept = cl.id_client
                WHERE re.idrecepetion = '$des'")->row();

            $this->company = $this->m_entreprises->get_key($ckey);

            
            $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                        $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $cpus);
                $this->property['conex'] = $conex;
            
                $this->courriers = $this->m_courrier_expedieresc->getexpedition($this->company->ekey, $coli_id);
                    $this->property['single'] = $this->courriers;

                $this->expediteurs = $this->m_expediteur->getper($ex);
                $this->property['exped'] = $this->expediteurs;

                if($tyr === NULL){

                $this->destinateurs = $this->m_recepteur->getper($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
           
            }
            else
            {
                
               $this->destinateurs = $this->m_recepteur->getcl($des);
                $this->property['destin'] = $this->destinateurs;

                $this->_print_couresc_57x40();
           
            }
           
        }

        public function courescalepsonrap($ckey, $idconnex, $n, $g, $cpus, $idsg)
        {
            $this->company = $this->m_entreprises->get_key($ckey);
                    $comp = $this->input->post('_compag');

                $ncomp = $this->m_compagnies->getn($comp);
                $this->property['ncomp'] = $ncomp;
                $bus_stop = $this->m_sousgare->sget($this->company->ekey, $g, $idsg);
                $this->property['bus_stop'] = $bus_stop;
                $conex = $this->m_compte_user->getusergare($this->company->ekey, $g, $cpus);
                $this->property['conex'] = $conex;

               
                $this->property['reponsexp'] = $this->m_courrier_expedieresc->rapexp($this->company->ekey, $idconnex, $comp, $g, $idsg);

            $this->layout->view('_tickets/pdfeprapcour', $this->property);
        }
    }
    
    /** End of file: Historiquesescal.php **/
    /** File location: application/controllers/Historiquesescal.php **/
