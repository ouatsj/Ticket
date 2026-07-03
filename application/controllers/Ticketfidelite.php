<?php defined('BASEPATH') OR exit('No direct script access allowed');
    
    // include the main labraries TCPDF
    require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');

    class Ticketfidelite extends CI_Controller
    {
        public $property = array('title' => 'FIDELITE');
        public $entreprise = stdClass::class;
        public $req;
        
        public function __construct()
        {
            parent::__construct();
            $this->property['update_success'] = FALSE;
            $this->property['INSERT'] = FALSE;
            $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
        }
        
        
        //print A4 ticket aller
        public function printpdf($ckey, $code_id, $hr)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            if($hr === '0')
            {
                $reponse = $this->m_passager->reduction($this->entreprise->ekey, $code_id, $hr);
                $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                              $heures = sprintf("%02d:%02d", $heur, $secondes);
                              //$heures = $heur.':'.$secondes;
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                                    
                              //$heures = $heur.':'.$secondes;
                              $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                              $heures = sprintf("%02d:%02d", $heur, $secondes);      
                              //$heures = $heur.':'.$secondes;

                        }
            
                $nom = $reponse->code_progr;
                $nge = substr($nom, 6, 6);
    
                $dat = explode("-", $reponse->date_progr);
                $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];

                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->setCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('TICKET-' . $reponse->nom_client);
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                //remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(FALSE);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // add a page
                $pdf->AddPage('P', 'A4');
                
                //----------------------------------------------------------------------------
                
                // GROUPE DE GAUCHE
                
                $pdf->SetFont('Courier', 'B', 10);
                
                $pdf->MultiCell(60, 0, '' . $reponse->code_ticket, 0, 'L', 0, 0, 4, 20, true);
                
                /* AXE ET PRIX */
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(40, 0, '' . $reponse->nom_ligne, 0, 'L', 0, 0, 4, 25, true);
                $pdf->SetFont('Courier', 'B', 10);
                
                /* DATE ET HEURE */
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare, 0, 'L', 0, 0, 4, 30, true);
                
                $pdf->MultiCell(30, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 4, 35, true);
                $pdf->SetFont('courier', 'B', 10);
                $pdf->MultiCell(60, 0, '' .$reponse->quart, 0, 'L', 0, 0, 15, 35, true);
                $pdf->MultiCell(50, 0, '' . $day, 0, 'L', 0, 0, 4, 40, true);
                
                $pdf->MultiCell(35, 0, '' . $heures, 0, 'L', 0, 0, 27, 40, true);
                /* NOM */$pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(70, 0, '' . $reponse->nom_client, 0, 'L', 0, 0, 4, 45, true);
                /* PRENOM */
                $pdf->MultiCell(70, 0, '' . $reponse->prenom_client, 0, 'L', 0, 0, 4, 50, true);
                /* TELEPHONE */
                $pdf->MultiCell(75, 0, '' . $reponse->contact_client, 0, 'L', 0, 0, 4, 55, true);
                $pdf->SetFont('Courier', '', 7);
                $pdf->MultiCell(35, 0, 'TICKET GRATUIT', 0, 'L', 0, 0, 4, 60, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(25, 0, $nge, 0, 'L', 0, 0, 27, 60, true);
                
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 4, 65, true);
                
                // - - - - - - -  GROUPE DE DROITE  - - - - - - - - -
                /* AXE ET CODE ET SIEGE NOM ET PRENOM */
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(100, 0, $reponse->nom_client.' '.$reponse->prenom_client, 0, 'L', 0, 0, 60, 20, true);
                $pdf->setFont('courier', 'BI', 7);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 140, 20, true);
                $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 169, 20, true);
                $pdf->SetFont('courier', '', 10);
                $pdf->MultiCell(60, 0, 'CODE:', 0, 'L', 0, 0, 60, 25, true);
                $pdf->SetFont('Helvetica', 'BI', 12);
                $pdf->MultiCell(50, 0, '' .$reponse->code_ticket, 0, 'L', 0, 0, 75, 25, true);
                $pdf->SetFont('Courier', '', 11);
                $pdf->MultiCell(16, 0, 'SIEGE:', 0, 'L', 0, 0, 140, 25, true);
                $pdf->SetFont('Courier', 'BI', 12);
                $pdf->MultiCell(15, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 160, 25, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(20, 0, 'AXE: ', 0, 'L', 0, 0, 60, 30, true);
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->MultiCell(60, 0, '' . $reponse->nom_ligne, 0, 'L', 0, 0, 75, 30, true);
                $pdf->SetFont('courier', '', 10);
                $pdf->MultiCell(60, 0, 'QUARTIER:', 0, 'L', 0, 0, 140, 30, true);
                $pdf->SetFont('courier', 'B', 12);
                $pdf->MultiCell(60, 0, '' .$reponse->quart, 0, 'L', 0, 0, 165, 30, true);
                /* DATE ET HEURE ET PRIX */
                $pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 60, 35, true);
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 83, 35, true);
                $pdf->SetFont('courier', '', 10);
                $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 140, 35, true);
                $pdf->SetFont('helvetica', 'B', 10);
                
                $pdf->MultiCell(20, 0, $heures, 0, 'L', 0, 0, 160, 35, true);
                $pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 140, 40, true);
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(35, 0, 'TICKET GRATUIT', 0, 'L', 0, 0, 153, 40, true);
                /* NOM ET TELEPHONE */
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 60, 40, true);
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->MultiCell(60, 0, $reponse->contact_client, 0, 'L', 0, 0, 75, 40, true);
                /* CONVOCATION */
                $pdf->setFont('Courier', 'B', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->MultiCell(120, 0, 'CONVOCATION 45 mn avant le départ' , 0, 'C', 0, 0, 75, 45, true);
                /* BILLET NON REMBOURSABLE */
                $pdf->SetTextColor(0, 0, 0);
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->SetFillColor(255, 125, 60);
                $pdf->MultiCell(60, 0, 'Billet valable 1 mois.', 0, 'R', 0, 0, 70, 50, true);
                $pdf->MultiCell(60, 0, 'Billet non remboursable', 0, 'L', 0, 0, 130, 50, true);
                /* DECLINAISON DE PERTE */
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(130, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 70, 55, true);
                /* MISE EN GARE ET RAPPEL */
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->MultiCell(125, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 70, 60, true);
                /* BON VOYAGE */
                $pdf->setFont('Helvetica', '', 7);
                $pdf->MultiCell(60, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 75, 65, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(60, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 135, 65, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(60, 0, $nge, 0, 'L', 0, 0, 190, 65, true);
                

                
                // Clean any content of the output buffer
                ob_end_clean();
                //Close and output PDF document
                
                $pdf->Output($reponse->code_passager . '', 'I');
            }
            else
            {
                $reponse = $this->m_passager->reduction($this->entreprise->ekey, $code_id, $hr);
                $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                              $heures = sprintf("%02d:%02d", $heur, $secondes);
                              //$heures = $heur.':'.$secondes;
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                                    
                              //$heures = $heur.':'.$secondes;
                              $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                              $heures = sprintf("%02d:%02d", $heur, $secondes);      
                              //$heures = $heur.':'.$secondes;

                        }
            
                $nom = $reponse->code_progr;
                $nge = substr($nom, 6, 6);
    
                $dat = explode("-", $reponse->date_progr);
                $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->setCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('TICKET-' . $reponse->nom_client);
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                //remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(FALSE);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // add a page
                $pdf->AddPage('P', 'A4');
                
                //----------------------------------------------------------------------------
                
                // GROUPE DE GAUCHE
                $pdf->SetFont('Courier', 'B', 10);
            
                $pdf->MultiCell(60, 0, '' . $reponse->code_ticket, 0, 'L', 0, 0, 4, 20, true);
                
                /* AXE ET PRIX */
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(40, 0, '' . $reponse->nom_ligne, 0, 'L', 0, 0, 4, 25, true);
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare, 0, 'L', 0, 0, 4, 30, true);
                
                $pdf->MultiCell(30, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 4, 35, true);
                $pdf->SetFont('courier', 'B', 10);
                $pdf->MultiCell(60, 0, '' .$reponse->quart, 0, 'L', 0, 0, 15, 35, true);
                $pdf->MultiCell(50, 0, '' . $day, 0, 'L', 0, 0, 4, 40, true);
                
                $pdf->MultiCell(35, 0, '' . $heures, 0, 'L', 0, 0, 27, 40, true);
                /* NOM */$pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(70, 0, '' . $reponse->nom_client, 0, 'L', 0, 0, 4, 45, true);
                /* PRENOM */
                $pdf->MultiCell(70, 0, '' . $reponse->prenom_client, 0, 'L', 0, 0, 4, 50, true);
                /* TELEPHONE */
                $pdf->MultiCell(75, 0, '' . $reponse->contact_client, 0, 'L', 0, 0, 4, 55, true);
                
                $pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(35, 0, '' . number_format($reponse->prixvente, 0, '', ' ').' '. 'HT', 0, 'L', 0, 0, 4, 60, true);
                
                $pdf->setFont('Helvetica', 'BI', 8);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 4, 65, true);
                $pdf->setFont('Helvetica', 'BI', 8);
                $pdf->MultiCell(25, 0, $nge, 0, 'L', 0, 0, 25, 60, true);
                
                // - - - - - - -  GROUPE DE DROITE  - - - - - - - - -
                
                /* AXE ET CODE ET SIEGE NOM ET PRENOM */
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(100, 0, $reponse->nom_client.' '.$reponse->prenom_client, 0, 'L', 0, 0, 60, 20, true);
                $pdf->setFont('courier', 'BI', 7);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 140, 20, true);
                $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 169, 20, true);
                $pdf->SetFont('courier', '', 10);
                $pdf->MultiCell(60, 0, 'CODE:', 0, 'L', 0, 0, 60, 25, true);
                $pdf->SetFont('Helvetica', 'BI', 12);
                $pdf->MultiCell(50, 0, '' .$reponse->code_ticket, 0, 'L', 0, 0, 75, 25, true);
                $pdf->SetFont('Courier', '', 11);
                $pdf->MultiCell(16, 0, 'SIEGE:', 0, 'L', 0, 0, 140, 25, true);
                $pdf->SetFont('Courier', 'BI', 12);
                $pdf->MultiCell(15, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 160, 25, true);
                $pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(20, 0, 'AXE: ', 0, 'L', 0, 0, 60, 30, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(60, 0, '' . $reponse->nom_ligne, 0, 'L', 0, 0, 75, 30, true);
                $pdf->SetFont('courier', '', 10);
                $pdf->MultiCell(60, 0, 'QUARTIER:', 0, 'L', 0, 0, 140, 30, true);
                $pdf->SetFont('courier', 'B', 10);
                $pdf->MultiCell(60, 0, '' .$reponse->quart, 0, 'L', 0, 0, 165, 30, true);
                /* DATE ET HEURE ET PRIX */
                $pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 60, 35, true);
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 83, 35, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 140, 35, true);
                $pdf->SetFont('helvetica', 'B', 12);
                
                $pdf->MultiCell(20, 0, $heures, 0, 'L', 0, 0, 160, 35, true);
                $pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 140, 40, true);
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(30, 0, number_format($reponse->prixvente, 0, '', ' ').' '. 'HT', 0, 'L', 0, 0, 160, 40, true);
                /* NOM ET TELEPHONE */
                $pdf->SetFont('Courier', '', 10);
                $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 60, 40, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(60, 0, $reponse->contact_client, 0, 'L', 0, 0, 75, 40, true);
                /* CONVOCATION */
                $pdf->setFont('Courier', 'B', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->MultiCell(120, 0, 'CONVOCATION 45 mn avant le départ' , 0, 'C', 0, 0, 75, 45, true);
                /* BILLET NON REMBOURSABLE */
                $pdf->SetTextColor(0, 0, 0);
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->SetFillColor(255, 125, 60);
                $pdf->MultiCell(60, 0, 'Billet valable 1 mois.', 0, 'R', 0, 0, 70, 50, true);
                $pdf->MultiCell(60, 0, 'Billet non remboursable', 0, 'L', 0, 0, 130, 50, true);
                /* DECLINAISON DE PERTE */
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(130, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 70, 55, true);
                /* MISE EN GARE ET RAPPEL */
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->MultiCell(125, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 70, 60, true);
                /* BON VOYAGE */
                $pdf->setFont('Helvetica', '', 7);
                $pdf->MultiCell(60, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 75, 65, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(60, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 135, 65, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(60, 0, $nge, 0, 'L', 0, 0, 190, 65, true);

                // Clean any content of the output buffer
                ob_end_clean();
                //Close and output PDF document
                
                $pdf->Output($reponse->code_passager . '', 'I');
                //============================================================+
                // END OF FILEgit
                //============================================================+   
            }

            
        }

        public function printpdfepson($ckey, $code_id, $hr)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);
            if($hr === '0')
            {
                $reponse = $this->m_passager->reduction($this->entreprise->ekey, $code_id, $hr);
                $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                              $heures = sprintf("%02d:%02d", $heur, $secondes);
                              //$heures = $heur.':'.$secondes;
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                                    
                              //$heures = $heur.':'.$secondes;
                              $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                              $heures = sprintf("%02d:%02d", $heur, $secondes);      
                              //$heures = $heur.':'.$secondes;

                        }
            
                $nom = $reponse->code_progr;
                $nge = substr($nom, 6, 6);
    
                $dat = explode("-", $reponse->date_progr);
                $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->setCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('TICKET-' . $reponse->nom_client);
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                //remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(FALSE);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // add a page
               $pdf->AddPage('', 'A5');
                        
                        
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                /* LOGO*/
                
          
                // GROUPE DE HAUT
                $pdf->SetFont('helvetica', 'B', 5.5);
                /* SIEGE SOCIAL ET ADRESSE */
                $pdf->MultiCell(80, 0, '' . $reponse->adresse . ' Tel: ' . $reponse->contact_comp . ' / ' . 'Email: ' . $reponse->adresse, 0, 'L', 0, 0, 5, 30, true);
                $pdf->setFont('Helvetica', 'BI', 5);
                $pdf->MultiCell(60, 0, $nge, 0, 'L', 0, 0, 5, 37, true);
                $pdf->setFont('courier', 'BI', 7);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 15, 37, true);
                $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 50, 37, true);
                /* AXE */
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(80, 0, '' . $reponse->nom_ligne.'  '.$reponse->quart, 0, 'C', 0, 0, 5, 44, true);
                $pdf->SetFont('helvetica', '', 10);
                /* TELEPHONE ET PRIX*/
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 51, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 51, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 49, 51, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(20, 0, 'TICKET GRATUIT', 0, 'L', 0, 0, 60, 51, true);
                
                /* CODE*/
                $pdf->MultiCell(15, 0, 'CODE:', 0, 'L', 0, 0, 5, 58, true);
                $pdf->SetFont('helvetica', 'BI', 10);
                $pdf->MultiCell(60, 0, '' . $reponse->code_ticket, 0, 'L', 0, 0, 17, 58, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'L', 0, 0, 55, 58, true);
                $pdf->SetFont('helvetica', 'BI', 10);
                $pdf->MultiCell(10, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 68, 58, true);
                /* DATE ET HEURE ET PRIX */
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare, 0, 'L', 0, 0, 5, 65, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(30, 0, $day, 0, 'C', 0, 0, 28, 65, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 57, 65, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 69, 65, true);
                $pdf->SetFont('helvetica', '', 10);
                
                /* NOM */
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 72, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 72, true);
                
                
                /* PRENOMS */
                $pdf->setFont('Helvetica', '', 10);
                $pdf->MultiCell(25, 0, 'PRÉNOMS:', 0, 'L', 0, 0, 5, 79, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(50, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 79, true);
                
                /* CONVOCATION */
                $pdf->setFont('Helvetica', 'B', 9.5);
                $pdf->MultiCell(75, 0, 'CONVOCATION 45 minutes avant le départ.', 0, 'L', 0, 0, 5, 86, true);
                /* BILLET NON REMBOURSABLE */
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->MultiCell(37, 0, 'Billet valable 1 mois', 0, 'L', 0, 0, 5, 93, true);
                $pdf->MultiCell(43, 0, 'Billet non remboursable.', 0, 'L', 0, 0, 36, 93, true);
                /* DECLINAISON DE PERTE */
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(75, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte', 0, 'L', 0, 0, 5, 96, true);
                $pdf->setFont('Helvetica', 'BI', 8);
                $pdf->MultiCell(75, 0, 'ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 5, 100, true);
                /* MISE EN GARE ET RAPPEL */
                $pdf->setFont('Helvetica', 'B', 7);
                $pdf->MultiCell(70, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 5, 107, true);
                /* NOTE D'INFORMATION*/
                $pdf->setFont('courier', '', 6);
                $pdf->MultiCell(100, 0, 'Chers clients,nous vous souhaitons la bienvenue à '.$reponse->nom_entreprise.' transport.', 0, 'L', 0, 0, 5, 112, true);
                $pdf->setFont('courier', '', 6);
                $pdf->MultiCell(115, 0, 'Pour permettre à '.$reponse->nom_entreprise. ' transport de bien vous servir tout en respectant les horaires, nous vous prions de bien vouloir observer les conditions de voyages ci-dessous:', 0, 'L', 0, 0, 5, 115, true);
                $pdf->setFont('courier', '', 6);
                $pdf->MultiCell(90, 0, 'Conformément aux conditions de transport, '.$reponse->nom_entreprise. ' transport ne peut en aucun cas etre engagé pour la perte de vos bagages;' , 0, 'L', 0, 0, 5, 122, true);
                $pdf->setFont('courier', '', 6);
                $pdf->MultiCell(70, 0, 'Toute perte de bagage par la faute de la compagnie:', 0, 'L', 0, 0, 5, 128, true);
                $pdf->MultiCell(60, 0, '=> Lors d\'accident', 0, 'L', 0, 0, 7, 131, true);
                $pdf->MultiCell(60, 0, '=> ouverture de coffre pendant la circulation', 0, 'L', 0, 0, 7, 134, true);
                $pdf->MultiCell(75, 0, 'Dont la valeur n\'avait pas été déclarée à ', 0, 'L', 0, 0, 5, 137, true);
                $pdf->MultiCell(75, 0, 'l\'enregistrement ne peut être remboursé qu\'au niveau ', 0, 'L', 0, 0, 5, 140, true);
                $pdf->MultiCell(75, 0, 'maximum à vingt cinq mille (25 000) F CFA.', 0, 'L', 0, 0, 5, 143, true);
                $pdf->MultiCell(75, 0, 'Les objets des valeur doivent faire l\'objet ', 0, 'L', 0, 0, 5, 147, true);
                $pdf->MultiCell(75, 0, 'd\'une déclaration en sus de l\'enregistrement', 0, 'L', 0, 0, 5, 150, true);
                $pdf->MultiCell(60, 0, 'avec pièces justificatives avant le départ.', 0, 'L', 0, 0, 5, 153, true);
                /* BON VOYAGE */
                $pdf->setFont('Helvetica', '', 6);
                $pdf->MultiCell(40, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 5, 158, true);
                $pdf->setFont('Helvetica', 'BI', 6);
                $pdf->MultiCell(30, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 44, 158, true);

                
                
                // ---------------------------------------------------------
                // add a page
                $pdf->AddPage('P', 'A5');
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                
                /* SOUCHE DE CONTROL */
                $pdf->setFont('Helvetica', 'B', 10);
                $pdf->MultiCell(60, 0, 'SOUCHE DE CONTROL', 0, 'C', 0, 0, 5, 5, true);
                $pdf->setFont('Helvetica', '', 10);
                /**CODE */
                $pdf->MultiCell(15, 0, 'CODE:', 0, 'L', 0, 0, 5, 17, true);
                $pdf->MultiCell(50, 0, $reponse->code_ticket, 0, 'L', 0, 0, 20, 17, true);
                /**DIRECTION */
                $pdf->MultiCell(40, 0, $reponse->nom_ligne, 0, 'C', 0, 0, 2, 25, true);
                /**NOM ET SIEGE */
                $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 33, true);
                $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 33, true);
                $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'C', 0, 0, 40, 33, true);
                $pdf->MultiCell(20, 0, str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'C', 0, 0, 45, 33, true);
                /**PRENOM */
                $pdf->MultiCell(25, 0, 'PRENOMS:', 0, 'L', 0, 0, 5, 41, true);
                $pdf->MultiCell(30, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 41, true);
                /**TELEPHONE ET PRIX */
                $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 49, true);
                $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 49, true);
                $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 43, 49, true);
                $pdf->MultiCell(20, 0, 'TICKET GRATUIT', 0, 'C', 0, 0, 50, 49, true);
                /**DATE ET HEURE */
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 56, true);
                $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 27, 56, true);
                $pdf->MultiCell(17, 0, 'HEURE:', 0, 'L', 0, 0, 43, 56, true);
                $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 55, 56, true);
                

                
                // Clean any content of the output buffer
                ob_end_clean();
                //Close and output PDF document
                
                $pdf->Output($reponse->code_passager . '', 'I');
            }
            else
            {
                $reponse = $this->m_passager->reduction($this->entreprise->ekey, $code_id, $hr);
                $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                        if($ressougare->possitiongare === 'Maintenant'){

                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                              $heures = sprintf("%02d:%02d", $heur, $secondes);
                              //$heures = $heur.':'.$secondes;
                        }

                        if($ressougare->possitiongare === 'Avant'){
                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                                    
                              //$heures = $heur.':'.$secondes;
                              $heures = sprintf("%02d:%02d", $heur, $secondes);
                        }

                        if($ressougare->possitiongare === 'Apres'){
                              $g = explode(":", $reponse->heure);
                              $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                              $heur = ($gt / 60); 
                              $secondes = round($gt % 60);
                              $heures = sprintf("%02d:%02d", $heur, $secondes);      
                              //$heures = $heur.':'.$secondes;

                        }
                
            
                $nom = $reponse->code_progr;
                $nge = substr($nom, 6, 6);
    
                $dat = explode("-", $reponse->date_progr);
                $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->setCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('TICKET-' . $reponse->nom_client);
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                //remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(FALSE);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                // add a page
                $pdf->AddPage('', 'A5');
                
                
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                /* LOGO*/
                
          
                // GROUPE DE HAUT
                $pdf->SetFont('helvetica', 'B', 5.5);
                /* SIEGE SOCIAL ET ADRESSE */
                $pdf->MultiCell(80, 0, '' . $reponse->adresse . ' Tel: ' . $reponse->contact_comp . ' / ' . 'Email: ' . $reponse->adresse, 0, 'L', 0, 0, 5, 30, true);
                $pdf->setFont('Helvetica', 'BI', 5);
                $pdf->MultiCell(60, 0, $nge, 0, 'L', 0, 0, 5, 37, true);
                $pdf->setFont('courier', 'BI', 7);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 15, 37, true);
                $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 50, 37, true);
                /* AXE */
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(80, 0, '' . $reponse->nom_ligne.'  '.$reponse->quart, 0, 'C', 0, 0, 5, 44, true);
                $pdf->SetFont('helvetica', '', 10);
                /* TELEPHONE ET PRIX*/
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 51, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 51, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 49, 51, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(20, 0, number_format($reponse->prixvente, 0, '', ' '), 0, 'L', 0, 0, 60, 51, true);
                
                /* CODE*/
                $pdf->MultiCell(15, 0, 'CODE:', 0, 'L', 0, 0, 5, 58, true);
                $pdf->SetFont('helvetica', 'BI', 10);
                $pdf->MultiCell(60, 0, '' . $reponse->code_ticket, 0, 'L', 0, 0, 17, 58, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'L', 0, 0, 55, 58, true);
                $pdf->SetFont('helvetica', 'BI', 10);
                $pdf->MultiCell(10, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 68, 58, true);
                /* DATE ET HEURE ET PRIX */
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 65, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(30, 0, $day, 0, 'C', 0, 0, 28, 65, true);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 57, 65, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 69, 65, true);
                $pdf->SetFont('helvetica', '', 10);
                
                /* NOM */
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 72, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 72, true);
                
                
                /* PRENOMS */
                $pdf->setFont('Helvetica', '', 10);
                $pdf->MultiCell(25, 0, 'PRÉNOMS:', 0, 'L', 0, 0, 5, 79, true);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->MultiCell(50, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 79, true);
                
                /* CONVOCATION */
                $pdf->setFont('Helvetica', 'B', 9.5);
                $pdf->MultiCell(75, 0, 'CONVOCATION 45 minutes avant le départ.', 0, 'L', 0, 0, 5, 86, true);
                /* BILLET NON REMBOURSABLE */
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->MultiCell(37, 0, 'Billet valable 1 mois', 0, 'L', 0, 0, 5, 93, true);
                $pdf->MultiCell(43, 0, 'Billet non remboursable.', 0, 'L', 0, 0, 36, 93, true);
                /* DECLINAISON DE PERTE */
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(75, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte', 0, 'L', 0, 0, 5, 96, true);
                $pdf->setFont('Helvetica', 'BI', 8);
                $pdf->MultiCell(75, 0, 'ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 5, 100, true);
                /* MISE EN GARE ET RAPPEL */
                $pdf->setFont('Helvetica', 'B', 7);
                $pdf->MultiCell(70, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 5, 107, true);
                /* NOTE D'INFORMATION*/
                $pdf->setFont('courier', '', 6);
                $pdf->MultiCell(100, 0, 'Chers clients,nous vous souhaitons la bienvenue à '.$reponse->nom_entreprise.' transport.', 0, 'L', 0, 0, 5, 112, true);
                $pdf->setFont('courier', '', 6);
                $pdf->MultiCell(115, 0, 'Pour permettre à '.$reponse->nom_entreprise. ' transport de bien vous servir tout en respectant les horaires, nous vous prions de bien vouloir observer les conditions de voyages ci-dessous:', 0, 'L', 0, 0, 5, 115, true);
                $pdf->setFont('courier', '', 6);
                $pdf->MultiCell(90, 0, 'Conformément aux conditions de transport, '.$reponse->nom_entreprise. ' transport ne peut en aucun cas etre engagé pour la perte de vos bagages;' , 0, 'L', 0, 0, 5, 122, true);
                $pdf->setFont('courier', '', 6);
                $pdf->MultiCell(70, 0, 'Toute perte de bagage par la faute de la compagnie:', 0, 'L', 0, 0, 5, 128, true);
                $pdf->MultiCell(60, 0, '=> Lors d\'accident', 0, 'L', 0, 0, 7, 131, true);
                $pdf->MultiCell(60, 0, '=> ouverture de coffre pendant la circulation', 0, 'L', 0, 0, 7, 134, true);
                $pdf->MultiCell(75, 0, 'Dont la valeur n\'avait pas été déclarée à ', 0, 'L', 0, 0, 5, 137, true);
                $pdf->MultiCell(75, 0, 'l\'enregistrement ne peut être remboursé qu\'au niveau ', 0, 'L', 0, 0, 5, 140, true);
                $pdf->MultiCell(75, 0, 'maximum à vingt cinq mille (25 000) F CFA.', 0, 'L', 0, 0, 5, 143, true);
                $pdf->MultiCell(75, 0, 'Les objets des valeur doivent faire l\'objet ', 0, 'L', 0, 0, 5, 147, true);
                $pdf->MultiCell(75, 0, 'd\'une déclaration en sus de l\'enregistrement', 0, 'L', 0, 0, 5, 150, true);
                $pdf->MultiCell(60, 0, 'avec pièces justificatives avant le départ.', 0, 'L', 0, 0, 5, 153, true);
                /* BON VOYAGE */
                $pdf->setFont('Helvetica', '', 6);
                $pdf->MultiCell(40, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 5, 158, true);
                $pdf->setFont('Helvetica', 'BI', 6);
                $pdf->MultiCell(30, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 44, 158, true);

                
                
                // ---------------------------------------------------------
                // add a page
                $pdf->AddPage('P', 'A5');
                // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                
                /* SOUCHE DE CONTROL */
                $pdf->setFont('Helvetica', 'B', 10);
                $pdf->MultiCell(60, 0, 'SOUCHE DE CONTROL', 0, 'C', 0, 0, 5, 5, true);
                $pdf->setFont('Helvetica', '', 10);
                /**CODE */
                $pdf->MultiCell(15, 0, 'CODE:', 0, 'L', 0, 0, 5, 17, true);
                $pdf->MultiCell(50, 0, $reponse->code_ticket, 0, 'L', 0, 0, 20, 17, true);
                /**DIRECTION */
                $pdf->MultiCell(40, 0, $reponse->nom_ligne, 0, 'C', 0, 0, 2, 25, true);
                /**NOM ET SIEGE */
                $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 33, true);
                $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 33, true);
                $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'C', 0, 0, 40, 33, true);
                $pdf->MultiCell(20, 0, str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'C', 0, 0, 45, 33, true);
                /**PRENOM */
                $pdf->MultiCell(25, 0, 'PRENOMS:', 0, 'L', 0, 0, 5, 41, true);
                $pdf->MultiCell(30, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 41, true);
                /**TELEPHONE ET PRIX */
                $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 49, true);
                $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 49, true);
                $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 43, 49, true);
                $pdf->MultiCell(20, 0, number_format($reponse->prixvente, 0, '', ' '), 0, 'C', 0, 0, 50, 49, true);
                /**DATE ET HEURE */
                $pdf->MultiCell(15, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 56, true);
                $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 17, 56, true);
                $pdf->MultiCell(17, 0, 'HEURE:', 0, 'L', 0, 0, 43, 56, true);
                $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 55, 56, true);
                        

                // Clean any content of the output buffer
                ob_end_clean();
                //Close and output PDF document
                
                $pdf->Output($reponse->code_passager . '', 'I');
                //============================================================+
                // END OF FILEgit
                //============================================================+   
            }

            
        }
        
        //print A4 ticket aller_retour
        public function printpdfar($ckey, $code_p, $code_np, $hre)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            if($hre === '0'){
                $reponse = $this->m_passager->reduction($this->entreprise->ekey, $code_p, $hre);
                $fiche = $this->m_non_passager->reduit($this->entreprise->ekey, $code_np);
                $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                if($ressougare->possitiongare === 'Maintenant'){

                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                      $heures = sprintf("%02d:%02d", $heur, $secondes);
                      //$heures = $heur.':'.$secondes;
                }

                if($ressougare->possitiongare === 'Avant'){
                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                            
                      //$heures = $heur.':'.$secondes;
                      $heures = sprintf("%02d:%02d", $heur, $secondes);
                }

                if($ressougare->possitiongare === 'Apres'){
                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                      $heures = sprintf("%02d:%02d", $heur, $secondes);      
                      //$heures = $heur.':'.$secondes;

                }
                $dat = explode("-", $reponse->date_progr);
                $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
    
                $cdbus = $reponse->code_progr;
                $codb = substr($cdbus, 6, 6);
    
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('TICKET-' . $reponse->nom_client);
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                // remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                //aller
    
                $pdf->AddPage('P', 'A4');
                
                //----------------------------------------------------------------------------
                
                // GROUPE DE GAUCHE
                    $pdf->SetFont('Courier', 'B', 9);
                /* CODE ET SIEGE */
                $pdf->MultiCell(60, 0, '' .$reponse->code_ticket, 0, 'L', 0, 0, 4, 20, true);
                $pdf->MultiCell(50, 0, '' .$fiche->codeticket, 0, 'L', 0, 0, 4, 25, true);
                /* AXE ET PRIX */
                $pdf->SetFont('Courier', 'B', 9);
                $pdf->MultiCell(40, 0, '' . $reponse->nom_ligne, 0, 'L', 0, 0, 4, 30, true);
                /* DATE ET HEURE */
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare, 0, 'L', 0, 0, 4, 35, true);
                  $pdf->MultiCell(30, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 30, 35, true);
                  $pdf->MultiCell(30, 0, '' .$day, 0, 'L', 0, 0, 4, 40, true);
                  
                  $pdf->MultiCell(35, 0, '' . $heures, 0, 'L', 0, 0, 25, 40, true);
                $pdf->SetFont('Courier', '', 9);
                
                $pdf->MultiCell(35, 0, 'TICKET GRATUIT', 0, 'L', 0, 0, 4, 43, true);
                $pdf->MultiCell(60, 0, '' .$reponse->quart, 0, 'L', 0, 0, 30, 45, true);
                /* NOM */
                $pdf->MultiCell(70, 0, '' . $reponse->nom_client, 0, 'L', 0, 0, 4, 50, true);
                /* PRENOM */
                $pdf->MultiCell(70, 0, '' . $reponse->prenom_client, 0, 'L', 0, 0, 4, 55, true);
                /* TELEPHONE */
                $pdf->MultiCell(75, 0, '' . $reponse->contact_client, 0, 'L', 0, 0, 4, 60, true);
                $pdf->SetFont('Courier', 'BI', 8);
                $pdf->MultiCell(30, 0, 'ALLER-RETOUR', 0, 'L', 0, 0, 4, 63, true);
                $pdf->setFont('Helvetica', 'BI', 6);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 4, 66, true);
                $pdf->setFont('Helvetica', 'BI', 6);
                $pdf->MultiCell(25, 0, $codb, 0, 'L', 0, 0, 35, 63, true);
                // - - - - - - -  GROUPE DE DROITE  - - - - - - - - -
                
                /* AXE ET CODE ET SIEGE NOM ET PRENOM */
                $pdf->SetFont('Courier', 'B', 13);
                $pdf->MultiCell(130, 0, $reponse->nom_client.' '.$reponse->prenom_client, 0, 'L', 0, 0, 60, 20, true);
                $pdf->setFont('courier', 'BI', 7);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 140, 20, true);
                $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 169, 20, true);
                $pdf->SetFont('Courier', 'B', 12);
                $pdf->MultiCell(100, 0, $reponse->nom_ligne, 0, 'L', 0, 0, 60, 25, true);
                $pdf->SetFont('Courier', 'BI', 12);
                $pdf->MultiCell(100, 0, $reponse->quart, 0, 'L', 0, 0, 60, 30, true);
                $pdf->SetFont('Courier', 'B', 12);

                $pdf->MultiCell(60, 0, $reponse->contact_client, 0, 'L', 0, 0, 60, 35, true);
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(40, 0, 'TICKET GRATUIT', 0, 'L', 0, 0, 60, 40, true);
                
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(15, 0, 'ALLER', 0, 'L', 0, 0, 108, 25, true);
                $pdf->SetFont('Helvetica', 'BI', 10);
                $pdf->MultiCell(60, 0, '' . $reponse->code_ticket, 0, 'L', 0, 0, 122, 25, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(40, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 97, 30, true);
                $pdf->SetFont('Courier', 'B', 12);
                $pdf->MultiCell(35, 0, ''.$day, 0, 'L', 0, 0, 125, 30, true);
                $pdf->SetFont('courier', '', 12);
                $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 108, 35, true);
                $pdf->SetFont('courier', 'B', 12);
                $pdf->MultiCell(30, 0, ''.$heures, 0, 'L', 0, 0, 127, 35, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(30, 0, 'SIEGE:', 0, 'L', 0, 0, 108, 40, true);
                $pdf->SetFont('Courier', 'BI', 12);
                $pdf->MultiCell(30, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 127, 40, true);

                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(15, 0, 'RETOUR', 0, 'L', 0, 0, 160, 25, true);
                $pdf->SetFont('Helvetica', 'BI', 10);
                $pdf->MultiCell(60, 0, '' . $fiche->codeticket, 0, 'L', 0, 0, 175, 25, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(19, 0, 'DATE:', 0, 'L', 0, 0, 160, 30, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(19, 0, 'HEURE:', 0, 'L', 0, 0, 160, 35, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(19, 0, 'SIEGE:', 0, 'L', 0, 0, 160, 40, true);
                
                /* CONVOCATION */
                
                $pdf->setFont('Courier', 'B', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->MultiCell(120, 0, 'CONVOCATION 45 mn avant le départ', 0, 'C', 0, 0, 75, 45, true);
                /* BILLET NON REMBOURSABLE */
                $pdf->SetTextColor(0, 0, 0);
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->SetFillColor(255, 125, 60);
                $pdf->MultiCell(60, 0, 'Billet valable 1 mois.', 0, 'R', 0, 0, 80, 50, true);
                $pdf->MultiCell(60, 0, 'Billet non remboursable', 0, 'L', 0, 0, 140, 50, true);
                /* DECLINAISON DE PERTE */
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(130, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 70, 55, true);
                /* MISE EN GARE ET RAPPEL */
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->MultiCell(125, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 80, 60, true);
                /* BON VOYAGE */
                $pdf->setFont('Helvetica', '', 7);
                $pdf->MultiCell(60, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 80, 65, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(60, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 140, 65, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(60, 0, $codb, 0, 'L', 0, 0, 190, 65, true);
                 // Clean any content of the output buffer
                ob_end_clean();
                //Close and output PDF document
                
                $pdf->Output($fiche->code_non_pass . '', 'I');
                //============================================================+
                // END OF FILEgit
                //============================================================+
            }
            else
            {
                $reponse = $this->m_passager->reduction($this->entreprise->ekey, $code_p, $hre);
                $fiche = $this->m_non_passager->reduit($this->entreprise->ekey, $code_np);
                $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                if($ressougare->possitiongare === 'Maintenant'){

                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                      $heures = sprintf("%02d:%02d", $heur, $secondes);
                      //$heures = $heur.':'.$secondes;
                }

                if($ressougare->possitiongare === 'Avant'){
                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                            
                      //$heures = $heur.':'.$secondes;
                      $heures = sprintf("%02d:%02d", $heur, $secondes);
                }

                if($ressougare->possitiongare === 'Apres'){
                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                      $heures = sprintf("%02d:%02d", $heur, $secondes);      
                      //$heures = $heur.':'.$secondes;

                }
                $dat = explode("-", $reponse->date_progr);
                $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
    
                $cdbus = $reponse->code_progr;
                $codb = substr($cdbus, 6, 6);
    
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('TICKET-' . $reponse->nom_client);
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                // remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                //aller
    
                $pdf->AddPage('P', 'A4');
                
                //----------------------------------------------------------------------------
                
                // GROUPE DE GAUCHE
                
                $pdf->SetFont('Courier', 'B', 9);
                /* CODE ET SIEGE */
                $pdf->MultiCell(60, 0, '' .$reponse->code_ticket, 0, 'L', 0, 0, 4, 20, true);
                $pdf->MultiCell(50, 0, '' .$fiche->codeticket, 0, 'L', 0, 0, 4, 25, true);
                /* AXE ET PRIX */
                $pdf->SetFont('Courier', 'B', 9);
                $pdf->MultiCell(40, 0, '' . $reponse->nom_ligne, 0, 'L', 0, 0, 4, 30, true);
                /* DATE ET HEURE */
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare, 0, 'L', 0, 0, 4, 35, true);
                $pdf->MultiCell(30, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 30, 35, true);
                $pdf->MultiCell(30, 0, '' .$day, 0, 'L', 0, 0, 4, 40, true);
                
                $pdf->MultiCell(35, 0, '' . $heures, 0, 'L', 0, 0, 27, 40, true);
                $pdf->SetFont('Courier', '', 8);
                $pdf->MultiCell(35, 0, '' . number_format($reponse->prix+$reponse->prix, 0, '', ' ').' '. 'HT', 0, 'L', 0, 0, 4, 45, true);
                $pdf->SetFont('courier', 'B', 9);
                $pdf->MultiCell(60, 0, '' .$reponse->quart, 0, 'L', 0, 0, 30, 45, true);
                  
                /* NOM */
                $pdf->MultiCell(70, 0, '' . $reponse->nom_client, 0, 'L', 0, 0, 4, 50, true);
                /* PRENOM */
                $pdf->MultiCell(70, 0, '' . $reponse->prenom_client, 0, 'L', 0, 0, 4, 55, true);
                /* TELEPHONE */
                $pdf->MultiCell(75, 0, '' . $reponse->contact_client, 0, 'L', 0, 0, 4, 60, true);
                $pdf->SetFont('Courier', 'BI', 8);
                $pdf->MultiCell(30, 0, 'ALLER-RETOUR', 0, 'L', 0, 0, 4, 63, true);
                $pdf->setFont('Helvetica', 'BI', 6);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 4, 66, true);
                $pdf->setFont('Helvetica', 'BI', 6);
                $pdf->MultiCell(25, 0, $codb, 0, 'L', 0, 0, 35, 63, true);
                // - - - - - - -  GROUPE DE DROITE  - - - - - - - - -
                
                /* AXE ET CODE ET SIEGE NOM ET PRENOM */
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(130, 0, $reponse->nom_client.' '.$reponse->prenom_client, 0, 'L', 0, 0, 60, 20, true);
                $pdf->setFont('courier', 'BI', 7);
                $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 140, 20, true);
                $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 169, 20, true);
                $pdf->SetFont('Courier', 'B', 12);
                $pdf->MultiCell(100, 0, $reponse->nom_ligne, 0, 'L', 0, 0, 60, 25, true);
                $pdf->SetFont('Courier', 'BI', 12);
                $pdf->MultiCell(100, 0, $reponse->quart, 0, 'L', 0, 0, 60, 30, true);
                $pdf->SetFont('Courier', 'B', 12);

                $pdf->MultiCell(60, 0, $reponse->contact_client, 0, 'L', 0, 0, 60, 35, true);
                $pdf->SetFont('Courier', 'B', 12);
                $pdf->MultiCell(40, 0, ''.number_format($reponse->prixvente + $reponse->prixvente, 0, '', ' ').' '. 'HT', 0, 'L', 0, 0, 60, 40, true);
                
                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(15, 0, 'ALLER', 0, 'L', 0, 0, 108, 25, true);
                $pdf->SetFont('Helvetica', 'BI', 10);
                $pdf->MultiCell(60, 0, '' . $reponse->code_ticket, 0, 'L', 0, 0, 122, 25, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 108, 30, true);
                $pdf->SetFont('Courier', 'B', 12);
                $pdf->MultiCell(35, 0, ''.$day, 0, 'L', 0, 0, 125, 30, true);
                $pdf->SetFont('courier', '', 12);
                $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 108, 35, true);
                $pdf->SetFont('courier', 'B', 12);
                $pdf->MultiCell(30, 0, ''.$heures, 0, 'L', 0, 0, 127, 35, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(30, 0, 'SIEGE:', 0, 'L', 0, 0, 108, 40, true);
                $pdf->SetFont('Courier', 'BI', 12);
                $pdf->MultiCell(30, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 127, 40, true);

                $pdf->SetFont('Courier', 'B', 10);
                $pdf->MultiCell(15, 0, 'RETOUR', 0, 'L', 0, 0, 160, 25, true);
                $pdf->SetFont('Helvetica', 'BI', 10);
                $pdf->MultiCell(60, 0, '' . $fiche->codeticket, 0, 'L', 0, 0, 175, 25, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(19, 0, 'DATE:', 0, 'L', 0, 0, 160, 30, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(19, 0, 'HEURE:', 0, 'L', 0, 0, 160, 35, true);
                $pdf->SetFont('Courier', '', 12);
                $pdf->MultiCell(19, 0, 'SIEGE:', 0, 'L', 0, 0, 160, 40, true);
                
                
                /* CONVOCATION */
                
                $pdf->setFont('Courier', 'B', 12);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->MultiCell(120, 0, 'CONVOCATION 45 mn avant le départ', 0, 'C', 0, 0, 75, 45, true);
                /* BILLET NON REMBOURSABLE */
                $pdf->SetTextColor(0, 0, 0);
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->SetFillColor(255, 125, 60);
                $pdf->MultiCell(60, 0, 'Billet valable 1 mois.', 0, 'R', 0, 0, 80, 50, true);
                $pdf->MultiCell(60, 0, 'Billet non remboursable', 0, 'L', 0, 0, 140, 50, true);
                /* DECLINAISON DE PERTE */
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(130, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 70, 55, true);
                /* MISE EN GARE ET RAPPEL */
                $pdf->setFont('Helvetica', 'I', 7);
                $pdf->MultiCell(125, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 80, 60, true);
                /* BON VOYAGE */
                $pdf->setFont('Helvetica', '', 7);
                $pdf->MultiCell(60, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 80, 65, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(60, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 140, 65, true);
                $pdf->setFont('Helvetica', 'BI', 7);
                $pdf->MultiCell(60, 0, $codb, 0, 'L', 0, 0, 190, 65, true);
                 // Clean any content of the output buffer
                ob_end_clean();
                //Close and output PDF document
                
                $pdf->Output($fiche->code_non_pass . '', 'I');
                //============================================================+
                // END OF FILEgit
                //============================================================+
            }

        }

        public function printpdfarepson($ckey, $code_p, $code_np, $hre)
        {
            $this->entreprise = $this->m_entreprises->get_key($ckey);

            if($hre === '0'){
                $reponse = $this->m_passager->reduction($this->entreprise->ekey, $code_p, $hre);
                $fiche = $this->m_non_passager->reduit($this->entreprise->ekey, $code_np);
                $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                if($ressougare->possitiongare === 'Maintenant'){

                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                      $heures = sprintf("%02d:%02d", $heur, $secondes);
                      //$heures = $heur.':'.$secondes;
                }

                if($ressougare->possitiongare === 'Avant'){
                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                            
                      //$heures = $heur.':'.$secondes;
                      $heures = sprintf("%02d:%02d", $heur, $secondes);
                }

                if($ressougare->possitiongare === 'Apres'){
                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                      $heures = sprintf("%02d:%02d", $heur, $secondes);      
                      //$heures = $heur.':'.$secondes;

                }
                $dat = explode("-", $reponse->date_progr);
                $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
    
                $cdbus = $reponse->code_progr;
                $codb = substr($cdbus, 6, 6);
    
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('TICKET-' . $reponse->nom_client);
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                // remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                //aller
    
                $pdf->AddPage('', 'A5');
                  
                  
                  // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                  /* LOGO*/
                  
            
                  // GROUPE DE HAUT
                  $pdf->SetFont('helvetica', 'B', 5.5);
                  /* SIEGE SOCIAL ET ADRESSE */
                  $pdf->MultiCell(80, 0, '' . $reponse->adresse . ' Tel: ' . $reponse->contact_comp . ' / ' . 'Email: ' . $reponse->adresse, 0, 'L', 0, 0, 5, 30, true);
                  $pdf->setFont('Helvetica', 'BI', 5);
                  $pdf->MultiCell(60, 0, $codb, 0, 'L', 0, 0, 5, 37, true);
                  $pdf->setFont('courier', 'BI', 7);
                  $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 15, 37, true);
                  $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 50, 37, true);
                  /* AXE */
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(80, 0, '' . $reponse->nom_ligne.'  '.$reponse->quart, 0, 'C', 0, 0, 5, 44, true);
                  $pdf->SetFont('helvetica', '', 10);
                  /* TELEPHONE ET PRIX*/
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 51, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 51, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 49, 51, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(20, 0, number_format($reponse->prix+$reponse->prix, 0, '', ' '), 0, 'L', 0, 0, 60, 51, true);
                  
                  /* CODE*/
                  $pdf->MultiCell(16, 0, 'ALLER:', 0, 'L', 0, 0, 5, 58, true);
                  $pdf->SetFont('helvetica', 'BI', 10);
                  $pdf->MultiCell(60, 0, '' . $reponse->code_ticket, 0, 'L', 0, 0, 19, 58, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'L', 0, 0, 55, 58, true);
                  $pdf->SetFont('helvetica', 'BI', 10);
                  $pdf->MultiCell(10, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 68, 58, true);
                  /* DATE ET HEURE ET PRIX */
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 65, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(30, 0, $day, 0, 'C', 0, 0, 28, 65, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 57, 65, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 69, 65, true);
                  $pdf->SetFont('helvetica', '', 10);
                  
                  /* NOM */
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 72, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 72, true);
                  
                  
                  /* PRENOMS */
                  $pdf->setFont('Helvetica', '', 10);
                  $pdf->MultiCell(25, 0, 'PRÉNOMS:', 0, 'L', 0, 0, 5, 79, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(50, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 79, true);
                  
                  /* CONVOCATION */
                  $pdf->setFont('Helvetica', 'B', 9.5);
                  $pdf->MultiCell(75, 0, 'CONVOCATION 45 minutes avant le départ.', 0, 'L', 0, 0, 5, 86, true);
                  /* BILLET NON REMBOURSABLE */
                  $pdf->setFont('Helvetica', 'I', 7);
                  $pdf->MultiCell(37, 0, 'Billet valable 1 mois', 0, 'L', 0, 0, 5, 93, true);
                  $pdf->MultiCell(43, 0, 'Billet non remboursable.', 0, 'L', 0, 0, 36, 93, true);
                  /* DECLINAISON DE PERTE */
                  $pdf->setFont('Helvetica', 'BI', 7);
                  $pdf->MultiCell(75, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte', 0, 'L', 0, 0, 5, 96, true);
                  $pdf->setFont('Helvetica', 'BI', 8);
                  $pdf->MultiCell(75, 0, 'ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 5, 100, true);
                  /* MISE EN GARE ET RAPPEL */
                  $pdf->setFont('Helvetica', 'B', 7);
                  $pdf->MultiCell(70, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 5, 107, true);
                  /* NOTE D'INFORMATION*/
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(100, 0, 'Chers clients,nous vous souhaitons la bienvenue à '.$reponse->nom_entreprise.' transport.', 0, 'L', 0, 0, 5, 112, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(115, 0, 'Pour permettre à '.$reponse->nom_entreprise. ' transport de bien vous servir tout en respectant les horaires, nous vous prions de bien vouloir observer les conditions de voyages ci-dessous:', 0, 'L', 0, 0, 5, 115, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(90, 0, 'Conformément aux conditions de transport, '.$reponse->nom_entreprise. ' transport ne peut en aucun cas etre engagé pour la perte de vos bagages;' , 0, 'L', 0, 0, 5, 122, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(70, 0, 'Toute perte de bagage par la faute de la compagnie:', 0, 'L', 0, 0, 5, 128, true);
                  $pdf->MultiCell(60, 0, '=> Lors d\'accident', 0, 'L', 0, 0, 7, 131, true);
                  $pdf->MultiCell(60, 0, '=> ouverture de coffre pendant la circulation', 0, 'L', 0, 0, 7, 134, true);
                  $pdf->MultiCell(75, 0, 'Dont la valeur n\'avait pas été déclarée à ', 0, 'L', 0, 0, 5, 137, true);
                  $pdf->MultiCell(75, 0, 'l\'enregistrement ne peut être remboursé qu\'au niveau ', 0, 'L', 0, 0, 5, 140, true);
                  $pdf->MultiCell(75, 0, 'maximum à vingt cinq mille (25 000) F CFA.', 0, 'L', 0, 0, 5, 143, true);
                  $pdf->MultiCell(75, 0, 'Les objets des valeur doivent faire l\'objet ', 0, 'L', 0, 0, 5, 147, true);
                  $pdf->MultiCell(75, 0, 'd\'une déclaration en sus de l\'enregistrement', 0, 'L', 0, 0, 5, 150, true);
                  $pdf->MultiCell(60, 0, 'avec pièces justificatives avant le départ.', 0, 'L', 0, 0, 5, 153, true);
                  /* BON VOYAGE */
                  $pdf->setFont('Helvetica', '', 6);
                  $pdf->MultiCell(40, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 5, 158, true);
                  $pdf->setFont('Helvetica', 'BI', 6);
                  $pdf->MultiCell(30, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 44, 158, true);
                  // add a page
                  $pdf->AddPage('P', 'A5');
                  // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                  
                  /* LOGO*/
                  
            
                  // GROUPE DE HAUT
                  $pdf->SetFont('helvetica', 'B', 5.5);
                  /* SIEGE SOCIAL ET ADRESSE */
                  $pdf->MultiCell(80, 0, '' . $reponse->adresse . ' Tel: ' . $reponse->contact_comp . ' / ' . 'Email: ' . $reponse->adresse, 0, 'L', 0, 0, 5, 30, true);
                  $pdf->setFont('Helvetica', 'BI', 5);
                  $pdf->MultiCell(60, 0, $nge, 0, 'L', 0, 0, 5, 37, true);
                  $pdf->setFont('courier', 'BI', 7);
                  $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 15, 37, true);
                  $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 50, 37, true);
                  /* AXE */
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(80, 0, '' . $reponse->nom_ligne.'  '.$reponse->quart, 0, 'C', 0, 0, 5, 44, true);
                  $pdf->SetFont('helvetica', '', 10);
                  /* TELEPHONE ET PRIX*/
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 51, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 51, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 49, 51, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(20, 0, 'TICKET GRATUIT', 0, 'L', 0, 0, 60, 51, true);
                  
                  /* CODE*/
                  $pdf->MultiCell(18, 0, 'RETOUR:', 0, 'L', 0, 0, 5, 58, true);
                  $pdf->SetFont('helvetica', 'BI', 10);
                  $pdf->MultiCell(60, 0, '' . $fiche->codeticket, 0, 'L', 0, 0, 22, 58, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'L', 0, 0, 55, 58, true);
                  
                  /* DATE ET HEURE ET PRIX */
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(30, 0, 'DATE', 0, 'L', 0, 0, 28, 65, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 57, 65, true);
                  /* NOM */
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 72, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 72, true);
                  
                  
                  /* PRENOMS */
                  $pdf->setFont('Helvetica', '', 10);
                  $pdf->MultiCell(25, 0, 'PRÉNOMS:', 0, 'L', 0, 0, 5, 79, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(50, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 79, true);
                  
                  /* CONVOCATION */
                  $pdf->setFont('Helvetica', 'B', 9.5);
                  $pdf->MultiCell(75, 0, 'CONVOCATION 45 minutes avant le départ.', 0, 'L', 0, 0, 5, 86, true);
                  /* BILLET NON REMBOURSABLE */
                  $pdf->setFont('Helvetica', 'I', 7);
                  $pdf->MultiCell(37, 0, 'Billet valable 1 mois', 0, 'L', 0, 0, 5, 93, true);
                  $pdf->MultiCell(43, 0, 'Billet non remboursable.', 0, 'L', 0, 0, 36, 93, true);
                  /* DECLINAISON DE PERTE */
                  $pdf->setFont('Helvetica', 'BI', 7);
                  $pdf->MultiCell(75, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte', 0, 'L', 0, 0, 5, 96, true);
                  $pdf->setFont('Helvetica', 'BI', 8);
                  $pdf->MultiCell(75, 0, 'ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 5, 100, true);
                  /* MISE EN GARE ET RAPPEL */
                  $pdf->setFont('Helvetica', 'B', 7);
                  $pdf->MultiCell(70, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 5, 107, true);
                  /* NOTE D'INFORMATION*/
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(100, 0, 'Chers clients,nous vous souhaitons la bienvenue à '.$reponse->nom_entreprise.' transport.', 0, 'L', 0, 0, 5, 112, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(115, 0, 'Pour permettre à '.$reponse->nom_entreprise. ' transport de bien vous servir tout en respectant les horaires, nous vous prions de bien vouloir observer les conditions de voyages ci-dessous:', 0, 'L', 0, 0, 5, 115, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(90, 0, 'Conformément aux conditions de transport, '.$reponse->nom_entreprise. ' transport ne peut en aucun cas etre engagé pour la perte de vos bagages;' , 0, 'L', 0, 0, 5, 122, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(70, 0, 'Toute perte de bagage par la faute de la compagnie:', 0, 'L', 0, 0, 5, 128, true);
                  $pdf->MultiCell(60, 0, '=> Lors d\'accident', 0, 'L', 0, 0, 7, 131, true);
                  $pdf->MultiCell(60, 0, '=> ouverture de coffre pendant la circulation', 0, 'L', 0, 0, 7, 134, true);
                  $pdf->MultiCell(75, 0, 'Dont la valeur n\'avait pas été déclarée à ', 0, 'L', 0, 0, 5, 137, true);
                  $pdf->MultiCell(75, 0, 'l\'enregistrement ne peut être remboursé qu\'au niveau ', 0, 'L', 0, 0, 5, 140, true);
                  $pdf->MultiCell(75, 0, 'maximum à vingt cinq mille (25 000) F CFA.', 0, 'L', 0, 0, 5, 143, true);
                  $pdf->MultiCell(75, 0, 'Les objets des valeur doivent faire l\'objet ', 0, 'L', 0, 0, 5, 147, true);
                  $pdf->MultiCell(75, 0, 'd\'une déclaration en sus de l\'enregistrement', 0, 'L', 0, 0, 5, 150, true);
                  $pdf->MultiCell(60, 0, 'avec pièces justificatives avant le départ.', 0, 'L', 0, 0, 5, 153, true);
                  /* BON VOYAGE */
                  $pdf->setFont('Helvetica', '', 6);
                  $pdf->MultiCell(40, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 5, 158, true);
                  $pdf->setFont('Helvetica', 'BI', 6);
                  $pdf->MultiCell(30, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 44, 158, true);
                  // add a page
                  $pdf->AddPage('P', 'A5');
                  /* SOUCHE DE CONTROL */
                  $pdf->setFont('Helvetica', 'B', 10);
                  $pdf->MultiCell(60, 0, 'SOUCHE DE CONTROL', 0, 'C', 0, 0, 5, 5, true);
                  $pdf->setFont('Helvetica', '', 10);
                  /**CODE */
                  $pdf->MultiCell(15, 0, 'CODE:', 0, 'L', 0, 0, 5, 17, true);
                  $pdf->MultiCell(50, 0, $reponse->code_ticket, 0, 'L', 0, 0, 20, 17, true);
                  /**DIRECTION */
                  $pdf->MultiCell(40, 0, $reponse->nom_ligne, 0, 'C', 0, 0, 2, 25, true);
                  /**NOM ET SIEGE */
                  $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 33, true);
                  $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 33, true);
                  $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'C', 0, 0, 40, 33, true);
                  $pdf->MultiCell(20, 0, str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'C', 0, 0, 45, 33, true);
                  /**PRENOM */
                  $pdf->MultiCell(25, 0, 'PRENOMS:', 0, 'L', 0, 0, 5, 41, true);
                  $pdf->MultiCell(30, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 41, true);
                  /**TELEPHONE ET PRIX */
                  $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 49, true);
                  $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 49, true);
                  $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 43, 49, true);
                  $pdf->MultiCell(20, 0, 'TICKET GRATUIT', 0, 'C', 0, 0, 50, 49, true);
                  /**DATE ET HEURE */
                  $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 56, true);
                  $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 27, 56, true);
                  $pdf->MultiCell(17, 0, 'HEURE:', 0, 'L', 0, 0, 43, 56, true);
                  $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 55, 56, true);
                  $pdf->MultiCell(30, 0, 'ALLER_RETOUR', 0, 'C', 0, 0, 5, 63, true);
                  $pdf->MultiCell(16, 0, 'ALLER', 0, 'L', 0, 0, 5, 70, true);
                  $pdf->MultiCell(30, 0, $reponse->code_ticket, 0, 'L', 0, 0, 25, 70, true);
                  $pdf->MultiCell(18, 0, 'RETOUR', 0, 'L', 0, 0, 5, 77, true);
                  $pdf->MultiCell(30, 0, $fiche->codeticket, 0, 'L', 0, 0, 25, 77, true);

                 // Clean any content of the output buffer
                ob_end_clean();
                //Close and output PDF document
                
                $pdf->Output($fiche->code_non_pass . '', 'I');
                //============================================================+
                // END OF FILEgit
                //============================================================+
            }
            else
            {
                $reponse = $this->m_passager->reduction($this->entreprise->ekey, $code_p, $hre);
                $fiche = $this->m_non_passager->reduit($this->entreprise->ekey, $code_np);
                $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                if($ressougare->possitiongare === 'Maintenant'){

                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                      $heures = sprintf("%02d:%02d", $heur, $secondes);
                      //$heures = $heur.':'.$secondes;
                }

                if($ressougare->possitiongare === 'Avant'){
                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                            
                      //$heures = $heur.':'.$secondes;
                      $heures = sprintf("%02d:%02d", $heur, $secondes);
                }

                if($ressougare->possitiongare === 'Apres'){
                      $g = explode(":", $reponse->heure);
                      $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                      $heur = ($gt / 60); 
                      $secondes = round($gt % 60);
                      $heures = sprintf("%02d:%02d", $heur, $secondes);      
                      //$heures = $heur.':'.$secondes;

                }
                $dat = explode("-", $reponse->date_progr);
                $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
    
                $cdbus = $reponse->code_progr;
                $codb = substr($cdbus, 6, 6);
    
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('NET SOLUTIONS');
                $pdf->SetTitle('TICKET-' . $reponse->nom_client);
                $pdf->SetSubject('RESERVATIONS');
                $pdf->SetKeywords('--');
                // remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                
                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                
                
                // set auto page breaks
                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
                
                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                
                //aller
    
                $pdf->AddPage('', 'A5');
                  
                  
                  // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                  /* LOGO*/
                  
            
                  // GROUPE DE HAUT
                  $pdf->SetFont('helvetica', 'B', 5.5);
                  /* SIEGE SOCIAL ET ADRESSE */
                  $pdf->MultiCell(80, 0, '' . $reponse->adresse . ' Tel: ' . $reponse->contact_comp . ' / ' . 'Email: ' . $reponse->adresse, 0, 'L', 0, 0, 5, 30, true);
                  $pdf->setFont('Helvetica', 'BI', 5);
                  $pdf->MultiCell(60, 0, $codb, 0, 'L', 0, 0, 5, 37, true);
                  $pdf->setFont('courier', 'BI', 7);
                  $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 15, 37, true);
                  $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 50, 37, true);
                  /* AXE */
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(80, 0, '' . $reponse->nom_ligne.'  '.$reponse->quart, 0, 'C', 0, 0, 5, 44, true);
                  $pdf->SetFont('helvetica', '', 10);
                  /* TELEPHONE ET PRIX*/
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 51, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 51, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 49, 51, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(20, 0, number_format($reponse->prixvente+$reponse->prixvente, 0, '', ' '), 0, 'L', 0, 0, 60, 51, true);
                  
                  /* CODE*/
                  $pdf->MultiCell(16, 0, 'ALLER:', 0, 'L', 0, 0, 5, 58, true);
                  $pdf->SetFont('helvetica', 'BI', 10);
                  $pdf->MultiCell(60, 0, '' . $reponse->code_ticket, 0, 'L', 0, 0, 19, 58, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'L', 0, 0, 55, 58, true);
                  $pdf->SetFont('helvetica', 'BI', 10);
                  $pdf->MultiCell(10, 0, '' . str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'L', 0, 0, 68, 58, true);
                  /* DATE ET HEURE ET PRIX */
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 65, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(30, 0, $day, 0, 'C', 0, 0, 28, 65, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 57, 65, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 69, 65, true);
                  $pdf->SetFont('helvetica', '', 10);
                  
                  /* NOM */
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 72, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 72, true);
                  
                  
                  /* PRENOMS */
                  $pdf->setFont('Helvetica', '', 10);
                  $pdf->MultiCell(25, 0, 'PRÉNOMS:', 0, 'L', 0, 0, 5, 79, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(50, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 79, true);
                  
                  /* CONVOCATION */
                  $pdf->setFont('Helvetica', 'B', 9.5);
                  $pdf->MultiCell(75, 0, 'CONVOCATION 45 minutes avant le départ.', 0, 'L', 0, 0, 5, 86, true);
                  /* BILLET NON REMBOURSABLE */
                  $pdf->setFont('Helvetica', 'I', 7);
                  $pdf->MultiCell(37, 0, 'Billet valable 1 mois', 0, 'L', 0, 0, 5, 93, true);
                  $pdf->MultiCell(43, 0, 'Billet non remboursable.', 0, 'L', 0, 0, 36, 93, true);
                  /* DECLINAISON DE PERTE */
                  $pdf->setFont('Helvetica', 'BI', 7);
                  $pdf->MultiCell(75, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte', 0, 'L', 0, 0, 5, 96, true);
                  $pdf->setFont('Helvetica', 'BI', 8);
                  $pdf->MultiCell(75, 0, 'ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 5, 100, true);
                  /* MISE EN GARE ET RAPPEL */
                  $pdf->setFont('Helvetica', 'B', 7);
                  $pdf->MultiCell(70, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 5, 107, true);
                  /* NOTE D'INFORMATION*/
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(100, 0, 'Chers clients,nous vous souhaitons la bienvenue à '.$reponse->nom_entreprise.' transport.', 0, 'L', 0, 0, 5, 112, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(115, 0, 'Pour permettre à '.$reponse->nom_entreprise. ' transport de bien vous servir tout en respectant les horaires, nous vous prions de bien vouloir observer les conditions de voyages ci-dessous:', 0, 'L', 0, 0, 5, 115, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(90, 0, 'Conformément aux conditions de transport, '.$reponse->nom_entreprise. ' transport ne peut en aucun cas etre engagé pour la perte de vos bagages;' , 0, 'L', 0, 0, 5, 122, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(70, 0, 'Toute perte de bagage par la faute de la compagnie:', 0, 'L', 0, 0, 5, 128, true);
                  $pdf->MultiCell(60, 0, '=> Lors d\'accident', 0, 'L', 0, 0, 7, 131, true);
                  $pdf->MultiCell(60, 0, '=> ouverture de coffre pendant la circulation', 0, 'L', 0, 0, 7, 134, true);
                  $pdf->MultiCell(75, 0, 'Dont la valeur n\'avait pas été déclarée à ', 0, 'L', 0, 0, 5, 137, true);
                  $pdf->MultiCell(75, 0, 'l\'enregistrement ne peut être remboursé qu\'au niveau ', 0, 'L', 0, 0, 5, 140, true);
                  $pdf->MultiCell(75, 0, 'maximum à vingt cinq mille (25 000) F CFA.', 0, 'L', 0, 0, 5, 143, true);
                  $pdf->MultiCell(75, 0, 'Les objets des valeur doivent faire l\'objet ', 0, 'L', 0, 0, 5, 147, true);
                  $pdf->MultiCell(75, 0, 'd\'une déclaration en sus de l\'enregistrement', 0, 'L', 0, 0, 5, 150, true);
                  $pdf->MultiCell(60, 0, 'avec pièces justificatives avant le départ.', 0, 'L', 0, 0, 5, 153, true);
                  /* BON VOYAGE */
                  $pdf->setFont('Helvetica', '', 6);
                  $pdf->MultiCell(40, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 5, 158, true);
                  $pdf->setFont('Helvetica', 'BI', 6);
                  $pdf->MultiCell(30, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 44, 158, true);
                  // add a page
                  $pdf->AddPage('P', 'A5');
                  // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                  
                  /* LOGO*/
                  
            
                  // GROUPE DE HAUT
                  $pdf->SetFont('helvetica', 'B', 5.5);
                  /* SIEGE SOCIAL ET ADRESSE */
                  $pdf->MultiCell(80, 0, '' . $reponse->adresse . ' Tel: ' . $reponse->contact_comp . ' / ' . 'Email: ' . $reponse->adresse, 0, 'L', 0, 0, 5, 30, true);
                  $pdf->setFont('Helvetica', 'BI', 5);
                  $pdf->MultiCell(60, 0, $codb, 0, 'L', 0, 0, 5, 37, true);
                  $pdf->setFont('courier', 'BI', 7);
                  $pdf->MultiCell(125, 0, $reponse->tamponcod, 0, 'L', 0, 0, 15, 37, true);
                  $pdf->MultiCell(60, 0, 'émis:'. $reponse->date_emis, 0, 'L', 0, 0, 50, 37, true);
                  /* AXE */
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(80, 0, '' . $reponse->nom_ligne.'  '.$reponse->quart, 0, 'C', 0, 0, 5, 44, true);
                  $pdf->SetFont('helvetica', '', 10);
                  /* TELEPHONE ET PRIX*/
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 51, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 51, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 49, 51, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(20, 0, number_format($reponse->prixvente+$reponse->prixvente, 0, '', ' '), 0, 'L', 0, 0, 60, 51, true);
                  
                  /* CODE*/
                  $pdf->MultiCell(18, 0, 'RETOUR:', 0, 'L', 0, 0, 5, 58, true);
                  $pdf->SetFont('helvetica', 'BI', 10);
                  $pdf->MultiCell(60, 0, '' . $fiche->codeticket, 0, 'L', 0, 0, 22, 58, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'L', 0, 0, 55, 58, true);
                
                  /* DATE ET HEURE ET PRIX */
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(30, 0, 'DATE', 0, 'L', 0, 0, 28, 65, true);
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(20, 0, 'HEURE:', 0, 'L', 0, 0, 57, 65, true);
                  /* NOM */
                  $pdf->SetFont('helvetica', '', 10);
                  $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 72, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 72, true);
                  
                  
                  /* PRENOMS */
                  $pdf->setFont('Helvetica', '', 10);
                  $pdf->MultiCell(25, 0, 'PRÉNOMS:', 0, 'L', 0, 0, 5, 79, true);
                  $pdf->SetFont('helvetica', 'B', 10);
                  $pdf->MultiCell(50, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 79, true);
                  
                  /* CONVOCATION */
                  $pdf->setFont('Helvetica', 'B', 9.5);
                  $pdf->MultiCell(75, 0, 'CONVOCATION 45 minutes avant le départ.', 0, 'L', 0, 0, 5, 86, true);
                  /* BILLET NON REMBOURSABLE */
                  $pdf->setFont('Helvetica', 'I', 7);
                  $pdf->MultiCell(37, 0, 'Billet valable 1 mois', 0, 'L', 0, 0, 5, 93, true);
                  $pdf->MultiCell(43, 0, 'Billet non remboursable.', 0, 'L', 0, 0, 36, 93, true);
                  /* DECLINAISON DE PERTE */
                  $pdf->setFont('Helvetica', 'BI', 7);
                  $pdf->MultiCell(75, 0, $reponse->nom_compagnie . ' décline toute responsabilité en cas de perte', 0, 'L', 0, 0, 5, 96, true);
                  $pdf->setFont('Helvetica', 'BI', 8);
                  $pdf->MultiCell(75, 0, 'ou de vol de billet et de bagages même payés.', 0, 'C', 0, 0, 5, 100, true);
                  /* MISE EN GARE ET RAPPEL */
                  $pdf->setFont('Helvetica', 'B', 7);
                  $pdf->MultiCell(70, 0, 'Suivez et surveillez bien vos bagages', 0, 'C', 0, 0, 5, 107, true);
                  /* NOTE D'INFORMATION*/
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(100, 0, 'Chers clients,nous vous souhaitons la bienvenue à '.$reponse->nom_entreprise.' transport.', 0, 'L', 0, 0, 5, 112, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(115, 0, 'Pour permettre à '.$reponse->nom_entreprise. ' transport de bien vous servir tout en respectant les horaires, nous vous prions de bien vouloir observer les conditions de voyages ci-dessous:', 0, 'L', 0, 0, 5, 115, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(90, 0, 'Conformément aux conditions de transport, '.$reponse->nom_entreprise. ' transport ne peut en aucun cas etre engagé pour la perte de vos bagages;' , 0, 'L', 0, 0, 5, 122, true);
                  $pdf->setFont('courier', '', 6);
                  $pdf->MultiCell(70, 0, 'Toute perte de bagage par la faute de la compagnie:', 0, 'L', 0, 0, 5, 128, true);
                  $pdf->MultiCell(60, 0, '=> Lors d\'accident', 0, 'L', 0, 0, 7, 131, true);
                  $pdf->MultiCell(60, 0, '=> ouverture de coffre pendant la circulation', 0, 'L', 0, 0, 7, 134, true);
                  $pdf->MultiCell(75, 0, 'Dont la valeur n\'avait pas été déclarée à ', 0, 'L', 0, 0, 5, 137, true);
                  $pdf->MultiCell(75, 0, 'l\'enregistrement ne peut être remboursé qu\'au niveau ', 0, 'L', 0, 0, 5, 140, true);
                  $pdf->MultiCell(75, 0, 'maximum à vingt cinq mille (25 000) F CFA.', 0, 'L', 0, 0, 5, 143, true);
                  $pdf->MultiCell(75, 0, 'Les objets des valeur doivent faire l\'objet ', 0, 'L', 0, 0, 5, 147, true);
                  $pdf->MultiCell(75, 0, 'd\'une déclaration en sus de l\'enregistrement', 0, 'L', 0, 0, 5, 150, true);
                  $pdf->MultiCell(60, 0, 'avec pièces justificatives avant le départ.', 0, 'L', 0, 0, 5, 153, true);
                  /* BON VOYAGE */
                  $pdf->setFont('Helvetica', '', 6);
                  $pdf->MultiCell(40, 0, "BON VOYAGE AVEC", 0, 'R', 0, 0, 5, 158, true);
                  $pdf->setFont('Helvetica', 'BI', 6);
                  $pdf->MultiCell(30, 0, "{$reponse->nom_compagnie}", 0, 'L', 0, 0, 44, 158, true);
                  // add a page
                  $pdf->AddPage('P', 'A5');
                  /* SOUCHE DE CONTROL */
                  $pdf->setFont('Helvetica', 'B', 10);
                  $pdf->MultiCell(60, 0, 'SOUCHE DE CONTROL', 0, 'C', 0, 0, 5, 5, true);
                  $pdf->setFont('Helvetica', '', 10);
                  /**CODE */
                  $pdf->MultiCell(15, 0, 'CODE:', 0, 'L', 0, 0, 5, 17, true);
                  $pdf->MultiCell(50, 0, $reponse->code_ticket, 0, 'L', 0, 0, 20, 17, true);
                  /**DIRECTION */
                  $pdf->MultiCell(40, 0, $reponse->nom_ligne, 0, 'C', 0, 0, 2, 25, true);
                  /**NOM ET SIEGE */
                  $pdf->MultiCell(15, 0, 'NOM:', 0, 'L', 0, 0, 5, 33, true);
                  $pdf->MultiCell(50, 0, $reponse->nom_client, 0, 'L', 0, 0, 15, 33, true);
                  $pdf->MultiCell(15, 0, 'SIEGE:', 0, 'C', 0, 0, 40, 33, true);
                  $pdf->MultiCell(20, 0, str_pad($reponse->num_siege_categorie, 2, "0", STR_PAD_LEFT), 0, 'C', 0, 0, 45, 33, true);
                  /**PRENOM */
                  $pdf->MultiCell(25, 0, 'PRENOMS:', 0, 'L', 0, 0, 5, 41, true);
                  $pdf->MultiCell(30, 0, $reponse->prenom_client, 0, 'L', 0, 0, 25, 41, true);
                  /**TELEPHONE ET PRIX */
                  $pdf->MultiCell(15, 0, 'TEL:', 0, 'L', 0, 0, 5, 49, true);
                  $pdf->MultiCell(35, 0, $reponse->contact_client, 0, 'L', 0, 0, 15, 49, true);
                  $pdf->MultiCell(15, 0, 'PRIX:', 0, 'L', 0, 0, 43, 49, true);
                  $pdf->MultiCell(20, 0, number_format($reponse->prixvente+$reponse->prixvente, 0, '', ' '), 0, 'C', 0, 0, 50, 49, true);
                  /**DATE ET HEURE */
                  $pdf->MultiCell(50, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 56, true);
                  $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 27, 56, true);
                  $pdf->MultiCell(17, 0, 'HEURE:', 0, 'L', 0, 0, 43, 56, true);
                  $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 55, 56, true);
                  $pdf->MultiCell(30, 0, 'ALLER_RETOUR', 0, 'C', 0, 0, 5, 63, true);
                  $pdf->MultiCell(16, 0, 'ALLER', 0, 'L', 0, 0, 5, 70, true);
                  $pdf->MultiCell(30, 0, $reponse->code_ticket, 0, 'L', 0, 0, 25, 70, true);
                  $pdf->MultiCell(18, 0, 'RETOUR', 0, 'L', 0, 0, 5, 77, true);
                  $pdf->MultiCell(30, 0, $fiche->codeticket, 0, 'L', 0, 0, 25, 77, true);
                 // Clean any content of the output buffer
                ob_end_clean();
                //Close and output PDF document
                
                $pdf->Output($fiche->code_non_pass . '', 'I');
                //============================================================+
                // END OF FILEgit
                //============================================================+
            }

        }
          
    }
    
    /** End of file: Ticketfidelite.php **/
    /** File location: application/controllers/Ticketfidelite.php **/
