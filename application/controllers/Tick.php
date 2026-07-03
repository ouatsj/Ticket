<?php defined('BASEPATH') OR exit('No direct script access allowed');
        require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');
      class Tick extends CI_Controller
      {
            public $property = array('title' => 'TICKETS');
            public $entreprise;
              
            public function __construct()
            {
                  parent::__construct();
                  $this->property['update_success'] = FALSE;
                  $this->property['INSERT'] = FALSE;
                  $this->property['pagetitle'] = utf8_encode(strftime("%d %b %G", now()));
            }
            
            public function printpdfepson($ckey, $code_id, $tf, $h, $g, $cpus, $idsg)
            {
                  $this->entreprise = $this->m_entreprises->get_key($ckey);
                  if ($this->session->agent->userole === '1' OR $this->session->agent->userole === '2'){
                        $reponse = $this->m_passager->get($this->entreprise->ekey, $code_id, $tf, $h);
                  }
                  else
                  {
                        $reponse = $this->m_passager->get($this->entreprise->ekey, $code_id, $tf, $h);
                  }
                  $ressougare = $this->m_gare_depart->getgar($this->entreprise->id_entreprise, $reponse->code_gaexp, $reponse->departclient_idgare, $reponse->ident_ligne);
                  if($ressougare->possitiongare === 'Maintenant'){

                        $g = explode(":", $reponse->heure);
                        $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                        $heur = ($gt / 60); 
                        $secondes = round($gt % 60);
                        $heures = sprintf("%02d:%02d", $heur, $secondes);

                  $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    
                        $nom = $reponse->code_progr;
                        $nge = substr($nom, 6, 6);

                        $dat = explode("-", $reponse->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
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
                        $pdf->MultiCell(20, 0, number_format($reponse->prix, 0, '', ' '), 0, 'L', 0, 0, 60, 51, true);
                        
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
                        $pdf->MultiCell(20, 0, number_format($reponse->prix, 0, '', ' '), 0, 'C', 0, 0, 50, 49, true);
                        /**DATE ET HEURE */
                        $pdf->MultiCell(40, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 56, true);
                        $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 25, 56, true);
                        $pdf->MultiCell(17, 0, 'HEURE:', 0, 'L', 0, 0, 43, 56, true);
                        $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 55, 56, true);
                        
                        // Clean any content of the output buffer
                        ob_end_clean();
                        //Close and output PDF document
                        
                        //$pdf->Output('Tick.php'. '', 'F');
                        $pdf->Output($_SERVER['DOCUMENT_ROOT'] . 'outpu.pdf', 'F');
                        //============================================================+
                        // END OF FILE
                        //============================================================+
                  }

                  if($ressougare->possitiongare === 'Avant'){
                        $g = explode(":", $reponse->heure);
                        $gt = (($g[0] * 60) + $g[1] - $ressougare->minutetemps); 
                        $heur = ($gt / 60); 
                        $secondes = round(($gt % 60));
                              
                        $heures = sprintf("%02d:%02d", $heur, $secondes);

                        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
                        $nom = $reponse->code_progr;
                        $nge = substr($nom, 6, 6);

                        $dat = explode("-", $reponse->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
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
                        $pdf->MultiCell(20, 0, number_format($reponse->prix, 0, '', ' '), 0, 'L', 0, 0, 60, 51, true);
                        
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
                        $pdf->MultiCell(20, 0, number_format($reponse->prix, 0, '', ' '), 0, 'C', 0, 0, 50, 49, true);
                        /**DATE ET HEURE */
                        $pdf->MultiCell(40, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 56, true);
                        $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 17, 56, true);
                        $pdf->MultiCell(17, 0, 'HEURE:', 0, 'L', 0, 0, 43, 56, true);
                        $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 55, 56, true);
                        
                        // Clean any content of the output buffer
                        ob_end_clean();
                        //Close and output PDF document
                        $pdf->Output($_SERVER['DOCUMENT_ROOT'] . 'output.pdf', 'F');
                        //$pdf->Output('Tick.php'. '', 'F');
                        //============================================================+
                        // END OF FILE
                        //============================================================+
                  } 
                  if($ressougare->possitiongare === 'Apres'){
                        $g = explode(":", $reponse->heure);
                        $gt = (($g[0] * 60) + $g[1] + $ressougare->minutetemps); 
                        $heur = ($gt / 60); 
                        $secondes = round($gt % 60);
                        $heures = sprintf("%02d:%02d", $heur, $secondes);      
                        
                        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                    
                        $nom = $reponse->code_progr;
                        $nge = substr($nom, 6, 6);

                        $dat = explode("-", $reponse->date_progr);
                        $day = $dat[2]. '-'. $dat[1]. '-' .$dat[0];
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
                        $pdf->MultiCell(20, 0, number_format($reponse->prix, 0, '', ' '), 0, 'L', 0, 0, 60, 51, true);
                        
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
                        $pdf->MultiCell(20, 0, number_format($reponse->prix, 0, '', ' '), 0, 'C', 0, 0, 50, 49, true);
                        /**DATE ET HEURE */
                        $pdf->MultiCell(40, 0, '' .$ressougare->nomsousgare.':', 0, 'L', 0, 0, 5, 56, true);
                        $pdf->MultiCell(30, 0, $day, 0, 'L', 0, 0, 17, 56, true);
                        $pdf->MultiCell(17, 0, 'HEURE:', 0, 'L', 0, 0, 43, 56, true);
                        $pdf->MultiCell(15, 0, $heures, 0, 'C', 0, 0, 55, 56, true);
                        
                        // Clean any content of the output buffer
                        ob_end_clean();
                        //Close and output PDF document
                        $pdf->Output($_SERVER['DOCUMENT_ROOT'] . 'output.pdf', 'F');
                        //$pdf->Output('Tick.php'. '', 'F');
                        //============================================================+
                        // END OF FILE
                        //============================================================+
                  } 
            }
            
      }